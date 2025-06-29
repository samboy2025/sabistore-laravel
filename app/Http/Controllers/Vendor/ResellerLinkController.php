<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ResellerLink;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ResellerLinkController extends Controller
{
    public function index(): View
    {
        $resellerLinks = ResellerLink::whereHas('product', function($query) {
                $query->where('shop_id', auth()->user()->shop->id);
            })
            ->with(['product'])
            ->latest()
            ->paginate(15);
            
        return view('vendor.reseller-links.index', compact('resellerLinks'));
    }

    public function create(): View
    {
        $products = Product::where('shop_id', auth()->user()->shop->id)
            ->where('is_resellable', true)
            ->get();
            
        return view('vendor.reseller-links.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // Ensure product belongs to vendor's shop
        if ($product->shop_id !== auth()->user()->shop->id) {
            abort(403, 'Unauthorized');
        }

        ResellerLink::create([
            'product_id' => $request->product_id,
            'code' => Str::random(10),
            'commission_rate' => $request->commission_rate,
            'description' => $request->description,
            'is_active' => true
        ]);

        return redirect()->route('vendor.reseller-links.index')
            ->with('success', 'Reseller link created successfully!');
    }

    public function show(ResellerLink $resellerLink): View
    {
        $this->authorize('view', $resellerLink);
        
        $resellerLink->load(['product', 'orders']);
        
        return view('vendor.reseller-links.show', compact('resellerLink'));
    }

    public function edit(ResellerLink $resellerLink): View
    {
        $this->authorize('update', $resellerLink);
        
        $products = Product::where('shop_id', auth()->user()->shop->id)
            ->where('is_resellable', true)
            ->get();
            
        return view('vendor.reseller-links.edit', compact('resellerLink', 'products'));
    }

    public function update(Request $request, ResellerLink $resellerLink): RedirectResponse
    {
        $this->authorize('update', $resellerLink);
        
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $resellerLink->update($request->all());

        return redirect()->route('vendor.reseller-links.index')
            ->with('success', 'Reseller link updated successfully!');
    }

    public function destroy(ResellerLink $resellerLink): RedirectResponse
    {
        $this->authorize('delete', $resellerLink);
        
        $resellerLink->delete();

        return redirect()->route('vendor.reseller-links.index')
            ->with('success', 'Reseller link deleted successfully!');
    }
} 