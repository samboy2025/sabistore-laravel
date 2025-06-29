<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorDirectoryController extends Controller
{
    /**
     * Display the vendor directory page
     */
    public function index(Request $request): View
    {
        $query = Shop::query()
            ->where('is_active', true)
            ->where('setup_completed', true)
            ->with(['badge', 'vendor'])
            ->withCount(['products', 'orders']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('business_category', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by badge
        if ($request->filled('badge')) {
            $query->whereHas('badge', function($q) use ($request) {
                $q->where('slug', $request->badge);
            });
        }

        // Filter by business category
        if ($request->filled('category')) {
            $query->where('business_category', $request->category);
        }

        // Sorting
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'popular':
                $query->orderByDesc('orders_count');
                break;
            case 'products':
                $query->orderByDesc('products_count');
                break;
            case 'badge':
                $query->orderByDesc('badge_id');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default: // latest
                $query->latest();
                break;
        }

        $vendors = $query->paginate(12)->withQueryString();

        // Get all badges for filter
        $badges = \App\Models\Badge::all();

        // Get unique business categories for filter
        $businessTypes = Shop::where('is_active', true)
            ->where('setup_completed', true)
            ->whereNotNull('business_category')
            ->distinct()
            ->pluck('business_category')
            ->filter()
            ->sort();

        return view('public.vendors.index', compact(
            'vendors',
            'badges', 
            'businessTypes'
        ));
    }

    /**
     * Display a specific vendor shop
     */
    public function show(Shop $shop): View
    {
        // Ensure shop is active and completed
        if (!$shop->is_active || !$shop->setup_completed) {
            abort(404, 'Shop not found or not active');
        }

        // Load relationships
        $shop->load([
            'badge',
            'vendor',
            'products' => function($query) {
                $query->where('is_active', true)->latest()->take(12);
            }
        ]);

        // Get shop stats
        $stats = [
            'total_products' => $shop->products()->where('is_active', true)->count(),
            'total_orders' => $shop->orders()->count(),
            'badge_name' => $shop->badge->name ?? 'No Badge',
            'member_since' => $shop->created_at->format('M Y'),
        ];

        return view('public.vendors.show', compact('shop', 'stats'));
    }
} 