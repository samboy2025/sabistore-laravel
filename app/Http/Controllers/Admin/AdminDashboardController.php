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
use Illuminate\Support\Collection;

/**
 * Class AdminDashboardController
 *
 * Handles the display of the main admin dashboard and analytics pages.
 *
 * @package App\Http\Controllers\Admin
 */
class AdminDashboardController extends Controller
{
    /**
     * Display the main admin dashboard.
     *
     * Gathers various statistics, recent activities, and revenue data to be displayed on the dashboard.
     *
     * @return View Returns the view for the admin dashboard.
     */
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

    /**
     * Display the analytics page.
     *
     * Gathers advanced analytics data for display on the analytics page.
     *
     * @return View Returns the view for the analytics page.
     */
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

    /**
     * Get user growth data for the last 12 months.
     *
     * @return array An array of user growth data, with each element containing the month and user count.
     */
    private function getUserGrowthData(): array
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

    /**
     * Get a breakdown of revenue by payment type.
     *
     * @return array An array containing the total revenue for memberships and products.
     */
    private function getRevenueBreakdown(): array
    {
        return [
            'membership' => Payment::where('type', 'membership')->where('status', 'success')->sum('amount'),
            'products' => Payment::where('type', 'product')->where('status', 'success')->sum('amount'),
        ];
    }

    /**
     * Get the top 10 vendors based on the number of orders.
     *
     * @return Collection A collection of the top vendors.
     */
    private function getTopVendors(): Collection
    {
        return Shop::withCount(['products', 'orders'])
            ->with('vendor', 'badge')
            ->orderBy('orders_count', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get a count of products in each category (type).
     *
     * @return Collection A collection of product categories with their respective counts.
     */
    private function getProductCategories(): Collection
    {
        return Product::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
    }

    /**
     * Calculate the signup and conversion rates.
     *
     * Note: Total visitors are currently hardcoded and should be replaced with a real analytics source.
     *
     * @return array An array containing the signup and conversion rates.
     */
    private function getConversionRate(): array
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