@extends('layouts.payment')

@section('title', 'Payment Manager')

@section('content')
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', async function() {
        // Initialize payment manager state
        window.paymentManagerState = {
                branchId: {{ $currentBranch->id }},
            isInitialized: false
        };

            console.log('Initializing payment manager state with branch ID:', window.paymentManagerState.branchId);
            
            // Function to get auth token
            async function getAuthToken() {
            const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
            if (!authToken) {
                console.error('No auth token found');
                    // Try to refresh the token
                    try {
                        const response = await fetch('/api/refresh-token', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            throw new Error('Token refresh failed');
                        }

                        const data = await response.json();
                        if (!data.token) {
                            throw new Error('No token in response');
                        }

                        // Update the meta tag with the new token
                        const metaTag = document.querySelector('meta[name="auth-token"]');
                        if (metaTag) {
                            metaTag.setAttribute('content', data.token);
                            console.log('Token refreshed successfully');
                            return data.token;
                        }
                    } catch (error) {
                        console.error('Token refresh failed:', error);
                        window.location.href = '{{ route("login") }}';
                        return null;
                    }
                }
                return authToken;
            }

            // Function to check session status
            async function checkSessionStatus() {
                try {
                    const authToken = await getAuthToken();
                    if (!authToken) {
                return;
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        console.error('No branch ID found');
                        return;
                    }

                    const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${authToken}`,
                            'X-Branch-ID': branchId
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        if (response.status === 401 || response.status === 403) {
                            const newToken = await getAuthToken();
                            if (newToken) {
                                return checkSessionStatus();
                            }
                        }
                        return;
                    }

                    const data = await response.json();
                    updateSessionUI(data.session);
                } catch (error) {
                    console.error('Error checking session status:', error);
                }
            }

            // Function to update session UI
            function updateSessionUI(session) {
                const sessionInfo = document.getElementById('sessionInfo');
                const openSessionBtn = document.getElementById('openSessionBtn');
                const closeSessionBtn = document.getElementById('closeSessionBtn');

                if (session) {
                    // Session is open
                    if (sessionInfo) {
                        sessionInfo.textContent = `Active session by ${session.opened_by} (${new Date(session.opened_at).toLocaleTimeString()})`;
                    }
                    if (openSessionBtn) openSessionBtn.classList.add('hidden');
                    if (closeSessionBtn) closeSessionBtn.classList.remove('hidden');
                    
                    // Update cash drawer status
                    updateCashDrawerStatus();
                } else {
                    // No active session
                    if (sessionInfo) {
                        sessionInfo.textContent = 'No active session';
                    }
                    if (openSessionBtn) openSessionBtn.classList.remove('hidden');
                    if (closeSessionBtn) closeSessionBtn.classList.add('hidden');
                }
            }

            // Function to start polling
            function startPolling() {
                // Poll for session status every 5 seconds
                setInterval(async () => {
                    try {
                        const authToken = await getAuthToken();
                        if (!authToken) {
                            return;
                        }

                        const branchId = window.paymentManagerState.branchId;
                        if (!branchId) {
                            console.error('No branch ID found');
                            return;
                        }

                        const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': `Bearer ${authToken}`,
                                'X-Branch-ID': branchId
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            if (response.status === 401 || response.status === 403) {
                                const newToken = await getAuthToken();
                                if (newToken) {
                                    return checkSessionStatus();
                                }
                            }
                            return;
                        }

                        const data = await response.json();
                        updateSessionUI(data.session);
                    } catch (error) {
                        console.error('Error polling session status:', error);
                    }
                }, 5000);
            }

            // Initialize the payment manager
            window.paymentManagerState.isInitialized = true;
            
            // Check initial session status
            await checkSessionStatus();
            // Start polling
            startPolling();
        });
    </script>

<!-- Session Blur Overlay -->
<div id="sessionBlurOverlay" class="fixed inset-0 bg-white bg-opacity-75 backdrop-blur-sm z-50 hidden">
    <div class="flex flex-col items-center justify-center h-full">
        <div class="text-center p-8 bg-white rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Cash Drawer Session Required</h2>
            <p class="text-gray-600 mb-6">Please open a cash drawer session to continue.</p>
            <button id="overlayOpenSessionBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Open Session
            </button>
        </div>
    </div>
</div>

<div class="min-h-screen bg-gray-100">
    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Session Management Controls -->
            <div class="flex justify-end items-center space-x-4 mb-4">
                <!-- Open Session Button -->
                <button id="openSessionBtn" class="px-4 py-2 text-sm bg-green-100 text-green-800 rounded hover:bg-green-200">
                    Open Session
                </button>
                <!-- Close Session Button -->
                <button id="closeSessionBtn" class="px-4 py-2 text-sm bg-red-100 text-red-800 rounded hover:bg-red-200 hidden">
                    Close Session
                </button>
                <!-- Session Status -->
                <div id="sessionStatus" class="text-sm text-gray-600 bg-white px-4 py-2 rounded border border-gray-200">
                    <span id="sessionInfo">No active session</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6">

                <!-- LEFT PANEL: POS, ONLINE, HISTORY -->
                <div class="col-span-1 space-y-6">

                    <!-- POS Orders -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-lg font-semibold mb-2">POS Orders</h2>
                        <div class="flex space-x-2 mb-2">
                            <button class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded">Paid</button>
                            <button class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded">Unpaid</button>
                        </div>
                        <div id="posOrdersList" class="max-h-[200px] overflow-y-auto space-y-2">
                            @forelse($posOrders as $order)
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="{{ $order->id }}">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="font-medium">Order #{{ $order->id }}</p>
                                            <p class="text-xs text-gray-500">
                                                @if($order->order_type === 'dine_in' && $order->table)
                                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium">
                                                        Table {{ $order->table->name }} ({{ $order->table->capacity }} seats)
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-medium">
                                                        {{ ucfirst($order->order_type) }}
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600">Rs. {{ number_format($order->total, 2) }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded {{ $order->payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">No POS orders.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Online Orders -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-lg font-semibold mb-2">Online Orders</h2>
                        <div class="flex space-x-2 mb-2">
                            <button class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded">Paid</button>
                            <button class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded">Unpaid</button>
                        </div>
                        <div id="onlineOrdersList" class="max-h-[200px] overflow-y-auto space-y-2">
                            @forelse($onlineOrders as $order)
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="{{ $order->id }}">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="font-medium">Order #{{ $order->id }}</p>
                                            <p class="text-xs text-gray-500">{{ $order->user->name ?? 'Guest' }}</p>
                                            <p class="text-sm text-gray-600">${{ number_format($order->total, 2) }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded {{ $order->payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">No online orders.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Order History -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-lg font-semibold mb-2">Order History</h2>
                        <div class="space-y-2 max-h-[200px] overflow-y-auto">
                            @forelse($orderHistory as $order)
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="{{ $order->id }}">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="font-medium">Order #{{ $order->id }}</p>
                                            <p class="text-xs text-gray-500">
                                                @if($order->order_type === 'dine_in' && $order->table)
                                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium">
                                                        Table {{ $order->table->name }} ({{ $order->table->capacity }} seats)
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-medium">
                                                        {{ ucfirst($order->order_type) }}
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600">Rs. {{ number_format($order->total, 2) }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded {{ $order->payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">No order history.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

                <!-- RIGHT PANEL: ITEM DETAILS -->
                <div class="col-span-2 bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Order Details</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="border px-4 py-2">Item</th>
                                    <th class="border px-4 py-2 text-right">Qty</th>
                                    <th class="border px-4 py-2 text-right">Price</th>
                                    <th class="border px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be loaded dynamically via JS -->
                            </tbody>
                            <tfoot class="text-right">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 font-semibold text-right">Subtotal:</td>
                                    <td id="subtotal" class="px-4 py-2 font-semibold">$0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 font-semibold text-right">Total:</td>
                                    <td id="total" class="px-4 py-2 font-semibold">$0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Payment Form -->
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Process Payment</h3>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <form id="paymentForm" class="space-y-6">
                                @csrf
                                <input type="hidden" name="order_id" id="selectedOrderId">
                                <input type="hidden" name="amount" id="paymentAmount">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Payment Method Selection -->
                                    <div>
                                        <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                        <select id="paymentMethod" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="wallet">Wallet</option>
                                            <option value="khalti">Khalti</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Payment Amount -->
                                    <div>
                                        <label for="paymentAmountDisplay" class="block text-sm font-medium text-gray-700">Payment Amount</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text" id="paymentAmountDisplay" class="block w-full px-3 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cash Payment Fields -->
                                <div id="cashPaymentFields" class="hidden space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount Received</label>
                                        <input type="number" id="amountReceived" name="amount_received" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Change</label>
                                        <input type="number" id="changeAmount" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                                    </div>
                                </div>
                                
                                <!-- Currency Denominations -->
                                <div id="currencyDenominationsContainer" class="hidden">
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Currency Received -->
                                        <div class="border rounded-lg p-4">
                                            <div class="mt-4">
                                                <h4 class="font-medium text-base text-gray-600 mb-3">Currency Received</h4>
                                                <div class="space-y-3">
                                                    @php
                                                        $denominations = [
                                                            '1000' => 'Rs. 1000',
                                                            '500' => 'Rs. 500',
                                                            '100' => 'Rs. 100',
                                                            '50' => 'Rs. 50',
                                                            '20' => 'Rs. 20',
                                                            '10' => 'Rs. 10',
                                                            '5' => 'Rs. 5',
                                                            '1' => 'Rs. 1'
                                                        ];
                                                        $defaultDenominations = [
                                                            '1000' => 0,
                                                            '500' => 4,
                                                            '100' => 20,
                                                            '50' => 20,
                                                            '20' => 20,
                                                            '10' => 30,
                                                            '5' => 25,
                                                            '1' => 10
                                                        ];
                                                    @endphp
                                                    @foreach($denominations as $value => $label)
                                                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                                            <div class="flex items-center space-x-2">
                                                                <button type="button" 
                                                                        onclick="decrementDenomination('currency_received[{{ $value }}]')"
                                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                                    </svg>
                                                                </button>
                                                                <input type="number" 
                                                                       name="currency_received[{{ $value }}]" 
                                                                       min="0" 
                                                                       class="w-16 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                                       placeholder="0"
                                                                       onchange="updateTotalReceived()"
                                                                       oninput="updateTotalReceived()">
                                                                <button type="button"
                                                                        onclick="incrementDenomination('currency_received[{{ $value }}]')"
                                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="border-t pt-3 mt-3">
                                                        <div class="flex justify-between items-center bg-indigo-50 p-3 rounded-lg">
                                                            <span class="text-sm font-medium text-indigo-700">Total Received</span>
                                                            <span class="text-lg font-semibold text-indigo-900" id="totalReceived">Rs. 0.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Change Given -->
                                        <div id="changeDenominations" class="border rounded-lg p-4">
                                            <h4 class="font-medium mb-3">Change Given</h4>
                                            <div class="space-y-2">
                                                @foreach($denominations as $value => $label)
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm text-gray-600">{{ $label }}</span>
                                                        <input type="number" name="change_given[{{ $value }}]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                                    </div>
                                                @endforeach
                                                <div class="border-t pt-2 mt-2">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm font-medium text-gray-600">Total Change</span>
                                                        <span class="text-lg font-semibold" id="totalChange">Rs. 0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Payment Fields -->
                                <div id="cardPaymentFields" class="hidden space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                                        <input type="text" id="referenceNumber" name="reference_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <!-- Wallet Payment Fields -->
                                <div id="walletPaymentFields" class="hidden space-y-4">
                                    <div>
                                        <label for="wallet_number" class="block text-sm font-medium text-gray-700">Wallet Number</label>
                                        <input type="text" name="wallet_number" id="wallet_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Khalti Payment Fields -->
                                <div id="khaltiPaymentFields" class="hidden space-y-4">
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    Scan the QR code below with your Khalti app to complete the payment.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-center">
                                        <div id="khaltiQRCode" class="w-64 h-64 bg-white p-4 rounded-lg shadow-lg">
                                            <!-- QR code will be inserted here -->
                                        </div>
                                    </div>
                                    <div class="text-center mt-4">
                                        <p class="text-sm text-gray-600">Payment Status: <span id="khaltiPaymentStatus">Waiting for payment...</span></p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Process Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Success Modal -->
            <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 id="successModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2"></h3>
                        <div class="mt-2 px-7 py-3">
                            <p id="successModalMessage" class="text-sm text-gray-500"></p>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button id="successModalClose" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Modal -->
            <div id="errorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 id="errorModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2"></h3>
                        <div class="mt-2 px-7 py-3">
                            <p id="errorModalMessage" class="text-sm text-gray-500"></p>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button id="errorModalClose" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 px-4 flex justify-between items-center">
                <div class="relative">
                    <button id="cashDrawerBtn" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>Cash Drawer</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="cashDrawerDropdown" class="absolute bottom-full left-0 mb-2 w-[600px] bg-white rounded-lg shadow-lg border border-gray-200 hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold">Cash Drawer Status</h3>
                                <button id="closeCashDrawer" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-8">
                                <!-- Starting Amount -->
                                <div>
                                    <h4 class="font-medium text-base text-gray-600 mb-3">Starting Amount</h4>
                                    <table class="w-full text-base">
                                        <thead>
                                            <tr class="border-b-2 border-gray-200">
                                                <th class="text-left py-2">Denomination</th>
                                                <th class="text-right py-2">Count</th>
                                                <th class="text-right py-2">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="startingDenominations">
                                            @foreach($denominations as $value => $label)
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2">{{ $label }}</td>
                                                    <td class="text-right py-2">
                                                        <span class="starting-denomination" data-denomination="{{ $value }}">0</span>
                                                    </td>
                                                    <td class="text-right py-2">
                                                        <span class="starting-amount" data-denomination="{{ $value }}">Rs. 0.00</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                <tfoot>
                                    <tr class="font-semibold">
                                        <td class="py-2">Total</td>
                                        <td class="text-right py-2" id="startingTotalCount">0</td>
                                        <td class="text-right py-2" id="startingTotalAmount">Rs. 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Current Amount -->
                        <div>
                            <h4 class="font-medium text-base text-gray-600 mb-3">Current Amount</h4>
                            <table class="w-full text-base">
                                <thead>
                                    <tr class="border-b-2 border-gray-200">
                                        <th class="text-left py-2">Denomination</th>
                                        <th class="text-right py-2">Count</th>
                                        <th class="text-right py-2">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="currentDenominations">
                                    @foreach($denominations as $value => $label)
                                        <tr class="border-b border-gray-100">
                                            <td class="py-2">{{ $label }}</td>
                                            <td class="text-right py-2">
                                                <span class="current-denomination" data-denomination="{{ $value }}">0</span>
                                            </td>
                                            <td class="text-right py-2">
                                                <span class="current-amount" data-denomination="{{ $value }}">Rs. 0.00</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="font-semibold">
                                        <td class="py-2">Total</td>
                                        <td class="text-right py-2" id="currentTotalCount">0</td>
                                        <td class="text-right py-2" id="currentTotalAmount">Rs. 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Modal -->
            <div id="sessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-4">
                            <h3 id="sessionModalTitle" class="text-xl font-semibold text-gray-900"></h3>
                            <button id="closeSessionModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <!-- Denominations -->
                            <div>
                                <h4 class="font-medium text-base text-gray-600 mb-3">Denominations</h4>
                                <div class="space-y-3">
                                    @php
                                        $denominations = [
                                            '1000' => 'Rs. 1000',
                                            '500' => 'Rs. 500',
                                            '100' => 'Rs. 100',
                                            '50' => 'Rs. 50',
                                            '20' => 'Rs. 20',
                                            '10' => 'Rs. 10',
                                            '5' => 'Rs. 5',
                                            '1' => 'Rs. 1'
                                        ];
                                        $defaultDenominations = [
                                            '1000' => 0,
                                            '500' => 4,
                                            '100' => 20,
                                            '50' => 20,
                                            '20' => 20,
                                            '10' => 30,
                                            '5' => 25,
                                            '1' => 10
                                        ];
                                    @endphp
                                    @foreach($denominations as $value => $label)
                                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" 
                                                        onclick="decrementSessionDenomination('{{ $value }}')"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                                <input type="number" 
                                                       id="session_denomination_{{ $value }}"
                                                       min="0" 
                                                       class="w-16 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                       placeholder="0"
                                                       onchange="updateSessionTotal()"
                                                       oninput="updateSessionTotal()">
                                                <button type="button"
                                                        onclick="incrementSessionDenomination('{{ $value }}')"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="border-t pt-3 mt-3">
                                        <div class="flex justify-between items-center bg-indigo-50 p-3 rounded-lg">
                                            <span class="text-sm font-medium text-indigo-700">Total Amount</span>
                                            <span class="text-lg font-semibold text-indigo-900" id="sessionTotalAmount">Rs. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <h4 class="font-medium text-base text-gray-600 mb-3">Notes</h4>
                                <textarea id="sessionNotes" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add any notes here..."></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button id="cancelSessionBtn" class="px-4 py-2 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                                Cancel
                            </button>
                            <button id="confirmSessionBtn" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            // Format currency helper function
            function formatCurrency(amount) {
                if (amount === undefined || amount === null) return 'Rs. 0.00';
                return 'Rs. ' + parseFloat(amount).toFixed(2);
            }

            // Denomination helper functions
            function incrementDenomination(inputName) {
                const input = document.querySelector(`input[name="${inputName}"]`);
                if (input) {
                    const currentValue = parseInt(input.value) || 0;
                    input.value = currentValue + 1;
                    updateTotalReceived();
                }
            }

            function decrementDenomination(inputName) {
                const input = document.querySelector(`input[name="${inputName}"]`);
                if (input) {
                    const currentValue = parseInt(input.value) || 0;
                    if (currentValue > 0) {
                        input.value = currentValue - 1;
                        updateTotalReceived();
                    }
                }
            }

            function updateTotalReceived() {
                let total = 0;
                const inputs = document.querySelectorAll('input[name^="currency_received"]');
                inputs.forEach(input => {
                    const value = parseInt(input.name.match(/\[(\d+)\]/)[1]);
                    const count = parseInt(input.value) || 0;
                    total += value * count;
                });
                
                // Update total received display
                const totalReceivedElement = document.getElementById('totalReceived');
                if (totalReceivedElement) {
                    totalReceivedElement.textContent = formatCurrency(total);
                }
                
                // Update amount received field
                const amountReceived = document.getElementById('amountReceived');
                if (amountReceived) {
                    amountReceived.value = total;
                }

                // Calculate and update change
                calculateChange();
            }

            function calculateChange() {
                const total = parseFloat(document.getElementById('paymentAmount').value) || 0;
                const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
                const change = amountReceived - total;
                
                // Update change amount field
                const changeAmount = document.getElementById('changeAmount');
                if (changeAmount) {
                    changeAmount.value = change >= 0 ? change.toFixed(2) : '0.00';
                }

                // Calculate change denominations
                if (change >= 0) {
                    calculateChangeDenominations(change);
                } else {
                    // Reset change denominations if amount received is less than total
                    const denominationInputs = document.querySelectorAll('input[name^="change_given"]');
                    denominationInputs.forEach(input => {
                        input.value = '0';
                    });
                    const totalChangeElement = document.getElementById('totalChange');
                    if (totalChangeElement) {
                        totalChangeElement.textContent = formatCurrency(0);
                    }
                }
            }

            function calculateChangeDenominations(change) {
                const denominations = [1000, 500, 100, 50, 20, 10, 4, 1];
                let remainingChange = Math.round(change * 100) / 100; // Round to 2 decimal places

                denominations.forEach(denomination => {
                    const input = document.querySelector(`input[name="change_given[${denomination}]"]`);
                    if (input) {
                        const count = Math.floor(remainingChange / denomination);
                        input.value = count;
                        remainingChange -= count * denomination;
                    }
                });

                // Update total change display
                const totalChange = document.getElementById('totalChange');
                if (totalChange) {
                    totalChange.textContent = formatCurrency(change);
                }
            }

            function updateCurrentDenominations(type) {
                const branchId = window.paymentManagerState.branchId;
                if (!branchId) {
                    console.error('No branch ID found');
                    return;
                }

                // Get current denominations from the display
                const currentDenominations = {};
                const denominationElements = document.querySelectorAll('.current-denomination');
                denominationElements.forEach(element => {
                    const value = element.getAttribute('data-denomination');
                    const count = parseInt(element.textContent) || 0;
                    currentDenominations[value] = count;
                });

                // Update denominations based on type
                if (type === 'received') {
                    // Add received denominations to current denominations
                    const receivedInputs = document.querySelectorAll('input[name^="currency_received"]');
                    receivedInputs.forEach(input => {
                        const value = input.name.match(/\[(\d+)\]/)[1];
                        const count = parseInt(input.value) || 0;
                        currentDenominations[value] = (currentDenominations[value] || 0) + count;
                    });
                } else if (type === 'change') {
                    // Subtract change denominations from current denominations
                    const changeInputs = document.querySelectorAll('input[name^="change_given"]');
                    changeInputs.forEach(input => {
                        const value = input.name.match(/\[(\d+)\]/)[1];
                        const count = parseInt(input.value) || 0;
                        currentDenominations[value] = (currentDenominations[value] || 0) - count;
                    });
                }

                // Update display
                Object.entries(currentDenominations).forEach(([value, count]) => {
                    const element = document.querySelector(`.current-denomination[data-denomination="${value}"]`);
                    if (element) {
                        element.textContent = count;
                        const amountElement = document.querySelector(`.current-amount[data-denomination="${value}"]`);
                        if (amountElement) {
                            amountElement.textContent = formatCurrency(count * value);
                        }
                    }
                });

                // Update totals
                const totalCount = Object.values(currentDenominations).reduce((sum, count) => sum + count, 0);
                const totalAmount = Object.entries(currentDenominations).reduce((sum, [value, count]) => sum + (value * count), 0);

                const totalCountElement = document.getElementById('currentTotalCount');
                const totalAmountElement = document.getElementById('currentTotalAmount');

                if (totalCountElement) totalCountElement.textContent = totalCount;
                if (totalAmountElement) totalAmountElement.textContent = formatCurrency(totalAmount);

                // Save to server
                saveCurrentDenominations(currentDenominations).catch(error => {
                    console.error('Error saving denominations:', error);
                    showErrorModal('Error', 'Failed to update denominations. Please try again.');
                });
            }

            async function saveCurrentDenominations(denominations) {
                try {
                    const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                    console.log('Auth token found:', authToken ? 'Yes' : 'No'); // Debug log

                    if (!authToken) {
                        showErrorModal('Authentication Error', 'Please refresh the page or log in again.');
                        throw new Error('No auth token found');
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        showErrorModal('Error', 'Branch ID not found. Please refresh the page.');
                        throw new Error('No branch ID found');
                    }

                    console.log('Sending request to update denominations:', {
                        branchId,
                        denominations,
                        authToken: authToken.substring(0, 10) + '...' // Log partial token for debugging
                    });

                    const response = await fetch('/api/admin/cash-drawer/update-denominations', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: JSON.stringify({
                            branch_id: branchId,
                            denominations: denominations
                        })
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        console.error('Server response error:', error);
                        throw new Error(error.message || 'Failed to update denominations');
                    }

                    const data = await response.json();
                    console.log('Denominations updated successfully:', data);
                    return data;
                } catch (error) {
                    console.error('Error updating denominations:', error);
                    showErrorModal('Error', error.message || 'Failed to update denominations');
                    throw error;
                }
            }

            // Error Modal Functions
            function showErrorModal(title, message) {
                const modal = document.getElementById('errorModal');
                const titleElement = document.getElementById('errorModalTitle');
                const messageElement = document.getElementById('errorModalMessage');
                
                if (titleElement) titleElement.textContent = title;
                if (messageElement) messageElement.textContent = message;
                if (modal) modal.classList.remove('hidden');
            }

            function showSuccessModal(title, message) {
                const modal = document.getElementById('successModal');
                const titleElement = document.getElementById('successModalTitle');
                const messageElement = document.getElementById('successModalMessage');
                
                if (titleElement) titleElement.textContent = title;
                if (messageElement) messageElement.textContent = message;
                if (modal) modal.classList.remove('hidden');
            }

            // Close modal functions
            document.getElementById('errorModalClose')?.addEventListener('click', () => {
                document.getElementById('errorModal').classList.add('hidden');
            });

            document.getElementById('successModalClose')?.addEventListener('click', () => {
                document.getElementById('successModal').classList.add('hidden');
            });

            // Initialize state
            if (typeof window.paymentManagerState === 'undefined') {
                const urlParams = new URLSearchParams(window.location.search);
                const branchId = document.querySelector('meta[name="branch-id"]')?.getAttribute('content') || urlParams.get('branch');
                
                console.log('Initializing payment manager state with branch ID:', branchId);
                
                window.paymentManagerState = {
                    selectedOrderId: null,
                    isPolling: false,
                    branchId: branchId,
                    paymentViewerWindow: null,
                    isInitialized: false,
                    isOpeningWindow: false,
                    lastOrderUpdate: null
                };
            }

            // Start polling for updates
            function startPolling() {
                if (window.paymentManagerState.isPolling) {
                    return;
                }

                window.paymentManagerState.isPolling = true;
                window.poller = setInterval(async () => {
                    try {
                        const orderId = window.paymentManagerState.selectedOrderId;
                        if (!orderId) return;

                        const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                        if (!authToken) return;

                        const branchId = window.paymentManagerState.branchId;
                        if (!branchId) return;

                        const response = await fetch(`/api/admin/orders/${orderId}?branch=${branchId}&include=items,order_items,products`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': `Bearer ${authToken}`,
                                'X-Branch-ID': branchId
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) return;

                        const order = await response.json();
                        
                        // Check if order has been updated
                        const orderUpdateKey = JSON.stringify({
                            id: order.id,
                            total: order.total,
                            payment_status: order.payment_status,
                            updated_at: order.updated_at
                        });

                        if (orderUpdateKey !== window.paymentManagerState.lastOrderUpdate) {
                            window.paymentManagerState.lastOrderUpdate = orderUpdateKey;
                            
                            // Update UI
                            updateOrderDetails(order);

                            // Update payment viewer if it exists
                            if (window.paymentManagerState.paymentViewerWindow && !window.paymentManagerState.paymentViewerWindow.closed) {
                                window.paymentManagerState.paymentViewerWindow.postMessage({
                                    type: 'UPDATE_ORDER',
                                    order: order
                                }, window.location.origin);
                            }
                        }
                    } catch (error) {
                        console.error('Error polling for updates:', error);
                    }
                }, 5000); // Poll every 5 seconds
            }

            // Update order details
            function updateOrderDetails(order) {
                if (!order) {
                    console.error('No order data provided');
                    return;
                }

                try {
                    console.log('Updating order details:', order); // Debug log

                    // Update order ID in hidden input
                    const selectedOrderIdInput = document.getElementById('selectedOrderId');
                    if (selectedOrderIdInput) selectedOrderIdInput.value = order.id;
                    
                    // Update payment amount
                    const paymentAmountInput = document.getElementById('paymentAmount');
                    const paymentAmountDisplay = document.getElementById('paymentAmountDisplay');
                    if (paymentAmountInput) paymentAmountInput.value = order.total || 0;
                    if (paymentAmountDisplay) paymentAmountDisplay.value = formatCurrency(order.total);
                    
                    // Update items table
                    const tbody = document.getElementById('itemsTableBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        
                        if (order.items && Array.isArray(order.items)) {
                            console.log('Order items data:', order.items); // Debug log
                            order.items.forEach(item => {
                                console.log('Processing item:', item); // Debug log for each item
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td class="border px-4 py-2">${item.product?.name || item.item_name || 'Unknown Item'}</td>
                                    <td class="border px-4 py-2 text-right">${item.quantity || 0}</td>
                                    <td class="border px-4 py-2 text-right">${formatCurrency(item.price)}</td>
                                    <td class="border px-4 py-2 text-right">${formatCurrency(item.subtotal)}</td>
                                `;
                                tbody.appendChild(row);
                            });
                        } else {
                            console.log('No items array found in order:', order); // Debug log if items array is missing
                        }
                    }
                    
                    // Update totals
                    const subtotalElement = document.getElementById('subtotal');
                    const totalElement = document.getElementById('total');
                    if (subtotalElement) subtotalElement.textContent = formatCurrency(order.subtotal);
                    if (totalElement) totalElement.textContent = formatCurrency(order.total);

                    // Update payment status
                    const paymentStatus = document.getElementById('paymentStatus');
                    if (paymentStatus) {
                        paymentStatus.innerHTML = `
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 rounded-full ${order.payment_status === 'paid' ? 'bg-green-400' : 'bg-yellow-400 animate-pulse'}"></div>
                                <span class="text-gray-600">${order.payment_status === 'paid' ? 'Payment Complete' : 'Waiting for payment...'}</span>
                            </div>
                        `;
                    }

                    // Update payment viewer if it exists
                    if (window.paymentManagerState.paymentViewerWindow && !window.paymentManagerState.paymentViewerWindow.closed) {
                        console.log('Sending order update to payment viewer:', order); // Debug log
                        window.paymentManagerState.paymentViewerWindow.postMessage({
                            type: 'UPDATE_ORDER',
                            order: order
                        }, window.location.origin);
                    }

                } catch (error) {
                    console.error('Error updating order details:', error);
                    showErrorModal('Error', 'Failed to update order details. Please try again.');
                }
            }

            // Initialize payment manager
            async function initializePaymentManager() {
                try {
                    // Check if already initialized
                    if (window.paymentManagerState.isInitialized) {
                        console.log('Payment manager already initialized');
                        return;
                    }

                    const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                    if (!authToken) {
                        console.error('No auth token found');
                        return;
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        console.error('No branch ID found');
                        return;
                    }

                    // Check for order ID in URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const orderId = urlParams.get('order');

                    if (orderId) {
                        await selectOrder(orderId);
                    }

                    // Start polling for updates
                    startPolling();

                    // Mark as initialized
                    window.paymentManagerState.isInitialized = true;

                    // Automatically open payment viewer
                    await openCustomerView();

                    // Add cleanup on page unload
                    window.addEventListener('beforeunload', () => {
                        if (window.paymentManagerState.paymentViewerWindow && !window.paymentManagerState.paymentViewerWindow.closed) {
                            window.paymentManagerState.paymentViewerWindow.close();
                        }
                        if (window.poller) {
                            clearInterval(window.poller);
                        }
                    });

                } catch (error) {
                    console.error('Error initializing payment manager:', error);
                    showErrorModal('Error', 'Failed to initialize payment manager. Please refresh the page.');
                }
            }

            // Open customer view
            async function openCustomerView() {
                try {
                    // Prevent multiple window opens
                    if (window.paymentManagerState.isOpeningWindow) {
                        console.log('Window open already in progress');
                        return;
                    }

                    window.paymentManagerState.isOpeningWindow = true;

                    const orderId = window.paymentManagerState.selectedOrderId;
                    console.log('Opening customer view with order ID:', orderId);

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        showErrorModal('Error', 'Branch ID not found');
                        window.paymentManagerState.isOpeningWindow = false;
                        return;
                    }

                    console.log('Opening payment viewer with:', { orderId, branchId });

                    // Check if window already exists and is not closed
                    if (window.paymentManagerState.paymentViewerWindow && !window.paymentManagerState.paymentViewerWindow.closed) {
                        window.paymentManagerState.paymentViewerWindow.focus();
                        window.paymentManagerState.isOpeningWindow = false;
                        return;
                    }

                    // Calculate window size and position
                    const width = 400;
                    const height = 600;
                    const left = (window.screen.width - width) / 2;
                    const top = (window.screen.height - height) / 2;

                    // Open new window with both order and branch IDs
                    const viewerUrl = `/customer/payment-viewer?order=${orderId || ''}&branch=${branchId}`;
                    console.log('Opening payment viewer URL:', viewerUrl);

                    const viewerWindow = window.open(
                        viewerUrl,
                        'paymentViewer',
                        `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
                    );

                    if (viewerWindow) {
                        window.paymentManagerState.paymentViewerWindow = viewerWindow;
                        
                        // Add event listener for window close
                        viewerWindow.addEventListener('beforeunload', () => {
                            window.paymentManagerState.paymentViewerWindow = null;
                        });
                    }

                } catch (error) {
                    console.error('Error opening customer view:', error);
                    showErrorModal('Error', 'Failed to open customer view. Please try again.');
                } finally {
                    window.paymentManagerState.isOpeningWindow = false;
                }
            }

            // Select order
            async function selectOrder(orderId) {
                try {
                    if (!orderId) {
                        console.error('No order ID provided');
                        return;
                    }

                    console.log('Selecting order:', orderId);

                    const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                    if (!authToken) {
                        console.error('No auth token found');
                        return;
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        console.error('No branch ID found');
                        return;
                    }

                    console.log('Fetching order details for:', orderId); // Debug log

                    const response = await fetch(`/api/admin/orders/${orderId}?branch=${branchId}&include=items,order_items,products`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${authToken}`,
                            'X-Branch-ID': branchId
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch order details');
                    }

                    const order = await response.json();
                    console.log('Raw API response:', order); // Debug log for raw API response
                    
                    // Update state with the selected order ID
                    window.paymentManagerState.selectedOrderId = orderId;
                    console.log('Updated payment manager state:', window.paymentManagerState);

                    // Update UI
                    updateOrderDetails(order);

                    // Update payment viewer if it exists
                    if (window.paymentManagerState.paymentViewerWindow && !window.paymentManagerState.paymentViewerWindow.closed) {
                        console.log('Sending order update to payment viewer:', order); // Debug log
                        window.paymentManagerState.paymentViewerWindow.postMessage({
                            type: 'UPDATE_ORDER',
                            order: order
                        }, window.location.origin);
                    }

                } catch (error) {
                    console.error('Error selecting order:', error);
                    showErrorModal('Error', 'Failed to load order details. Please try again.');
                }
            }

            // Handle messages from payment viewer
            window.addEventListener('message', function(event) {
                if (event.origin !== window.location.origin) return;

                const data = event.data;
                if (!data || !data.type) return;

                console.log('Received message from viewer:', data);

                switch (data.type) {
                    case 'UPDATE_PAYMENT_METHOD':
                        if (data.method) {
                            console.log('Updating payment method from viewer:', data.method);
                            const paymentMethodSelect = document.getElementById('paymentMethod');
                            if (paymentMethodSelect) {
                                paymentMethodSelect.value = data.method;
                                // Trigger change event to update UI
                                const event = new Event('change');
                                paymentMethodSelect.dispatchEvent(event);
                            }
                        }
                        break;
                }
            });

            // Add event listeners for payment method selection
            document.getElementById('paymentMethod')?.addEventListener('change', function() {
                const method = this.value;
                const cashFields = document.getElementById('cashPaymentFields');
                const cardFields = document.getElementById('cardPaymentFields');
                const walletFields = document.getElementById('walletPaymentFields');
                const khaltiFields = document.getElementById('khaltiPaymentFields');
                const currencyDenominationsContainer = document.getElementById('currencyDenominationsContainer');
                const amountReceived = document.getElementById('amountReceived');
                const changeAmount = document.getElementById('changeAmount');

                // Hide all fields first
                cashFields.classList.add('hidden');
                cardFields.classList.add('hidden');
                walletFields.classList.add('hidden');
                khaltiFields.classList.add('hidden');
                currencyDenominationsContainer.classList.add('hidden');

                // Show relevant fields based on payment method
                if (method === 'cash') {
                    cashFields.classList.remove('hidden');
                    currencyDenominationsContainer.classList.remove('hidden');
                    amountReceived.required = true;
                } else if (method === 'card') {
                    cardFields.classList.remove('hidden');
                    amountReceived.required = false;
                } else if (method === 'wallet') {
                    walletFields.classList.remove('hidden');
                    amountReceived.required = false;
                } else if (method === 'khalti') {
                    khaltiFields.classList.remove('hidden');
                    amountReceived.required = false;
                }

                // Reset change amount
                changeAmount.value = '';

                // Update payment viewer if it exists
                if (window.paymentManagerState.paymentViewerWindow && !window.paymentManagerState.paymentViewerWindow.closed) {
                    window.paymentManagerState.paymentViewerWindow.postMessage({
                        type: 'UPDATE_PAYMENT_METHOD',
                        method: method
                    }, window.location.origin);
                }
            });

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', () => {
                initializePaymentManager();

                // Add click event listeners to all order elements
                document.querySelectorAll('[data-order-id]').forEach(row => {
                    row.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        if (orderId) {
                            selectOrder(orderId);
                        }
                    });
                });
            });

            // Add reload orders function
            async function reloadOrders() {
                try {
                    console.log('Starting to reload orders...');
                    
                    const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                    if (!authToken) {
                        console.error('No auth token found');
                        showErrorModal('Error', 'Authentication token not found. Please refresh the page.');
                        return;
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        console.error('No branch ID found');
                        showErrorModal('Error', 'Branch ID not found. Please refresh the page.');
                        return;
                    }

                    console.log('Using branch ID:', branchId);

                    // Fetch POS orders
                    console.log('Fetching POS orders...');
                    const posResponse = await fetch(`/api/admin/orders?branch=${branchId}&type=pos&include=items,order_items,products`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${authToken}`,
                            'X-Branch-ID': branchId
                        },
                        credentials: 'same-origin'
                    });

                    if (!posResponse.ok) {
                        console.error('Failed to fetch POS orders:', posResponse.status);
                        const errorText = await posResponse.text();
                        console.error('Error response:', errorText);
                        throw new Error('Failed to fetch POS orders');
                    }

                    const posOrdersData = await posResponse.json();
                    console.log('POS orders response:', posOrdersData);
                    const posOrders = posOrdersData.orders || [];  // Changed from posOrdersData.data to posOrdersData.orders

                    // Update POS orders list
                    const posOrdersList = document.getElementById('posOrdersList');
                    if (posOrdersList) {
                        console.log('Updating POS orders list with', posOrders.length, 'orders');
                        
                        // Create a map of existing orders
                        const existingOrders = new Map();
                        posOrdersList.querySelectorAll('[data-order-id]').forEach(orderElement => {
                            const orderId = orderElement.getAttribute('data-order-id');
                            existingOrders.set(orderId, orderElement);
                        });

                        // Process new orders
                        posOrders.forEach(order => {
                            const orderId = order.id.toString();
                            const orderHtml = `
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="${order.id}">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="font-medium">Order #${order.id}</p>
                                            <p class="text-xs text-gray-500">
                                                ${order.order_type === 'dine_in' && order.table ? 
                                                    `<span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium">
                                                        Table ${order.table.name} (${order.table.capacity} seats)
                                                    </span>` :
                                                    `<span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-medium">
                                                        ${order.order_type.charAt(0).toUpperCase() + order.order_type.slice(1)}
                                                    </span>`
                                                }
                                            </p>
                                            <p class="text-sm text-gray-600">Rs. ${formatCurrency(order.total)}</p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded ${order.payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'}">
                                            ${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                            `;

                            if (existingOrders.has(orderId)) {
                                // Update existing order
                                const existingElement = existingOrders.get(orderId);
                                const newElement = document.createElement('div');
                                newElement.innerHTML = orderHtml;
                                existingElement.replaceWith(newElement.firstElementChild);
                                existingOrders.delete(orderId);
                            } else {
                                // Add new order
                                posOrdersList.insertAdjacentHTML('afterbegin', orderHtml);
                            }
                        });

                        // Remove orders that no longer exist
                        existingOrders.forEach(element => element.remove());

                        // Reattach click event listeners
                        posOrdersList.querySelectorAll('[data-order-id]').forEach(row => {
                            row.addEventListener('click', function() {
                                const orderId = this.getAttribute('data-order-id');
                                if (orderId) {
                                    selectOrder(orderId);
                                }
                            });
                        });
                    }

                    // Fetch online orders
                    console.log('Fetching online orders...');
                    const onlineResponse = await fetch(`/api/admin/orders?branch=${branchId}&type=online&include=items,order_items,products`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${authToken}`,
                            'X-Branch-ID': branchId
                        },
                        credentials: 'same-origin'
                    });

                    if (!onlineResponse.ok) {
                        console.error('Failed to fetch online orders:', onlineResponse.status);
                        const errorText = await onlineResponse.text();
                        console.error('Error response:', errorText);
                        throw new Error('Failed to fetch online orders');
                    }

                    const onlineOrdersData = await onlineResponse.json();
                    console.log('Online orders response:', onlineOrdersData);
                    const onlineOrders = onlineOrdersData.orders || [];  // Changed from onlineOrdersData.data to onlineOrdersData.orders

                    // Update online orders list
                    const onlineOrdersList = document.getElementById('onlineOrdersList');
                    if (onlineOrdersList) {
                        console.log('Updating online orders list with', onlineOrders.length, 'orders');
                        
                        // Create a map of existing orders
                        const existingOrders = new Map();
                        onlineOrdersList.querySelectorAll('[data-order-id]').forEach(orderElement => {
                            const orderId = orderElement.getAttribute('data-order-id');
                            existingOrders.set(orderId, orderElement);
                        });

                        // Process new orders
                        onlineOrders.forEach(order => {
                            const orderId = order.id.toString();
                            const orderHtml = `
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="${order.id}">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="font-medium">Order #${order.id}</p>
                                            <p class="text-xs text-gray-500">${order.user?.name || 'Guest'}</p>
                                            <p class="text-sm text-gray-600">Rs. ${formatCurrency(order.total)}</p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded ${order.payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'}">
                                            ${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                            `;

                            if (existingOrders.has(orderId)) {
                                // Update existing order
                                const existingElement = existingOrders.get(orderId);
                                const newElement = document.createElement('div');
                                newElement.innerHTML = orderHtml;
                                existingElement.replaceWith(newElement.firstElementChild);
                                existingOrders.delete(orderId);
                            } else {
                                // Add new order
                                onlineOrdersList.insertAdjacentHTML('afterbegin', orderHtml);
                            }
                        });

                        // Remove orders that no longer exist
                        existingOrders.forEach(element => element.remove());

                        // Reattach click event listeners
                        onlineOrdersList.querySelectorAll('[data-order-id]').forEach(row => {
                            row.addEventListener('click', function() {
                                const orderId = this.getAttribute('data-order-id');
                                if (orderId) {
                                    selectOrder(orderId);
                                }
                            });
                        });
                    }

                    console.log('Orders reloaded successfully');
                    showSuccessModal('Success', 'Orders reloaded successfully');

                } catch (error) {
                    console.error('Error reloading orders:', error);
                    showErrorModal('Error', 'Failed to reload orders. Please try again.');
                }
            }

            // Add reload button to the UI
            const reloadButton = document.createElement('button');
            reloadButton.className = 'px-4 py-2 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200 ml-4';
            reloadButton.innerHTML = `
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reload Orders
            `;
            reloadButton.onclick = reloadOrders;

            // Insert reload button after session status
            const sessionStatus = document.getElementById('sessionStatus');
            if (sessionStatus) {
                sessionStatus.parentNode.insertBefore(reloadButton, sessionStatus.nextSibling);
            }

            
            // Add session status check function
            async function checkSessionStatus() {
                try {
                    const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                    if (!authToken) {
                        console.error('No auth token found');
                        return;
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        console.error('No branch ID found');
                        return;
                    }

                    const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${authToken}`,
                            'X-Branch-ID': branchId
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) return;
                    const data = await response.json();

                    const sessionInfo = document.getElementById('sessionInfo');
                    const openSessionBtn = document.getElementById('openSessionBtn');
                    const closeSessionBtn = document.getElementById('closeSessionBtn');

                    if (data.session) {
                        // Session is open
                        sessionInfo.textContent = `Active session by ${data.session.opened_by} (${new Date(data.session.opened_at).toLocaleTimeString()})`;
                        openSessionBtn.classList.add('hidden');
                        closeSessionBtn.classList.remove('hidden');
                    } else {
                        // No active session
                        sessionInfo.textContent = 'No active session';
                        openSessionBtn.classList.remove('hidden');
                        closeSessionBtn.classList.add('hidden');
                    }
                } catch (error) {
                    console.error('Error checking session status:', error);
                }
            }

            // Check session status on page load
            document.addEventListener('DOMContentLoaded', () => {
                checkSessionStatus();
                // Check session status every minute
                setInterval(checkSessionStatus, 60000);
            });

            // Add event listeners for session buttons
            document.getElementById('openSessionBtn')?.addEventListener('click', () => showSessionModal('open'));
            document.getElementById('closeSessionBtn')?.addEventListener('click', () => showSessionModal('close'));
            document.getElementById('closeSessionModal')?.addEventListener('click', () => {
                document.getElementById('sessionModal').classList.add('hidden');
            });
            document.getElementById('cancelSessionBtn')?.addEventListener('click', () => {
                document.getElementById('sessionModal').classList.add('hidden');
            });

            // Session denomination functions
            function incrementSessionDenomination(value) {
                const input = document.getElementById(`session_denomination_${value}`);
                if (input) {
                    const currentValue = parseInt(input.value) || 0;
                    input.value = currentValue + 1;
                    updateSessionTotal();
                }
            }

            function decrementSessionDenomination(value) {
                const input = document.getElementById(`session_denomination_${value}`);
                if (input) {
                    const currentValue = parseInt(input.value) || 0;
                    if (currentValue > 0) {
                        input.value = currentValue - 1;
                        updateSessionTotal();
                    }
                }
            }

            function updateSessionTotal() {
                let total = 0;
                const denominations = ['1000', '500', '100', '50', '20', '10', '4', '1'];
                
                denominations.forEach(value => {
                    const input = document.getElementById(`session_denomination_${value}`);
                    if (input) {
                        const count = parseInt(input.value) || 0;
                        total += count * parseInt(value);
                    }
                });

                const totalElement = document.getElementById('sessionTotalAmount');
                if (totalElement) {
                    totalElement.textContent = formatCurrency(total);
                }
            }

            function getSessionDenominations() {
                const denominations = {};
                ['1000', '500', '100', '50', '20', '10', '4', '1'].forEach(value => {
                    const input = document.getElementById(`session_denomination_${value}`);
                    denominations[value] = parseInt(input?.value) || 0;
                });
                return denominations;
            }

            function resetSessionModal() {
                ['1000', '500', '100', '50', '20', '10', '4', '1'].forEach(value => {
                    const input = document.getElementById(`session_denomination_${value}`);
                    if (input) input.value = '0';
                });
                const notes = document.getElementById('sessionNotes');
                if (notes) notes.value = '';
                updateSessionTotal();
            }

            // Show session modal
            function showSessionModal(type) {
                const modal = document.getElementById('sessionModal');
                const title = document.getElementById('sessionModalTitle');
                const confirmBtn = document.getElementById('confirmSessionBtn');
                
                if (modal && title && confirmBtn) {
                    resetSessionModal();
                    
                    if (type === 'open') {
                        title.textContent = 'Open Cash Drawer Session';
                        confirmBtn.textContent = 'Open Session';
                        confirmBtn.onclick = handleOpenSession;
                        
                        // Set default denominations for opening session
                        const defaultDenominations = {
                            '1000': 0,
                            '500': 4,
                            '100': 20,
                            '50': 20,
                            '20': 20,
                            '10': 30,
                            '5': 25,
                            '1': 10
                        };
                        
                        Object.entries(defaultDenominations).forEach(([value, count]) => {
                            const input = document.getElementById(`session_denomination_${value}`);
                            if (input) {
                                input.value = count;
                            }
                        });
                        
                        updateSessionTotal();
                    } else {
                        title.textContent = 'Close Cash Drawer Session';
                        confirmBtn.textContent = 'Close Session';
                        confirmBtn.onclick = handleCloseSession;
                        
                        // Fetch and display current denominations when closing
                        fetchCurrentDenominations().then(() => {
                            console.log('Current denominations loaded');
                        }).catch(error => {
                            console.error('Error loading current denominations:', error);
                        });
                    }
                    
                    modal.classList.remove('hidden');
                }
            }

            // Handle open session
            async function handleOpenSession() {
                try {
                    const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
                    if (!authToken) {
                        console.error('No auth token found');
                            // Try to refresh the token first
                            const refreshResponse = await fetch('/api/refresh-token', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                credentials: 'same-origin'
                            });

                            if (!refreshResponse.ok) {
                                window.location.href = '{{ route("login") }}';
                        return;
                            }

                            const refreshData = await refreshResponse.json();
                            if (!refreshData.token) {
                                window.location.href = '{{ route("login") }}';
                                return;
                            }

                            // Update the meta tag with the new token
                            document.querySelector('meta[name="auth-token"]').setAttribute('content', refreshData.token);
                    }

                    const branchId = window.paymentManagerState.branchId;
                    if (!branchId) {
                        console.error('No branch ID found');
                        return;
                    }

                    // Use default denominations for opening session
                    const denominations = {
                        '1000': 0,
                        '500': 4,
                        '100': 20,
                        '50': 20,
                        '20': 20,
                        '10': 30,
                        '5': 25,
                        '1': 10
                    };

                    const total = Object.entries(denominations).reduce((sum, [value, count]) => sum + (parseInt(value) * count), 0);
                    const notes = document.getElementById('sessionNotes')?.value || '';

                    const response = await fetch('/api/admin/cash-drawer/open', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': `Bearer ${document.querySelector('meta[name="auth-token"]').getAttribute('content')}`,
                            'X-Branch-ID': branchId
                        },
                        body: JSON.stringify({
                            branch_id: branchId,
                            opening_balance: total,
                            opening_denominations: denominations,
                            notes: notes
                        }),
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        const error = await response.json();
                            if (response.status === 401 || response.status === 403) {
                                // Try to refresh the token
                                const refreshResponse = await fetch('/api/refresh-token', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                        credentials: 'same-origin'
                    });

                                if (!refreshResponse.ok) {
                                    window.location.href = '{{ route("login") }}';
                        return;
                    }

                                const refreshData = await refreshResponse.json();
                                if (!refreshData.token) {
                                    window.location.href = '{{ route("login") }}';
                        return;
                    }
