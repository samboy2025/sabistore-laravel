<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class WalletController
 *
 * Handles the user's wallet functionality, including displaying the wallet dashboard and initiating funding.
 *
 * @package App\Http\Controllers
 */
class WalletController extends Controller
{
    /**
     * WalletController constructor.
     *
     * Ensures that the user is authenticated before they can access any wallet-related pages.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the wallet dashboard.
     *
     * Retrieves and displays the user's wallet information, recent transactions, and summary statistics.
     *
     * @return View Returns the view for the wallet dashboard.
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
     * Initiate wallet funding.
     *
     * Validates the funding amount and redirects the user to the payment gateway to complete the transaction.
     *
     * @param Request $request The request object containing the funding amount.
     * @return RedirectResponse Redirects the user to the payment gateway.
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
