<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    private $paystackSecretKey;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:vendor');
        $this->paystackSecretKey = env('PAYSTACK_SECRET_KEY', 'sk_test_de5caee37f77fba3c2db5bc87461c55de7d36d8f');
    }

    /**
     * Display the vendor wallet dashboard
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Check membership status first
        if (!$user->membership_active || !$user->membership_paid_at) {
            return redirect()->route('membership.payment')
                ->with('warning', 'Please complete your membership payment to access wallet features.');
        }
        
        $wallet = $user->getOrCreateWallet();
        
        // Get recent transactions
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(15);

        // Get summary statistics
        $stats = [
            'total_funded' => $user->walletTransactions()
                ->where('type', 'funding')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_spent' => abs($user->walletTransactions()
                ->where('type', 'purchase')
                ->where('status', 'completed')
                ->sum('amount')),
            'total_earned' => $user->walletTransactions()
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->sum('amount'),
            'commission_paid' => abs($user->walletTransactions()
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->where('amount', '<', 0) // Negative amounts are commissions paid out
                ->sum('amount')),
            'pending_transactions' => $user->walletTransactions()
                ->where('status', 'pending')
                ->count(),
        ];

        // Get monthly wallet activity for chart
        $monthlyData = $this->getMonthlyWalletData($user);

        return view('vendor.wallet.index', compact('wallet', 'transactions', 'stats', 'monthlyData'));
    }

    /**
     * Initiate wallet funding
     */
    public function fund(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|max:1000000', // ₦500 to ₦1M
        ]);

        $user = Auth::user();
        $amount = $request->amount * 100; // Convert to kobo
        $reference = 'wallet_' . Str::random(10) . '_' . time();

        try {
            // Initialize Paystack transaction
            $response = Http::withToken($this->paystackSecretKey)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'amount' => $amount,
                    'email' => $user->email,
                    'reference' => $reference,
                    'callback_url' => route('vendor.wallet.callback'),
                    'metadata' => [
                        'user_id' => $user->id,
                        'purpose' => 'wallet_funding',
                        'original_amount' => $request->amount
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Create pending transaction record
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'funding',
                    'amount' => $request->amount,
                    'balance_after' => $user->wallet_balance, // Current balance
                    'reference' => $reference,
                    'description' => "Wallet funding of ₦" . number_format($request->amount, 2),
                    'status' => 'pending',
                    'metadata' => $data
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'authorization_url' => $data['data']['authorization_url'],
                        'reference' => $reference
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize payment'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Paystack callback
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('vendor.wallet.index')
                ->with('error', 'Invalid payment reference.');
        }

        try {
            // Verify transaction with Paystack
            $response = Http::withToken($this->paystackSecretKey)
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['data']['status'] === 'success') {
                    // Find the pending transaction
                    $transaction = WalletTransaction::where('reference', $reference)
                        ->where('status', 'pending')
                        ->first();

                    if ($transaction) {
                        $user = $transaction->user;
                        $wallet = $user->getOrCreateWallet();
                        
                        // Credit wallet and update transaction
                        $wallet->credit(
                            $transaction->amount,
                            'funding',
                            "Wallet funding completed - {$reference}",
                            $reference
                        );

                        // Update the original pending transaction
                        $transaction->update([
                            'status' => 'completed',
                            'balance_after' => $wallet->fresh()->balance
                        ]);

                        // Process any pending commissions
                        $commissionService = app(\App\Services\CommissionService::class);
                        $pendingCommissionsProcessed = $commissionService->processPendingCommissions($user);

                        $message = 'Wallet funded successfully! ₦' . number_format($transaction->amount, 2) . ' has been added to your wallet.';

                        if ($pendingCommissionsProcessed['processed'] > 0) {
                            $message .= ' ' . $pendingCommissionsProcessed['processed'] . ' pending commission(s) have been processed.';
                        }

                        return redirect()->route('vendor.wallet.index')
                            ->with('success', $message);
                    }
                }
            }

            return redirect()->route('vendor.wallet.index')
                ->with('error', 'Payment verification failed. Please contact support if money was deducted.');

        } catch (\Exception $e) {
            return redirect()->route('vendor.wallet.index')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly wallet data for charts
     */
    private function getMonthlyWalletData($user): array
    {
        $months = [];
        $funding = [];
        $spending = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $monthlyFunding = $user->walletTransactions()
                ->where('type', 'funding')
                ->where('status', 'completed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
                
            $monthlySpending = abs($user->walletTransactions()
                ->whereIn('type', ['purchase', 'commission'])
                ->where('status', 'completed')
                ->where('amount', '<', 0)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount'));
            
            $funding[] = $monthlyFunding;
            $spending[] = $monthlySpending;
        }
        
        return [
            'months' => $months,
            'funding' => $funding,
            'spending' => $spending
        ];
    }
}
