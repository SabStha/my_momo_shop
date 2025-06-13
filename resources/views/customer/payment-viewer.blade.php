<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white p-4">
                <h1 class="text-xl font-bold">Welcome to Amsko Momo</h1>
            </div>

            <!-- Welcome Message (shown when no order) -->
            <div id="welcomeMessage" class="p-6 text-center">
                <div class="mb-6">
                    <img src="/images/momo-logo.png" alt="Amsko Momo Logo" class="w-32 h-32 mx-auto mb-4">
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Delicious Momos Await!</h2>
                <p class="text-gray-600 mb-6">Your order will appear here shortly. Get ready for an amazing culinary experience!</p>
                <div class="flex justify-center space-x-4">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600">Quick Service</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600">Fresh Food</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600">Easy Payment</p>
                    </div>
                </div>
            </div>

            <!-- Order Details (hidden when no order) -->
            <div id="orderDetails" class="hidden">
                <!-- Order Items -->
                <div class="p-4">
                    <div id="orderItems" class="space-y-2">
                        <!-- Items will be populated here -->
                    </div>

                    <!-- Totals -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="flex justify-between font-bold mt-2">
                            <span>Total</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="mt-4" id="paymentStatus">
                        <!-- Status will be populated here -->
                    </div>

                    <!-- Payment Processing Details -->
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg" id="paymentProcessingDetails">
                        <div class="space-y-3">
                            <!-- Payment Method Options -->
                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <button class="payment-method-option p-2 border rounded-lg text-center" data-method="cash">
                                    <div class="text-sm font-medium">Cash</div>
                                </button>
                                <button class="payment-method-option p-2 border rounded-lg text-center" data-method="card">
                                    <div class="text-sm font-medium">Card</div>
                                </button>
                                <button class="payment-method-option p-2 border rounded-lg text-center" data-method="wallet">
                                    <div class="text-sm font-medium">Wallet</div>
                                </button>
                                <button class="payment-method-option p-2 border rounded-lg text-center" data-method="khalti">
                                    <div class="text-sm font-medium">Khalti</div>
                                </button>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Payment Method</span>
                                <span id="paymentMethodDisplay" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Amount</span>
                                <span id="paymentAmountDisplay" class="font-medium"></span>
                            </div>
                            <div id="cashPaymentDetails" class="hidden">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Amount Received</span>
                                    <span id="amountReceivedDisplay" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Change</span>
                                    <span id="changeAmountDisplay" class="font-medium"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mt-4" id="khaltiQR">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-64 h-64 bg-white p-4 rounded-lg shadow-lg flex items-center justify-center">
                                <!-- QR code will be generated here -->
                            </div>
                            <div class="text-center mt-2 text-sm text-gray-600">
                                <!-- Payment method text will be added here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thank You Modal -->
    <div id="thankYouModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full flex flex-col items-center animate-fade-in">
            <div class="bg-green-100 rounded-full p-4 mb-4">
                <svg class="w-16 h-16 text-green-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Thank You!</h2>
            <p class="text-gray-600 mb-4 text-center">Your payment was successful.<br>We appreciate your business and hope you enjoy your meal!</p>
            <button id="closeThankYouModal" class="mt-2 px-6 py-2 bg-green-500 text-white rounded-lg font-semibold shadow hover:bg-green-600 transition">Close</button>
        </div>
    </div>

    <style>
    @keyframes fade-in {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    .animate-fade-in { animation: fade-in 0.4s ease; }
    </style>

    <script>
        // State management
        const state = {
            orderId: null,
            branchId: null,
            pollInterval: null,
            lastUpdate: null
        };

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
            wrapper.className = 'flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-md';
            container.appendChild(wrapper);

            // Add a div for the QR code
            const qrDiv = document.createElement('div');
            wrapper.appendChild(qrDiv);

            // Add a div for the payment method text
            const textDiv = document.createElement('div');
            textDiv.className = 'text-center mt-2 text-sm text-gray-600';
            textDiv.textContent = methodText;
            wrapper.appendChild(textDiv);

            // Only generate QR if data is valid
            if (data && data.orderId && data.amount && data.amount !== '0' && data.amount !== '$0.00') {
                new QRCode(qrDiv, {
                    text: JSON.stringify(data),
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } else {
                qrDiv.innerHTML = '<span class="text-red-500">Cannot generate QR: missing order or amount</span>';
            }
        }

        // Add click handlers for payment method buttons
        document.querySelectorAll('.payment-method-option').forEach(button => {
            button.addEventListener('click', function() {
                const method = this.dataset.method;
                console.log('Payment method selected in viewer:', method);
                
                // Update UI
                document.querySelectorAll('.payment-method-option').forEach(opt => {
                    opt.classList.remove('bg-blue-100', 'border-blue-500');
                });
                this.classList.add('bg-blue-100', 'border-blue-500');
                
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
                        console.log('Generating wallet QR code with data:', walletData);
                        generateQRCode('khaltiQR', walletData, 'Scan to pay with Wallet');
                    } else if (method === 'khalti') {
                        const khaltiData = {
                            type: 'khalti',
                            orderId: state.orderId,
                            amount: document.getElementById('paymentAmountDisplay')?.textContent || '0',
                            timestamp: new Date().toISOString()
                        };
                        console.log('Generating Khalti QR code with data:', khaltiData);
                        generateQRCode('khaltiQR', khaltiData, 'Scan to pay with Khalti');
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
            if (event.origin !== window.location.origin) return;

            const data = event.data;
            if (!data || !data.type) return;

            console.log('Received message:', data);

            switch (data.type) {
                case 'UPDATE_ORDER':
                    if (data.order) {
                        console.log('Updating order display:', data.order);
                        state.orderId = data.order.id;
                        updateOrderDisplay(data.order);
                    }
                    break;
                case 'UPDATE_PAYMENT_METHOD':
                    if (data.method) {
                        console.log('Updating payment method:', data.method);
                        // Update payment method display
                        const paymentMethodDisplay = document.getElementById('paymentMethodDisplay');
                        if (paymentMethodDisplay) {
                            paymentMethodDisplay.textContent = data.method.toUpperCase();
                        }
                        // Highlight selected payment method
                        document.querySelectorAll('.payment-method-option').forEach(option => {
                            option.classList.remove('bg-blue-100', 'border-blue-500');
                            if (option.dataset.method === data.method) {
                                option.classList.add('bg-blue-100', 'border-blue-500');
                            }
                        });
                        // Show/hide cash payment details
                        const cashPaymentDetails = document.getElementById('cashPaymentDetails');
                        if (cashPaymentDetails) {
                            if (data.method === 'cash') {
                                cashPaymentDetails.classList.remove('hidden');
                            } else {
                                cashPaymentDetails.classList.add('hidden');
                            }
                        }
                        // Handle QR code generation
                        const qrContainer = document.getElementById('khaltiQR');
                        if (qrContainer) {
                            qrContainer.innerHTML = '';
                            if (data.method === 'wallet') {
                                const walletData = {
                                    type: 'wallet',
                                    orderId: state.orderId,
                                    amount: document.getElementById('paymentAmountDisplay')?.textContent || '0',
                                    timestamp: new Date().toISOString()
                                };
                                console.log('Generating wallet QR code with data:', walletData);
                                generateQRCode('khaltiQR', walletData, 'Scan to pay with Wallet');
                            } else if (data.method === 'khalti') {
                                const khaltiData = {
                                    type: 'khalti',
                                    orderId: state.orderId,
                                    amount: document.getElementById('paymentAmountDisplay')?.textContent || '0',
                                    timestamp: new Date().toISOString()
                                };
                                console.log('Generating Khalti QR code with data:', khaltiData);
                                generateQRCode('khaltiQR', khaltiData, 'Scan to pay with Khalti');
                            }
                        }
                    }
                    break;
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
                                <div class="flex justify-between items-center py-2 border-b">
                                    <div class="flex-1">
                                        <div class="font-medium">${itemName}</div>
                                        <div class="text-sm text-gray-500">${item.quantity} × ${formatCurrency(item.price)}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">${formatCurrency(item.subtotal)}</div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        console.log('No items found in order:', order);
                        itemsContainer.innerHTML = '<div class="text-center text-gray-500 py-4">No items found</div>';
                    }
                }

                // Update totals
                const subtotalElement = document.getElementById('subtotal');
                const totalElement = document.getElementById('total');
                if (subtotalElement) subtotalElement.textContent = formatCurrency(order.subtotal);
                if (totalElement) totalElement.textContent = formatCurrency(order.total);

                // Update payment status and method
                const paymentStatus = document.getElementById('paymentStatus');
                if (paymentStatus) {
                    const paymentMethod = order.payment_method ? 
                        `<div class="text-sm text-gray-500 mt-1">Payment Method: ${order.payment_method.toUpperCase()}</div>` : '';
                    
                    paymentStatus.innerHTML = `
                        <div class="flex flex-col items-center space-y-1">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 rounded-full ${order.payment_status === 'paid' ? 'bg-green-400' : 'bg-yellow-400 animate-pulse'}"></div>
                                <span class="text-gray-600">${order.payment_status === 'paid' ? 'Payment Complete' : ''}</span>
                            </div>
                            ${paymentMethod}
                        </div>
                    `;
                }

                // Show thank you modal if payment is complete
                if (order.payment_status === 'paid') {
                    showThankYouModal();
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
                            option.classList.remove('bg-blue-100', 'border-blue-500');
                            if (option.dataset.method === order.payment_method) {
                                option.classList.add('bg-blue-100', 'border-blue-500');
                            }
                        });
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
                            <div class="text-center text-green-600">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <div class="text-lg font-medium">Payment Complete</div>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                console.error('Error updating order display:', error);
                const orderItems = document.getElementById('orderItems');
                if (orderItems) {
                    orderItems.innerHTML = `<div class="text-red-500 p-4">Error: ${error.message}</div>`;
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
                console.error('Error polling for updates:', error);
                // Show error in the UI
                const orderItems = document.getElementById('orderItems');
                if (orderItems) {
                    orderItems.innerHTML = `<div class="text-red-500 p-4">Error: ${error.message}</div>`;
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
                    document.getElementById('orderItems').innerHTML = '<div class="text-red-500 p-4">Error: Branch ID is required</div>';
                    return;
                }

                // Initial order fetch
                await pollOrderUpdates();

                // Start polling for updates
                state.pollInterval = setInterval(pollOrderUpdates, 5000);

            } catch (error) {
                console.error('Error initializing order display:', error);
                document.getElementById('orderItems').innerHTML = '<div class="text-red-500 p-4">Error: Failed to initialize order display</div>';
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

        function showThankYouModal() {
            const modal = document.getElementById('thankYouModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Auto-close after 6 seconds
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 6000);
            }
        }
        document.getElementById('closeThankYouModal')?.addEventListener('click', function() {
            document.getElementById('thankYouModal').classList.add('hidden');
        });
    </script>
</body>
</html> 