<!-- Card Payment Confirmation -->
<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" id="card-confirmation" style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-xl">💳</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Card Payment</h3>
                        <p class="text-indigo-100 text-sm">Secure Card Processing</p>
                    </div>
                </div>
                <button onclick="closePaymentConfirmation('card')" class="text-white/80 hover:text-white">
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
                <div class="text-3xl font-bold text-gray-800" id="card-amount">Rs.0.00</div>
                <p class="text-gray-600 text-sm">Payment Amount</p>
            </div>

            <!-- Card Form -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                    <input type="text" id="card-number" placeholder="1234 5678 9012 3456" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           maxlength="19">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                        <input type="text" id="card-expiry" placeholder="MM/YY" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               maxlength="5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                        <input type="text" id="card-cvv" placeholder="123" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               maxlength="4">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cardholder Name</label>
                    <input type="text" id="card-name" placeholder="John Doe" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4" id="card-status">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                    <span class="text-yellow-800 font-medium">Ready to process payment</span>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-blue-800 text-sm">Your payment is secured with SSL encryption</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3 pt-4">
                <button onclick="closePaymentConfirmation('card')" 
                        class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button onclick="processCardPayment()" 
                        class="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                    Pay Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showCardConfirmation(amount) {
    document.getElementById('card-amount').textContent = `Rs.${amount.toFixed(2)}`;
    document.getElementById('card-confirmation').style.display = 'flex';
}

function closePaymentConfirmation(type) {
    document.getElementById(`${type}-confirmation`).style.display = 'none';
}

function processCardPayment() {
    // Validate card details
    const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
    const cardExpiry = document.getElementById('card-expiry').value;
    const cardCvv = document.getElementById('card-cvv').value;
    const cardName = document.getElementById('card-name').value;
    
    if (!cardNumber || !cardExpiry || !cardCvv || !cardName) {
        alert('Please fill in all card details');
        return;
    }
    
    if (cardNumber.length < 13) {
        alert('Please enter a valid card number');
        return;
    }
    
    // Show processing status
    const statusElement = document.getElementById('card-status');
    statusElement.innerHTML = `
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-blue-400 rounded-full animate-pulse"></div>
            <span class="text-blue-800 font-medium">Processing payment...</span>
        </div>
    `;
    
    // Simulate payment processing
    setTimeout(() => {
        statusElement.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-400 rounded-full"></div>
                <span class="text-green-800 font-medium">Payment successful!</span>
            </div>
        `;
        
        // Process order after successful payment
        setTimeout(() => {
            closePaymentConfirmation('card');
            processOrder();
        }, 2000);
    }, 3000);
}

// Card number formatting
document.addEventListener('DOMContentLoaded', function() {
    const cardNumber = document.getElementById('card-number');
    if (cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = formattedValue;
        });
    }
    
    const cardExpiry = document.getElementById('card-expiry');
    if (cardExpiry) {
        cardExpiry.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
    
    const cardCvv = document.getElementById('card-cvv');
    if (cardCvv) {
        cardCvv.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
});
</script> 