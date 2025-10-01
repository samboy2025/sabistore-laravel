<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Course;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class HomeController
 *
 * Handles the display of the main homepage, gathering all necessary data
 * for featured vendors, latest products, platform statistics, and more.
 *
 * @package App\Http\Controllers\Public
 */
class HomeController extends Controller
{
    /**
     * Display the application's homepage.
     *
     * Gathers data for featured vendors, latest products, platform statistics,
     * featured courses, and top vendors to be displayed on the welcome page.
     *
     * @return View Returns the view for the homepage.
     */
    public function index(): View
    {
        // Featured vendors (active shops with good ratings/badge)
        $featuredVendors = Shop::active()
            ->completed()
            ->with(['badge', 'vendor'])
            ->withCount(['products', 'orders'])
            ->orderByDesc('badge_id')
            ->orderByDesc('products_count')
            ->take(8)
            ->get();

        // Latest products from all vendors
        $latestProducts = Product::active()
            ->with(['shop', 'shop.badge'])
            ->whereHas('shop', function($query) {
                $query->active()->completed();
            })
            ->latest()
            ->take(12)
            ->get();

        // Platform statistics
        $stats = [
            'total_vendors' => Shop::active()->completed()->count(),
            'total_products' => Product::active()
                ->whereHas('shop', fn($q) => $q->active()->completed())
                ->count(),
            'total_orders' => \App\Models\Order::count(),
            'active_badges' => Badge::active()->count(),
        ];

        // Featured courses for learning center
        $featuredCourses = Course::active()
            ->featured()
            ->ordered()
            ->take(6)
            ->get();

        // Top vendors by badge level
        $topVendors = Shop::active()
            ->completed()
            ->with(['badge', 'vendor'])
            ->whereNotNull('badge_id')
            ->orderByDesc('badge_id')
            ->take(4)
            ->get();

        return view('welcome', compact(
            'featuredVendors',
            'latestProducts',
            'stats',
            'featuredCourses',
            'topVendors'
        ));
    }
}