<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('order')->get()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->setTypedValue($value);
                $setting->save();
            }
        }

        // Handle file uploads
        foreach ($request->allFiles() as $key => $file) {
            if (strpos($key, 'settings_') === 0) {
                $settingKey = str_replace('settings_', '', $key);
                $setting = Setting::where('key', $settingKey)->first();

                if ($setting && $setting->type === 'file') {
                    $path = $file->store('settings', 'public');
                    $setting->value = $path;
                    $setting->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
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