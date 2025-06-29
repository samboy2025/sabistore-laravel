<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => User::count(),
            'total_vendors' => User::where('role', 'vendor')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'active_shops' => Shop::where('is_active', true)->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_payments' => Payment::where('status', 'success')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'total_courses' => Course::count(),
        ];

        // Recent activities
        $recent_users = User::latest()->take(5)->get();
        $recent_payments = Payment::with('user')->latest()->take(5)->get();
        $recent_shops = Shop::with('vendor')->latest()->take(5)->get();

        // Monthly revenue (last 6 months)
        $monthly_revenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Payment::where('status', 'success')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            $monthly_revenue[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue
            ];
        }

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_payments', 'recent_shops', 'monthly_revenue'));
    }

    public function analytics(): View
    {
        // Advanced analytics data
        $analytics = [
            'user_growth' => $this->getUserGrowthData(),
            'revenue_breakdown' => $this->getRevenueBreakdown(),
            'top_vendors' => $this->getTopVendors(),
            'product_categories' => $this->getProductCategories(),
            'conversion_rate' => $this->getConversionRate(),
        ];

        return view('admin.analytics', compact('analytics'));
    }

    private function getUserGrowthData()
    {
        $growth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $growth[] = [
                'month' => $month->format('M Y'),
                'users' => $count
            ];
        }
        return $growth;
    }

    private function getRevenueBreakdown()
    {
        return [
            'membership' => Payment::where('type', 'membership')->where('status', 'success')->sum('amount'),
            'products' => Payment::where('type', 'product')->where('status', 'success')->sum('amount'),
        ];
    }

    private function getTopVendors()
    {
        return Shop::withCount(['products', 'orders'])
            ->with('vendor', 'badge')
            ->orderBy('orders_count', 'desc')
            ->take(10)
            ->get();
    }

    private function getProductCategories()
    {
        return Product::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
    }

    private function getConversionRate()
    {
        $total_visitors = 1000; // This would come from analytics
        $total_signups = User::count();
        $paid_members = User::where('membership_active', true)->count();
        
        return [
            'signup_rate' => $total_visitors > 0 ? ($total_signups / $total_visitors) * 100 : 0,
            'conversion_rate' => $total_signups > 0 ? ($paid_members / $total_signups) * 100 : 0,
        ];
    }
} 