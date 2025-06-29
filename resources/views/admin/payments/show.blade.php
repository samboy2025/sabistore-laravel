@extends('layouts.app')

@section('title', 'Payment Details - Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payment Details</h1>
                <p class="text-gray-600 mt-2">View payment information and transaction details</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" 
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                ← Back to Payments
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Payment Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Payment Information</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Reference</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">{{ $payment->reference }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Amount</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">₦{{ number_format($payment->amount, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $payment->type === 'membership' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($payment->type) }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <p class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $payment->status === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $payment->status === 'refunded' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Payment Method</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->payment_method ?: 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Gateway</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->gateway ?: 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Gateway Reference</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">{{ $payment->gateway_reference ?: 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Fee</label>
                        <p class="mt-1 text-sm text-gray-900">₦{{ number_format($payment->fee ?? 0, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Date Created</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->updated_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>
                
                @if($payment->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Update Status -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Update Payment Status</h2>
                
                <form method="POST" action="{{ route('admin.payments.update', $payment) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <select name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="pending" {{ $payment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="success" {{ $payment->status === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ $payment->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $payment->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        
                        <button type="submit" 
                                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Information -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Customer Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->user->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->user->email }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Role</label>
                        <p class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $payment->user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $payment->user->role === 'vendor' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $payment->user->role === 'buyer' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst($payment->user->role) }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Membership</label>
                        <p class="mt-1">
                            @if($payment->user->membership_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('admin.users.show', $payment->user) }}" 
                       class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View User Details →
                    </a>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Actions</h2>
                
                <div class="space-y-3">
                    <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Download Receipt
                    </button>
                    
                    <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Send Notification
                    </button>
                    
                    @if($payment->status === 'success')
                        <button class="w-full text-left px-3 py-2 text-sm text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                            Process Refund
                        </button>
                    @endif
                    
                    <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Are you sure you want to delete this payment record?')"
                                class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            Delete Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 