<!-- Khalti Payment Confirmation -->
<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" id="khalti-confirmation" style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-xl">💜</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Khalti Payment</h3>
                        <p class="text-purple-100 text-sm">Digital Payment Gateway</p>
                    </div>
                </div>
                <button onclick="closePaymentConfirmation('khalti')" class="text-white/80 hover:text-white">
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
                <div class="text-3xl font-bold text-gray-800" id="khalti-amount">Rs.0.00</div>
                <p class="text-gray-600 text-sm">Payment Amount</p>
            </div>

            <!-- Khalti Interface -->
            <div class="bg-gray-100 rounded-lg p-6 text-center">
                <div class="w-32 h-32 bg-purple-200 rounded-lg mx-auto mb-4 flex items-center justify-center">
                    <span class="text-4xl">💜</span>
                </div>
                <p class="text-sm text-gray-600">Connecting to Khalti...</p>
            </div>

            <!-- Payment Status -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                    <span class="text-yellow-800 font-medium">Initializing payment...</span>
                </div>
            </div>

            <!-- Instructions -->
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                    <span>You will be redirected to Khalti</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                    <span>Enter your Khalti PIN</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                    <span>Confirm the payment</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3 pt-4">
                <button onclick="closePaymentConfirmation('khalti')" 
                        class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button onclick="redirectToKhalti()" 
                        class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 transition-colors">
                    Continue to Khalti
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showKhaltiConfirmation(amount) {
    document.getElementById('khalti-amount').textContent = `Rs.${amount.toFixed(2)}`;
    document.getElementById('khalti-confirmation').style.display = 'flex';
}

function closePaymentConfirmation(type) {
    document.getElementById(`${type}-confirmation`).style.display = 'none';
}

function redirectToKhalti() {
    // Simulate redirect to Khalti
    const statusElement = document.querySelector('#khalti-confirmation .bg-yellow-50');
    statusElement.innerHTML = `
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-blue-400 rounded-full animate-pulse"></div>
            <span class="text-blue-800 font-medium">Redirecting to Khalti...</span>
        </div>
    `;
    
    // Simulate successful payment after redirect
    setTimeout(() => {
        statusElement.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-400 rounded-full"></div>
                <span class="text-green-800 font-medium">Payment successful!</span>
            </div>
        `;
        
        // Process order after successful payment
        setTimeout(() => {
            closePaymentConfirmation('khalti');
            processOrder();
        }, 2000);
    }, 3000);
}
</script> 