@extends('layouts.admin')

@section('title', 'Wallet Management')

@section('content')
<!-- Second Authentication Modal -->
<div id="secondAuthModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Wallet Access Authentication</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Please authenticate to access wallet features
                </p>
            </div>
            <form id="secondAuthForm" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeSecondAuthModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Authenticate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="p-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Wallet Management</h2>
                <p class="text-sm text-gray-500 mt-1">Current Branch: {{ $currentBranch->name ?? 'No Branch Selected' }}</p>
            </div>
            <div class="flex space-x-4">
                <button onclick="showSecondAuthModal()" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                    Re-authenticate
                </button>
                <a href="{{ route('wallet.topup.logout') }}" 
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    Logout Wallet Access
                </a>
            </div>
        </div>
    </div>

    <!-- Main Wallet Content -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white">💳 User Wallets</h3>
                <div class="flex space-x-4">
                    <a href="{{ route('wallet.qr-generator') }}" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                        <i class="fas fa-qrcode mr-2"></i>
                        QR Top-Up
                    </a>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200" onclick="document.getElementById('topUpModal').classList.remove('hidden')">
                        <i class="fas fa-plus mr-2"></i>
                        Top Up Wallet
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Balance</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs {{ number_format($totalBalance, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-exchange-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Today's Transactions</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $todayTransactions }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Branch</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $currentBranch->name ?? 'No Branch' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Transaction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Rs {{ number_format($user->wallet->balance ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($user->wallet && $user->wallet->transactions->isNotEmpty())
                                    <div class="text-xs">
                                        <span class="font-medium">{{ $user->wallet->transactions->first()->type }}</span>
                                        <span class="text-gray-500">Rs {{ number_format($user->wallet->transactions->first()->amount, 2) }}</span>
                                        <br>
                                        <span class="text-gray-400">{{ $user->wallet->transactions->first()->created_at->diffForHumans() }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">No transactions</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <button type="button" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            onclick="topUpUser({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="fas fa-plus mr-1"></i>
                                        Top Up
                                    </button>
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            onclick="showTransactions({{ $user->id }})">
                                        <i class="fas fa-history mr-1"></i>
                                        History
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Up Modal -->
<div id="topUpModal" 
     class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="modalTitle">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full" 
             role="document">
            <form action="{{ route('wallet.top-up') }}" method="POST" id="topUpForm">
                @csrf
                <input type="hidden" name="user_id" id="topUpUserId">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Top Up Wallet</h3>
                        <button type="button" 
                                class="text-gray-400 hover:text-gray-500" 
                                onclick="closeModal()"
                                aria-label="Close modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="userSearch" class="block text-sm font-medium text-gray-700">Search User</label>
                        <input type="text" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               id="userSearch" 
                               placeholder="Type to search users..."
                               autocomplete="off">
                        <div id="searchResults" 
                             class="mt-1 bg-white border border-gray-300 rounded-md shadow-sm hidden"
                             role="listbox"
                             aria-label="Search results"></div>
                    </div>
                    
                    <div>
                        <label for="topUpUserName" class="block text-sm font-medium text-gray-700">Selected User</label>
                        <input type="text" 
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm"
                               id="topUpUserName" 
                               readonly
                               aria-readonly="true">
                    </div>
                    
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rs</span>
                            </div>
                            <input type="number" 
                                   id="amount"
                                   step="0.01" 
                                   min="0.01" 
                                   name="amount" 
                                   class="block w-full pl-7 pr-12 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   required
                                   aria-label="Amount in rupees">
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" 
                                  id="description"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  rows="2" 
                                  placeholder="Optional description for this transaction"></textarea>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                    <button type="button" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            onclick="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-1"></i>
                        Top Up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction History Modal -->
<div id="transactionHistoryModal" 
     class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full" 
             role="document">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Transaction History</h3>
                    <button type="button" 
                            class="text-gray-400 hover:text-gray-500" 
                            onclick="closeTransactionHistoryModal()"
                            aria-label="Close modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performed By</th>
                            </tr>
                        </thead>
                        <tbody id="transactionHistoryBody" class="bg-white divide-y divide-gray-200">
                            <!-- Transaction history will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Session timeout check
        let sessionTimeout;
        const TIMEOUT_DURATION = 10 * 60 * 1000; // 10 minutes in milliseconds
        let isAuthenticated = {{ Session::has('wallet_authenticated') ? 'true' : 'false' }};

        function resetSessionTimeout() {
            if (!isAuthenticated) return;
            
            clearTimeout(sessionTimeout);
            sessionTimeout = setTimeout(() => {
                isAuthenticated = false;
                showSecondAuthModal();
            }, TIMEOUT_DURATION);
        }

        // Reset timeout on user activity
        document.addEventListener('mousemove', resetSessionTimeout);
        document.addEventListener('keypress', resetSessionTimeout);
        document.addEventListener('click', resetSessionTimeout);

        // Initialize timeout only if authenticated
        if (isAuthenticated) {
            resetSessionTimeout();
        }

        // Second Authentication Modal
        function showSecondAuthModal() {
            document.getElementById('secondAuthModal').classList.remove('hidden');
        }

        function closeSecondAuthModal() {
            document.getElementById('secondAuthModal').classList.add('hidden');
        }

        // Handle second authentication form submission
        document.getElementById('secondAuthForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("wallet.topup.login.submit") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Authentication failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Authentication failed. Please try again.');
            }
        });

        // Check session status on page load
        window.addEventListener('load', function() {
            // Only show auth modal if not authenticated
            if (!isAuthenticated) {
                showSecondAuthModal();
            }
        });

        function showTransactions(userId) {
            // Show loading state
            document.getElementById('transactionHistoryBody').innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">Loading...</td></tr>';
            document.getElementById('transactionHistoryModal').classList.remove('hidden');

            // Fetch transaction history
            fetch(`/admin/wallet/transactions/${userId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('transactionHistoryBody');
                    tbody.innerHTML = '';

                    data.transactions.forEach(transaction => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${new Date(transaction.created_at).toLocaleString()}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${transaction.type}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Rs ${parseFloat(transaction.amount).toFixed(2)}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                ${transaction.description || '-'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${transaction.reference_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${transaction.performed_by_name} (${transaction.performed_by_branch_name})
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('transactionHistoryBody').innerHTML = 
                        '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading transactions</td></tr>';
                });
        }

        function closeTransactionHistoryModal() {
            document.getElementById('transactionHistoryModal').classList.add('hidden');
        }
    </script>
@endpush
