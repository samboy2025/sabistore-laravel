@extends('layouts.app')

@section('title', 'Reseller Links - SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reseller Links</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Generate and manage affiliate links for your products
                        </p>
                    </div>
                    @if(auth()->user()->membership_active)
                        <a href="{{ route('vendor.reseller-links.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Generate New Link
                        </a>
                    @else
                        <div class="text-sm text-gray-500 bg-yellow-50 px-3 py-2 rounded-lg">
                            Complete membership payment to generate reseller links
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Membership Notice -->
        @if(!auth()->user()->membership_active)
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Complete Your Membership Payment</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You need to complete your ₦1,000 membership payment before you can generate reseller links.</p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('membership.payment') }}" 
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200 transition-colors">
                                Pay Membership Fee
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Info Box -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">How Reseller Links Work</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Generate special links for your products that resellers can share. When customers buy through these links, you can track which reseller made the sale and pay them commissions.</p>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($resellerLinks) && $resellerLinks->count() > 0)
            <!-- Reseller Links Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($resellerLinks as $link)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <!-- Product Info -->
                        <div class="mb-4">
                            @if($link->product)
                                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $link->product->title }}</h3>
                                <p class="text-sm text-gray-600">₦{{ number_format($link->product->price, 2) }}</p>
                            @else
                                <h3 class="text-lg font-medium text-gray-500 mb-1">Product Deleted</h3>
                            @endif
                        </div>

                        <!-- Reseller Info -->
                        <div class="mb-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700">Reseller:</span>
                                <span class="text-sm text-gray-900">{{ $link->reseller_name ?? 'Anonymous' }}</span>
                            </div>
                            @if($link->reseller_phone)
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-sm font-medium text-gray-700">Phone:</span>
                                    <span class="text-sm text-gray-900">{{ $link->reseller_phone }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Stats -->
                        <div class="mb-4 grid grid-cols-2 gap-4 text-center">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-2xl font-bold text-gray-900">{{ $link->clicks ?? 0 }}</div>
                                <div class="text-xs text-gray-600">Clicks</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-2xl font-bold text-green-600">{{ $link->sales ?? 0 }}</div>
                                <div class="text-xs text-gray-600">Sales</div>
                            </div>
                        </div>

                        <!-- Link -->
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Reseller Link</label>
                            <div class="flex items-center">
                                <input type="text" 
                                       value="{{ route('shop.reseller', $link->code) }}" 
                                       readonly 
                                       class="flex-1 text-xs bg-gray-50 border border-gray-300 rounded-l-lg px-3 py-2">
                                <button onclick="copyToClipboard('{{ route('shop.reseller', $link->code) }}')" 
                                        class="px-3 py-2 bg-red-600 text-white text-xs rounded-r-lg hover:bg-red-700 transition-colors">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('vendor.reseller-links.show', $link) }}" 
                               class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                View Details
                            </a>
                            <form method="POST" action="{{ route('vendor.reseller-links.destroy', $link) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Are you sure you want to delete this reseller link?')"
                                        class="px-3 py-2 border border-red-300 rounded-lg text-xs font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($resellerLinks->hasPages())
                <div class="mt-8">
                    {{ $resellerLinks->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No reseller links yet</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(auth()->user()->membership_active)
                        Generate reseller links to let others promote your products for commission.
                    @else
                        Complete your membership payment to start creating reseller links.
                    @endif
                </p>
                <div class="mt-6">
                    @if(auth()->user()->membership_active)
                        <a href="{{ route('vendor.reseller-links.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            Generate First Reseller Link
                        </a>
                    @else
                        <a href="{{ route('membership.payment') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Pay Membership Fee
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    });
}
</script>
@endsection 