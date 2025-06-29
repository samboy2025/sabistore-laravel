<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the vendor profile edit form
     */
    public function edit(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Check membership status first
        if (!$user->membership_active || !$user->membership_paid_at) {
            return redirect()->route('membership.payment')
                ->with('warning', 'Please complete your membership payment to access profile settings.');
        }
        
        $shop = $user->shop;
        
        if (!$shop) {
            return redirect()->route('vendor.shop.setup')
                ->with('info', 'Please complete your shop setup first.');
        }

        return view('vendor.profile.edit', compact('shop'));
    }

    /**
     * Update the vendor profile
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('vendor.shop.setup')
                ->with('error', 'Shop not found. Please complete shop setup first.');
        }

        $request->validate([
            'facebook_handle' => 'nullable|string|max:255',
            'instagram_handle' => 'nullable|string|max:255',
            'twitter_handle' => 'nullable|string|max:255',
            'tiktok_handle' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:1000',
            'business_location' => 'nullable|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'description' => 'nullable|string|max:2000',
        ]);

        // Clean social media handles (remove @ and URLs)
        $data = $request->only([
            'facebook_handle',
            'instagram_handle', 
            'twitter_handle',
            'tiktok_handle',
            'business_address',
            'business_location',
            'whatsapp_number',
            'description'
        ]);

        // Clean social media handles
        foreach (['facebook_handle', 'instagram_handle', 'twitter_handle', 'tiktok_handle'] as $field) {
            if (!empty($data[$field])) {
                // Remove @ symbol and common URL patterns
                $data[$field] = preg_replace('/^@/', '', $data[$field]);
                $data[$field] = preg_replace('/^https?:\/\/(www\.)?(facebook|instagram|twitter|tiktok)\.com\/(@)?/', '', $data[$field]);
                $data[$field] = trim($data[$field], '/');
            }
        }

        $shop->update($data);

        return redirect()->route('vendor.profile.edit')
            ->with('success', 'Profile updated successfully!');
    }
}
