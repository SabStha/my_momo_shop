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
                    <label for="admin-amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="admin-amount" name="amount" step="0.01" required>
                </div>

                <div id="cashFields" class="space-y-4" style="display: none;">
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
                        <label for="modalWalletNumber2" class="block text-sm font-medium text-gray-700 mb-2">Wallet Number</label>
                        <div class="flex">
                            <input type="text" class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="modalWalletNumber2" name="wallet_number">
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

<script>
// Payment Modal Functions
document.getElementById('paymentMethod').addEventListener('change', function() {
    const method = this.value;
    document.getElementById('cashFields').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('cardFields').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('walletFields').style.display = method === 'wallet' ? 'block' : 'none';
});

function checkWalletBalance() {
    const walletNumber = document.getElementById('modalWalletNumber2').value;
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
    // Try multiple possible form selectors
    let form = document.getElementById('paymentForm');
    
    // If not found, try to find any form with payment-related inputs
    if (!form) {
        form = document.querySelector('form');
        console.log('Using fallback form:', form);
    }
    
    // Check if form exists
    if (!form) {
        console.error('Payment form not found');
        showErrorModal('Error', 'Payment form not found. Please refresh the page and try again.');
        return;
    }
    
    // Check if form is actually an HTMLFormElement
    if (!(form instanceof HTMLFormElement)) {
        console.error('Element found is not a form:', form);
        showErrorModal('Error', 'Invalid form element. Please refresh the page and try again.');
        return;
    }
    
    const formData = new FormData(form);
    const orderId = formData.get('order_id');
    
    // Check if order ID exists
    if (!orderId) {
        console.error('Order ID not found');
        showErrorModal('Error', 'Order ID not found. Please select an order first.');
        return;
    }

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