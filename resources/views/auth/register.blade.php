@extends('layouts.app')

@section('title', 'Join SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" x-data="{ selectedRole: 'buyer' }">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Join SabiStore</h2>
            <p class="mt-2 text-sm text-gray-600">Start your journey as a vendor or buyer</p>
        </div>

        <!-- Role Selection -->
        <div class="bg-white py-6 px-6 shadow-lg rounded-xl border border-gray-200 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Choose Your Account Type</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Buyer Option -->
                <label class="relative cursor-pointer" @click="selectedRole = 'buyer'">
                    <input type="radio" x-model="selectedRole" value="buyer" class="sr-only">
                    <div class="p-4 border-2 rounded-lg transition-all duration-200" 
                         :class="selectedRole === 'buyer' ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-gray-400'">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6" :class="selectedRole === 'buyer' ? 'text-red-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium" :class="selectedRole === 'buyer' ? 'text-red-900' : 'text-gray-900'">I want to Buy</h4>
                                <p class="text-xs" :class="selectedRole === 'buyer' ? 'text-red-700' : 'text-gray-500'">Browse and purchase from vendors</p>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Vendor Option -->
                <label class="relative cursor-pointer" @click="selectedRole = 'vendor'">
                    <input type="radio" x-model="selectedRole" value="vendor" class="sr-only">
                    <div class="p-4 border-2 rounded-lg transition-all duration-200" 
                         :class="selectedRole === 'vendor' ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-gray-400'">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6" :class="selectedRole === 'vendor' ? 'text-red-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium" :class="selectedRole === 'vendor' ? 'text-red-900' : 'text-gray-900'">I want to Sell</h4>
                                <p class="text-xs" :class="selectedRole === 'vendor' ? 'text-red-700' : 'text-gray-500'">Create shop and sell products</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="bg-white py-8 px-6 shadow-lg rounded-xl border border-gray-200">
            <form class="space-y-6" method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Hidden Role Field -->
                <input type="hidden" name="role" :value="selectedRole">

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name
                        </label>
                        <input 
                            id="name" 
                            name="name" 
                            type="text" 
                            autocomplete="name" 
                            required 
                            value="{{ old('name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="Enter your full name"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="Enter your email"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input 
                            id="phone" 
                            name="phone" 
                            type="tel" 
                            autocomplete="tel" 
                            required 
                            value="{{ old('phone') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="e.g., +234 801 234 5678"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WhatsApp (Vendor Only) -->
                    <div x-show="selectedRole === 'vendor'" x-transition>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                            WhatsApp Number
                        </label>
                        <input 
                            id="whatsapp_number" 
                            name="whatsapp_number" 
                            type="tel" 
                            value="{{ old('whatsapp_number') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="e.g., +234 801 234 5678"
                        >
                        @error('whatsapp_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Password Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="new-password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="Create a strong password"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password
                        </label>
                        <input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            autocomplete="new-password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="Confirm your password"
                        >
                    </div>
                </div>

                <!-- Vendor Additional Fields -->
                <div x-show="selectedRole === 'vendor'" x-transition class="space-y-6 border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-900">Vendor Information</h4>
                    
                    <!-- BVN/NIN -->
                    <div>
                        <label for="bvn_nin" class="block text-sm font-medium text-gray-700 mb-2">
                            BVN or NIN <span class="text-xs text-gray-500">(For verification)</span>
                        </label>
                        <input 
                            id="bvn_nin" 
                            name="bvn_nin" 
                            type="text" 
                            value="{{ old('bvn_nin') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                            placeholder="Enter your BVN or NIN"
                        >
                        @error('bvn_nin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-600">This information is kept secure and used for vendor verification only.</p>
                    </div>

                    <!-- Membership Notice -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Vendor Membership Required</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>After registration, you'll need to pay a one-time membership fee of ₦1,000 to activate your vendor account and start selling.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input 
                            id="terms" 
                            name="terms" 
                            type="checkbox" 
                            required
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                        >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="text-gray-700">
                            I agree to the 
                            <a href="#" class="font-medium text-red-600 hover:text-red-500">Terms of Service</a> 
                            and 
                            <a href="#" class="font-medium text-red-600 hover:text-red-500">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 transform hover:scale-[1.02]"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Create Account
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="font-medium text-red-600 hover:text-red-500 transition-colors">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 