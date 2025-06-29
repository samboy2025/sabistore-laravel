@extends('layouts.app')

@section('title', $shop->name . ' - Vendor Profile - SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Vendor Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-start space-x-6">
                <!-- Vendor Logo -->
                <div class="flex-shrink-0">
                    @if($shop->logo_path)
                        <img src="{{ Storage::url($shop->logo_path) }}" 
                             alt="{{ $shop->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                    @else
                        <div class="w-24 h-24 rounded-full bg-red-100 flex items-center justify-center border-4 border-white shadow-lg">
                            <span class="text-2xl font-bold text-red-600">
                                {{ strtoupper(substr($shop->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Vendor Info -->
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $shop->name }}</h1>
                        @if($shop->badge)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $shop->badge->color }}-100 text-{{ $shop->badge->color }}-800">
                                {{ $shop->badge->name }}
                            </span>
                        @endif
                    </div>
                    
                    <p class="text-gray-600 mb-4">{{ $shop->description }}</p>
                    
                    <!-- Shop Stats -->
                    <div class="flex items-center space-x-6 text-sm text-gray-500 mb-4">
                        <span>{{ $shop->products()->count() }} Products</span>
                        <span>{{ $shop->vendor->followers_count }} Followers</span>
                        @if($shop->business_category)
                            <span>{{ ucfirst($shop->business_category) }}</span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-3">
                        @auth
                            @if(auth()->id() !== $shop->vendor_id)
                                @if(auth()->user()->isFollowing($shop->vendor))
                                    <form action="{{ route('vendors.unfollow', $shop->vendor) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                            Following ({{ $shop->vendor->followers_count }})
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('vendors.follow', $shop->vendor) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            Follow ({{ $shop->vendor->followers_count }})
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <!-- Wallet Balance Display -->
                            <div class="inline-flex items-center px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span class="text-sm font-medium text-green-700">
                                    Wallet: {{ auth()->user()->formatted_wallet_balance }}
                                </span>
                            </div>
                        @else
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                Login to Follow
                            </a>
                        @endauth
                        
                        @if($shop->whatsapp_number)
                            <a href="{{ $shop->whatsapp_link }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-2.462-.96-4.779-2.705-6.526-1.746-1.746-4.065-2.707-6.526-2.709-5.452 0-9.887 4.434-9.889 9.887-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.092-.641zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                Contact via WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Banner -->
    @if($shop->banner_path)
        <div class="h-64 bg-gray-200">
            <img class="w-full h-full object-cover" src="{{ asset('storage/' . $shop->banner_path) }}" alt="{{ $shop->name }} banner">
        </div>
    @endif

    <!-- Business Video -->
    @if($shop->business_video)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">About Our Business</h2>
                <div class="aspect-w-16 aspect-h-9">
                    <iframe src="{{ $shop->business_video }}" 
                            class="w-full h-64 rounded-lg"
                            frameborder="0" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Products -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Latest Products</h2>
            @if($stats['total_products'] > 12)
                <a href="{{ route('vendors.show', $shop->slug) }}" class="text-red-600 hover:text-red-700 font-medium">
                    View All Products →
                </a>
            @endif
        </div>

        @if($shop->products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($shop->products as $product)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <!-- Product Image -->
                        <div class="aspect-w-1 aspect-h-1">
                            @if($product->image)
                                <img class="w-full h-48 object-cover" src="{{ $product->image }}" alt="{{ $product->title }}">
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $product->title }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($product->description, 100) }}</p>
                            
                            <!-- Price -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xl font-bold text-red-600">₦{{ number_format($product->price, 2) }}</span>
                                @if($product->type === 'digital')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Digital
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                @if($shop->whatsapp_number)
                                    <a href="https://wa.me/{{ $shop->whatsapp_number }}?text=I'm%20interested%20in%20{{ urlencode($product->title) }}%20-%20₦{{ number_format($product->price, 2) }}" 
                                       target="_blank"
                                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.688z"/>
                                        </svg>
                                        Order via WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products yet</h3>
                <p class="mt-1 text-sm text-gray-500">This vendor hasn't added any products yet.</p>
            </div>
        @endif
    </div>

    <!-- Back to Directory -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <a href="{{ route('vendors.directory') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Vendor Directory
        </a>
    </div>
</div>

<!-- Purchase Confirmation Modal -->
<div id="purchaseModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" onclick="closePurchaseModal()"></div>
        <div class="relative bg-white rounded-xl p-8 max-w-md w-full">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Purchase</h3>
            
            <div id="purchaseDetails" class="mb-6">
                <!-- Details will be populated by JavaScript -->
            </div>
            
            <div class="flex items-center space-x-4">
                <button id="confirmPurchaseBtn" 
                        class="flex-1 px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Confirm Purchase
                </button>
                <button onclick="closePurchaseModal()"
                        class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentProductId = null;

function buyWithWallet(productId, productTitle, productPrice) {
    currentProductId = productId;
    
    const details = document.getElementById('purchaseDetails');
    const walletBalance = {{ auth()->user()->wallet_balance ?? 0 }};
    
    details.innerHTML = `
        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <h4 class="font-medium text-gray-900 mb-2">${productTitle}</h4>
            <p class="text-2xl font-bold text-red-600 mb-2">₦${productPrice.toLocaleString()}</p>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Your wallet balance:</span>
            <span class="font-medium">₦${walletBalance.toLocaleString()}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">After purchase:</span>
            <span class="font-medium">₦${(walletBalance - productPrice).toLocaleString()}</span>
        </div>
    `;
    
    document.getElementById('purchaseModal').classList.remove('hidden');
}

function closePurchaseModal() {
    document.getElementById('purchaseModal').classList.add('hidden');
    currentProductId = null;
}

document.getElementById('confirmPurchaseBtn').addEventListener('click', function() {
    if (!currentProductId) return;
    
    this.disabled = true;
    this.textContent = 'Processing...';
    
    fetch(`/api/products/${currentProductId}/buy`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reseller_code: sessionStorage.getItem('reseller_code')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Purchase successful! Your order has been placed.');
            location.reload();
        } else {
            alert('Purchase failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('Purchase failed. Please try again.');
        console.error('Error:', error);
    })
    .finally(() => {
        this.disabled = false;
        this.textContent = 'Confirm Purchase';
        closePurchaseModal();
    });
});

function generateResellerLink(productId) {
    // This would open a modal or redirect to generate reseller link
    alert('Reseller link generation coming soon!');
}

// Handle reseller code from URL
const urlParams = new URLSearchParams(window.location.search);
const resellerCode = urlParams.get('ref');
if (resellerCode) {
    sessionStorage.setItem('reseller_code', resellerCode);
}
</script>
@endsection 