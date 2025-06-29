@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Badge Status</h1>
            <p class="text-gray-600 mt-2">Track your progress and unlock higher badge levels</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Current Badge Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Badge</h2>
                    
                    @if($currentBadge)
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center" 
                                 style="background-color: {{ $currentBadge->color ?? '#B10020' }}20;">
                                <span class="text-2xl font-bold" style="color: {{ $currentBadge->color ?? '#B10020' }};">
                                    {{ strtoupper(substr($currentBadge->name, 0, 1)) }}
                                </span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $currentBadge->name }}</h3>
                            <p class="text-gray-600 text-sm mt-1">{{ $currentBadge->description }}</p>
                        </div>
                    @else
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <span class="text-2xl font-bold text-gray-400">?</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">No Badge</h3>
                            <p class="text-gray-600 text-sm mt-1">Complete requirements to earn your first badge</p>
                        </div>
                    @endif
                </div>

                <!-- Current Stats -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Statistics</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Products</span>
                            <span class="font-semibold text-gray-900">{{ $stats['products_count'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Completed Orders</span>
                            <span class="font-semibold text-gray-900">{{ $stats['orders_count'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Followers</span>
                            <span class="font-semibold text-gray-900">{{ $stats['followers_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="lg:col-span-2">
                @if($nextBadge)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Next Badge: {{ $nextBadge->name }}</h2>
                        <p class="text-gray-600 mb-6">{{ $nextBadge->description }}</p>
                        
                        <!-- Progress Bars -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Products</span>
                                    <span class="text-sm text-gray-600">
                                        {{ $progress['products']['current'] }} / {{ $progress['products']['required'] }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $progress['products']['percentage'] }}%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Completed Orders</span>
                                    <span class="text-sm text-gray-600">
                                        {{ $progress['orders']['current'] }} / {{ $progress['orders']['required'] }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $progress['orders']['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Next Steps -->
                        <div class="mt-6 p-4 bg-red-50 rounded-lg">
                            <h4 class="font-medium text-red-900 mb-2">What you need to do:</h4>
                            <ul class="text-sm text-red-800 space-y-1">
                                @if($progress['products']['current'] < $progress['products']['required'])
                                    <li>• Upload {{ $progress['products']['required'] - $progress['products']['current'] }} more products</li>
                                @endif
                                @if($progress['orders']['current'] < $progress['orders']['required'])
                                    <li>• Complete {{ $progress['orders']['required'] - $progress['orders']['current'] }} more orders</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Congratulations!</h3>
                            <p class="text-gray-600">You've achieved the highest badge level available.</p>
                        </div>
                    </div>
                @endif

                <!-- All Badges Overview -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">All Badge Levels</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($badges as $badge)
                            <div class="border rounded-lg p-4 {{ $currentBadge && $currentBadge->id === $badge->id ? 'border-red-200 bg-red-50' : 'border-gray-200' }}">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" 
                                         style="background-color: {{ $badge->color ?? '#B10020' }}20;">
                                        <span class="text-sm font-bold" style="color: {{ $badge->color ?? '#B10020' }};">
                                            {{ strtoupper(substr($badge->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 flex items-center">
                                            {{ $badge->name }}
                                            @if($currentBadge && $currentBadge->id === $badge->id)
                                                <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Current</span>
                                            @endif
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">{{ $badge->description }}</p>
                                        <div class="text-xs text-gray-500 mt-2">
                                            Requirements: {{ $badge->min_products }} products, {{ $badge->min_orders }} orders
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ready to grow your business?</h3>
                <p class="text-gray-600 mb-4">Upload more products and provide excellent service to unlock higher badges.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('vendor.products.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Product
                    </a>
                    <a href="{{ route('vendor.learning.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Learning Center
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 