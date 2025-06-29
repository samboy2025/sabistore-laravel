<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopPageController extends Controller
{
    public function index(Request $request): View
    {
        $shop = Shop::where('slug', $request->route('shop'))->firstOrFail();
        
        $products = Product::where('shop_id', $shop->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(12);
            
        return view('public.shop.index', compact('shop', 'products'));
    }

    public function products(Request $request): View
    {
        $shop = Shop::where('slug', $request->route('shop'))->firstOrFail();
        
        $products = Product::where('shop_id', $shop->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(20);
            
        return view('public.shop.products', compact('shop', 'products'));
    }

    public function product(Request $request, Product $product): View
    {
        $shop = Shop::where('slug', $request->route('shop'))->firstOrFail();
        
        if ($product->shop_id !== $shop->id) {
            abort(404);
        }
        
        return view('public.shop.product', compact('shop', 'product'));
    }
} 