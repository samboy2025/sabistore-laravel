@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Wallets</h1>
                    <p class="text-gray-600 mt-2">Manage individual user wallets and balances</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.wallets.index') }}" 
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Back to Overview
                    </a>
                    <button onclick="openBulkAdjustModal()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Bulk Adjust
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-8">
            <form method="GET" action="{{ route('admin.wallets.users') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search User</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Name or email..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">All Roles</option>
                        <option value="vendor" {{ request('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="buyer" {{ request('role') === 'buyer' ? 'selected' : '' }}>Buyer</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Min Balance</label>
                    <input type="number" 
                           name="min_balance" 
                           value="{{ request('min_balance') }}"
                           placeholder="0.00" 
                           step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Balance</label>
                    <input type="number" 
                           name="max_balance" 
                           value="{{ request('max_balance') }}"
                           placeholder="1000000.00" 
                           step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('admin.wallets.users') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Users ({{ $users->total() }})
                    </h3>
                    <div class="flex items-center space-x-2">
                        <button onclick="selectAll()" 
                                class="text-sm text-blue-600 hover:text-blue-800">
                            Select All
                        </button>
                        <button onclick="selectNone()" 
                                class="text-sm text-gray-600 hover:text-gray-800">
                            Select None
                        </button>
                    </div>
                </div>
            </div>
            
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)" 
                                           class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Transaction</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               name="selected_users[]" 
                                               value="{{ $user->id }}" 
                                               class="user-checkbox rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                               ($user->role === 'vendor' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->formatted_wallet_balance }}</div>
                                        @if($user->wallet)
                                            <div class="text-xs text-gray-500">Updated {{ $user->wallet->updated_at->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $lastTransaction = $user->walletTransactions()->latest()->first();
                                        @endphp
                                        @if($lastTransaction)
                                            <div class="text-sm text-gray-900">{{ $lastTransaction->formatted_amount }}</div>
                                            <div class="text-xs text-gray-500">{{ $lastTransaction->created_at->diffForHumans() }}</div>
                                        @else
                                            <span class="text-sm text-gray-500">No transactions</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('admin.wallets.show', $user) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </a>
                                        <button onclick="openAdjustModal({{ $user->id }}, '{{ $user->name }}', {{ $user->wallet_balance }})" 
                                                class="text-green-600 hover:text-green-900">
                                            Adjust
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                    <p class="text-gray-600">Try adjusting your filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Individual Adjustment Modal -->
<div id="adjustModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" onclick="closeAdjustModal()"></div>
        <div class="relative bg-white rounded-xl p-8 max-w-md w-full">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Adjust Wallet Balance</h3>
            
            <form action="" method="POST" id="adjustForm">
                @csrf
                
                <div id="userInfo" class="bg-gray-50 p-4 rounded-lg mb-6">
                    <!-- User info will be populated by JavaScript -->
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Type</option>
                        <option value="credit">Credit (Add Money)</option>
                        <option value="debit">Debit (Subtract Money)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount (₦)</label>
                    <input type="number" 
                           name="amount" 
                           required 
                           min="0.01" 
                           step="0.01"
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" 
                              required 
                              rows="3" 
                              placeholder="Reason for adjustment..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button type="submit" 
                            class="flex-1 px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Apply Adjustment
                    </button>
                    <button type="button" 
                            onclick="closeAdjustModal()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Adjustment Modal -->
<div id="bulkAdjustModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" onclick="closeBulkAdjustModal()"></div>
        <div class="relative bg-white rounded-xl p-8 max-w-md w-full">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Bulk Wallet Adjustment</h3>
            
            <form action="{{ route('admin.wallets.bulk-adjust') }}" method="POST" id="bulkAdjustForm">
                @csrf
                
                <div id="selectedUsersInfo" class="bg-gray-50 p-4 rounded-lg mb-6">
                    <p class="text-sm text-gray-600">No users selected</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Type</option>
                        <option value="credit">Credit (Add Money)</option>
                        <option value="debit">Debit (Subtract Money)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount per User (₦)</label>
                    <input type="number" 
                           name="amount" 
                           required 
                           min="0.01" 
                           step="0.01"
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" 
                              required 
                              rows="3" 
                              placeholder="Reason for bulk adjustment..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button type="submit" 
                            class="flex-1 px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Apply to All Selected
                    </button>
                    <button type="button" 
                            onclick="closeBulkAdjustModal()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdjustModal(userId, userName, currentBalance) {
    const modal = document.getElementById('adjustModal');
    const form = document.getElementById('adjustForm');
    const userInfo = document.getElementById('userInfo');
    
    form.action = `/admin/wallets/users/${userId}/adjust`;
    userInfo.innerHTML = `
        <div>
            <p class="font-medium text-gray-900">${userName}</p>
            <p class="text-sm text-gray-600">Current Balance: ₦${currentBalance.toLocaleString()}</p>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeAdjustModal() {
    document.getElementById('adjustModal').classList.add('hidden');
}

function openBulkAdjustModal() {
    const selectedUsers = getSelectedUsers();
    
    if (selectedUsers.length === 0) {
        alert('Please select at least one user.');
        return;
    }
    
    const modal = document.getElementById('bulkAdjustModal');
    const selectedUsersInfo = document.getElementById('selectedUsersInfo');
    const form = document.getElementById('bulkAdjustForm');
    
    // Clear existing hidden inputs
    const existingInputs = form.querySelectorAll('input[name="user_ids[]"]');
    existingInputs.forEach(input => input.remove());
    
    // Add selected user IDs as hidden inputs
    selectedUsers.forEach(userId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = userId;
        form.appendChild(input);
    });
    
    selectedUsersInfo.innerHTML = `
        <p class="text-sm text-gray-700 font-medium">${selectedUsers.length} users selected</p>
        <p class="text-xs text-gray-500">Adjustment will be applied to all selected users</p>
    `;
    
    modal.classList.remove('hidden');
}

function closeBulkAdjustModal() {
    document.getElementById('bulkAdjustModal').classList.add('hidden');
}

function getSelectedUsers() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => checkbox.value);
}

function toggleAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    masterCheckbox.checked = true;
}

function selectNone() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    masterCheckbox.checked = false;
}

// Update master checkbox state when individual checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('user-checkbox')) {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const masterCheckbox = document.getElementById('selectAllCheckbox');
        
        masterCheckbox.checked = checkboxes.length === checkedBoxes.length;
        masterCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
    }
});
</script>
@endsection 