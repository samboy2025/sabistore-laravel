@extends('layouts.app')

@section('title', 'Edit Product - SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('vendor.products.index') }}" 
                   class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
                    <p class="mt-2 text-gray-600">Update your product information</p>
                </div>
            </div>
        </div>

        <!-- Product Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Product Information</h3>
                <p class="text-sm text-gray-600 mt-1">Update the details about your product</p>
            </div>

            <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Product Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Product Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="{{ old('title', $product->title) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="Enter product title"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Product Description <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                        placeholder="Describe your product in detail..."
                    >{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price and Type Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Price (₦) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            value="{{ old('price', $product->price) }}"
                            min="0"
                            step="0.01"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="0.00"
                        >
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Type <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="type" 
                            name="type" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            onchange="toggleDigitalFields()"
                        >
                            <option value="">Select product type</option>
                            <option value="physical" {{ old('type', $product->type) == 'physical' ? 'selected' : '' }}>Physical Product</option>
                            <option value="digital" {{ old('type', $product->type) == 'digital' ? 'selected' : '' }}>Digital Product</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Current Images -->
                @if($product->image_paths)
                    @php
                        $images = json_decode($product->image_paths, true);
                    @endphp
                    @if($images && count($images) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                @foreach($images as $image)
                                    <div class="relative">
                                        <img class="w-full h-24 object-cover rounded-lg" src="{{ asset('storage/' . $image) }}" alt="{{ $product->title }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Product Images -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                        Update Product Images
                    </label>
                    <input 
                        type="file" 
                        id="images" 
                        name="images[]" 
                        accept="image/*"
                        multiple
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                    <p class="mt-1 text-xs text-gray-600">Upload new images to replace current ones (PNG, JPG, max 2MB each)</p>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Digital File Upload (hidden by default) -->
                <div id="digital-file-section" style="display: {{ old('type', $product->type) == 'digital' ? 'block' : 'none' }};">
                    @if($product->type === 'digital' && $product->file_path)
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Current file uploaded</span>
                                <a href="{{ asset('storage/' . $product->file_path) }}" target="_blank" class="text-xs text-red-600 hover:text-red-700">
                                    Download
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <label for="digital_file" class="block text-sm font-medium text-gray-700 mb-2">
                        Update Digital File
                    </label>
                    <input 
                        type="file" 
                        id="digital_file" 
                        name="digital_file" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                    <p class="mt-1 text-xs text-gray-600">Upload a new file to replace the current one (PDF, ZIP, etc., max 10MB)</p>
                    @error('digital_file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product Options -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900">Product Options</h4>
                    
                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="is_active" 
                            name="is_active" 
                            value="1"
                            {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Make product active (visible to customers)
                        </label>
                    </div>

                    <!-- Is Resellable -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="is_resellable" 
                            name="is_resellable" 
                            value="1"
                            {{ old('is_resellable', $product->is_resellable) ? 'checked' : '' }}
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                        >
                        <label for="is_resellable" class="ml-2 block text-sm text-gray-900">
                            Allow resellers to promote this product
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a 
                        href="{{ route('vendor.products.show', $product) }}" 
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDigitalFields() {
    const typeSelect = document.getElementById('type');
    const digitalSection = document.getElementById('digital-file-section');
    
    if (typeSelect.value === 'digital') {
        digitalSection.style.display = 'block';
    } else {
        digitalSection.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDigitalFields();
});
</script>
@endsection 