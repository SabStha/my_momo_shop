<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Viewer - Amsko Momo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-6 py-8">
        <div class="max-w-6xl mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-utensils text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold">Amsko Momo</h1>
                            <p class="text-blue-100 text-lg">Payment Terminal</p>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end space-y-2">
                        <div class="flex items-center space-x-2 bg-blue-800 bg-opacity-30 rounded-lg px-3 py-1">
                            <button id="soundMuteBtn" onclick="toggleSoundMute()" class="text-white hover:text-blue-200 transition-colors" title="Mute sounds">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <input type="range" id="soundVolumeSlider" min="0" max="100" value="70" 
                                   onchange="setSoundVolume(this.value / 100)" 
                                   class="w-16 h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer">
                            <span class="text-xs text-blue-100">Sound</span>
                            <button onclick="playPaymentSuccess()" class="text-xs text-green-200 hover:text-green-400 px-2 py-1 rounded" title="Test success sound">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="playPaymentFailed()" class="text-xs text-red-200 hover:text-red-400 px-2 py-1 rounded" title="Test failure sound">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="text-lg text-blue-200 mb-2">Branch #{{ request('branch', 'N/A') }}</div>
                        <div class="text-2xl font-bold" id="currentTime"></div>
                        <div class="text-sm text-blue-200 mt-1">Today's Date</div>
                    </div>
                </div>
            </div>

            <!-- Welcome Message (shown when no order) -->
            <div id="welcomeMessage" class="p-12 text-center">
                <div class="mb-12">
                    <div class="w-32 h-32 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl">
                        <i class="fas fa-utensils text-white text-5xl"></i>
                    </div>
                </div>
                <h2 class="text-5xl font-bold text-gray-800 mb-6">Welcome!</h2>
                <p class="text-gray-600 mb-12 text-2xl max-w-3xl mx-auto">Your order will appear here shortly. Get ready for an amazing culinary experience!</p>
                
                <!-- Features Grid -->
                <div class="grid grid-cols-3 gap-8 mb-12 max-w-4xl mx-auto">
                    <div class="text-center">
                        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-clock text-red-500 text-3xl"></i>
                        </div>
                        <p class="text-lg text-gray-600 font-medium">Quick Service</p>
                    </div>
                    <div class="text-center">
                        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-leaf text-green-500 text-3xl"></i>
                        </div>
                        <p class="text-lg text-gray-600 font-medium">Fresh Food</p>
                    </div>
                    <div class="text-center">
                        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-credit-card text-blue-500 text-3xl"></i>
                        </div>
                        <p class="text-lg text-gray-600 font-medium">Easy Payment</p>
                    </div>
                </div>

                <!-- Loading Animation -->
                <div class="flex justify-center">
                    <div class="animate-pulse">
                        <div class="w-12 h-12 bg-blue-500 rounded-full"></div>
                    </div>
                </div>
            </div>

            <!-- Order Details (hidden when no order) -->
            <div id="orderDetails" class="hidden">
                <!-- Order Header -->
                <div class="bg-gray-50 p-8 border-b">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-3xl font-bold text-gray-800">Order #<span id="orderNumber">-</span></h3>
                            <p class="text-lg text-gray-500 mt-2" id="orderTime">-</p>
                        </div>
                        <div class="text-right">
                            <div class="text-5xl font-bold text-gray-800" id="total">Rs 0.00</div>
                            <div class="text-lg text-gray-500">Total Amount</div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex">
                    <!-- Left Column - Order Items -->
                    <div class="flex-1 p-8 border-r border-gray-200">
                        <h4 class="text-2xl font-bold text-gray-800 mb-6">Order Items</h4>
                        <div id="orderItems" class="space-y-4">
                            <!-- Items will be populated here -->
                        </div>

                        <!-- Order Summary -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h5 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h5>
                            <div class="space-y-3">
                                <div class="flex justify-between text-lg text-gray-600">
                                    <span>Subtotal</span>
                                    <span id="subtotal">Rs 0.00</span>
                                </div>
                                <div class="flex justify-between text-lg text-gray-600">
                                    <span>Tax</span>
                                    <span id="tax">Rs 0.00</span>
                                </div>
                                <div class="flex justify-between font-bold text-2xl border-t pt-3">
                                    <span>Total</span>
                                    <span id="totalAmount">Rs 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status -->
                        <div class="mt-8" id="paymentStatus">
                            <!-- Status will be populated here -->
                        </div>
                    </div>

                    <!-- Right Column - Payment Processing -->
                    <div class="w-1/2 p-8">
                        <!-- Payment Processing Details -->
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl border p-8" id="paymentProcessingDetails">
                            <h4 class="text-2xl font-bold text-gray-800 mb-6">Payment Method</h4>
                            
                            <!-- Payment Method Options -->
                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <button class="payment-method-option p-6 border-2 rounded-2xl text-center transition-all duration-200 hover:shadow-lg hover:scale-105" data-method="cash">
                                    <i class="fas fa-money-bill-wave text-4xl mb-3 text-green-500"></i>
                                    <div class="text-lg font-medium">Cash</div>
                                </button>
                                <button class="payment-method-option p-6 border-2 rounded-2xl text-center transition-all duration-200 hover:shadow-lg hover:scale-105" data-method="card">
                                    <i class="fas fa-credit-card text-4xl mb-3 text-blue-500"></i>
                                    <div class="text-lg font-medium">Card</div>
                                </button>
                                <button class="payment-method-option p-6 border-2 rounded-2xl text-center transition-all duration-200 hover:shadow-lg hover:scale-105" data-method="wallet">
                                    <i class="fas fa-wallet text-4xl mb-3 text-purple-500"></i>
                                    <div class="text-lg font-medium">Wallet</div>
                                </button>
                                <button class="payment-method-option p-6 border-2 rounded-2xl text-center transition-all duration-200 hover:shadow-lg hover:scale-105" data-method="khalti">
                                    <i class="fas fa-qrcode text-4xl mb-3 text-orange-500"></i>
                                    <div class="text-lg font-medium">Khalti</div>
                                </button>
                            </div>

                            <!-- Payment Details -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-4 bg-white rounded-xl shadow-sm">
                                    <span class="text-lg text-gray-600">Payment Method</span>
                                    <span id="paymentMethodDisplay" class="font-bold text-xl text-gray-800">-</span>
                                </div>
                                <div class="flex justify-between items-center p-4 bg-white rounded-xl shadow-sm">
                                    <span class="text-lg text-gray-600">Amount</span>
                                    <span id="paymentAmountDisplay" class="font-bold text-xl text-gray-800">-</span>
                                </div>
                                <div id="cashPaymentDetails" class="hidden space-y-4">
                                    <div class="flex justify-between items-center p-4 bg-white rounded-xl shadow-sm">
                                        <span class="text-lg text-gray-600">Amount Received</span>
                                        <span id="amountReceivedDisplay" class="font-bold text-xl text-green-600">-</span>
                                    </div>
                                    <div class="flex justify-between items-center p-4 bg-white rounded-xl shadow-sm">
                                        <span class="text-lg text-gray-600">Change</span>
                                        <span id="changeAmountDisplay" class="font-bold text-xl text-blue-600">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Instructions -->
                            <div id="paymentInstructions" class="mt-6 p-4 bg-blue-50 rounded-xl hidden">
                                <h5 class="font-bold text-blue-800 mb-3 text-lg">Payment Instructions</h5>
                                <p id="instructionText" class="text-blue-700 text-lg"></p>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="mt-8" id="khaltiQR">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-96 h-96 bg-white p-8 rounded-3xl shadow-2xl flex items-center justify-center border-2 border-gray-100">
                                    <!-- QR code will be generated here -->
                                </div>
                                <div class="text-center mt-6 text-lg text-gray-600">
                                    <!-- Payment method text will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thank You Modal -->
    <div id="thankYouModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl shadow-2xl p-12 max-w-2xl w-full mx-8 flex flex-col items-center animate-fade-in">
            <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-full p-8 mb-8 shadow-2xl">
                <i class="fas fa-check text-white text-6xl"></i>
            </div>
            <h2 class="text-5xl font-bold text-gray-800 mb-6 text-center">Thank You!</h2>
            <p class="text-gray-600 mb-8 text-center text-2xl">Your payment was successful.<br>We appreciate your business and hope you enjoy your meal!</p>
            <div class="flex space-x-6">
                <button id="closeThankYouModal" class="px-10 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl font-bold text-lg shadow-lg hover:from-green-600 hover:to-green-700 transition-all duration-200">
                    <i class="fas fa-check mr-3"></i>Close
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Processing Modal -->
    <div id="processingModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl shadow-2xl p-12 max-w-2xl w-full mx-8 flex flex-col items-center">
            <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-full p-8 mb-8 shadow-2xl animate-pulse">
                <i class="fas fa-spinner fa-spin text-white text-6xl"></i>
            </div>
            <h2 class="text-4xl font-bold text-gray-800 mb-6 text-center">Processing Payment</h2>
            <p class="text-gray-600 mb-8 text-center text-xl">Please wait while we process your payment...</p>
        </div>
    </div>

    <style>
    @keyframes fade-in {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    .animate-fade-in { animation: fade-in 0.4s ease; }
    
    .payment-method-option.selected {
        @apply bg-blue-100 border-blue-500 shadow-lg;
    }
    
    .payment-method-option:hover {
        @apply transform scale-105;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .container {
            @apply px-4;
        }
        .max-w-6xl {
            @apply max-w-full;
        }
        .flex {
            @apply flex-col;
        }
        .w-1/2 {
            @apply w-full;
        }
        .border-r {
            @apply border-r-0 border-b;
        }
    }
    </style>

    <script>
        // State management
        const state = {
            orderId: null,
            branchId: null,
            pollInterval: null,
            lastUpdate: null
        };

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true, 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Update time every second
        setInterval(updateTime, 1000);
        updateTime(); // Initial call

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount || 0);
        }

        // Add QR code generation function
        function generateQRCode(containerId, data, methodText) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = '';

            // Add a wrapper div for QR code
            const wrapper = document.createElement('div');
            wrapper.className = 'flex flex-col items-center justify-center p-8 bg-white rounded-3xl shadow-2xl border-2 border-gray-100';
            container.appendChild(wrapper);

            // Add a div for the QR code
            const qrDiv = document.createElement('div');
            wrapper.appendChild(qrDiv);

            // Add a div for the payment method text
            const textDiv = document.createElement('div');
            textDiv.className = 'text-center mt-6 text-lg text-gray-600 font-medium';
            textDiv.textContent = methodText;
            wrapper.appendChild(textDiv);

            // Only generate QR if data is valid
            if (data && data.orderId && data.amount && data.amount !== '0' && data.amount !== 'Rs 0.00') {
                new QRCode(qrDiv, {
                    text: JSON.stringify(data),
                    width: 320,
                    height: 320,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } else {
                qrDiv.innerHTML = '<div class="text-red-500 text-center"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><div class="text-xl">Cannot generate QR: missing order or amount</div></div>';
            }
        }

        // Show payment instructions
        function showPaymentInstructions(method) {
            const instructionsDiv = document.getElementById('paymentInstructions');
            const instructionText = document.getElementById('instructionText');
            
            if (!instructionsDiv || !instructionText) return;

            const instructions = {
                cash: 'Please hand over the exact amount to the cashier. Change will be provided if needed.',
                card: 'Please insert or tap your card on the card reader. Follow the prompts on the terminal.',
                wallet: 'Please scan the QR code with your mobile wallet app to complete the payment.',
                khalti: 'Please scan the QR code with your Khalti app to complete the payment.'
            };

            instructionText.textContent = instructions[method] || '';
            instructionsDiv.classList.remove('hidden');
        }

        // Add click handlers for payment method buttons
        document.querySelectorAll('.payment-method-option').forEach(button => {
            button.addEventListener('click', function() {
                playSelectSound(); // Play select sound
                const method = this.dataset.method;
                console.log('Payment method selected in viewer:', method);
                
                // Update UI
                document.querySelectorAll('.payment-method-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                
                // Update payment method display
                const paymentMethodDisplay = document.getElementById('paymentMethodDisplay');
                if (paymentMethodDisplay) {
                    paymentMethodDisplay.textContent = method.toUpperCase();
                }
                
                // Show/hide cash payment details
                const cashPaymentDetails = document.getElementById('cashPaymentDetails');
                if (cashPaymentDetails) {
                    if (method === 'cash') {
                        cashPaymentDetails.classList.remove('hidden');
                    } else {
                        cashPaymentDetails.classList.add('hidden');
                    }
                }

                // Show payment instructions
                showPaymentInstructions(method);

                // Handle QR code generation
                const qrContainer = document.getElementById('khaltiQR');
                if (qrContainer) {
                    qrContainer.innerHTML = '';
                    if (method === 'wallet') {
                        const walletData = {
                            type: 'wallet',
                            orderId: state.orderId,
                            amount: document.getElementById('paymentAmountDisplay')?.textContent || '0',
                            timestamp: new Date().toISOString()
                        };
                        generateQRCode('khaltiQR', walletData, 'Scan with Mobile Wallet');
                        playScanSound(); // Play scan sound
                    } else if (method === 'khalti') {
                        const khaltiData = {
                            type: 'khalti',
                            orderId: state.orderId,
                            amount: document.getElementById('paymentAmountDisplay')?.textContent || '0',
                            timestamp: new Date().toISOString()
                        };
                        generateQRCode('khaltiQR', khaltiData, 'Scan with Khalti App');
                        playScanSound(); // Play scan sound
                    }
                }
                
                // Send message to payment manager
                if (window.opener) {
                    window.opener.postMessage({
                        type: 'UPDATE_PAYMENT_METHOD',
                        method: method
                    }, window.location.origin);
                }
            });
        });

        // Handle messages from payment manager
        window.addEventListener('message', function(event) {
            // Only accept messages from the same origin
            if (event.origin !== window.location.origin) {
                return;
            }

            console.log('Payment viewer received message:', event.data);

            switch (event.data.type) {
                case 'UPDATE_ORDER':
                    console.log('Updating order:', event.data.orderId);
                    state.orderId = event.data.orderId;
                    // Fetch the order details immediately
                    pollOrderUpdates();
                    break;
                    
                case 'UPDATE_PAYMENT_METHOD':
                    console.log('Updating payment method:', event.data.method);
                    // Update the payment method display
                    const paymentMethodDisplay = document.getElementById('paymentMethodDisplay');
                    if (paymentMethodDisplay) {
                        paymentMethodDisplay.textContent = event.data.method.toUpperCase();
                    }
                    
                    // Highlight the selected payment method
                    document.querySelectorAll('.payment-method-option').forEach(option => {
                        option.classList.remove('selected', 'ring-2', 'ring-blue-500', 'bg-blue-50');
                        if (option.dataset.method === event.data.method) {
                            option.classList.add('selected', 'ring-2', 'ring-blue-500', 'bg-blue-50');
                        }
                    });
                    
                    // Show payment instructions
                    showPaymentInstructions(event.data.method);
                    break;
                    
                case 'UPDATE_PAYMENT_AMOUNT':
                    console.log('Updating payment amount:', event.data.amount);
                    const paymentAmountDisplay = document.getElementById('paymentAmountDisplay');
                    if (paymentAmountDisplay) {
                        paymentAmountDisplay.textContent = formatCurrency(event.data.amount);
                    }
                    break;
                    
                default:
                    console.log('Unknown message type:', event.data.type);
            }
        });

        // Update order display
        function updateOrderDisplay(order) {
            if (!order) {
                console.error('No order data provided');
                return;
            }

            try {
                console.log('Full order data:', order);
                console.log('Order items:', order.items);

                // Check if order has been updated
                const orderKey = JSON.stringify({
                    id: order.id,
                    total: order.total,
                    payment_status: order.payment_status,
                    updated_at: order.updated_at,
                    payment_method: order.payment_method,
                    amount_received: order.amount_received,
                    change_amount: order.change_amount
                });

                if (orderKey === state.lastUpdate) {
                    console.log('Order not changed, skipping update');
                    return;
                }

                state.lastUpdate = orderKey;

                // Show order details and hide welcome message
                document.getElementById('welcomeMessage').classList.add('hidden');
                document.getElementById('orderDetails').classList.remove('hidden');

                // Update order header
                const orderNumber = document.getElementById('orderNumber');
                const orderTime = document.getElementById('orderTime');
                if (orderNumber) orderNumber.textContent = order.order_number || order.id;
                if (orderTime) orderTime.textContent = new Date().toLocaleTimeString('en-US', { hour12: true });

                // Update order items
                const itemsContainer = document.getElementById('orderItems');
                if (itemsContainer) {
                    const items = order.items || order.order_items || [];
                    console.log('Items to display:', items);

                    if (items && items.length > 0) {
                        itemsContainer.innerHTML = items.map(item => {
                            console.log('Processing item:', item);
                            const itemName = item.product?.name || item.item_name || item.name || 'Unknown Item';
                            console.log('Item name resolved to:', itemName);
                            return `
                                <div class="flex justify-between items-center p-6 bg-gray-50 rounded-2xl shadow-sm">
                                    <div class="flex-1">
                                        <div class="font-bold text-xl text-gray-800">${itemName}</div>
                                        <div class="text-lg text-gray-500 mt-1">${item.quantity} × ${formatCurrency(item.price)}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-xl text-gray-800">${formatCurrency(item.subtotal)}</div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        console.log('No items found in order:', order);
                        itemsContainer.innerHTML = '<div class="text-center text-gray-500 py-12"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><div class="text-xl">No items found</div></div>';
                    }
                }

                // Update totals
                const subtotalElement = document.getElementById('subtotal');
                const totalElement = document.getElementById('total');
                const totalAmountElement = document.getElementById('totalAmount');
                const taxElement = document.getElementById('tax');
                
                if (subtotalElement) subtotalElement.textContent = formatCurrency(order.subtotal);
                if (totalElement) totalElement.textContent = formatCurrency(order.total);
                if (totalAmountElement) totalAmountElement.textContent = formatCurrency(order.total);
                
                // Calculate tax (assuming 10% tax rate)
                const tax = (order.total - order.subtotal) || (order.total * 0.1);
                if (taxElement) taxElement.textContent = formatCurrency(tax);

                // Update payment status and method
                const paymentStatus = document.getElementById('paymentStatus');
                if (paymentStatus) {
                    const statusColor = order.payment_status === 'paid' ? 'green' : 'yellow';
                    const statusIcon = order.payment_status === 'paid' ? 'check-circle' : 'clock';
                    const statusText = order.payment_status === 'paid' ? 'Payment Complete' : 'Awaiting Payment';
                    const paymentMethod = order.payment_method ? 
                        `<div class="text-lg text-gray-500 mt-3">Payment Method: ${order.payment_method.toUpperCase()}</div>` : '';
                    
                    paymentStatus.innerHTML = `
                        <div class="flex flex-col items-center space-y-4 p-6 bg-${statusColor}-50 rounded-2xl border border-${statusColor}-200">
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-${statusIcon} text-${statusColor}-500 text-4xl"></i>
                                <span class="text-${statusColor}-800 font-bold text-2xl">${statusText}</span>
                            </div>
                            ${paymentMethod}
                        </div>
                    `;
                }

                // Show thank you modal if payment is complete
                if (order.payment_status === 'paid') {
                    showThankYouModal();
                    playPaymentSuccess(); // Play success sound
                }

                // Update payment processing details
                const paymentProcessingDetails = document.getElementById('paymentProcessingDetails');
                const paymentMethodDisplay = document.getElementById('paymentMethodDisplay');
                const paymentAmountDisplay = document.getElementById('paymentAmountDisplay');
                const cashPaymentDetails = document.getElementById('cashPaymentDetails');
                const amountReceivedDisplay = document.getElementById('amountReceivedDisplay');
                const changeAmountDisplay = document.getElementById('changeAmountDisplay');

                if (paymentProcessingDetails && paymentMethodDisplay && paymentAmountDisplay) {
                    // Show payment method
                    if (order.payment_method) {
                        paymentMethodDisplay.textContent = order.payment_method.toUpperCase();
                        // Highlight the selected payment method
                        document.querySelectorAll('.payment-method-option').forEach(option => {
                            option.classList.remove('selected');
                            if (option.dataset.method === order.payment_method) {
                                option.classList.add('selected');
                            }
                        });
                        // Show payment instructions
                        showPaymentInstructions(order.payment_method);
                    }

                    // Show payment amount
                    paymentAmountDisplay.textContent = formatCurrency(order.total);

                    // Show cash payment details if payment method is cash
                    if (order.payment_method === 'cash') {
                        cashPaymentDetails.classList.remove('hidden');
                        if (amountReceivedDisplay && order.amount_received) {
                            amountReceivedDisplay.textContent = formatCurrency(order.amount_received);
                        }
                        if (changeAmountDisplay && order.change_amount) {
                            changeAmountDisplay.textContent = formatCurrency(order.change_amount);
                        }
                    } else {
                        cashPaymentDetails.classList.add('hidden');
                    }
                }

                // Update QR code if payment is pending
                if (order.payment_status !== 'paid') {
                    const qrContainer = document.getElementById('khaltiQR');
                    if (qrContainer) {
                        qrContainer.innerHTML = '';
                    }
                } else {
                    const qrContainer = document.getElementById('khaltiQR');
                    if (qrContainer) {
                        qrContainer.innerHTML = `
                            <div class="text-center text-green-600 p-12">
                                <i class="fas fa-check-circle text-8xl mb-6"></i>
                                <div class="text-4xl font-bold mb-4">Payment Complete</div>
                                <div class="text-2xl">Thank you for your payment!</div>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                playErrorSound(); // Play error sound
                console.error('Error updating order display:', error);
                const orderItems = document.getElementById('orderItems');
                if (orderItems) {
                    orderItems.innerHTML = `<div class="text-red-500 p-8 text-center"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><div class="text-xl">Error: ${error.message}</div></div>`;
                }
            }
        }

        // Poll for order updates
        async function pollOrderUpdates() {
            try {
                if (!state.orderId) {
                    console.log('No order ID, skipping poll');
                    return;
                }

                const response = await fetch(`/api/customer/active-order?order=${state.orderId}&branch=${state.branchId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Raw API response:', data);

                if (data.error) {
                    throw new Error(data.error);
                }

                if (data.order) {
                    console.log('Polled order data:', data);
                    updateOrderDisplay(data.order);
                } else if (!state.orderId) {
                    // Only show welcome message if no order is selected
                    document.getElementById('welcomeMessage').classList.remove('hidden');
                    document.getElementById('orderDetails').classList.add('hidden');
                }
            } catch (error) {
                playErrorSound(); // Play error sound
                console.error('Error polling for updates:', error);
                // Show error in the UI
                const orderItems = document.getElementById('orderItems');
                if (orderItems) {
                    orderItems.innerHTML = `<div class="text-red-500 p-8 text-center"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><div class="text-xl">Error: ${error.message}</div></div>`;
                }
            }
        }

        // Initialize order display on load
        async function initializeOrderDisplay() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                state.orderId = urlParams.get('order');
                state.branchId = urlParams.get('branch');

                console.log('Payment viewer initialization:', { 
                    orderId: state.orderId, 
                    branchId: state.branchId,
                    url: window.location.href,
                    params: Object.fromEntries(urlParams.entries())
                });

                if (!state.branchId) {
                    console.error('Missing branch ID in URL:', window.location.href);
                    document.getElementById('orderItems').innerHTML = '<div class="text-red-500 p-8 text-center"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><div class="text-xl">Error: Branch ID is required</div></div>';
                    return;
                }

                // Initial order fetch
                await pollOrderUpdates();

                // Start polling for updates
                state.pollInterval = setInterval(pollOrderUpdates, 5000);

            } catch (error) {
                playErrorSound(); // Play error sound
                console.error('Error initializing order display:', error);
                document.getElementById('orderItems').innerHTML = '<div class="text-red-500 p-8 text-center"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><div class="text-xl">Error: Failed to initialize order display</div></div>';
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (state.pollInterval) {
                clearInterval(state.pollInterval);
            }
        });

        // Call initializeOrderDisplay when the page loads
        document.addEventListener('DOMContentLoaded', initializeOrderDisplay);

        // Add click event listeners to payment method options in viewer
        document.addEventListener('click', function(event) {
            if (event.target.closest('.payment-method-option')) {
                const methodOption = event.target.closest('.payment-method-option');
                const method = methodOption.dataset.method;
                
                console.log('Payment method selected in viewer:', method);
                
                // Highlight the selected payment method in viewer
                document.querySelectorAll('.payment-method-option').forEach(option => {
                    option.classList.remove('selected', 'ring-2', 'ring-blue-500', 'bg-blue-50');
                });
                methodOption.classList.add('selected', 'ring-2', 'ring-blue-500', 'bg-blue-50');
                
                // Update payment method display
                const paymentMethodDisplay = document.getElementById('paymentMethodDisplay');
                if (paymentMethodDisplay) {
                    paymentMethodDisplay.textContent = method.toUpperCase();
                }
                
                // Show payment instructions
                showPaymentInstructions(method);
                
                // Send message to manager to update payment method selection
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'VIEWER_PAYMENT_METHOD_SELECTED',
                        method: method
                    }, window.location.origin);
                }
            }
        });

        function showThankYouModal() {
            const modal = document.getElementById('thankYouModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Auto-close after 8 seconds
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 8000);
            }
        }

        function showProcessingModal() {
            const modal = document.getElementById('processingModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function hideProcessingModal() {
            const modal = document.getElementById('processingModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.getElementById('closeThankYouModal')?.addEventListener('click', function() {
            document.getElementById('thankYouModal').classList.add('hidden');
        });

        // --- SoundManager and sound functions ---
        class SoundManager {
            constructor() {
                this.audioContext = null;
                this.isMuted = false;
                this.volume = 0.7;
                this.initializeAudioContext();
                this.loadUserPreferences();
            }
            initializeAudioContext() {
                try {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                } catch (error) {
                    console.log('Web Audio API not supported:', error);
                }
            }
            loadUserPreferences() {
                const savedVolume = localStorage.getItem('paymentViewerVolume');
                const savedMuted = localStorage.getItem('paymentViewerMuted');
                if (savedVolume !== null) this.volume = parseFloat(savedVolume);
                if (savedMuted !== null) this.isMuted = JSON.parse(savedMuted);
            }
            saveUserPreferences() {
                localStorage.setItem('paymentViewerVolume', this.volume.toString());
                localStorage.setItem('paymentViewerMuted', this.isMuted.toString());
            }
            playTone(frequency, duration, type = 'sine') {
                if (this.isMuted || !this.audioContext) return;
                try {
                    const oscillator = this.audioContext.createOscillator();
                    const gainNode = this.audioContext.createGain();
                    oscillator.connect(gainNode);
                    gainNode.connect(this.audioContext.destination);
                    oscillator.frequency.setValueAtTime(frequency, this.audioContext.currentTime);
                    oscillator.type = type;
                    gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(this.volume * 0.3, this.audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + duration);
                    oscillator.start(this.audioContext.currentTime);
                    oscillator.stop(this.audioContext.currentTime + duration);
                } catch (error) {
                    console.log('Tone generation failed:', error);
                }
            }
            playSound(soundName) {
                if (this.isMuted) return;
                switch (soundName) {
                    case 'paymentSuccess':
                        this.playTone(523.25, 0.2, 'sine');
                        setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100);
                        setTimeout(() => this.playTone(783.99, 0.3, 'sine'), 200);
                        break;
                    case 'paymentFailed':
                        this.playTone(783.99, 0.2, 'sine');
                        setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100);
                        setTimeout(() => this.playTone(523.25, 0.3, 'sine'), 200);
                        break;
                    case 'select':
                        this.playTone(660, 0.08, 'triangle');
                        break;
                    case 'scan':
                        this.playTone(880, 0.1, 'square');
                        break;
                    case 'error':
                        this.playTone(220, 0.15, 'sawtooth');
                        break;
                    default:
                        console.log('Unknown sound:', soundName);
                }
            }
            setVolume(volume) {
                this.volume = Math.max(0, Math.min(1, volume));
                this.saveUserPreferences();
            }
            toggleMute() {
                this.isMuted = !this.isMuted;
                this.saveUserPreferences();
                this.updateMuteButton();
            }
            updateMuteButton() {
                const muteBtn = document.getElementById('soundMuteBtn');
                if (muteBtn) {
                    const icon = muteBtn.querySelector('i');
                    if (this.isMuted) {
                        icon.className = 'fas fa-volume-mute';
                        muteBtn.title = 'Unmute sounds';
                    } else {
                        icon.className = 'fas fa-volume-up';
                        muteBtn.title = 'Mute sounds';
                    }
                }
            }
        }
        let soundManager;
        document.addEventListener('DOMContentLoaded', function() {
            soundManager = new SoundManager();
            soundManager.updateMuteButton();
            document.getElementById('soundVolumeSlider').value = Math.round(soundManager.volume * 100);
            document.addEventListener('click', function initAudio() {
                if (soundManager && soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                document.removeEventListener('click', initAudio);
            }, { once: true });
        });
        function playPaymentSuccess() {
            if (soundManager) {
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('paymentSuccess');
            }
        }
        function playPaymentFailed() {
            if (soundManager) {
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('paymentFailed');
            }
        }
        function playSelectSound() {
            if (soundManager) {
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('select');
            }
        }
        function playScanSound() {
            if (soundManager) {
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('scan');
            }
        }
        function playErrorSound() {
            if (soundManager) {
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('error');
            }
        }
        function toggleSoundMute() {
            if (soundManager) {
                soundManager.toggleMute();
            }
        }
        function setSoundVolume(volume) {
            if (soundManager) {
                soundManager.setVolume(volume);
            }
        }
        // --- End SoundManager ---
    </script>
</body>
</html> 