<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WalletController extends Controller
{
    /**
     * Display wallet dashboard
     */
    public function index(): View
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(20);

        $stats = [
            'current_balance' => $wallet->balance,
            'total_funded' => $user->walletTransactions()
                ->where('type', 'funding')
                ->sum('amount'),
            'total_spent' => abs($user->walletTransactions()
                ->where('type', 'purchase')
                ->sum('amount')),
            'total_earned' => $user->walletTransactions()
                ->where('type', 'commission')
                ->sum('amount'),
            'this_month_transactions' => $user->walletTransactions()
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return view('buyer.wallet.index', compact('wallet', 'transactions', 'stats'));
    }

    /**
     * Show funding form
     */
    public function fund(): View
    {
        return view('buyer.wallet.fund');
    }

    /**
     * Process wallet funding
     */
    public function processFunding(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:500000',
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        // Generate payment reference
        $reference = 'FUND_' . $user->id . '_' . time();

        // Initialize Paystack payment
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => $user->email,
            'amount' => $amount * 100, // Convert to kobo
            'reference' => $reference,
            'callback_url' => route('buyer.wallet.callback'),
            'metadata' => [
                'user_id' => $user->id,
                'type' => 'wallet_funding',
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Create pending transaction record
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'funding',
                'amount' => $amount,
                'balance_after' => $user->wallet_balance, // Will be updated on success
                'reference' => $reference,
                'description' => 'Wallet funding via Paystack',
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'authorization_url' => $data['data']['authorization_url'],
                'reference' => $reference,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to initialize payment. Please try again.',
        ], 500);
    }

    /**
     * Handle Paystack callback
     */
    public function callback(Request $request): View
    {
        $reference = $request->reference;
        
        if (!$reference) {
            return view('buyer.wallet.callback', [
                'success' => false,
                'message' => 'Invalid payment reference.'
            ]);
        }

        // Verify payment with Paystack
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->successful()) {
            return view('buyer.wallet.callback', [
                'success' => false,
                'message' => 'Failed to verify payment.'
            ]);
        }

        $data = $response->json();
        
        if ($data['data']['status'] === 'success') {
            $amount = $data['data']['amount'] / 100; // Convert from kobo
            $userId = $data['data']['metadata']['user_id'];
            
            $user = \App\Models\User::find($userId);
            if ($user) {
                $wallet = $user->getOrCreateWallet();
                
                // Credit wallet
                $wallet->credit(
                    $amount,
                    'funding',
                    'Wallet funding via Paystack',
                    $reference
                );

                // Update transaction status
                WalletTransaction::where('reference', $reference)
                    ->update([
                        'status' => 'completed',
                        'balance_after' => $wallet->fresh()->balance,
                    ]);

                return view('buyer.wallet.callback', [
                    'success' => true,
                    'message' => 'Wallet funded successfully!',
                    'amount' => $amount,
                    'balance' => $wallet->fresh()->balance,
                ]);
            }
        }

        return view('buyer.wallet.callback', [
            'success' => false,
            'message' => 'Payment verification failed.'
        ]);
    }

    /**
     * Show transaction details
     */
    public function transaction(WalletTransaction $transaction): View
    {
        $user = Auth::user();

        // Verify ownership
        if ($transaction->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this transaction.');
        }

        return view('buyer.wallet.transaction', compact('transaction'));
    }

    /**
     * Export transactions
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $transactions = $user->walletTransactions()
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->from_date, function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->to_date, function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->get();

        $filename = 'wallet_transactions_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date',
                'Type',
                'Amount',
                'Balance After',
                'Description',
                'Reference',
                'Status'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    ucfirst($transaction->type),
                    $transaction->formatted_amount,
                    $transaction->formatted_balance_after,
                    $transaction->description,
                    $transaction->reference,
                    ucfirst($transaction->status)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
