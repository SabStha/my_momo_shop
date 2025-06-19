{{-- resources/views/partials/payment-modals.blade.php --}}

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
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
<div id="errorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
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

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 id="loadingModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2">Processing...</h3>
            <div class="mt-2 px-7 py-3">
                <p id="loadingModalMessage" class="text-sm text-gray-500">Please wait while we process your request.</p>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Process Payment</h3>
                <button onclick="closePaymentModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="paymentForm">
                <input type="hidden" id="orderIdInput" name="order_id">
                <input type="hidden" name="branch_id" value="{{ session('selected_branch_id', 1) }}">
                
                <div class="mb-4">
                    <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="paymentMethod" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="wallet">Wallet</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="amount" name="amount" step="0.01" required>
                </div>

                <div id="cashFields" class="space-y-4" style="display: none;">
                    <div>
                        <label for="amountReceived" class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                        <input type="number" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="amountReceived" name="amount_received" step="0.01">
                    </div>
                    <div>
                        <label for="changeAmount" class="block text-sm font-medium text-gray-700 mb-2">Change Amount</label>
                        <input type="number" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" id="changeAmount" name="change_amount" step="0.01" readonly>
                    </div>
                </div>

                <div id="cardFields" style="display: none;">
                    <div class="mb-4">
                        <label for="referenceNumber" class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="referenceNumber" name="reference_number">
                    </div>
                </div>

                <div id="walletFields" class="space-y-4" style="display: none;">
                    <div>
                        <label for="walletNumber" class="block text-sm font-medium text-gray-700 mb-2">Wallet Number</label>
                        <div class="flex">
                            <input type="text" class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="walletNumber" name="wallet_number">
                            <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-r-md" onclick="checkWalletBalance()">
                                Check Balance
                            </button>
                        </div>
                    </div>
                    <div id="walletBalanceInfo" class="hidden p-3 rounded-md"></div>
                </div>
            </form>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closePaymentModal()" class="px-4 py-2 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                    Cancel
                </button>
                <button onclick="submitPayment()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                    Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Session Modal -->
<div id="sessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
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
                    <div class="space-y-3" id="sessionDenominations">
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
                        @endphp
                        @foreach($denominations as $value => $label)
                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                <div class="flex items-center space-x-2">
                                    <button type="button" 
                                            data-action="decrement"
                                            data-denomination="{{ $value }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <input type="number" 
                                           id="session_denomination_{{ $value }}"
                                           data-denomination="{{ $value }}"
                                           min="0" 
                                           class="w-16 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="0">
                                    <button type="button"
                                            data-action="increment"
                                            data-denomination="{{ $value }}"
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
                    <textarea id="sessionNotes" rows="10" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add any notes here..."></textarea>
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
// Payment Modal Functions
document.getElementById('paymentMethod').addEventListener('change', function() {
    const method = this.value;
    document.getElementById('cashFields').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('cardFields').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('walletFields').style.display = method === 'wallet' ? 'block' : 'none';
});

document.getElementById('amountReceived').addEventListener('input', function() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const received = parseFloat(this.value) || 0;
    document.getElementById('changeAmount').value = (received - amount).toFixed(2);
});

function checkWalletBalance() {
    const walletNumber = document.getElementById('walletNumber').value;
    if (!walletNumber) {
        alert('Please enter a wallet number');
        return;
    }

    fetch(`/admin/payments/wallet/number/${walletNumber}`)
        .then(response => response.json())
        .then(data => {
            const balanceInfo = document.getElementById('walletBalanceInfo');
            if (data.success) {
                balanceInfo.innerHTML = `Available Balance: ₹${data.balance}`;
                balanceInfo.className = 'bg-blue-100 border border-blue-400 text-blue-700 p-3 rounded-md';
                balanceInfo.classList.remove('hidden');
            } else {
                balanceInfo.innerHTML = data.message;
                balanceInfo.className = 'bg-yellow-100 border border-yellow-400 text-yellow-700 p-3 rounded-md';
                balanceInfo.classList.remove('hidden');
            }
        });
}

function submitPayment() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    const orderId = formData.get('order_id');

    fetch(`/admin/payments/order/${orderId}/process`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment processed successfully');
            closePaymentModal();
            // Reload the page after a short delay
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert(data.message);
        }
    });
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

// Global function to open payment modal
function processPayment(orderId) {
    document.getElementById('orderIdInput').value = orderId;
    document.getElementById('paymentModal').classList.remove('hidden');
}
</script>