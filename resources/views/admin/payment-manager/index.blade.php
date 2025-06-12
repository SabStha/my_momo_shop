@extends('layouts.payment')

@section('title', 'Payment Manager')

@section('content')
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
            
            <!-- Cash Drawer Status -->
            <div id="cashDrawerStatus" class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-medium">Cash Drawer Status</h4>
                        <p class="text-sm text-gray-600">Current Balance: <span id="currentBalance">$0.00</span></p>
                    </div>
                    <div id="cashDrawerAlerts" class="hidden">
                        <span class="px-2 py-1 text-sm rounded bg-yellow-100 text-yellow-800">Low Change</span>
                        <span class="px-2 py-1 text-sm rounded bg-red-100 text-red-800">Excess Cash</span>
                    </div>
                </div>
            </div>

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
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 1000</span>
                                    <input type="number" name="currency_received[1000]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 500</span>
                                    <input type="number" name="currency_received[500]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 100</span>
                                    <input type="number" name="currency_received[100]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 50</span>
                                    <input type="number" name="currency_received[50]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 20</span>
                                    <input type="number" name="currency_received[20]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 10</span>
                                    <input type="number" name="currency_received[10]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 4</span>
                                    <input type="number" name="currency_received[4]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Rs. 1</span>
                                    <input type="number" name="currency_received[1]" min="0" class="w-20 text-right rounded-md border-gray-300" placeholder="0">
                                </div>
                                <div class="border-t pt-2 mt-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Total Received</span>
                                        <span class="text-lg font-semibold" id="totalReceived">Rs. 0.00</span>
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
                        <table class="w-full text-base">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-left py-2">Denomination</th>
                                    <th class="text-right py-2">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(is_array($denominations))
                                    @foreach($denominations as $denomination => $count)
                                        <tr class="border-b border-gray-100">
                                            <td class="py-2">{{ (int)$denomination }}</td>
                                            <td class="text-right py-2">{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr class="font-medium border-t-2 border-gray-200">
                                    <td class="py-2">Total</td>
                                    <td class="text-right py-2">{{ (int)$cashDrawer->total_cash }}</td>
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

@endsection

@push('scripts')
<script>
let currentOrderId = null;
let cashDrawerBalance = 0;

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
        '20': 0, '10': 0, '5': 0, '2': 0, '1': 0
    };
    
    let remaining = change;
    const denomValues = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
    
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
        const balance = parseFloat(data.balance || 0);
        document.getElementById('currentBalance').textContent = `Rs. ${balance.toFixed(2)}`;

        // Show alerts if needed
        const alertsDiv = document.getElementById('cashDrawerAlerts');
        alertsDiv.classList.remove('hidden');
        alertsDiv.innerHTML = '';
        
        if (balance < data.minimum_balance) {
            alertsDiv.innerHTML += '<span class="px-2 py-1 text-sm rounded bg-yellow-100 text-yellow-800">Low Change</span>';
        }
        if (balance > data.maximum_balance) {
            alertsDiv.innerHTML += '<span class="px-2 py-1 text-sm rounded bg-red-100 text-red-800">Excess Cash</span>';
        }
    } catch (error) {
        console.error('Error updating cash drawer status:', error);
        document.getElementById('currentBalance').textContent = 'Rs. 0.00';
        document.getElementById('cashDrawerAlerts').classList.add('hidden');
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

// Function to increment denomination
function incrementDenomination(inputName) {
    const input = document.querySelector(`input[name="${inputName}"]`);
    const currentValue = parseInt(input.value) || 0;
    input.value = currentValue + 1;
    input.dispatchEvent(new Event('input'));
}

// Function to decrement denomination
function decrementDenomination(inputName) {
    const input = document.querySelector(`input[name="${inputName}"]`);
    const currentValue = parseInt(input.value) || 0;
    if (currentValue > 0) {
        input.value = currentValue - 1;
        input.dispatchEvent(new Event('input'));
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

document.addEventListener('DOMContentLoaded', function() {
    const cashDrawerBtn = document.getElementById('cashDrawerBtn');
    const cashDrawerDropdown = document.getElementById('cashDrawerDropdown');
    const closeCashDrawer = document.getElementById('closeCashDrawer');

    // Toggle dropdown
    cashDrawerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        cashDrawerDropdown.classList.toggle('hidden');
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

document.addEventListener('DOMContentLoaded', function() {
    const totalReceivedElement = document.getElementById('totalReceived');
    const totalChangeElement = document.getElementById('totalChange');
    const currencyInputs = document.querySelectorAll('input[name^="currency_received"]');
    const changeInputs = document.querySelectorAll('input[name^="change_given"]');
    const orderTotalElement = document.querySelector('input[name="order_total"]') || document.querySelector('#orderTotal');

    // Function to calculate total received
    function calculateTotalReceived() {
        let total = 0;
        currencyInputs.forEach(input => {
            const denomination = parseInt(input.name.match(/\[(.*?)\]/)[1]);
            const count = parseInt(input.value) || 0;
            total += denomination * count;
        });
        totalReceivedElement.textContent = `Rs. ${total.toFixed(2)}`;
        calculateChange();
    }

    // Function to calculate change denominations
    function calculateChangeDenominations(changeAmount) {
        const denominations = [1000, 500, 100, 50, 20, 10, 4, 1];
        let remaining = changeAmount;
        
        // Reset all change inputs
        changeInputs.forEach(input => input.value = '0');
        
        // Calculate denominations
        denominations.forEach(denomination => {
            const input = document.querySelector(`input[name="change_given[${denomination}]"]`);
            if (input && remaining >= denomination) {
                const count = Math.floor(remaining / denomination);
                input.value = count;
                remaining -= count * denomination;
            }
        });
    }

    // Function to calculate change
    function calculateChange() {
        let amountToPay = 0;
        if (orderTotalElement) {
            amountToPay = parseFloat(orderTotalElement.value) || 0;
        }
        const totalReceived = parseFloat(totalReceivedElement.textContent.replace('Rs. ', '')) || 0;
        const change = Math.max(0, totalReceived - amountToPay);
        
        totalChangeElement.textContent = `Rs. ${change.toFixed(2)}`;
        calculateChangeDenominations(change);
    }

    // Listen for changes in currency inputs
    currencyInputs.forEach(input => {
        input.addEventListener('input', calculateTotalReceived);
    });

    // Update when order total changes
    if (orderTotalElement) {
        orderTotalElement.addEventListener('change', calculateChange);
    }

    // Initial calculation
    calculateTotalReceived();
});
</script>
@endpush
