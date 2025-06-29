@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                    @if($vendor->shop && $vendor->shop->logo_path)
                        <img src="{{ Storage::url($vendor->shop->logo_path) }}" 
                             alt="{{ $vendor->name }}" 
                             class="w-16 h-16 rounded-full object-cover">
                    @else
                        <span class="text-xl font-bold text-red-600">
                            {{ strtoupper(substr($vendor->name, 0, 2)) }}
                        </span>
                    @endif
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $vendor->name }}'s Followers</h1>
                    <p class="text-gray-600">{{ $followers->total() }} {{ Str::plural('follower', $followers->total()) }}</p>
                </div>
            </div>
        </div>

        @if($followers->count() > 0)
            <!-- Followers Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($followers as $follower)
                            <div class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-red-200 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-semibold text-gray-600">
                                        {{ strtoupper(substr($follower->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 truncate">{{ $follower->name }}</h3>
                                    <p class="text-sm text-gray-500 capitalize">{{ $follower->role }}</p>
                                    <p class="text-xs text-gray-400">
                                        Following since {{ $follower->pivot->created_at->format('M Y') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                @if($followers->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $followers->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No followers yet</h3>
                <p class="text-gray-600 mb-6">Build your reputation to attract followers who will discover your products.</p>
                
                @if(auth()->id() === $vendor->id)
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('vendor.products.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            Add Products
                        </a>
                        <a href="{{ route('vendors.directory') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            Browse Vendors
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <!-- Back Button -->
        <div class="mt-8">
            <a href="{{ route('vendors.directory') }}" 
               class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Vendor Directory
            </a>
        </div>
    </div>
</div>
@endsection 