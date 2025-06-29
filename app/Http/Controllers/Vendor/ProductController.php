<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $shop = auth()->user()->shop;
        
        if (!$shop) {
            return redirect()->route('vendor.shop.setup')
                ->with('info', 'Please complete your shop setup before managing products.');
        }
        
        $products = Product::where('shop_id', $shop->id)
            ->latest()
            ->paginate(12);
            
        return view('vendor.products.index', compact('products'));
    }

    public function create(): View|RedirectResponse
    {
        $shop = auth()->user()->shop;
        
        if (!$shop) {
            return redirect()->route('vendor.shop.setup')
                ->with('info', 'Please complete your shop setup before adding products.');
        }
        
        return view('vendor.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = auth()->user()->shop;
        
        if (!$shop) {
            return redirect()->route('vendor.shop.setup')
                ->with('error', 'Please complete your shop setup before adding products.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:physical,digital',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'digital_file' => 'nullable|file|max:10240',
            'is_resellable' => 'boolean'
        ]);

        $data = $request->all();
        $data['shop_id'] = $shop->id;
        
        // Handle file uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
            $data['image_paths'] = json_encode($imagePaths);
        }
        
        if ($request->hasFile('digital_file')) {
            $data['file_path'] = $request->file('digital_file')->store('products/digital', 'public');
        }

        Product::create($data);

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully!');
    }

    public function show(Product $product): View
    {
        // Check if product belongs to current vendor's shop
        if ($product->shop->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        return view('vendor.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        // Check if product belongs to current vendor's shop
        if ($product->shop->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        return view('vendor.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        // Check if product belongs to current vendor's shop
        if ($product->shop->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:physical,digital',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'digital_file' => 'nullable|file|max:10240',
            'is_resellable' => 'boolean'
        ]);

        $data = $request->all();
        
        // Handle file uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
            $data['image_paths'] = json_encode($imagePaths);
        }
        
        if ($request->hasFile('digital_file')) {
            $data['file_path'] = $request->file('digital_file')->store('products/digital', 'public');
        }

        $product->update($data);

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Check if product belongs to current vendor's shop
        if ($product->shop->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully!');
    }
} 