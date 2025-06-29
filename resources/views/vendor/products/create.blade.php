@extends('layouts.app')

@section('title', 'Add New Product - SabiStore')

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
                    <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
                    <p class="mt-2 text-gray-600">Create a new product for your shop</p>
                </div>
            </div>
        </div>

        <!-- Product Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Product Information</h3>
                <p class="text-sm text-gray-600 mt-1">Fill in the details about your product</p>
            </div>

            <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Product Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Product Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="{{ old('title') }}"
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
                    >{{ old('description') }}</textarea>
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
                            value="{{ old('price') }}"
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
                            <option value="physical" {{ old('type') == 'physical' ? 'selected' : '' }}>Physical Product</option>
                            <option value="digital" {{ old('type') == 'digital' ? 'selected' : '' }}>Digital Product</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Product Images -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                        Product Images
                    </label>
                    <input 
                        type="file" 
                        id="images" 
                        name="images[]" 
                        accept="image/*"
                        multiple
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                    <p class="mt-1 text-xs text-gray-600">Upload product images (PNG, JPG, max 2MB each, multiple files allowed)</p>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Digital File Upload (hidden by default) -->
                <div id="digital-file-section" style="display: none;">
                    <label for="digital_file" class="block text-sm font-medium text-gray-700 mb-2">
                        Digital File <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="file" 
                        id="digital_file" 
                        name="digital_file" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    >
                    <p class="mt-1 text-xs text-gray-600">Upload the digital product file (PDF, ZIP, etc., max 10MB)</p>
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
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Make product active (visible to customers)
                        </label>
                    </div>

                    <!-- Is Resellable -->
                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" 
                                   name="is_resellable" 
                                   id="is_resellable" 
                                   value="1"
                                   {{ old('is_resellable') ? 'checked' : '' }}
                                   class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                            <label for="is_resellable" class="ml-2 text-sm font-medium text-gray-700">
                                Allow this product to be resold by others
                            </label>
                        </div>
                        
                        <!-- Commission Rate (shown when resellable is checked) -->
                        <div id="commission_section" class="hidden">
                            <label for="resell_commission_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                Reseller Commission Percentage
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="resell_commission_percent" 
                                       id="resell_commission_percent" 
                                       min="0" 
                                       max="50" 
                                       step="0.1"
                                       value="{{ old('resell_commission_percent', 10) }}"
                                       class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm">%</span>
                                </div>
                            </div>
                            <p class="text-gray-500 text-sm mt-1">
                                Set the commission percentage that resellers will earn (0% - 50%)
                            </p>
                            @error('resell_commission_percent')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a 
                        href="{{ route('vendor.products.index') }}" 
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Create Product
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
    const digitalFileInput = document.getElementById('digital_file');
    
    if (typeSelect.value === 'digital') {
        digitalSection.style.display = 'block';
        digitalFileInput.required = true;
    } else {
        digitalSection.style.display = 'none';
        digitalFileInput.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDigitalFields();
    const resellableCheckbox = document.getElementById('is_resellable');
    const commissionSection = document.getElementById('commission_section');
    
    // Show/hide commission section based on checkbox
    function toggleCommissionSection() {
        if (resellableCheckbox.checked) {
            commissionSection.classList.remove('hidden');
        } else {
            commissionSection.classList.add('hidden');
        }
    }
    
    // Initial state
    toggleCommissionSection();
    
    // Listen for changes
    resellableCheckbox.addEventListener('change', toggleCommissionSection);
});
</script>
@endsection 