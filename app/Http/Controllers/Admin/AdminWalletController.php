<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminWalletController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display wallet management dashboard
     */
    public function index(Request $request): View
    {
        // Wallet statistics
        $stats = [
            'total_wallets' => Wallet::count(),
            'total_balance' => Wallet::sum('balance'),
            'total_transactions' => WalletTransaction::count(),
            'pending_transactions' => WalletTransaction::where('status', 'pending')->count(),
            'today_transactions' => WalletTransaction::whereDate('created_at', today())->count(),
            'today_volume' => WalletTransaction::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        // Recent transactions
        $recentTransactions = WalletTransaction::with(['user', 'relatedOrder'])
            ->latest()
            ->take(10)
            ->get();

        // Top wallet holders
        $topWallets = Wallet::with('user')
            ->orderByDesc('balance')
            ->take(10)
            ->get();

        // Transaction types summary
        $transactionTypes = WalletTransaction::select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->where('status', 'completed')
            ->groupBy('type')
            ->get();

        return view('admin.wallets.index', compact('stats', 'recentTransactions', 'topWallets', 'transactionTypes'));
    }

    /**
     * Display user wallets with search and filters
     */
    public function users(Request $request): View
    {
        $query = User::with('wallet')->whereNotNull('id');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by balance range
        if ($request->filled('min_balance')) {
            $query->whereHas('wallet', function($q) use ($request) {
                $q->where('balance', '>=', $request->min_balance);
            });
        }

        if ($request->filled('max_balance')) {
            $query->whereHas('wallet', function($q) use ($request) {
                $q->where('balance', '<=', $request->max_balance);
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.wallets.users', compact('users'));
    }

    /**
     * Show specific user's wallet details
     */
    public function show(User $user): View
    {
        $wallet = $user->getOrCreateWallet();
        
        // User's transaction history
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(20);

        // User's wallet stats
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
            'admin_adjustments' => $user->walletTransactions()
                ->where('type', 'admin_adjustment')
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('admin.wallets.show', compact('user', 'wallet', 'transactions', 'stats'));
    }

    /**
     * Adjust user's wallet balance
     */
    public function adjust(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:credit,debit',
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $wallet = $user->getOrCreateWallet();
                $amount = abs($request->amount);
                $isCredit = $request->type === 'credit';

                if (!$isCredit && $wallet->balance < $amount) {
                    throw new \Exception('Insufficient wallet balance for debit adjustment');
                }

                // Perform the adjustment
                if ($isCredit) {
                    $wallet->credit(
                        $amount,
                        'admin_adjustment',
                        "Admin credit: {$request->description}",
                        'admin_' . time(),
                        null
                    );
                } else {
                    $wallet->debit(
                        $amount,
                        'admin_adjustment',
                        "Admin debit: {$request->description}",
                        'admin_' . time(),
                        null
                    );
                }

                // Log the admin action
                \Log::info("Admin wallet adjustment", [
                    'admin_id' => auth()->id(),
                    'user_id' => $user->id,
                    'type' => $request->type,
                    'amount' => $amount,
                    'description' => $request->description,
                    'new_balance' => $wallet->fresh()->balance
                ]);
            });

            return redirect()->back()->with('success', 
                "Wallet {$request->type} of ₦" . number_format(abs($request->amount), 2) . " applied successfully."
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk wallet adjustments
     */
    public function bulkAdjust(Request $request): RedirectResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string|max:255',
            'type' => 'required|in:credit,debit',
        ]);

        try {
            $successCount = 0;
            $errors = [];

            DB::transaction(function () use ($request, &$successCount, &$errors) {
                foreach ($request->user_ids as $userId) {
                    try {
                        $user = User::findOrFail($userId);
                        $wallet = $user->getOrCreateWallet();
                        $amount = abs($request->amount);
                        $isCredit = $request->type === 'credit';

                        if (!$isCredit && $wallet->balance < $amount) {
                            $errors[] = "User {$user->name}: Insufficient balance";
                            continue;
                        }

                        if ($isCredit) {
                            $wallet->credit(
                                $amount,
                                'admin_adjustment',
                                "Bulk admin credit: {$request->description}",
                                'bulk_admin_' . time(),
                                null
                            );
                        } else {
                            $wallet->debit(
                                $amount,
                                'admin_adjustment',
                                "Bulk admin debit: {$request->description}",
                                'bulk_admin_' . time(),
                                null
                            );
                        }

                        $successCount++;

                    } catch (\Exception $e) {
                        $errors[] = "User {$userId}: {$e->getMessage()}";
                    }
                }
            });

            $message = "Bulk adjustment completed. {$successCount} users processed successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Bulk adjustment failed: ' . $e->getMessage());
        }
    }

    /**
     * Export wallet transactions
     */
    public function export(Request $request)
    {
        $query = WalletTransaction::with(['user']);

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'wallet_transactions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User', 'Email', 'Type', 'Amount', 'Balance After', 
                'Description', 'Status', 'Reference', 'Created At'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->user->name,
                    $transaction->user->email,
                    $transaction->type,
                    $transaction->amount,
                    $transaction->balance_after,
                    $transaction->description,
                    $transaction->status,
                    $transaction->reference,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
