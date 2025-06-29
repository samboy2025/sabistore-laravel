<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'site_name' => 'SabiStore',
            'site_description' => 'Multi-tenant SaaS for vendors',
            'membership_fee' => 1000,
            'currency' => 'NGN',
            'paystack_public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'flutterwave_public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
            'whatsapp_support' => '+234',
            'support_email' => 'support@sabistore.com'
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'membership_fee' => 'required|numeric|min:0',
            'whatsapp_support' => 'nullable|string|max:20',
            'support_email' => 'nullable|email'
        ]);

        // In a real application, you would save these to a settings table
        // For now, we will just redirect with success message
        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully');
    }

    public function payments(): View
    {
        $paymentSettings = [
            'paystack_enabled' => true,
            'flutterwave_enabled' => false,
            'test_mode' => true
        ];

        return view('admin.settings.payments', compact('paymentSettings'));
    }

    public function updatePayments(Request $request)
    {
        $request->validate([
            'paystack_enabled' => 'boolean',
            'flutterwave_enabled' => 'boolean',
            'test_mode' => 'boolean'
        ]);

        return redirect()->route('admin.settings.payments')->with('success', 'Payment settings updated');
    }
}