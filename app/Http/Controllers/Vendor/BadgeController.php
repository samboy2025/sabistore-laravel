<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * Display vendor's badge status and progress
     */
    public function index()
    {
        $vendor = auth()->user();
        $shop = $vendor->shop;

        if (!$shop) {
            return redirect()->route('vendor.shop.setup')
                ->with('warning', 'Please complete your shop setup first.');
        }

        // Get all badges ordered by requirements
        $badges = Badge::orderBy('min_products')->orderBy('min_orders')->get();

        // Calculate vendor's current stats
        $stats = [
            'products_count' => $shop->products()->count(),
            'orders_count' => $shop->orders()->where('status', 'completed')->count(),
            'followers_count' => $vendor->followers()->count(),
            'reviews_count' => 0, // Will implement reviews later
        ];

        // Get current badge
        $currentBadge = $shop->badge;

        // Find next badge to achieve
        $nextBadge = Badge::where('min_products', '>', $stats['products_count'])
                         ->orWhere('min_orders', '>', $stats['orders_count'])
                         ->orderBy('min_products')
                         ->orderBy('min_orders')
                         ->first();

        // Calculate progress towards next badge
        $progress = [];
        if ($nextBadge) {
            $progress = [
                'products' => [
                    'current' => $stats['products_count'],
                    'required' => $nextBadge->min_products,
                    'percentage' => $nextBadge->min_products > 0 ? 
                        min(100, ($stats['products_count'] / $nextBadge->min_products) * 100) : 100
                ],
                'orders' => [
                    'current' => $stats['orders_count'],
                    'required' => $nextBadge->min_orders,
                    'percentage' => $nextBadge->min_orders > 0 ? 
                        min(100, ($stats['orders_count'] / $nextBadge->min_orders) * 100) : 100
                ]
            ];
        }

        return view('vendor.badge.index', compact(
            'badges', 
            'currentBadge', 
            'nextBadge', 
            'stats', 
            'progress'
        ));
    }
}