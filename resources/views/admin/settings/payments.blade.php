@extends('layouts.app')

@section('title', 'Payment Settings - Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Payment Settings</h1>
        <p class="text-gray-600 mt-2">Configure payment gateways and processing options</p>
    </div>

    <!-- Settings Navigation -->
    <div class="mb-6">
        <nav class="flex space-x-8">
            <a href="{{ route('admin.settings') }}" class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                General
            </a>
            <a href="{{ route('admin.settings.payments') }}" class="py-2 px-1 border-b-2 border-red-500 font-medium text-sm text-red-600">
                Payments
            </a>
        </nav>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.update-payments') }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-8">
                <!-- Payment Gateway Settings -->
                <div class="border-b border-gray-200 pb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Payment Gateways</h3>
                    
                    <!-- Paystack Settings -->
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-medium text-gray-900">Paystack</h4>
                                <p class="text-sm text-gray-500">Nigerian payment gateway</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="paystack_enabled" 
                                       value="1" 
                                       {{ ($paymentSettings['paystack_enabled'] ?? false) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-600">Enabled</span>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="paystack_public_key" class="block text-sm font-medium text-gray-700 mb-2">Public Key</label>
                                <input type="text" 
                                       name="paystack_public_key" 
                                       id="paystack_public_key" 
                                       value="{{ env('PAYSTACK_PUBLIC_KEY') }}"
                                       placeholder="pk_test_..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                            
                            <div>
                                <label for="paystack_secret_key" class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                                <input type="password" 
                                       name="paystack_secret_key" 
                                       id="paystack_secret_key" 
                                       value="{{ env('PAYSTACK_SECRET_KEY') ? '••••••••••••' : '' }}"
                                       placeholder="sk_test_..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                        </div>
                    </div>

                    <!-- Flutterwave Settings -->
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-medium text-gray-900">Flutterwave</h4>
                                <p class="text-sm text-gray-500">African payment gateway</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="flutterwave_enabled" 
                                       value="1" 
                                       {{ ($paymentSettings['flutterwave_enabled'] ?? false) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-600">Enabled</span>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="flutterwave_public_key" class="block text-sm font-medium text-gray-700 mb-2">Public Key</label>
                                <input type="text" 
                                       name="flutterwave_public_key" 
                                       id="flutterwave_public_key" 
                                       value="{{ env('FLUTTERWAVE_PUBLIC_KEY') }}"
                                       placeholder="FLWPUBK_TEST-..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                            
                            <div>
                                <label for="flutterwave_secret_key" class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                                <input type="password" 
                                       name="flutterwave_secret_key" 
                                       id="flutterwave_secret_key" 
                                       value="{{ env('FLUTTERWAVE_SECRET_KEY') ? '••••••••••••' : '' }}"
                                       placeholder="FLWSECK_TEST-..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Environment Settings -->
                <div class="border-b border-gray-200 pb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Environment Settings</h3>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="test_mode" 
                                   value="1" 
                                   {{ ($paymentSettings['test_mode'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Test Mode</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Enable test mode for development and testing. Disable for production.</p>
                    </div>
                </div>

                <!-- Webhook Settings -->
                <div class="border-b border-gray-200 pb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Webhook Settings</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="paystack_webhook_url" class="block text-sm font-medium text-gray-700 mb-2">Paystack Webhook URL</label>
                            <input type="url" 
                                   name="paystack_webhook_url" 
                                   id="paystack_webhook_url" 
                                   value="{{ url('/webhooks/paystack') }}"
                                   readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                            <p class="mt-1 text-sm text-gray-500">Copy this URL to your Paystack dashboard webhook settings.</p>
                        </div>
                        
                        <div>
                            <label for="flutterwave_webhook_url" class="block text-sm font-medium text-gray-700 mb-2">Flutterwave Webhook URL</label>
                            <input type="url" 
                                   name="flutterwave_webhook_url" 
                                   id="flutterwave_webhook_url" 
                                   value="{{ url('/webhooks/flutterwave') }}"
                                   readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                            <p class="mt-1 text-sm text-gray-500">Copy this URL to your Flutterwave dashboard webhook settings.</p>
                        </div>
                    </div>
                </div>

                <!-- Transaction Settings -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Transaction Settings</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="transaction_fee" class="block text-sm font-medium text-gray-700 mb-2">Transaction Fee (%)</label>
                            <input type="number" 
                                   name="transaction_fee" 
                                   id="transaction_fee" 
                                   value="1.5"
                                   step="0.1" 
                                   min="0" 
                                   max="10"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        
                        <div>
                            <label for="minimum_amount" class="block text-sm font-medium text-gray-700 mb-2">Minimum Amount (₦)</label>
                            <input type="number" 
                                   name="minimum_amount" 
                                   id="minimum_amount" 
                                   value="100"
                                   step="1" 
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Save Payment Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 