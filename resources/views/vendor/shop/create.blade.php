@extends('layouts.app')

@section('title', 'Create Your Shop - SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create Your Shop</h1>
            <p class="mt-2 text-gray-600">Set up your shop profile to start selling on SabiStore</p>
        </div>

        <!-- Setup Progress -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-900">Setup Progress</h2>
                <span class="text-sm text-gray-500">Step 2 of 3</span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-red-600 h-2 rounded-full" style="width: 66%"></div>
            </div>
            
            <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
                <div class="text-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="text-green-600 font-medium">Registration</span>
                </div>
                <div class="text-center">
                    <div class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-sm font-medium">2</span>
                    </div>
                    <span class="text-red-600 font-medium">Create Shop</span>
                </div>
                <div class="text-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-sm font-medium">3</span>
                    </div>
                    <span class="text-gray-500">Start Selling</span>
                </div>
            </div>
        </div>

        <!-- Shop Creation Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Shop Information</h3>
                <p class="text-sm text-gray-600 mt-1">Tell customers about your business</p>
            </div>

            <form method="POST" action="{{ route('vendor.shop.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Shop Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Shop Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="Enter your shop name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Business Category -->
                <div>
                    <label for="business_category" class="block text-sm font-medium text-gray-700 mb-2">
                        Business Category <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="business_category" 
                        name="business_category" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                        <option value="">Select a category</option>
                        <option value="fashion" {{ old('business_category') == 'fashion' ? 'selected' : '' }}>Fashion & Clothing</option>
                        <option value="electronics" {{ old('business_category') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                        <option value="beauty" {{ old('business_category') == 'beauty' ? 'selected' : '' }}>Beauty & Cosmetics</option>
                        <option value="food" {{ old('business_category') == 'food' ? 'selected' : '' }}>Food & Beverages</option>
                        <option value="health" {{ old('business_category') == 'health' ? 'selected' : '' }}>Health & Wellness</option>
                        <option value="digital" {{ old('business_category') == 'digital' ? 'selected' : '' }}>Digital Products</option>
                        <option value="services" {{ old('business_category') == 'services' ? 'selected' : '' }}>Services</option>
                        <option value="other" {{ old('business_category') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('business_category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Shop Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Shop Description <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="Tell customers about your business, products, and what makes you special..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- WhatsApp Number -->
                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                        WhatsApp Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="whatsapp_number" 
                        name="whatsapp_number" 
                        value="{{ old('whatsapp_number', auth()->user()->whatsapp_number ?? '') }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="e.g., +234 801 234 5678"
                    >
                    <p class="mt-1 text-xs text-gray-600">This number will be used for customer orders via WhatsApp</p>
                    @error('whatsapp_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Business Video URL -->
                <div>
                    <label for="business_video" class="block text-sm font-medium text-gray-700 mb-2">
                        Business Video URL
                    </label>
                    <input 
                        type="url" 
                        id="business_video" 
                        name="business_video" 
                        value="{{ old('business_video') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="https://youtube.com/watch?v=..."
                    >
                    <p class="mt-1 text-xs text-gray-600">Share a video about your business (YouTube, Vimeo, etc.)</p>
                    @error('business_video')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Logo Upload -->
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                        Shop Logo
                    </label>
                    <input 
                        type="file" 
                        id="logo" 
                        name="logo" 
                        accept="image/*"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                    <p class="mt-1 text-xs text-gray-600">Upload your shop logo (PNG, JPG, max 2MB)</p>
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Banner Upload -->
                <div>
                    <label for="banner" class="block text-sm font-medium text-gray-700 mb-2">
                        Shop Banner
                    </label>
                    <input 
                        type="file" 
                        id="banner" 
                        name="banner" 
                        accept="image/*"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                    <p class="mt-1 text-xs text-gray-600">Upload a banner image for your shop (PNG, JPG, max 5MB)</p>
                    @error('banner')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a 
                        href="{{ route('vendor.dashboard') }}" 
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Skip for Now
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Create Shop
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Need Help?</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Create your shop profile to start selling. You can always update this information later from your dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 