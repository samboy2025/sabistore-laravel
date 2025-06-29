@extends('layouts.app')

@section('title', 'Edit Shop - SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Shop Information</h1>
            <p class="mt-2 text-gray-600">Update your shop profile and business details</p>
        </div>

        <!-- Shop Edit Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Shop Information</h3>
                <p class="text-sm text-gray-600 mt-1">Update your business information</p>
            </div>

            <form method="POST" action="{{ route('vendor.shop.update', $shop) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Shop Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Shop Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $shop->name) }}"
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
                        <option value="fashion" {{ old('business_category', $shop->business_category) == 'fashion' ? 'selected' : '' }}>Fashion & Clothing</option>
                        <option value="electronics" {{ old('business_category', $shop->business_category) == 'electronics' ? 'selected' : '' }}>Electronics</option>
                        <option value="beauty" {{ old('business_category', $shop->business_category) == 'beauty' ? 'selected' : '' }}>Beauty & Cosmetics</option>
                        <option value="food" {{ old('business_category', $shop->business_category) == 'food' ? 'selected' : '' }}>Food & Beverages</option>
                        <option value="health" {{ old('business_category', $shop->business_category) == 'health' ? 'selected' : '' }}>Health & Wellness</option>
                        <option value="digital" {{ old('business_category', $shop->business_category) == 'digital' ? 'selected' : '' }}>Digital Products</option>
                        <option value="services" {{ old('business_category', $shop->business_category) == 'services' ? 'selected' : '' }}>Services</option>
                        <option value="other" {{ old('business_category', $shop->business_category) == 'other' ? 'selected' : '' }}>Other</option>
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
                    >{{ old('description', $shop->description) }}</textarea>
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
                        value="{{ old('whatsapp_number', $shop->whatsapp_number) }}"
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
                        value="{{ old('business_video', $shop->business_video) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="https://youtube.com/watch?v=..."
                    >
                    <p class="mt-1 text-xs text-gray-600">Share a video about your business (YouTube, Vimeo, etc.)</p>
                    @error('business_video')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Logo -->
                @if($shop->logo_path)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                    <div class="flex items-center space-x-4">
                        <img src="{{ Storage::url($shop->logo_path) }}" alt="Shop Logo" class="w-16 h-16 object-cover rounded-lg border">
                        <span class="text-sm text-gray-600">Current shop logo</span>
                    </div>
                </div>
                @endif

                <!-- Logo Upload -->
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $shop->logo_path ? 'Update Logo' : 'Shop Logo' }}
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

                <!-- Current Banner -->
                @if($shop->banner_path)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Banner</label>
                    <div class="flex items-center space-x-4">
                        <img src="{{ Storage::url($shop->banner_path) }}" alt="Shop Banner" class="w-32 h-16 object-cover rounded-lg border">
                        <span class="text-sm text-gray-600">Current shop banner</span>
                    </div>
                </div>
                @endif

                <!-- Banner Upload -->
                <div>
                    <label for="banner" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $shop->banner_path ? 'Update Banner' : 'Shop Banner' }}
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
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Update Shop
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 