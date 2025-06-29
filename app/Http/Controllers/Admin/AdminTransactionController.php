<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Payment;
use App\Models\Order;
use App\Models\ResellerCommission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminTransactionController extends Controller
{
    /**
     * Display transaction monitoring dashboard
     */
    public function index(Request $request): View
    {
        $query = WalletTransaction::with(['user', 'relatedOrder']);

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by reference or description
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $transactions = $query->latest()->paginate(20);

        // Get filter options
        $users = User::select('id', 'name', 'email')->get();
        $types = WalletTransaction::distinct()->pluck('type');
        $statuses = WalletTransaction::distinct()->pluck('status');

        // Statistics
        $stats = [
            'total_transactions' => WalletTransaction::count(),
            'total_volume' => WalletTransaction::where('status', 'completed')->sum('amount'),
            'pending_transactions' => WalletTransaction::where('status', 'pending')->count(),
            'failed_transactions' => WalletTransaction::where('status', 'failed')->count(),
            'today_transactions' => WalletTransaction::whereDate('created_at', today())->count(),
            'today_volume' => WalletTransaction::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'users', 'types', 'statuses', 'stats'));
    }

    /**
     * Display all payment transactions
     */
    public function payments(Request $request): View
    {
        $query = Payment::with(['user', 'order']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by gateway
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $payments = $query->latest()->paginate(20);

        // Get filter options
        $users = User::select('id', 'name', 'email')->get();
        $types = Payment::distinct()->pluck('type');
        $statuses = Payment::distinct()->pluck('status');
        $gateways = Payment::distinct()->pluck('gateway');

        return view('admin.transactions.payments', compact('payments', 'users', 'types', 'statuses', 'gateways'));
    }

    /**
     * Display reseller commissions
     */
    public function commissions(Request $request): View
    {
        $query = ResellerCommission::with(['reseller', 'vendor', 'order', 'product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by reseller
        if ($request->filled('reseller_id')) {
            $query->where('reseller_id', $request->reseller_id);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('earned_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('earned_at', '<=', $request->date_to);
        }

        $commissions = $query->latest('earned_at')->paginate(20);

        // Get filter options
        $resellers = User::where('role', 'buyer')->select('id', 'name', 'email')->get();
        $vendors = User::where('role', 'vendor')->select('id', 'name', 'email')->get();
        $statuses = ResellerCommission::distinct()->pluck('status');

        // Statistics
        $stats = [
            'total_commissions' => ResellerCommission::count(),
            'total_amount' => ResellerCommission::sum('commission_amount'),
            'pending_commissions' => ResellerCommission::where('status', 'pending')->count(),
            'pending_amount' => ResellerCommission::where('status', 'pending')->sum('commission_amount'),
            'paid_commissions' => ResellerCommission::where('status', 'paid')->count(),
            'paid_amount' => ResellerCommission::where('status', 'paid')->sum('commission_amount'),
        ];

        return view('admin.transactions.commissions', compact('commissions', 'resellers', 'vendors', 'statuses', 'stats'));
    }

    /**
     * Show transaction details
     */
    public function show(WalletTransaction $transaction): View
    {
        $transaction->load(['user', 'relatedOrder', 'relatedUser']);
        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Get transaction analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        // Transaction volume over time
        $volumeData = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $volume = WalletTransaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('amount');
            
            $volumeData[] = [
                'date' => $date->format('Y-m-d'),
                'volume' => $volume
            ];
        }

        // Transaction count over time
        $countData = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = WalletTransaction::whereDate('created_at', $date)->count();
            
            $countData[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }

        // Transaction type distribution
        $typeDistribution = WalletTransaction::selectRaw('type, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('type')
            ->get();

        // Top users by transaction volume
        $topUsers = WalletTransaction::with('user')
            ->selectRaw('user_id, COUNT(*) as transaction_count, SUM(amount) as total_amount')
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->orderByDesc('total_amount')
            ->take(10)
            ->get();

        // Monthly revenue breakdown
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            
            $walletRevenue = WalletTransaction::where('type', 'funding')
                ->where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
                
            $membershipRevenue = Payment::where('type', 'membership')
                ->where('status', 'success')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
                
            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),
                'wallet_revenue' => $walletRevenue,
                'membership_revenue' => $membershipRevenue,
                'total_revenue' => $walletRevenue + $membershipRevenue
            ];
        }

        return response()->json([
            'volume_data' => $volumeData,
            'count_data' => $countData,
            'type_distribution' => $typeDistribution,
            'top_users' => $topUsers,
            'monthly_revenue' => $monthlyRevenue,
        ]);
    }

    /**
     * Export transactions
     */
    public function export(Request $request)
    {
        $query = WalletTransaction::with(['user', 'relatedOrder']);

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->get();

        $filename = 'transactions_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User Name', 'User Email', 'Type', 'Amount', 'Balance After',
                'Status', 'Reference', 'Description', 'Created At'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->user->name,
                    $transaction->user->email,
                    $transaction->type,
                    $transaction->amount,
                    $transaction->balance_after,
                    $transaction->status,
                    $transaction->reference,
                    $transaction->description,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
