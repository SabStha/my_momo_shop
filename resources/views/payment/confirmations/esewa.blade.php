<!-- eSewa Payment Confirmation -->
<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" id="esewa-confirmation" style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-xl">💳</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">eSewa Payment</h3>
                        <p class="text-green-100 text-sm">Digital Wallet Payment</p>
                    </div>
                </div>
                <button onclick="closePaymentConfirmation('esewa')" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="p-6 space-y-4">
            <!-- Amount -->
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-800" id="esewa-amount">Rs.0.00</div>
                <p class="text-gray-600 text-sm">Payment Amount</p>
            </div>

            <!-- eSewa Interface -->
            <div class="bg-gray-100 rounded-lg p-6 text-center">
                <div class="w-32 h-32 bg-green-200 rounded-lg mx-auto mb-4 flex items-center justify-center">
                    <span class="text-4xl">💳</span>
                </div>
                <p class="text-sm text-gray-600">Redirecting to eSewa...</p>
            </div>

            <!-- Payment Status -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                    <span class="text-yellow-800 font-medium">Connecting to eSewa...</span>
                </div>
            </div>

            <!-- Instructions -->
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>You will be redirected to eSewa</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>Login to your eSewa account</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>Confirm the payment</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3 pt-4">
                <button onclick="closePaymentConfirmation('esewa')" 
                        class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button onclick="redirectToEsewa()" 
                        class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors">
                    Continue to eSewa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showEsewaConfirmation(amount) {
    document.getElementById('esewa-amount').textContent = `Rs.${amount.toFixed(2)}`;
    document.getElementById('esewa-confirmation').style.display = 'flex';
}

function closePaymentConfirmation(type) {
    document.getElementById(`${type}-confirmation`).style.display = 'none';
}

function redirectToEsewa() {
    const statusElement = document.querySelector('#esewa-confirmation .bg-yellow-50');
    statusElement.innerHTML = `
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-blue-400 rounded-full animate-pulse"></div>
            <span class="text-blue-800 font-medium">Redirecting to eSewa...</span>
        </div>
    `;
    
    // Get the current order amount from the page
    const amount = parseFloat(document.getElementById('esewa-amount').textContent.replace('Rs.', ''));
    
    // Create a form to submit to eSewa
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("payments.initialize") }}';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add payment method
    const paymentMethod = document.createElement('input');
    paymentMethod.type = 'hidden';
    paymentMethod.name = 'payment_method';
    paymentMethod.value = 'esewa';
    form.appendChild(paymentMethod);
    
    // Add amount
    const amountInput = document.createElement('input');
    amountInput.type = 'hidden';
    amountInput.name = 'amount';
    amountInput.value = amount;
    form.appendChild(amountInput);
    
    // Add order ID if available
    const orderId = document.querySelector('input[name="order_id"]')?.value;
    if (orderId) {
        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'order_id';
        orderInput.value = orderId;
        form.appendChild(orderInput);
    }
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
}
</script> 