@extends('layouts.payment')

@section('title', 'Payment Manager')

@push('head')
<meta http-equiv="Content-Security-Policy" content="camera *">
@endpush

@section('content')
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
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" onclick="selectOrder({{ $order->id }})">
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
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" onclick="selectOrder({{ $order->id }})">
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
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" onclick="selectOrder({{ $order->id }})">
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
                                                            '4' => 'Rs. 4',
                                                            '1' => 'Rs. 1'
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
                                                                       placeholder="0">
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
                                        <label class="block text-sm font-medium text-gray-700">Wallet Number</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="text" id="walletNumber" name="wallet_number" class="flex-1 rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter wallet number">
                                            <button type="button" id="scanWalletBtn" class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-700 rounded-r-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v4m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                </svg>
                                                <span class="ml-2">Scan QR</span>
                                            </button>
                                        </div>
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
                                <div>
                                    <h4 class="font-medium text-base text-gray-600 mb-3">Starting Amount</h4>
                                    <table class="w-full text-base">
                                        <thead>
                                            <tr class="border-b-2 border-gray-200">
                                                <th class="text-left py-2">Denomination</th>
                                                <th class="text-right py-2">Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($denominations as $value => $label)
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2">{{ $label }}</td>
                                                    <td class="text-right py-2">
                                                        <span class="current-denomination" data-denomination="{{ $value }}">0</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="{{ asset('js/payment-manager.js') }}"></script>
            <script src="https://unpkg.com/html5-qrcode"></script>
        </div>
    </div>
</div>

@endsection
