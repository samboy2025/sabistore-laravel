<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the wallet dashboard
     */
    public function index(): View
    {
        $user = auth()->user();
        $wallet = $user->getOrCreateWallet();
        
        // Get recent transactions
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(20);

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
            'pending_transactions' => $user->walletTransactions()
                ->where('status', 'pending')
                ->count(),
        ];

        return view('wallet.index', compact('wallet', 'transactions', 'stats'));
    }

    /**
     * Initiate wallet funding
     */
    public function fund(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|max:1000000', // ₦500 to ₦1M
        ]);

        // This will redirect to Paystack payment page
        // The actual processing is handled by the API controller
        return redirect()->route('api.wallet.fund')
            ->with('fund_amount', $request->amount);
    }
}
