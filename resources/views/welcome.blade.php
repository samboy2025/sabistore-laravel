@extends('layouts.app')

@section('title', 'SabiStore - Your Digital Marketplace')

@section('content')
    <!-- Hero Section -->
    <div class="bg-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Build Your 
                    <span class="text-red-600">Digital Store</span>
                    Today
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Join thousands of vendors in Nigeria selling digital and physical products. 
                    Get your custom subdomain, accept payments, and grow your business online.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" 
                       class="bg-red-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-red-700 transition-colors">
                        Start Selling - 1,000
                    </a>
                    <a href="{{ url('/vendors') }}" 
                       class="border-2 border-red-600 text-red-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-red-50 transition-colors">
                        Browse Shops
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Stats -->
    @if(isset($stats))
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-3xl lg:text-4xl font-bold text-red-600 mb-2">{{ number_format($stats['total_vendors']) }}</div>
                    <div class="text-gray-600">Active Vendors</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl lg:text-4xl font-bold text-red-600 mb-2">{{ number_format($stats['total_products']) }}</div>
                    <div class="text-gray-600">Products Listed</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl lg:text-4xl font-bold text-red-600 mb-2">{{ number_format($stats['total_orders']) }}</div>
                    <div class="text-gray-600">Orders Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl lg:text-4xl font-bold text-red-600 mb-2">{{ number_format($stats['active_badges']) }}</div>
                    <div class="text-gray-600">Badge Levels</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- How It Works -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-lg text-gray-600">Start selling in 3 simple steps</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-red-600 font-bold text-xl">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Register & Pay</h3>
                    <p class="text-gray-600">Sign up as a vendor and pay the one-time 1,000 membership fee to unlock your store.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-red-600 font-bold text-xl">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Setup Your Shop</h3>
                    <p class="text-gray-600">Complete your shop profile, upload your logo, and get your custom subdomain.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-red-600 font-bold text-xl">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Start Selling</h3>
                    <p class="text-gray-600">Upload your products and start receiving orders via WhatsApp or direct checkout.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-red-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">Ready to Start Your Digital Store?</h2>
            <p class="text-xl text-red-100 mb-8">Join thousands of successful vendors already selling on SabiStore</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" 
                   class="bg-white text-red-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition-colors">
                    Get Started Now
                </a>
                <a href="{{ url('/vendors') }}" 
                   class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-red-700 transition-colors">
                    Explore Vendors
                </a>
            </div>
        </div>
    </div>
@endsection
