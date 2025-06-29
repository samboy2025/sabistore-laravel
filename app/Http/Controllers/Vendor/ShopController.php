<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function create(): View
    {
        return view('vendor.shop.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'business_category' => 'required|string',
            'whatsapp_number' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'business_video' => 'nullable|url'
        ]);

        $data = $request->all();
        $data['vendor_id'] = auth()->id();
        $data['slug'] = Str::slug($request->name);
        
        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('shops/logos', 'public');
        }
        
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('shops/banners', 'public');
        }

        Shop::create($data);

        return redirect()->route('vendor.dashboard')->with('success', 'Shop created successfully!');
    }

    public function edit(Shop $shop): View
    {
        $this->authorize('update', $shop);
        return view('vendor.shop.edit', compact('shop'));
    }

    public function update(Request $request, Shop $shop): RedirectResponse
    {
        $this->authorize('update', $shop);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'business_category' => 'required|string',
            'whatsapp_number' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'business_video' => 'nullable|url'
        ]);

        $data = $request->all();
        
        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('shops/logos', 'public');
        }
        
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('shops/banners', 'public');
        }

        $shop->update($data);

        return redirect()->route('vendor.dashboard')->with('success', 'Shop updated successfully!');
    }

    public function setup(): View
    {
        $shop = auth()->user()->shop;
        return view('vendor.shop.setup', compact('shop'));
    }

    public function completeSetup(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Check if vendor has paid membership before allowing setup completion
        if (!$user->membership_active || !$user->membership_paid_at) {
            return redirect()->route('membership.payment')
                ->with('error', 'Please complete your ₦1,000 membership payment before setting up your shop.')
                ->with('info', 'Your membership enables you to complete shop setup and upload products.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:shops,name,' . ($user->shop ? $user->shop->id : 'NULL'),
            'description' => 'required|string|min:20',
            'business_category' => 'required|string',
            'whatsapp_number' => 'required|string|max:20',
            'bvn_nin' => 'required|string|min:10|max:15',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'business_video' => 'nullable|url'
        ]);

        $data = $request->all();
        $data['vendor_id'] = $user->id;
        $data['slug'] = Str::slug($request->name);
        $data['setup_completed'] = true;
        $data['is_active'] = true;
        
        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('shops/logos', 'public');
        }
        
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('shops/banners', 'public');
        }

        // Create or update shop
        $shop = $user->shop;
        if ($shop) {
            // Preserve existing files if no new files uploaded
            if (!$request->hasFile('logo')) {
                unset($data['logo_path']);
            }
            if (!$request->hasFile('banner')) {
                unset($data['banner_path']);
            }
            $shop->update($data);
        } else {
            // Assign Bronze badge by default for new shops
            $bronzeBadge = \App\Models\Badge::where('name', 'Bronze')->first();
            if ($bronzeBadge) {
                $data['badge_id'] = $bronzeBadge->id;
            }
            $shop = Shop::create($data);
        }

        // Update user's BVN/NIN information
        $user->update([
            'bvn_nin' => $request->bvn_nin
        ]);

        return redirect()->route('vendor.dashboard')
            ->with('success', 'Shop setup completed successfully! You can now start adding products.')
            ->with('info', 'Your shop is now live and ready for customers.');
    }

    public function preview(): View
    {
        $shop = auth()->user()->shop;
        
        if (!$shop) {
            abort(404, 'Shop not found. Please complete your shop setup first.');
        }
        
        $products = $shop->products()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);
            
        return view('public.shop.index', compact('shop', 'products'));
    }
} 