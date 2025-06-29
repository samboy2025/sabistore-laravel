@extends('layouts.app')

@section('title', 'Settings - Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Platform Settings</h1>
        <p class="text-gray-600 mt-2">Configure your SaaS platform settings</p>
    </div>

    <!-- Settings Navigation -->
    <div class="mb-6">
        <nav class="flex space-x-8">
            <a href="{{ route('admin.settings') }}" class="py-2 px-1 border-b-2 border-red-500 font-medium text-sm text-red-600">
                General
            </a>
            <a href="{{ route('admin.settings.payments') }}" class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Payments
            </a>
        </nav>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Site Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Site Information</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                            <input type="text" name="site_name" id="site_name" value="{{ $settings['site_name'] ?? '' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        
                        <div>
                            <label for="support_email" class="block text-sm font-medium text-gray-700 mb-2">Support Email</label>
                            <input type="email" name="support_email" id="support_email" value="{{ $settings['support_email'] ?? '' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">Site Description</label>
                        <textarea name="site_description" id="site_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">{{ $settings['site_description'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Membership Settings -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Membership Settings</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="membership_fee" class="block text-sm font-medium text-gray-700 mb-2">Membership Fee (₦)</label>
                            <input type="number" name="membership_fee" id="membership_fee" 
                                   value="{{ $settings['membership_fee'] ?? 1000 }}"
                                   step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                            <select name="currency" id="currency" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="NGN" {{ ($settings['currency'] ?? 'NGN') == 'NGN' ? 'selected' : '' }}>NGN (₦)</option>
                                <option value="USD" {{ ($settings['currency'] ?? 'NGN') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="GBP" {{ ($settings['currency'] ?? 'NGN') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                    
                    <div>
                        <label for="whatsapp_support" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Support Number</label>
                        <input type="text" name="whatsapp_support" id="whatsapp_support" 
                               value="{{ $settings['whatsapp_support'] ?? '' }}"
                               placeholder="+234..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 