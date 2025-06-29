<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): View
    {
        $query = Product::with(['shop.vendor']);

        // Filter by status
        if ($request->filled('status')) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by resellable
        if ($request->filled('resellable')) {
            $resellable = $request->resellable === 'yes';
            $query->where('is_resellable', $resellable);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('shop', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhereHas('vendor', function($q) use ($request) {
                            $q->where('name', 'like', '%' . $request->search . '%');
                        });
                  });
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->latest()->paginate(20);

        // Get filter options
        $shops = Shop::with('vendor')->get();
        $types = Product::distinct()->pluck('type');

        return view('admin.products.index', compact('products', 'shops', 'types'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): View
    {
        $shops = Shop::with('vendor')->where('is_active', true)->get();
        return view('admin.products.create', compact('shops'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:physical,digital',
            'is_resellable' => 'boolean',
            'resell_commission_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'tags' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|file|max:10240', // 10MB max for digital products
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/images', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Handle file upload for digital products
        if ($request->hasFile('file_path') && $validated['type'] === 'digital') {
            $validated['file_path'] = $request->file('file_path')->store('products/files', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): View
    {
        $product->load(['shop.vendor', 'orders.buyer']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product): View
    {
        $shops = Shop::with('vendor')->where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'shops'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:physical,digital',
            'is_resellable' => 'boolean',
            'resell_commission_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'tags' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|file|max:10240',
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/images', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = array_merge($product->images ?? [], $imagePaths);
        }

        // Handle file upload for digital products
        if ($request->hasFile('file_path') && $validated['type'] === 'digital') {
            $validated['file_path'] = $request->file('file_path')->store('products/files', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Product {$status} successfully.");
    }

    /**
     * Flag product as inappropriate
     */
    public function flag(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Product flagged and deactivated successfully.');
    }

    /**
     * Verify product
     */
    public function verify(Product $product): RedirectResponse
    {
        $product->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Product verified and activated successfully.');
    }
}
