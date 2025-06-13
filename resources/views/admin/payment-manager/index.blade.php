@extends('layouts.payment')

@section('title', 'Payment Manager')

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
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" onclick="selectOrder({{ $order->id }}, '{{ $order->order_type }}', {{ $order->total }})">
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
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" onclick="selectOrder({{ $order->id }}, '{{ $order->order_type }}', {{ $order->total }})">
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
                                <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" onclick="selectOrder({{ $order->id }}, '{{ $order->order_type }}', {{ $order->total }})">
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
                        
                        <form id="paymentForm" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                    <select id="paymentMethod" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="mobile">Mobile Payment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input type="number" id="paymentAmount" name="amount" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" readonly>
                                </div>
                            </div>

                            <!-- Cash Payment Fields -->
                            <div id="cashPaymentFields" class="hidden space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount Received</label>
                                        <input type="number" id="amountReceived" name="amount_received" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Change</label>
                                    <input type="number" id="changeAmount" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                                </div>
                                </div>

                                <!-- Currency Denominations -->
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
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 1000</span>
                                            <input type="number" name="change_given[1000]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 500</span>
                                            <input type="number" name="change_given[500]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 100</span>
                                            <input type="number" name="change_given[100]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 50</span>
                                            <input type="number" name="change_given[50]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 20</span>
                                            <input type="number" name="change_given[20]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 10</span>
                                            <input type="number" name="change_given[10]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Rs. 4</span>
                                            <input type="number" name="change_given[4]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Rs. 1</span>
                                            <input type="number" name="change_given[1]" min="0" class="w-20 text-right rounded-md border-gray-300 bg-gray-50" readonly>
                                        </div>
                                        <div class="border-t pt-2 mt-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Total Change</span>
                                                <span class="text-lg font-semibold" id="totalChange">Rs. 0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Payment Fields -->
                            <div id="cardPaymentFields" class="hidden">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <input type="text" id="referenceNumber" name="reference_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="paymentNotes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" id="processPaymentBtn" class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center">
                                    <span id="buttonText">Process Payment</span>
                                    <svg id="loadingSpinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Success Modal -->
            <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Success</h3>
                        <div class="mt-2 px-7 py-3">
                            <p id="successMessage" class="text-sm text-gray-500"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Modal -->
            <div id="errorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Error</h3>
                        <div class="mt-2 px-7 py-3">
                            <p id="errorMessage" class="text-sm text-gray-500"></p>
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
                                            @php
                                                $denominations = [
                                                    '1000' => 0,
                                                    '500' => 4,
                                                    '100' => 20,
                                                    '50' => 20,
                                                    '20' => 20,
                                                    '10' => 30,
                                                    '4' => 25,
                                                    '1' => 10
                                                ];
                                                
                                                $totalAmount = 0;
                                                foreach ($denominations as $denomination => $count) {
                                                    $totalAmount += ($denomination * $count);
                                                }
                                            @endphp
                                                @foreach($denominations as $denomination => $count)
                                                    <tr class="border-b border-gray-100">
                                                    <td class="py-2">Rs. {{ (int)$denomination }}</td>
                                                        <td class="text-right py-2">{{ $count }}</td>
                                                    </tr>
                                                @endforeach
                                            <tr class="font-medium border-t-2 border-gray-200">
                                                <td class="py-2">Total</td>
                                                <td class="text-right py-2">Rs. {{ $totalAmount }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <h4 class="font-medium text-base text-gray-600 mb-3">Current Amount</h4>
                                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Quick Actions</span>
                                            <div class="space-x-2">
                                                <button type="button" onclick="showAdjustmentModal('add')" class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded hover:bg-green-200">
                                                    Add Cash
                                                </button>
                                                <button type="button" onclick="showAdjustmentModal('remove')" class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded hover:bg-red-200">
                                                    Remove Cash
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="w-full text-base">
                                        <thead>
                                            <tr class="border-b-2 border-gray-200">
                                                <th class="text-left py-2">Denomination</th>
                                                <th class="text-right py-2">Count</th>
                                                <th class="text-right py-2">Status</th>
                                                <th class="text-right py-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $startingDenominations = [
                                                    '1000' => 0,
                                                    '500' => 4,
                                                    '100' => 20,
                                                    '50' => 20,
                                                    '20' => 20,
                                                    '10' => 30,
                                                    '4' => 25,
                                                    '1' => 10
                                                ];
                                                
                                                $alertThresholds = [
                                                    '1000' => ['low' => null, 'high' => 10],
                                                    '500' => ['low' => 2, 'high' => 20],
                                                    '100' => ['low' => 10, 'high' => 50],
                                                    '50' => ['low' => 10, 'high' => 40],
                                                    '20' => ['low' => 10, 'high' => 50],
                                                    '10' => ['low' => 15, 'high' => 100],
                                                    '4' => ['low' => 10, 'high' => 75],
                                                    '1' => ['low' => 5, 'high' => 20]
                                                ];
                                            @endphp
                                            @foreach($startingDenominations as $denomination => $count)
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2">Rs. {{ (int)$denomination }}</td>
                                                    <td class="text-right py-2">
                                                        <span class="current-denomination" 
                                                              data-denomination="{{ $denomination }}" 
                                                              data-starting-count="{{ $count }}"
                                                              data-low-threshold="{{ $alertThresholds[$denomination]['low'] }}"
                                                              data-high-threshold="{{ $alertThresholds[$denomination]['high'] }}">{{ $count }}</span>
                                                    </td>
                                                    <td class="text-right py-2">
                                                        <span class="denomination-status" data-denomination="{{ $denomination }}"></span>
                                                    </td>
                                                    <td class="text-right py-2">
                                                        <div class="flex justify-end space-x-2">
                                                            <button type="button" 
                                                                    onclick="adjustDenomination('{{ $denomination }}', 'add')"
                                                                    class="p-1 text-green-600 hover:text-green-800">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                </svg>
                                                            </button>
                                                            <button type="button" 
                                                                    onclick="adjustDenomination('{{ $denomination }}', 'remove')"
                                                                    class="p-1 text-red-600 hover:text-red-800">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="font-medium border-t-2 border-gray-200">
                                                <td class="py-2">Total</td>
                                                <td class="text-right py-2" colspan="3">
                                                    <span class="current-balance">Rs. {{ number_format($totalAmount, 2) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    Total Sales Today: {{ number_format($todaySummary['total_sales'], 2) }}
                </div>
            </div>

            <!-- Adjustment Modal -->
            <div id="adjustmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900" id="adjustmentModalTitle">Adjust Cash</h3>
                        <div class="mt-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Denomination</label>
                                    <select id="adjustmentDenomination" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($startingDenominations as $denomination => $count)
                                            <option value="{{ $denomination }}">Rs. {{ (int)$denomination }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input type="number" id="adjustmentAmount" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason</label>
                                    <textarea id="adjustmentReason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-3">
                            <button type="button" onclick="closeAdjustmentModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="button" onclick="processAdjustment()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Open Session Modal -->
            <div id="openSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900">Open Cash Drawer Session</h3>
                        <div class="mt-4">
                            <form id="openSessionForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Opening Balance</label>
                                    <input type="number" id="openingBalance" name="opening_balance" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Opening Denominations</label>
                                    <div class="space-y-2">
                                        @php
                                            $denominations = [1000, 500, 100, 50, 20, 10, 4, 1];
                                        @endphp
                                        @foreach($denominations as $denomination)
                                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">Rs. {{ $denomination }}</span>
                                                <div class="flex items-center space-x-2">
                                                    <button type="button" 
                                                            onclick="decrementDenomination('opening_denominations[{{ $denomination }}]')"
                                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                        </svg>
                                                    </button>
                                                    <input type="number" 
                                                           name="opening_denominations[{{ $denomination }}]" 
                                                           min="0" 
                                                           class="w-16 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                           placeholder="0">
                                                    <button type="button" 
                                                            onclick="incrementDenomination('opening_denominations[{{ $denomination }}]')"
                                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="openingNotes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="closeOpenSessionModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Open Session
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Close Session Modal -->
            <div id="closeSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900">Close Cash Drawer Session</h3>
                        <div class="mt-4">
                            <form id="closeSessionForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Closing Denominations</label>
                                    <div class="space-y-2">
                                        @foreach($denominations as $denomination)
                                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">Rs. {{ $denomination }}</span>
                                                <div class="flex items-center space-x-2">
                                                    <button type="button" 
                                                            onclick="decrementDenomination('closing_denominations[{{ $denomination }}]')"
                                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                        </svg>
                                                    </button>
                                                    <input type="number" 
                                                           name="closing_denominations[{{ $denomination }}]" 
                                                           min="0" 
                                                           class="w-16 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                           placeholder="0">
                                                    <button type="button" 
                                                            onclick="incrementDenomination('closing_denominations[{{ $denomination }}]')"
                                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="closingNotes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="closeCloseSessionModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Close Session
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Add these functions at the beginning of your script
function showSessionBlur() {
    document.getElementById('sessionBlurOverlay').classList.remove('hidden');
}

function hideSessionBlur() {
    document.getElementById('sessionBlurOverlay').classList.add('hidden');
}

// Function to check if session is open
async function checkSessionStatus() {
    try {
        const branchId = new URLSearchParams(window.location.search).get('branch');
        const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        const hasSession = data.session !== null;
        
        // Show/hide blur based on session status
        if (!hasSession) {
            showSessionBlur();
            // Reset session status display
            const sessionStatusElement = document.getElementById('sessionStatus');
            if (sessionStatusElement) {
                sessionStatusElement.innerHTML = '';
            }
            // Update button visibility
            const openSessionBtn = document.getElementById('openSessionBtn');
            const closeSessionBtn = document.getElementById('closeSessionBtn');
            if (openSessionBtn) openSessionBtn.classList.remove('hidden');
            if (closeSessionBtn) closeSessionBtn.classList.add('hidden');
        } else {
            hideSessionBlur();
            // Update session status display if session exists
            updateSessionStatus(data.session);
        }
        
        return hasSession;
    } catch (error) {
        console.error('Error checking session status:', error);
        showSessionBlur();
        return false;
    }
}

let currentOrderId = null;
let cashDrawerBalance = 0;
let currentAdjustmentType = 'add';

// Function to calculate total from denominations
function calculateTotalFromDenominations(denominations) {
    let total = 0;
    for (const [denomination, count] of Object.entries(denominations)) {
        total += parseFloat(denomination) * parseInt(count);
    }
    return total;
}

// Function to calculate change denominations
function calculateChangeDenominations(change) {
    const denominations = {
        '1000': 0, '500': 0, '100': 0, '50': 0,
        '20': 0, '10': 0, '4': 0, '1': 0
    };
    
    let remaining = change;
    const denomValues = [1000, 500, 100, 50, 20, 10, 4, 1];
    
    for (const value of denomValues) {
        const key = value.toString();
        while (remaining >= value) {
            denominations[key]++;
            remaining = (remaining - value).toFixed(2);
        }
    }
    
    return denominations;
}

// Function to update cash drawer status
async function updateCashDrawerStatus() {
    try {
        const branchId = new URLSearchParams(window.location.search).get('branch');
        if (!branchId) {
            console.error('Branch ID is required');
            return;
        }

        const response = await fetch(`/api/admin/cash-drawer/balance?branch_id=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to fetch cash drawer status');
        }

        const data = await response.json();
        cashDrawerBalance = parseFloat(data.balance || 0);
        
        // Update the cash drawer dropdown content if it exists
        const cashDrawerDropdown = document.getElementById('cashDrawerDropdown');
        if (cashDrawerDropdown) {
            const currentBalanceElement = cashDrawerDropdown.querySelector('.current-balance');
            if (currentBalanceElement) {
                currentBalanceElement.textContent = `Rs. ${cashDrawerBalance.toFixed(2)}`;
            }
        }
    } catch (error) {
        console.error('Error updating cash drawer status:', error);
    }
}

// Update cash drawer status every minute
setInterval(updateCashDrawerStatus, 60000);
updateCashDrawerStatus();

async function selectOrder(orderId, orderType, total) {
    try {
        currentOrderId = orderId;
        // Get branch ID from URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const branchId = urlParams.get('branch');
        
        if (!branchId) {
            console.error('Branch ID is required');
            return;
        }

        // Get CSRF token and auth token from meta tags
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const authToken = document.querySelector('meta[name="auth-token"]').getAttribute('content');

        // Fetch order details from API with branch ID in header
        const response = await fetch(`/api/admin/orders/${orderId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': 'Bearer ' + authToken,
                'X-Branch-ID': branchId
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const order = await response.json();
        
        // Clear existing items
        const tableBody = document.getElementById('itemsTableBody');
        tableBody.innerHTML = '';
        
        // Add items to table
        order.items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="border px-4 py-2">${item.product ? item.product.name : item.item_name}</td>
                <td class="border px-4 py-2 text-right">${item.quantity}</td>
                <td class="border px-4 py-2 text-right">$${parseFloat(item.price).toFixed(2)}</td>
                <td class="border px-4 py-2 text-right">$${parseFloat(item.subtotal).toFixed(2)}</td>
            `;
            tableBody.appendChild(row);
        });
        
        // Update totals
        document.getElementById('subtotal').textContent = `Rs. ${parseFloat(order.subtotal || 0).toFixed(2)}`;
        document.getElementById('total').textContent = `Rs. ${parseFloat(order.total).toFixed(2)}`;
        
        // Set payment amount
        document.getElementById('paymentAmount').value = order.total;
        
    } catch (error) {
        console.error('Error fetching order details:', error);
    }
}

// Handle payment method change
document.getElementById('paymentMethod').addEventListener('change', function() {
    const cashFields = document.getElementById('cashPaymentFields');
    const cardFields = document.getElementById('cardPaymentFields');
    const amountReceived = document.getElementById('amountReceived');
    
    if (this.value === 'cash') {
        cashFields.classList.remove('hidden');
        cardFields.classList.add('hidden');
        amountReceived.setAttribute('required', 'required');
    } else if (this.value === 'card') {
        cashFields.classList.add('hidden');
        cardFields.classList.remove('hidden');
        amountReceived.removeAttribute('required');
    } else {
        cashFields.classList.add('hidden');
        cardFields.classList.add('hidden');
        amountReceived.removeAttribute('required');
    }
});

// Add event listener for amount received input
document.getElementById('amountReceived').addEventListener('input', function() {
    const amount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const received = parseFloat(this.value) || 0;
    const change = received - amount;
    document.getElementById('changeAmount').value = change >= 0 ? change.toFixed(2) : '';
});

// Calculate total from denominations
document.querySelectorAll('input[name^="received_denominations"]').forEach(input => {
    input.addEventListener('input', function() {
        const denominations = {};
        document.querySelectorAll('input[name^="received_denominations"]').forEach(input => {
            const value = input.value ? parseInt(input.value) : 0;
            denominations[input.name.match(/\[(.*?)\]/)[1]] = value;
        });
        
        const total = calculateTotalFromDenominations(denominations);
        document.getElementById('amountReceived').value = total.toFixed(2);
        
        // Calculate change
        const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
        const change = total - paymentAmount;
        
        if (change >= 0) {
            document.getElementById('changeAmount').value = `Rs. ${change.toFixed(2)}`;
            const changeDenoms = calculateChangeDenominations(change);
            
            // Update change denominations
            for (const [denomination, count] of Object.entries(changeDenoms)) {
                const input = document.querySelector(`input[name="received_denominations[${denomination}]"]`);
                if (input) input.value = count;
            }
            
            document.getElementById('changeDenominations').classList.remove('hidden');
        } else {
            document.getElementById('changeAmount').value = '';
            document.getElementById('changeDenominations').classList.add('hidden');
        }
    });
});

// Function to show success modal
function showSuccessModal(message) {
    const modal = document.getElementById('successModal');
    const messageElement = document.getElementById('successMessage');
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 3000);
    }
}

// Function to show error modal
function showErrorModal(message) {
    const modal = document.getElementById('errorModal');
    const messageElement = document.getElementById('errorMessage');
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 3000);
    }
}

// Function to set button loading state
function setButtonLoading(isLoading) {
    const button = document.getElementById('processPaymentBtn');
    const buttonText = document.getElementById('buttonText');
    const spinner = document.getElementById('loadingSpinner');
    
    if (button && buttonText && spinner) {
        button.disabled = isLoading;
        buttonText.textContent = isLoading ? 'Processing...' : 'Process Payment';
        spinner.classList.toggle('hidden', !isLoading);
    }
}

// Function to load orders
async function loadOrders() {
    try {
        const branchId = new URLSearchParams(window.location.search).get('branch') || '1';
        const token = document.querySelector('meta[name="auth-token"]').getAttribute('content');
        
        const response = await fetch(`/api/admin/orders?branch=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${token}`
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            if (response.status === 401) {
                // Handle unauthorized error - redirect to login
                window.location.href = '/login';
                return;
            }
            throw new Error('Failed to load orders');
        }

        const data = await response.json();

        // Update orders list
        const ordersList = document.getElementById('ordersList');
        if (ordersList) {
            ordersList.innerHTML = ''; // Clear existing orders
            
            if (data.orders && Array.isArray(data.orders)) {
                data.orders.forEach(order => {
                const orderElement = createOrderElement(order);
                ordersList.appendChild(orderElement);
            });
            } else {
                ordersList.innerHTML = '<p class="text-gray-500">No orders found</p>';
            }
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        showErrorModal('Failed to load orders');
    }
}

// Function to create order element
function createOrderElement(order) {
    const div = document.createElement('div');
    div.className = 'bg-white shadow rounded-lg p-4 mb-4';
    div.innerHTML = `
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-lg font-semibold">Order #${order.order_number}</h3>
                <p class="text-sm text-gray-600">${order.order_type === 'dine_in' ? `Table ${order.table?.number || 'N/A'}` : order.order_type}</p>
            </div>
            <span class="px-2 py-1 text-sm rounded-full ${order.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                ${order.status}
            </span>
        </div>
        <div class="mt-2">
            <p class="text-sm">Total: $${order.total}</p>
            <p class="text-sm">Items: ${order.items?.length || 0}</p>
        </div>
        ${order.status !== 'completed' ? `
            <button onclick="selectOrder(${order.id})" class="mt-2 w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Process Payment
            </button>
        ` : ''}
    `;
    return div;
}

// Load orders when page loads
document.addEventListener('DOMContentLoaded', loadOrders);

// Function to update cash drawer denominations display
function updateCashDrawerDenominations(denominations) {
    Object.entries(denominations).forEach(([denom, count]) => {
        const element = document.querySelector(`.current-denomination[data-denomination="${denom}"]`);
        if (element) {
            element.textContent = count;
            // Update status based on thresholds
            const lowThreshold = parseInt(element.dataset.lowThreshold);
            const highThreshold = parseInt(element.dataset.highThreshold);
            
            if (count <= lowThreshold) {
                element.classList.add('text-red-600');
                element.classList.remove('text-green-600');
            } else if (count >= highThreshold) {
                element.classList.add('text-green-600');
                element.classList.remove('text-red-600');
            } else {
                element.classList.remove('text-red-600', 'text-green-600');
            }
        }
    });
}

// Function to refresh cash drawer status
async function refreshCashDrawerStatus() {
    try {
        const branchId = new URLSearchParams(window.location.search).get('branch');
        const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        // Update denominations display
        if (data.denominations) {
            updateCashDrawerDenominations(data.denominations);
        }
        
        // Update total balance
        if (data.total_balance !== undefined) {
            const balanceElement = document.getElementById('cashDrawerBalance');
            if (balanceElement) {
                balanceElement.textContent = `Rs. ${parseFloat(data.total_balance).toFixed(2)}`;
            }
        }
        
        // Update session status
        if (data.session) {
            updateSessionStatus(data.session);
        }
        
        return data;
    } catch (error) {
        console.error('Error refreshing cash drawer status:', error);
        showErrorModal('Failed to refresh cash drawer status', error.message);
        return null;
    }
}

// Function to increment denomination
function incrementDenomination(inputName) {
    const input = document.querySelector(`input[name="${inputName}"]`);
    if (input) {
        const currentValue = parseInt(input.value) || 0;
        input.value = currentValue + 1;
        
        // Trigger input event to update totals
        const event = new Event('input', { bubbles: true });
        input.dispatchEvent(event);
        
        // Update cash drawer display if this is a current denomination
        if (inputName.startsWith('opening_denominations[') || inputName.startsWith('closing_denominations[')) {
            const denom = inputName.match(/\[(.*?)\]/)[1];
            const count = parseInt(input.value) || 0;
            updateCashDrawerDenominations({ [denom]: count });
        }
    }
}

// Function to decrement denomination
function decrementDenomination(inputName) {
    const input = document.querySelector(`input[name="${inputName}"]`);
    if (input) {
        const currentValue = parseInt(input.value) || 0;
        if (currentValue > 0) {
            input.value = currentValue - 1;
            
            // Trigger input event to update totals
            const event = new Event('input', { bubbles: true });
            input.dispatchEvent(event);
            
            // Update cash drawer display if this is a current denomination
            if (inputName.startsWith('opening_denominations[') || inputName.startsWith('closing_denominations[')) {
                const denom = inputName.match(/\[(.*?)\]/)[1];
                const count = parseInt(input.value) || 0;
                updateCashDrawerDenominations({ [denom]: count });
            }
        }
    }
}

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentOrderId) {
        alert('Please select an order first');
        return;
    }

    try {
        // Set button to loading state
        const button = document.getElementById('processPaymentBtn');
        const buttonText = document.getElementById('buttonText');
        const spinner = document.getElementById('loadingSpinner');
        
        if (button && buttonText && spinner) {
            button.disabled = true;
            buttonText.textContent = 'Processing...';
            spinner.classList.remove('hidden');
        }

        const formData = new FormData(this);
        const token = document.querySelector('meta[name="auth-token"]').getAttribute('content');
        
        const paymentData = {
            order_id: currentOrderId,
            amount: formData.get('amount'),
            payment_method: formData.get('payment_method'),
            notes: formData.get('notes')
        };

        // Add amount_received for cash payments
        if (formData.get('payment_method') === 'cash') {
            paymentData.amount_received = formData.get('amount_received');
        }

        // Add reference_number for card payments
        if (formData.get('payment_method') === 'card') {
            paymentData.reference_number = formData.get('reference_number');
        }
        
        const response = await fetch('/api/admin/payments', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }
            throw new Error(result.message || 'Failed to process payment');
        }

        // Show success message
        showSuccessModal('Payment processed successfully');

        // Reset form and refresh orders
        this.reset();
        loadOrders();

    } catch (error) {
        console.error('Error processing payment:', error);
        showErrorModal(error.message || 'Failed to process payment');
    } finally {
        // Reset button state
        const button = document.getElementById('processPaymentBtn');
        const buttonText = document.getElementById('buttonText');
        const spinner = document.getElementById('loadingSpinner');
        
        if (button && buttonText && spinner) {
            button.disabled = false;
            buttonText.textContent = 'Process Payment';
            spinner.classList.add('hidden');
        }
    }
});

// Function to show session required message
function showSessionRequiredMessage() {
    showErrorModal('Please open a cash drawer session first', 'You need to open a cash drawer session before performing any cash operations.');
}

document.addEventListener('DOMContentLoaded', function() {
    const cashDrawerBtn = document.getElementById('cashDrawerBtn');
    const cashDrawerDropdown = document.getElementById('cashDrawerDropdown');
    const closeCashDrawer = document.getElementById('closeCashDrawer');

    // Toggle dropdown
    cashDrawerBtn.addEventListener('click', async function(e) {
        e.stopPropagation();
        const hasOpenSession = await checkSessionStatus();
        
        if (!hasOpenSession) {
            showSessionRequiredMessage();
            return;
        }
        
        cashDrawerDropdown.classList.toggle('hidden');
        refreshCashDrawerStatus();
    });

    // Close dropdown when clicking close button
    closeCashDrawer.addEventListener('click', function(e) {
        e.stopPropagation();
        cashDrawerDropdown.classList.add('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!cashDrawerDropdown.contains(e.target) && !cashDrawerBtn.contains(e.target)) {
            cashDrawerDropdown.classList.add('hidden');
        }
    });
});

// Modify showCloseSessionModal to check for session
async function showCloseSessionModal() {
    const branchId = new URLSearchParams(window.location.search).get('branch');
    
    try {
        // First check if there's an open session
        const hasOpenSession = await checkSessionStatus();
        if (!hasOpenSession) {
            showSessionRequiredMessage();
            return;
        }

        // Fetch current denominations
        const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.denominations) {
            // Pre-fill the closing denominations with current values
            Object.entries(data.denominations).forEach(([denom, count]) => {
                const input = document.querySelector(`input[name="closing_denominations[${denom}]"]`);
                if (input) {
                    input.value = count;
                }
            });

            // Calculate and set the total closing balance
            const totalBalance = Object.entries(data.denominations).reduce((total, [denom, count]) => {
                return total + (parseFloat(denom) * parseInt(count));
            }, 0);

            const closingBalanceInput = document.querySelector('input[name="closing_balance"]');
            if (closingBalanceInput) {
                closingBalanceInput.value = totalBalance.toFixed(2);
            }
        }

        // Show the modal
        document.getElementById('closeSessionModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error in showCloseSessionModal:', error);
        showErrorModal('Failed to prepare close session modal', error.message);
    }
}

function closeCloseSessionModal() {
    document.getElementById('closeSessionModal').classList.add('hidden');
    document.getElementById('closeSessionForm').reset();
}

// Update session status display
function updateSessionStatus(session) {
    const sessionStatusElement = document.getElementById('sessionStatus');
    const openSessionBtn = document.getElementById('openSessionBtn');
    const closeSessionBtn = document.getElementById('closeSessionBtn');
    
    if (!sessionStatusElement) return;

    if (session) {
        const openedAt = new Date(session.opened_at).toLocaleString();
        const openingBalance = parseFloat(session.opening_balance).toFixed(2);
        
        // Update session status display
        sessionStatusElement.innerHTML = `
            <div class="text-sm text-gray-600">
                <p>Session opened by: ${session.opened_by}</p>
                <p>Opened at: ${openedAt}</p>
                <p>Opening balance: Rs. ${openingBalance}</p>
            </div>
        `;
        
        // Update button visibility
        if (openSessionBtn) openSessionBtn.classList.add('hidden');
        if (closeSessionBtn) closeSessionBtn.classList.remove('hidden');
        
        // Update denominations if they exist in the session
        if (session.opening_denominations) {
            updateCashDrawerDenominations(session.opening_denominations);
        }
    } else {
        sessionStatusElement.innerHTML = '';
        // Update button visibility
        if (openSessionBtn) openSessionBtn.classList.remove('hidden');
        if (closeSessionBtn) closeSessionBtn.classList.add('hidden');
    }
}

// Handle open session form submission
document.getElementById('openSessionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // First check if there's already an open session
    const hasOpenSession = await checkSessionStatus();
    if (hasOpenSession) {
        showErrorModal('Session Already Open', 'There is already an open cash drawer session. Please close the existing session before opening a new one.');
        closeOpenSessionModal();
        return;
    }
    
    const formData = new FormData(this);
    const branchId = new URLSearchParams(window.location.search).get('branch');
    
    // Get opening denominations
    const openingDenominations = Object.fromEntries(
        Array.from(formData.entries())
            .filter(([key]) => key.startsWith('opening_denominations'))
            .map(([key, value]) => [key.match(/\[(.*?)\]/)[1], parseInt(value) || 0])
    );
    
    try {
        const response = await fetch('/api/admin/cash-drawer/open', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').content
            },
            body: JSON.stringify({
                branch_id: branchId,
                opening_balance: parseFloat(formData.get('opening_balance')),
                opening_denominations: openingDenominations,
                notes: formData.get('notes')
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to open session');
        }

        closeOpenSessionModal();
        showSuccessModal('Cash drawer session opened successfully');
        
        // Update current denominations display with opening denominations
        updateCashDrawerDenominations(openingDenominations);
        
        // Update the cash drawer balance
        const balanceElement = document.getElementById('cashDrawerBalance');
        if (balanceElement) {
            balanceElement.textContent = `Rs. ${parseFloat(formData.get('opening_balance')).toFixed(2)}`;
        }
        
        // Update session status
        if (data.session) {
            updateSessionStatus(data.session);
        }
        
        // Hide blur overlay
        hideSessionBlur();

    } catch (error) {
        console.error('Error opening session:', error);
        showErrorModal('Failed to open session', error.message);
        // Don't hide the blur overlay on error
    }
});

// Handle close session form submission
document.getElementById('closeSessionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const branchId = new URLSearchParams(window.location.search).get('branch');
    const formData = new FormData(this);
    const closingDenominations = {};
    let hasNegativeValues = false;

    // Collect and validate denominations
    formData.forEach((value, key) => {
        if (key.startsWith('closing_denominations[')) {
            const denomination = key.match(/\[(.*?)\]/)[1];
            const count = parseInt(value);
            if (count < 0) {
                hasNegativeValues = true;
            }
            closingDenominations[denomination] = count;
        }
    });

    if (hasNegativeValues) {
        showErrorModal('Invalid Denominations', 'Closing denominations cannot be negative.');
        return;
    }

    try {
        const response = await fetch(`/api/admin/cash-drawer/close`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                branch_id: branchId,
                closing_denominations: closingDenominations,
                notes: formData.get('notes')
            })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to close session');
        }

        // Update UI
        await refreshCashDrawerStatus();
        closeCloseSessionModal();
        showSuccessModal('Session closed successfully');
        
        // Update session status display
        const sessionStatusElement = document.getElementById('sessionStatus');
        if (sessionStatusElement) {
            sessionStatusElement.innerHTML = `
                <div class="text-sm text-gray-600">
                    <p>No active session</p>
                </div>
            `;
        }

        // Show open session button
        const openSessionBtn = document.getElementById('openSessionBtn');
        if (openSessionBtn) {
            openSessionBtn.classList.remove('hidden');
        }

        // Hide close session button
        const closeSessionBtn = document.getElementById('closeSessionBtn');
        if (closeSessionBtn) {
            closeSessionBtn.classList.add('hidden');
        }

        // Show blur overlay since session is closed
        showSessionBlur();

    } catch (error) {
        console.error('Error closing session:', error);
        showErrorModal('Failed to close session', error.message);
    }
});

// Add event listeners for session buttons
document.getElementById('openSessionBtn').addEventListener('click', showOpenSessionModal);
document.getElementById('closeSessionBtn').addEventListener('click', showCloseSessionModal);

// Add initial session check when page loads
document.addEventListener('DOMContentLoaded', async function() {
    const hasOpenSession = await checkSessionStatus();
    if (!hasOpenSession) {
        // Show open session button prominently
        const openSessionBtn = document.getElementById('openSessionBtn');
        if (openSessionBtn) {
            openSessionBtn.classList.remove('hidden');
        }
    }
});

async function processAdjustment() {
    const hasOpenSession = await checkSessionStatus();
    if (!hasOpenSession) {
        showSessionRequiredMessage();
        return;
    }
    
    const denomination = document.getElementById('adjustmentDenomination').value;
    const amount = parseInt(document.getElementById('adjustmentAmount').value);
    const reason = document.getElementById('adjustmentReason').value;
    const branchId = new URLSearchParams(window.location.search).get('branch');
    
    if (!amount || amount < 1) {
        alert('Please enter a valid amount');
        return;
    }
    
    if (!reason.trim()) {
        alert('Please provide a reason for the adjustment');
        return;
    }

    if (!branchId) {
        alert('Branch ID is required');
        return;
    }

    // Show loading state
    const submitButton = document.querySelector('#adjustmentModal button[type="button"]:last-child');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';
    
    try {
        console.log('Sending adjustment request:', {
            denomination,
            amount: currentAdjustmentType === 'add' ? amount : -amount,
            reason,
            branch_id: branchId
        });

        const response = await fetch('/api/admin/cash-drawer/adjust', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').content
            },
            body: JSON.stringify({
                denomination,
                amount: currentAdjustmentType === 'add' ? amount : -amount,
                reason,
                branch_id: branchId
            })
        });
        
        const data = await response.json();
        console.log('Server response:', data);

        if (!response.ok) {
            throw new Error(data.message || 'Failed to process adjustment');
        }

        // Update the display with the new counts from the server
        if (data.denominations) {
            console.log('Updating denominations:', data.denominations);
            Object.entries(data.denominations).forEach(([denom, count]) => {
                const element = document.querySelector(`.current-denomination[data-denomination="${denom}"]`);
                if (element) {
                    console.log(`Updating denomination ${denom} to ${count}`);
                    element.textContent = count;
                    checkDenominationStatus(denom, count);
                } else {
                    console.warn(`Element not found for denomination ${denom}`);
                }
            });
        } else {
            console.warn('No denominations data in response');
        }
        
        // Update the total balance
        if (data.total_balance !== undefined) {
            console.log('Updating total balance to:', data.total_balance);
            const currentBalanceElement = document.querySelector('.current-balance');
            if (currentBalanceElement) {
                currentBalanceElement.textContent = `Rs. ${parseFloat(data.total_balance).toFixed(2)}`;
            } else {
                console.warn('Total balance element not found');
            }
        } else {
            console.warn('No total balance in response');
        }
        
        closeAdjustmentModal();
        showSuccessModal('Cash adjustment processed successfully');
        
        // Force refresh the cash drawer status
        await refreshCashDrawerStatus();
        
    } catch (error) {
        console.error('Error processing adjustment:', error);
        showErrorModal('Failed to process cash adjustment: ' + error.message);
    } finally {
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    }
}

// Session modal functions
function showOpenSessionModal() {
    const branchId = new URLSearchParams(window.location.search).get('branch');
    
    // Use predefined starting denominations
    const startingDenominations = {
        '1000': 0,
        '500': 4,
        '100': 20,
        '50': 20,
        '20': 20,
        '10': 30,
        '4': 25,
        '1': 10
    };
    
    // Pre-fill the opening denominations with starting values
    Object.entries(startingDenominations).forEach(([denom, count]) => {
        const input = document.querySelector(`input[name="opening_denominations[${denom}]"]`);
        if (input) {
            input.value = count;
        }
    });
    
    // Calculate and set the opening balance
    let totalBalance = 0;
    Object.entries(startingDenominations).forEach(([denom, count]) => {
        totalBalance += parseInt(denom) * count;
    });
    
    // Set the opening balance
    const openingBalanceInput = document.getElementById('openingBalance');
    if (openingBalanceInput) {
        openingBalanceInput.value = totalBalance.toFixed(2);
    }
    
    // Show the modal
    document.getElementById('openSessionModal').classList.remove('hidden');
}

function closeOpenSessionModal() {
    document.getElementById('openSessionModal').classList.add('hidden');
    document.getElementById('openSessionForm').reset();
}

function showCloseSessionModal() {
    const branchId = new URLSearchParams(window.location.search).get('branch');
    
    // Fetch current denominations before showing modal
    fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': 'Bearer ' + document.querySelector('meta[name="auth-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.denominations) {
            // Pre-fill the closing denominations with current values
            Object.entries(data.denominations).forEach(([denom, count]) => {
                const input = document.querySelector(`input[name="closing_denominations[${denom}]"]`);
                if (input) {
                    input.value = count;
                }
            });

            // Calculate and set the total closing balance
            const totalBalance = Object.entries(data.denominations).reduce((total, [denom, count]) => {
                return total + (parseFloat(denom) * parseInt(count));
            }, 0);

            const closingBalanceInput = document.querySelector('input[name="closing_balance"]');
            if (closingBalanceInput) {
                closingBalanceInput.value = totalBalance.toFixed(2);
            }
        }
        // Show the modal after fetching denominations
        document.getElementById('closeSessionModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error fetching denominations:', error);
        showErrorModal('Failed to fetch current denominations', error.message);
    });
}

function closeCloseSessionModal() {
    document.getElementById('closeSessionModal').classList.add('hidden');
    document.getElementById('closeSessionForm').reset();
}

// Add event listener for the overlay open session button
document.addEventListener('DOMContentLoaded', async function() {
    const overlayOpenSessionBtn = document.getElementById('overlayOpenSessionBtn');
    if (overlayOpenSessionBtn) {
        overlayOpenSessionBtn.addEventListener('click', function() {
            showOpenSessionModal();
            hideSessionBlur(); // Hide blur when opening the modal
        });
    }
    
    // Initial session check - make it async and await the result
    const hasSession = await checkSessionStatus();
    if (!hasSession) {
        showSessionBlur();
    }
});

// Also add a check when the page becomes visible again
document.addEventListener('visibilitychange', async function() {
    if (document.visibilityState === 'visible') {
        await checkSessionStatus();
    }
});
</script>
@endpush
