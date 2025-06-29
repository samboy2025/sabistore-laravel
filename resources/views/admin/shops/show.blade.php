@extends('layouts.app')

@section('title', 'Shop Details - Admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Shop Details</h1>
                <p class="text-gray-600 mt-2">Manage shop information and products</p>
            </div>
            <div class="flex space-x-3">
                <form method="POST" action="{{ route('admin.shops.toggle-status', $shop) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 {{ $shop->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} rounded-lg transition-colors">
                        {{ $shop->is_active ? 'Deactivate' : 'Activate' }} Shop
                    </button>
                </form>
                <a href="{{ route('admin.shops.index') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    ← Back to Shops
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Shop Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Shop Information</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Shop Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <p class="mt-1">
                            @if($shop->is_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Setup Completed</label>
                        <p class="mt-1">
                            @if($shop->setup_completed)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">No</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Business Category</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->business_category ?: 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">WhatsApp Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->whatsapp_number ?: 'Not provided' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Badge</label>
                        <p class="mt-1">
                            @if($shop->badge)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ $shop->badge->name }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">No badge</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->description ?: 'No description provided' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Created Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Vendor Information</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Vendor Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->vendor->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->vendor->email }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $shop->vendor->phone ?: 'Not provided' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Membership Status</label>
                        <p class="mt-1">
                            @if($shop->vendor->membership_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('admin.users.show', $shop->vendor) }}" 
                       class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View Vendor Details →
                    </a>
                </div>
            </div>

            <!-- Recent Products -->
            @if($shop->products->count() > 0)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Recent Products</h2>
                    
                    <div class="space-y-4">
                        @foreach($shop->products->take(5) as $product)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                                <div class="flex items-center space-x-3">
                                    @if($product->image_path)
                                        <img src="{{ $product->image_path }}" alt="{{ $product->title }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">No Image</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">{{ $product->title }}</h3>
                                        <p class="text-xs text-gray-500">{{ ucfirst($product->type) }} • ₦{{ number_format($product->price, 2) }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Statistics -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Shop Statistics</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Products</span>
                        <span class="text-sm font-medium text-gray-900">{{ $shop->products->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Active Products</span>
                        <span class="text-sm font-medium text-gray-900">{{ $shop->products->where('is_active', true)->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Orders</span>
                        <span class="text-sm font-medium text-gray-900">{{ $shop->orders->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Digital Products</span>
                        <span class="text-sm font-medium text-gray-900">{{ $shop->products->where('type', 'digital')->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Physical Products</span>
                        <span class="text-sm font-medium text-gray-900">{{ $shop->products->where('type', 'physical')->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Quick Actions</h2>
                
                <div class="space-y-3">
                    <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        View All Products
                    </button>
                    
                    <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        View Orders
                    </button>
                    
                    <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Send Message to Vendor
                    </button>
                    
                    <form method="POST" action="{{ route('admin.shops.destroy', $shop) }}" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Are you sure you want to delete this shop?')"
                                class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            Delete Shop
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 