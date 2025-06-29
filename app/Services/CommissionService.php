<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Process reseller commission for a completed order
     */
    public function processResellerCommission(Order $order): array
    {
        // Only process if order has reseller and commission hasn't been paid
        if (!$order->reseller_id || $order->commission_paid || $order->reseller_commission <= 0) {
            return [
                'success' => false,
                'message' => 'No commission to process'
            ];
        }

        // Only process for completed/delivered orders
        if (!in_array($order->status, ['completed', 'delivered'])) {
            return [
                'success' => false,
                'message' => 'Order not completed yet'
            ];
        }

        try {
            return DB::transaction(function () use ($order) {
                $vendor = $order->product->shop->vendor;
                $reseller = User::find($order->reseller_id);
                $commissionAmount = $order->reseller_commission;

                if (!$vendor || !$reseller) {
                    throw new \Exception('Vendor or reseller not found');
                }

                $vendorWallet = $vendor->getOrCreateWallet();
                $resellerWallet = $reseller->getOrCreateWallet();

                // Check if vendor has sufficient balance
                if (!$vendorWallet->hasSufficientBalance($commissionAmount)) {
                    // Mark as pending and notify admin
                    $this->createPendingCommissionRecord($order, $vendor, $reseller, $commissionAmount);
                    
                    return [
                        'success' => false,
                        'message' => 'Insufficient vendor wallet balance. Commission marked as pending.',
                        'pending' => true
                    ];
                }

                // Debit vendor wallet
                $vendorWallet->debit(
                    $commissionAmount,
                    'commission',
                    "Commission payment to {$reseller->name} for order #{$order->order_number}",
                    "commission_order_{$order->id}",
                    $order->id
                );

                // Credit reseller wallet
                $resellerWallet->credit(
                    $commissionAmount,
                    'commission',
                    "Commission earned from {$vendor->name} for order #{$order->order_number}",
                    "commission_order_{$order->id}",
                    $order->id
                );

                // Mark commission as paid
                $order->update(['commission_paid' => true]);

                // Update reseller link stats
                if ($order->reseller_link_id) {
                    $resellerLink = $order->resellerLink;
                    if ($resellerLink) {
                        $resellerLink->recordSale($commissionAmount);
                    }
                }

                Log::info('Commission processed successfully', [
                    'order_id' => $order->id,
                    'vendor_id' => $vendor->id,
                    'reseller_id' => $reseller->id,
                    'commission_amount' => $commissionAmount
                ]);

                return [
                    'success' => true,
                    'message' => 'Commission processed successfully',
                    'commission_amount' => $commissionAmount,
                    'vendor_balance' => $vendorWallet->fresh()->balance,
                    'reseller_balance' => $resellerWallet->fresh()->balance
                ];
            });

        } catch (\Exception $e) {
            Log::error('Commission processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Commission processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create pending commission record when vendor has insufficient balance
     */
    private function createPendingCommissionRecord(Order $order, User $vendor, User $reseller, float $commissionAmount): void
    {
        // Create pending transaction for vendor (debit)
        WalletTransaction::create([
            'user_id' => $vendor->id,
            'type' => 'commission',
            'amount' => -$commissionAmount,
            'balance_after' => $vendor->wallet_balance, // Current balance (unchanged)
            'reference' => "pending_commission_order_{$order->id}",
            'description' => "Pending commission payment to {$reseller->name} for order #{$order->order_number}",
            'status' => 'pending',
            'related_order_id' => $order->id,
            'related_user_id' => $reseller->id,
            'metadata' => [
                'reason' => 'insufficient_balance',
                'required_amount' => $commissionAmount,
                'vendor_balance' => $vendor->wallet_balance
            ]
        ]);

        // Create pending transaction for reseller (credit)
        WalletTransaction::create([
            'user_id' => $reseller->id,
            'type' => 'commission',
            'amount' => $commissionAmount,
            'balance_after' => $reseller->wallet_balance, // Current balance (unchanged)
            'reference' => "pending_commission_order_{$order->id}",
            'description' => "Pending commission from {$vendor->name} for order #{$order->order_number}",
            'status' => 'pending',
            'related_order_id' => $order->id,
            'related_user_id' => $vendor->id,
            'metadata' => [
                'reason' => 'vendor_insufficient_balance',
                'commission_amount' => $commissionAmount
            ]
        ]);

        Log::warning('Commission marked as pending due to insufficient vendor balance', [
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'reseller_id' => $reseller->id,
            'commission_amount' => $commissionAmount,
            'vendor_balance' => $vendor->wallet_balance
        ]);
    }

    /**
     * Process all pending commissions for a vendor (called when wallet is funded)
     */
    public function processPendingCommissions(User $vendor): array
    {
        $pendingCommissions = WalletTransaction::where('user_id', $vendor->id)
            ->where('type', 'commission')
            ->where('status', 'pending')
            ->where('amount', '<', 0) // Vendor debits
            ->get();

        $processed = 0;
        $failed = 0;
        $totalProcessed = 0;

        foreach ($pendingCommissions as $pendingTransaction) {
            $order = $pendingTransaction->relatedOrder;
            
            if ($order && !$order->commission_paid) {
                $result = $this->processResellerCommission($order);
                
                if ($result['success']) {
                    // Delete the pending transaction since it's now processed
                    $pendingTransaction->delete();
                    
                    // Also delete the corresponding reseller pending transaction
                    WalletTransaction::where('reference', $pendingTransaction->reference)
                        ->where('user_id', $pendingTransaction->related_user_id)
                        ->where('status', 'pending')
                        ->delete();
                    
                    $processed++;
                    $totalProcessed += abs($pendingTransaction->amount);
                } else {
                    $failed++;
                }
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total_amount' => $totalProcessed
        ];
    }

    /**
     * Calculate commission amount for an order
     */
    public function calculateCommission(Order $order): float
    {
        if (!$order->reseller_id || !$order->product->is_resellable) {
            return 0;
        }

        $commissionRate = $order->product->resell_commission_percent;
        return ($order->total_price * $commissionRate) / 100;
    }

    /**
     * Get pending commission summary for a vendor
     */
    public function getPendingCommissionSummary(User $vendor): array
    {
        $pendingCommissions = WalletTransaction::where('user_id', $vendor->id)
            ->where('type', 'commission')
            ->where('status', 'pending')
            ->where('amount', '<', 0)
            ->get();

        return [
            'count' => $pendingCommissions->count(),
            'total_amount' => abs($pendingCommissions->sum('amount')),
            'transactions' => $pendingCommissions
        ];
    }
}
