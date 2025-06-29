@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Following</h1>
            <p class="text-gray-600 mt-2">Vendors you're following ({{ $following->total() }})</p>
        </div>

        @if($following->count() > 0)
            <!-- Following Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($following as $vendor)
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-red-200 transition-colors">
                                <div class="flex items-start space-x-4">
                                    <!-- Vendor Logo -->
                                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                        @if($vendor->shop && $vendor->shop->logo_path)
                                            <img src="{{ Storage::url($vendor->shop->logo_path) }}" 
                                                 alt="{{ $vendor->name }}" 
                                                 class="w-16 h-16 rounded-full object-cover">
                                        @else
                                            <span class="text-lg font-bold text-red-600">
                                                {{ strtoupper(substr($vendor->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Vendor Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900">{{ $vendor->name }}</h3>
                                                @if($vendor->shop)
                                                    <p class="text-sm text-gray-600 mt-1">{{ $vendor->shop->name }}</p>
                                                    
                                                    <!-- Badge -->
                                                    @if($vendor->shop->badge)
                                                        <div class="flex items-center mt-2">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                                  style="background-color: {{ $vendor->shop->badge->color ?? '#B10020' }}20; color: {{ $vendor->shop->badge->color ?? '#B10020' }};">
                                                                {{ $vendor->shop->badge->name }}
                                                            </span>
                                                        </div>
                                                    @endif

                                                    <!-- Stats -->
                                                    <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                                                        <span>{{ $vendor->shop->products()->count() }} products</span>
                                                        <span>{{ $vendor->followers_count }} followers</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Unfollow Button -->
                                            <form action="{{ route('vendors.unfollow', $vendor) }}" method="POST" class="ml-4">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-gray-400 hover:text-red-600 transition-colors"
                                                        onclick="return confirm('Are you sure you want to unfollow {{ $vendor->name }}?')">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex items-center space-x-3 mt-4">
                                            @if($vendor->shop)
                                                <a href="{{ route('vendors.show', $vendor->shop->slug) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                                    Visit Shop
                                                </a>
                                                @if($vendor->shop->whatsapp_number)
                                                    <a href="{{ $vendor->shop->whatsapp_link }}" 
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-2.462-.96-4.779-2.705-6.526-1.746-1.746-4.065-2.707-6.526-2.709-5.452 0-9.887 4.434-9.889 9.887-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.092-.641zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                        </svg>
                                                        WhatsApp
                                                    </a>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Following Since -->
                                        <p class="text-xs text-gray-400 mt-3">
                                            Following since {{ $vendor->pivot->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                @if($following->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $following->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Not following anyone yet</h3>
                <p class="text-gray-600 mb-6">Discover amazing vendors and follow them to stay updated on their latest products.</p>
                
                <a href="{{ route('vendors.directory') }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Browse Vendors
                </a>
            </div>
        @endif

        <!-- Back to Dashboard -->
        <div class="mt-8">
            <a href="{{ route('buyer.dashboard') }}" 
               class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection 