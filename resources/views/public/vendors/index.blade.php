@extends('layouts.app')

@section('title', 'Vendor Directory - Find Digital Stores')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Vendor Directory</h1>
                <p class="text-lg text-gray-600 mb-8">Discover amazing vendors and their digital stores</p>
                
                <!-- Search & Filter Bar -->
                <form method="GET" class="max-w-4xl mx-auto">
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <!-- Search Input -->
                        <div class="flex-1 w-full">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search vendors, businesses, or categories..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="w-full md:w-auto">
                            <select name="category" class="w-full md:w-48 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Categories</option>
                                @foreach($businessTypes as $type)
                                    <option value="{{ $type }}" {{ request('category') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Sort Filter -->
                        <div class="w-full md:w-auto">
                            <select name="sort" class="w-full md:w-48 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                                <option value="products" {{ request('sort') == 'products' ? 'selected' : '' }}>Most Products</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <button type="submit" class="w-full md:w-auto px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vendors Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($vendors->count() > 0)
            <!-- Results Info -->
            <div class="mb-6">
                <p class="text-gray-600">
                    Showing {{ $vendors->firstItem() }}-{{ $vendors->lastItem() }} of {{ $vendors->total() }} vendors
                </p>
            </div>

            <!-- Vendors Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($vendors as $vendor)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <!-- Vendor Logo/Banner -->
                        <div class="h-32 bg-gradient-to-br from-red-500 to-red-600 relative">
                            @if($vendor->logo_path)
                                <img src="{{ $vendor->logo_path }}" alt="{{ $vendor->name }}" class="w-16 h-16 rounded-full border-4 border-white absolute bottom-0 left-4 transform translate-y-1/2">
                            @else
                                <div class="w-16 h-16 bg-white rounded-full border-4 border-white absolute bottom-0 left-4 transform translate-y-1/2 flex items-center justify-center">
                                    <span class="text-gray-600 font-bold text-lg">{{ substr($vendor->name, 0, 1) }}</span>
                                </div>
                            @endif
                            
                            <!-- Badge -->
                            @if($vendor->badge)
                                <div class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $vendor->badge->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Vendor Info -->
                        <div class="p-6 pt-10">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $vendor->name }}</h3>
                            
                            @if($vendor->business_category)
                                <p class="text-sm text-red-600 mb-2">{{ ucfirst($vendor->business_category) }}</p>
                            @endif
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ Str::limit($vendor->description, 80) }}
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>{{ $vendor->products_count }} products</span>
                                <span>{{ $vendor->orders_count }} orders</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <a href="{{ route('vendors.show', $vendor->slug) }}" 
                                   class="block w-full text-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    Visit Shop
                                </a>
                                
                                @auth
                                    @if(!auth()->user()->isVendor() || auth()->id() !== $vendor->vendor_id)
                                        @if(auth()->user()->isFollowing($vendor->vendor))
                                            <form action="{{ route('vendors.unfollow', $vendor->vendor) }}" method="POST" class="w-full">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                    </svg>
                                                    Following
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('vendors.follow', $vendor->vendor) }}" method="POST" class="w-full">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                    </svg>
                                                    Follow
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" 
                                       class="block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                        Login to Follow
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $vendors->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No vendors found</h3>
                <p class="text-gray-600 mb-6">Try adjusting your search criteria or check back later.</p>
                <a href="{{ route('vendors.directory') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Clear Filters
                </a>
            </div>
        @endif
    </div>
</div>
@endsection