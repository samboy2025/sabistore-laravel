<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Product;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ResellerLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VendorDashboardController extends Controller
{
    /**
     * Display the vendor dashboard
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Check membership status first
        if (!$user->membership_active || !$user->membership_paid_at) {
            return redirect()->route('membership.payment')
                ->with('warning', 'Please complete your membership payment to access the vendor dashboard.');
        }
        
        // Get or create vendor shop
        $shop = $user->shop;
        
        if (!$shop) {
            // Redirect to shop setup if no shop exists
            return redirect()->route('vendor.shop.setup')
                ->with('info', 'Please complete your shop setup to access the dashboard.');
        }

        // Dashboard statistics
        $stats = [
            'total_products' => $shop->products()->count(),
            'active_products' => $shop->products()->where('products.is_active', true)->count(),
            'total_orders' => $shop->orders()->count(),
            'pending_orders' => $shop->orders()->where('orders.status', 'pending')->count(),
            'completed_orders' => $shop->orders()->where('orders.status', 'delivered')->count(),
            'processing_orders' => $shop->orders()->where('orders.status', 'processing')->count(),
            'monthly_orders' => $shop->orders()
                ->whereMonth('orders.created_at', now()->month)
                ->whereYear('orders.created_at', now()->year)
                ->count(),
            'total_revenue' => $shop->orders()
                ->whereIn('orders.status', ['delivered', 'completed'])
                ->sum('orders.total_price'),
            'monthly_revenue' => $shop->orders()
                ->whereIn('orders.status', ['delivered', 'completed'])
                ->whereMonth('orders.created_at', now()->month)
                ->whereYear('orders.created_at', now()->year)
                ->sum('orders.total_price'),
            'followers_count' => $user->followers()->count(),
            'reseller_links' => $shop->resellerLinks()->count(),
            'active_reseller_links' => $shop->resellerLinks()->active()->count(),
        ];

        // Recent products
        $recent_products = $shop->products()
            ->latest()
            ->take(5)
            ->get();

        // Recent orders
        $recent_orders = $shop->orders()
            ->latest('orders.created_at')
            ->take(5)
            ->get();

        // Badge information
        $currentBadge = $shop->badge;
        $nextBadge = $this->getNextBadge($shop);
        $badgeProgress = $this->calculateBadgeProgress($shop, $nextBadge);

        // Monthly analytics for charts
        $monthlyData = $this->getMonthlyAnalytics($shop);

        // Top performing products
        $topProducts = $shop->products()
            ->withCount(['orders' => function($query) {
                $query->whereIn('orders.status', ['delivered', 'completed']);
            }])
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();

        // Recent activity feed
        $activityFeed = $this->getActivityFeed($shop);

        return view('vendor.dashboard', compact(
            'user',
            'shop',
            'stats',
            'recent_products',
            'recent_orders',
            'currentBadge',
            'nextBadge',
            'badgeProgress',
            'monthlyData',
            'topProducts',
            'activityFeed'
        ));
    }

    /**
     * Get the next badge level for the shop
     */
    private function getNextBadge(Shop $shop): ?Badge
    {
        $currentBadgeOrder = $shop->badge ? $shop->badge->order : 0;
        
        return Badge::where('order', '>', $currentBadgeOrder)
            ->orderBy('order')
            ->first();
    }

    /**
     * Calculate badge progress for the shop
     */
    private function calculateBadgeProgress(Shop $shop, ?Badge $nextBadge): array
    {
        if (!$nextBadge) {
            return [
                'products' => 100,
                'orders' => 100,
                'reviews' => 100,
                'overall' => 100,
            ];
        }

        $currentProducts = $shop->products()->count();
        $currentOrders = $shop->orders()->whereIn('orders.status', ['delivered', 'completed'])->count();
        $currentReviews = 0; // Implement when review system is added

        $productProgress = $nextBadge->min_products > 0 
            ? min(100, ($currentProducts / $nextBadge->min_products) * 100) 
            : 100;

        $orderProgress = $nextBadge->min_orders > 0 
            ? min(100, ($currentOrders / $nextBadge->min_orders) * 100) 
            : 100;

        $reviewProgress = $nextBadge->min_reviews > 0 
            ? min(100, ($currentReviews / $nextBadge->min_reviews) * 100) 
            : 100;

        $overallProgress = ($productProgress + $orderProgress + $reviewProgress) / 3;

        return [
            'products' => round($productProgress, 1),
            'orders' => round($orderProgress, 1),
            'reviews' => round($reviewProgress, 1),
            'overall' => round($overallProgress, 1),
        ];
    }

    /**
     * Get monthly analytics data for charts
     */
    private function getMonthlyAnalytics(Shop $shop): array
    {
        $months = [];
        $ordersData = [];
        $revenueData = [];

        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $monthlyOrders = $shop->orders()
                ->whereMonth('orders.created_at', $date->month)
                ->whereYear('orders.created_at', $date->year)
                ->count();
            
            $monthlyRevenue = $shop->orders()
                ->whereIn('orders.status', ['delivered', 'completed'])
                ->whereMonth('orders.created_at', $date->month)
                ->whereYear('orders.created_at', $date->year)
                ->sum('orders.total_price');
            
            $ordersData[] = $monthlyOrders;
            $revenueData[] = $monthlyRevenue;
        }

        return [
            'months' => $months,
            'orders' => $ordersData,
            'revenue' => $revenueData,
        ];
    }

    /**
     * Get recent activity feed
     */
    private function getActivityFeed(Shop $shop): array
    {
        $activities = [];

        // Recent products
        $recentProducts = $shop->products()
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentProducts as $product) {
            $activities[] = [
                'type' => 'product_created',
                'message' => "New product '{$product->title}' was added",
                'time' => $product->created_at,
                'icon' => 'plus',
                'color' => 'blue',
            ];
        }

        // Recent orders
        $recentOrders = $shop->orders()
            ->latest('orders.created_at')
            ->take(3)
            ->get();

        foreach ($recentOrders as $order) {
            $activities[] = [
                'type' => 'order_received',
                'message' => "New order #{$order->id} received",
                'time' => $order->created_at,
                'icon' => 'shopping-bag',
                'color' => 'green',
            ];
        }

        // Sort by time and take latest 10
        usort($activities, function($a, $b) {
            return $b['time']->timestamp - $a['time']->timestamp;
        });

        return array_slice($activities, 0, 10);
    }
} 