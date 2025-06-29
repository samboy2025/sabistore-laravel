<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SabiStore') }} - @yield('title', 'Multi-Tenant SaaS Platform')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-red: #B10020;
            --light-gray: #F8F9FA;
            --dark-text: #1E1E1E;
            --subtle-gray: #E8E8E8;
        }
        
        .bg-primary-red { background-color: var(--primary-red); }
        .text-primary-red { color: var(--primary-red); }
        .border-primary-red { border-color: var(--primary-red); }
        .hover\:bg-primary-red:hover { background-color: var(--primary-red); }
        .focus\:border-primary-red:focus { border-color: var(--primary-red); }
        
        .transition-smooth { transition: all 0.2s ease-in-out; }
        
        .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .card-shadow-hover:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <a href="{{ route('home') }}" class="flex items-center">
                                <span class="text-2xl font-bold text-primary-red">SabiStore</span>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                        {{ __('Admin Dashboard') }}
                                    </x-nav-link>
                                @elseif(auth()->user()->isVendor())
                                    <x-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">
                                        {{ __('Vendor Dashboard') }}
                                    </x-nav-link>
                                @elseif(auth()->user()->isBuyer())
                                    <x-nav-link :href="route('buyer.dashboard')" :active="request()->routeIs('buyer.*')">
                                        {{ __('My Dashboard') }}
                                    </x-nav-link>
                                @endif
                            @else
                                <x-nav-link :href="route('vendors.directory')" :active="request()->routeIs('vendors.*')">
                                    {{ __('Browse Vendors') }}
                                </x-nav-link>
                                <x-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">
                                    {{ __('Learning Center') }}
                                </x-nav-link>
                            @endauth
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        @auth
                            <!-- User Dropdown -->
                            <div class="ml-3 relative">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ auth()->user()->name }}</div>
                                            <div class="ml-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')">
                                            {{ __('Profile') }}
                                        </x-dropdown-link>

                                        <!-- Role-specific links -->
                                        @if(auth()->user()->isVendor())
                                            <x-dropdown-link :href="route('vendor.shop.setup')">
                                                {{ __('Shop Settings') }}
                                            </x-dropdown-link>
                                        @endif

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                    onclick="event.preventDefault();
                                                                this.closest('form').submit();">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @else
                            <!-- Guest Links -->
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-smooth">
                                    Log in
                                </a>
                                <a href="{{ route('register') }}" class="bg-primary-red hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-smooth">
                                    Join SabiStore
                                </a>
                            </div>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="sm:hidden flex items-center" x-data="{ open: false }">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div x-show="open" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                {{ __('Admin Dashboard') }}
                            </x-responsive-nav-link>
                        @elseif(auth()->user()->isVendor())
                            <x-responsive-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">
                                {{ __('Vendor Dashboard') }}
                            </x-responsive-nav-link>
                        @elseif(auth()->user()->isBuyer())
                            <x-responsive-nav-link :href="route('buyer.dashboard')" :active="request()->routeIs('buyer.*')">
                                {{ __('My Dashboard') }}
                            </x-responsive-nav-link>
                        @endif
                    @else
                        <x-responsive-nav-link :href="route('vendors.directory')" :active="request()->routeIs('vendors.*')">
                            {{ __('Browse Vendors') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">
                            {{ __('Learning Center') }}
                        </x-responsive-nav-link>
                    @endauth
                </div>

                @auth
                    <!-- Responsive User Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ auth()->user()->name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <x-responsive-nav-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-responsive-nav-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="space-y-1">
                            <x-responsive-nav-link :href="route('login')">
                                {{ __('Log in') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('register')">
                                {{ __('Join SabiStore') }}
                            </x-responsive-nav-link>
                        </div>
                    </div>
                @endauth
            </div>
        </nav>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-4 mx-4 mt-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-4 mx-4 mt-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md mb-4 mx-4 mt-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main class="min-h-screen">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-primary-red">SabiStore</span>
                        </div>
                        <p class="mt-4 text-gray-600 text-sm">
                            Nigeria's leading multi-tenant SaaS platform for vendors and digital product sellers. 
                            Build your brand, grow your business, and reach more customers.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">For Vendors</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900 text-sm">Start Selling</a></li>
                            <li><a href="{{ route('courses.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Learning Center</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Success Stories</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">For Buyers</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="{{ route('vendors.directory') }}" class="text-gray-600 hover:text-gray-900 text-sm">Browse Vendors</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Popular Products</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">How It Works</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-8">
                    <p class="text-gray-400 text-sm text-center">
                        &copy; {{ date('Y') }} SabiStore. All rights reserved. Built for Nigerian entrepreneurs.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html> 