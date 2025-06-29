<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShopController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shop::with(['vendor', 'badge']);

        if ($request->filled('status')) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('vendor', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $shops = $query->latest()->paginate(20);
        return view('admin.shops.index', compact('shops'));
    }

    public function show(Shop $shop): View
    {
        $shop->load(['vendor', 'badge', 'products', 'orders']);
        return view('admin.shops.show', compact('shop'));
    }

    public function edit(Shop $shop): View
    {
        return view('admin.shops.edit', compact('shop'));
    }

    public function update(Request $request, Shop $shop)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $shop->update($request->only(['name', 'description', 'is_active']));

        return redirect()->route('admin.shops.index')->with('success', 'Shop updated successfully');
    }

    public function toggleStatus(Shop $shop)
    {
        $shop->update(['is_active' => !$shop->is_active]);
        $status = $shop->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Shop has been {$status}");
    }

    public function destroy(Shop $shop)
    {
        $shop->delete();
        return redirect()->route('admin.shops.index')->with('success', 'Shop deleted successfully');
    }
} 