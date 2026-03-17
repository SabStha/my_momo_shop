// Payment panel module

const updatePaymentViewer = (orderId) => {
    console.log('Updating payment viewer with order:', orderId);
    if (!window.paymentViewerWindow || window.paymentViewerWindow.closed) {
        console.log('Payment viewer not open, attempting to open...');
        attemptAutoOpen();
        setTimeout(() => {
            if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
                window.paymentViewerWindow.postMessage({
                    type: 'UPDATE_ORDER',
                    orderId: orderId
                }, window.location.origin);
            }
        }, 1000);
    } else {
        window.paymentViewerWindow.postMessage({
            type: 'UPDATE_ORDER',
            orderId: orderId
        }, window.location.origin);
    }
};

const updatePaymentViewerMethod = (method) => {
    console.log('Updating payment viewer with method:', method);
    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        window.paymentViewerWindow.postMessage({
            type: 'UPDATE_PAYMENT_METHOD',
            method: method
        }, window.location.origin);
    }
};

const updatePaymentViewerAmount = (amount) => {
    console.log('Updating payment viewer with amount:', amount);
    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        window.paymentViewerWindow.postMessage({
            type: 'UPDATE_PAYMENT_AMOUNT',
            amount: amount
        }, window.location.origin);
    }
};

function selectOrder(order) {
    // Support being called with just an order ID (number)
    if (typeof order === 'number' || typeof order === 'string') {
        const id = parseInt(order, 10);
        order = allOrders.find(o => o.id === id);
        if (!order) {
            console.error('selectOrder: order not found for id', id);
            return;
        }
    }
    console.log('[PM] selectOrder called with:', order.id, '| status:', order.status, '| payment_status:', order.payment_status);
    console.log('selectOrder called with:', order);

    // Verify payment panel exists before selecting
    if (!document.getElementById('paymentPanel')) {
        console.warn('⚠️ Payment panel not ready yet, waiting...');
        // Wait for DOM to be fully ready and retry
        setTimeout(() => {
            if (document.getElementById('paymentPanel')) {
                console.log('✅ Payment panel found on retry, selecting order');
                selectOrder(order);
            } else {
                console.error('❌ Payment panel element does not exist in DOM after retry');
                console.error('Available elements with IDs:', Array.from(document.querySelectorAll('[id]')).map(el => el.id).join(', '));
            }
        }, 100);
        return;
    }

    // Play button click sound
    playButtonClick();

    // Remove previous selection
    document.querySelectorAll('.order-card-selected').forEach(card => {
        card.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500');
    });

    // Add selection to clicked card (only if event.currentTarget exists)
    if (typeof event !== 'undefined' && event.currentTarget) {
        event.currentTarget.classList.add('order-card-selected', 'ring-2', 'ring-blue-500');
    }

    // Populate payment panel with order details
    populatePaymentPanel(order);

    // Update payment viewer with selected order
    updatePaymentViewer(order.id);
}

function populatePaymentPanel(order) {
    // Update payment panel with order details
    const paymentPanel = document.getElementById('paymentPanel');
    if (!paymentPanel) {
        console.error('Payment panel not found');
        console.error('Available IDs in document:', Array.from(document.querySelectorAll('[id]')).map(el => el.id));

        // Try to wait a bit and retry once
        setTimeout(() => {
            const retryPanel = document.getElementById('paymentPanel');
            if (retryPanel) {
                console.log('✅ Payment panel found on retry');
                populatePaymentPanelContent(order, retryPanel);
            } else {
                console.error('❌ Payment panel still not found after retry');
            }
        }, 100);
        return;
    }

    console.log('Selected order:', order);
    populatePaymentPanelContent(order, paymentPanel);
}

function populatePaymentPanelContent(order, paymentPanel) {
    console.log('[PM] populatePaymentPanelContent called for order:', order.id, '| status:', order.status, '| payment_status:', order.payment_status);
    // Update order summary
    const orderDetails = document.getElementById('orderDetails');
    if (orderDetails) {
        const itemsList = (order.items || []).map(item =>
            `<div class="flex justify-between py-1">
                <span>${item.item_name} x${item.quantity}</span>
                <span>Rs ${parseFloat(item.subtotal).toFixed(2)}</span>
            </div>`
        ).join('');

        orderDetails.innerHTML = `
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <h5 class="font-semibold text-gray-900">${order.order_number}</h5>
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${getPaymentStatusColor(order.payment_status)}">${order.payment_status}</span>
                </div>
                <div class="text-sm">
                    <p><strong>Type:</strong> ${(order.type || order.order_type || '').replace('_', ' ').toUpperCase()}</p>
                    <p><strong>Status:</strong> ${order.status}</p>
                    ${order.table ? `<p><strong>Table:</strong> ${order.table.name}</p>` : ''}
                    <p><strong>Time:</strong> ${formatTime(order.created_at)}</p>
                </div>
                <div class="border-t pt-3">
                    <h6 class="font-medium text-gray-900 mb-2">Order Items:</h6>
                    <div class="space-y-1 text-sm">
                        ${itemsList}
                    </div>
                    <div class="border-t pt-2 mt-3">
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Total Amount:</span>
                            <span>Rs ${parseFloat(order.total_amount).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        console.error('Order details element not found');
    }

    // Populate order action buttons based on order status
    const orderActions = document.getElementById('orderActions');
    console.log('[PM] #orderActions element found:', !!orderActions);
    if (orderActions) {
        const status = (order.status || '').toLowerCase();
        const paymentStatus = (order.payment_status || '').toLowerCase();
        console.log('[PM] setting action buttons | status:', status, '| paymentStatus:', paymentStatus);
        let actionsHtml = '';

        const orderType = (order.type || order.order_type || '').toLowerCase();

        // --- Action buttons by order type & status ---
        const markReadyBtn = `
            <button type="button" onclick="markOrderAsReady(${order.id})"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                ✓ Mark as Ready
            </button>`;

        const awaitingPaymentBadge = `
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-center font-medium">
                ✓ Order Ready — Process Payment Below
            </div>`;

        const acceptDeclineBtns = `
            <div class="flex gap-2 mb-2">
                <button type="button" onclick="acceptOrder(${order.id})"
                    class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    ✓ Accept Order
                </button>
                <button type="button" onclick="declineOrder(${order.id})"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    ✗ Decline
                </button>
            </div>`;

        const markPreparingBtn = `
            <button type="button" onclick="markAsPreparing(${order.id})"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                🍳 Mark as Preparing
            </button>`;

        if (paymentStatus !== 'paid') {
            if (orderType === 'dine_in') {
                // FLOW 1: Dine-in — auto-set to 'preparing' on creation, skip pending entirely
                if (['pending', 'preparing', 'confirmed', 'accepted', 'processing'].includes(status)) {
                    actionsHtml = markReadyBtn;
                } else if (status === 'ready') {
                    actionsHtml = awaitingPaymentBadge;
                }
            } else if (orderType === 'takeaway') {
                // FLOW 2: Takeaway — needs explicit staff acceptance
                if (status === 'pending') {
                    actionsHtml = acceptDeclineBtns + markPreparingBtn;
                } else if (['preparing', 'confirmed', 'accepted', 'processing'].includes(status)) {
                    actionsHtml = markReadyBtn;
                } else if (status === 'ready') {
                    actionsHtml = awaitingPaymentBadge;
                }
            } else if (orderType === 'online' || orderType === 'delivery') {
                // FLOW 3: Online/delivery — accept flow + out_for_delivery step
                if (status === 'pending') {
                    actionsHtml = acceptDeclineBtns + markPreparingBtn;
                } else if (['preparing', 'confirmed', 'accepted', 'processing'].includes(status)) {
                    actionsHtml = markReadyBtn;
                } else if (status === 'ready') {
                    actionsHtml = `
                        <button type="button" onclick="markOutForDelivery(${order.id})"
                            class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg font-medium transition-colors mb-2">
                            🛵 Mark as Out for Delivery
                        </button>` + awaitingPaymentBadge;
                } else if (status === 'out_for_delivery') {
                    actionsHtml = `
                        <button type="button" onclick="markAsDelivered(${order.id})"
                            class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                            🏠 Mark as Delivered
                        </button>`;
                }
            } else {
                // Fallback for unknown types — generic flow
                if (status === 'pending') {
                    actionsHtml = markPreparingBtn;
                } else if (['preparing', 'confirmed', 'accepted', 'processing'].includes(status)) {
                    actionsHtml = markReadyBtn;
                } else if (status === 'ready') {
                    actionsHtml = awaitingPaymentBadge;
                }
            }
        }

        const actionsEl = document.getElementById('orderActions');
        if (actionsEl) {
            actionsEl.innerHTML = actionsHtml;
            actionsEl.style.display = actionsHtml ? 'block' : 'none';
            actionsEl.classList.remove('hidden');
        }
    }

    // Store selected order for payment processing
    window.selectedOrder = order;

    // Enable payment processing if order is unpaid
    const processPaymentBtn = document.getElementById('processPaymentBtn');
    if (processPaymentBtn) {
        if (order.payment_status === 'paid') {
            processPaymentBtn.disabled = true;
            processPaymentBtn.textContent = 'Payment Completed';
            processPaymentBtn.className = 'flex-1 bg-gray-400 text-white px-6 py-3 rounded-lg text-sm font-medium cursor-not-allowed';
        } else {
            processPaymentBtn.disabled = false;
            processPaymentBtn.textContent = 'Process Payment';
            processPaymentBtn.className = 'flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium';
        }
    }

    // Initialize payment method selection
    initializePaymentMethods();
}

function initializePaymentMethods() {
    // Payment method buttons
    const paymentMethodBtns = document.querySelectorAll('.payment-method-btn');
    const cashFields = document.getElementById('cashFields');
    const cardFields = document.getElementById('cardFields');
    const walletFields = document.getElementById('walletFields');
    const khaltiFields = document.getElementById('khaltiFields');
    const mobileFields = document.getElementById('mobileFields');

    paymentMethodBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Play button click sound
            playButtonClick();

            // Remove active class from all buttons
            paymentMethodBtns.forEach(b => b.classList.remove('ring-2', 'ring-blue-500'));

            // Add active class to clicked button
            this.classList.add('ring-2', 'ring-blue-500');

            const method = this.getAttribute('data-method');

            // Hide all payment fields first
            cashFields.classList.add('hidden');
            cardFields.classList.add('hidden');
            walletFields.classList.add('hidden');
            khaltiFields.classList.add('hidden');
            mobileFields.classList.add('hidden');

            // Show relevant fields based on selected method
            if (method === 'cash') {
                cashFields.classList.remove('hidden');
                initializeCashDenominations();
            } else if (method === 'card') {
                cardFields.classList.remove('hidden');
            } else if (method === 'wallet') {
                walletFields.classList.remove('hidden');
                initializeWalletFields();
            } else if (method === 'khalti') {
                khaltiFields.classList.remove('hidden');
                initializeKhaltiFields();
            } else if (method === 'mobile') {
                mobileFields.classList.remove('hidden');
            }

            // Store selected payment method
            window.selectedPaymentMethod = method;

            // Update payment viewer with payment method
            updatePaymentViewerMethod(method);
        });
    });

    // Process payment button
    const processPaymentBtn = document.getElementById('processPaymentBtn');
    if (processPaymentBtn) {
        processPaymentBtn.addEventListener('click', () => {
            console.log('Process Payment button clicked');
            // Play processing sound
            playPaymentProcessing();

            // Check if our function exists
            if (typeof window.processPaymentManager === 'function') {
                console.log('Calling window.processPaymentManager()');
                window.processPaymentManager();
            } else {
                console.error('window.processPaymentManager function not found');
                showErrorModal('Error', 'Payment processing function not available. Please refresh the page.');
            }
        });
    }

    // Cancel payment button
    const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
    if (cancelPaymentBtn) {
        cancelPaymentBtn.addEventListener('click', () => {
            // Play button click sound
            playButtonClick();

            // Clear selection
            document.querySelectorAll('.order-card-selected').forEach(card => {
                card.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500');
            });

            // Reset payment panel
            resetPaymentPanel();
        });
    }
}

function initializeCashDenominations() {
    const denominationInputs = document.querySelectorAll('.denomination-input');
    const denominationTotal = document.getElementById('denominationTotal');
    const changeAmount = document.getElementById('changeAmount');

    denominationInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Play button click sound for denomination input
            playButtonClick();
            calculateCashTotals();
        });
    });

    // Add event listener for direct cash input
    const totalCashReceived = document.getElementById('totalCashReceived');
    if (totalCashReceived) {
        totalCashReceived.addEventListener('input', function() {
            // Play button click sound
            playButtonClick();
            calculateCashTotalsFromDirectInput();
        });
    }

    function calculateCashTotals() {
        let totalReceived = 0;
        denominationInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            const denomination = parseInt(input.getAttribute('data-value'));
            totalReceived += value * denomination;
        });

        const orderTotal = window.selectedOrder ? parseFloat(window.selectedOrder.total_amount) : 0;
        const change = Math.max(0, totalReceived - orderTotal);

        denominationTotal.textContent = totalReceived.toFixed(2);
        changeAmount.textContent = change.toFixed(2);

        // Calculate change denominations
        calculateChangeDenominations(change);
    }

    function calculateCashTotalsFromDirectInput() {
        const directCashInput = document.getElementById('totalCashReceived');
        const orderTotal = window.selectedOrder ? parseFloat(window.selectedOrder.total_amount) : 0;

        if (directCashInput && directCashInput.value) {
            const totalReceived = parseFloat(directCashInput.value);
            const change = Math.max(0, totalReceived - orderTotal);

            // Update display totals
            denominationTotal.textContent = totalReceived.toFixed(2);
            changeAmount.textContent = change.toFixed(2);

            // Calculate change denominations
            calculateChangeDenominations(change);
        }
    }
}

function calculateChangeDenominations(changeAmount) {
    const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
    const changeInputs = document.querySelectorAll('.change-given-input');

    let remainingChange = Math.round(changeAmount);

    denominations.forEach((denomination, index) => {
        const input = changeInputs[index];
        if (input) {
            const count = Math.floor(remainingChange / denomination);
            input.value = count;
            remainingChange -= count * denomination;
        }
    });
}

// Payment Manager specific payment processing function
window.processPaymentManager = async function() {
    console.log('processPaymentManager called');
    console.log('Selected order:', window.selectedOrder);
    console.log('Selected payment method:', window.selectedPaymentMethod);

    if (!window.selectedOrder || !window.selectedPaymentMethod) {
        console.log('Missing order or payment method');
        showErrorModal('Error', 'Please select an order and payment method');
        return;
    }

    if (window.selectedOrder.payment_status === 'paid') {
        showErrorModal('Error', 'This order has already been paid');
        return;
    }

    try {
        const paymentData = {
            amount: parseFloat(window.selectedOrder.total_amount),
            payment_method: window.selectedPaymentMethod,
            branch_id: parseInt(document.getElementById('paymentApp').dataset.branchId),
            reference_number: document.getElementById('paymentPanelReferenceNumber').value || ''
        };

        // Add payment method specific data
        if (window.selectedPaymentMethod === 'cash') {
            const denominationInputs = document.querySelectorAll('.denomination-input');
            const cashDenominations = {};

            denominationInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                const denomination = input.getAttribute('data-value');
                if (value > 0) {
                    cashDenominations[denomination] = value;
                }
            });

            // Get amount received from direct input field or calculated from denominations
            const directCashInput = document.getElementById('totalCashReceived');
            const denominationTotal = document.getElementById('denominationTotal');

            if (directCashInput && directCashInput.value) {
                paymentData.amount_received = parseFloat(directCashInput.value);
            } else if (denominationTotal) {
                paymentData.amount_received = parseFloat(denominationTotal.textContent);
            } else {
                paymentData.amount_received = parseFloat(window.selectedOrder.total_amount);
            }

            // Calculate change amount
            paymentData.change_amount = paymentData.amount_received - paymentData.amount;
        } else if (window.selectedPaymentMethod === 'card') {
            paymentData.reference_number = document.getElementById('cardReferenceNumber').value || '';
            // Set default values for non-cash payments
            paymentData.amount_received = paymentData.amount;
            paymentData.change_amount = 0;
        } else if (window.selectedPaymentMethod === 'wallet') {
            paymentData.reference_number = `WALLET-${Date.now()}`;
            // Set default values for non-cash payments
            paymentData.amount_received = paymentData.amount;
            paymentData.change_amount = 0;
        } else {
            // For other payment methods (khalti, mobile), treat as card
            paymentData.payment_method = 'card';
            paymentData.reference_number = document.getElementById('mobileReferenceNumber')?.value || `OTHER-${Date.now()}`;
            paymentData.amount_received = paymentData.amount;
            paymentData.change_amount = 0;
        }

        console.log('Sending payment data:', paymentData);

        const response = await fetch(`/admin/payments/order/${window.selectedOrder.id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(paymentData),
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
            // Play success sound based on payment method
            playPaymentSuccessWithMethod(window.selectedPaymentMethod);

            showSuccessModal('Success', 'Payment processed successfully!');

            // Refresh orders to update status
            fetchOrders();

            // Update cash drawer status if it was a cash payment
            if (window.selectedPaymentMethod === 'cash') {
                updateDrawerButtonState();
            }

            // Reset payment panel
            resetPaymentPanel();

            // Clear order selection
            document.querySelectorAll('.order-card-selected').forEach(card => {
                card.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500');
            });
        } else {
            // Play failure sound
            playPaymentFailed();

            showErrorModal('Error', data.message || 'Failed to process payment');
        }
    } catch (error) {
        // Play failure sound
        playPaymentFailed();

        console.error('Payment processing error:', error);
        showErrorModal('Error', 'Failed to process payment. Please try again.');
    }
};

function resetPaymentPanel() {
    // Reset order details
    const orderDetails = document.getElementById('orderDetails');
    if (orderDetails) {
        orderDetails.innerHTML = '<p class="text-gray-500">Select an order to process payment</p>';
    }

    // Reset order action buttons
    const orderActions = document.getElementById('orderActions');
    if (orderActions) {
        orderActions.innerHTML = '';
        orderActions.classList.add('hidden');
        orderActions.style.display = '';
    }

    // Reset payment method selection
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-blue-500');
    });

    // Hide payment fields
    document.getElementById('cashFields').classList.add('hidden');
    document.getElementById('cardFields').classList.add('hidden');
    document.getElementById('walletFields').classList.add('hidden');
    document.getElementById('khaltiFields').classList.add('hidden');
    document.getElementById('mobileFields').classList.add('hidden');

    // Reset form fields
    document.getElementById('paymentNotes').value = '';
    document.getElementById('cardReferenceNumber').value = '';
    document.getElementById('walletNumber').value = '';
    document.getElementById('khaltiTransactionId').value = '';
    document.getElementById('mobileReferenceNumber').value = '';

    // Hide wallet balance display
    const walletBalanceDisplay = document.getElementById('walletBalanceDisplay');
    if (walletBalanceDisplay) {
        walletBalanceDisplay.classList.add('hidden');
    }

    // Clear Khalti QR code
    const khaltiQrCode = document.getElementById('khaltiQrCode');
    if (khaltiQrCode) {
        khaltiQrCode.innerHTML = `
            <div class="text-center text-gray-500">
                <i class="fas fa-qrcode text-4xl mb-2"></i>
                <p>QR code will be generated here</p>
            </div>
        `;
    }

    // Reset denomination inputs
    document.querySelectorAll('.denomination-input').forEach(input => {
        input.value = 0;
    });
    document.querySelectorAll('.change-given-input').forEach(input => {
        input.value = 0;
    });

    // Reset totals
    document.getElementById('denominationTotal').textContent = '0';
    document.getElementById('changeAmount').textContent = '0';

    // Reset direct cash input
    const totalCashReceived = document.getElementById('totalCashReceived');
    if (totalCashReceived) {
        totalCashReceived.value = '';
    }

    // Reset process payment button
    const processPaymentBtn = document.getElementById('processPaymentBtn');
    if (processPaymentBtn) {
        processPaymentBtn.disabled = true;
        processPaymentBtn.textContent = 'Select Order';
        processPaymentBtn.className = 'flex-1 bg-gray-400 text-white px-6 py-3 rounded-lg text-sm font-medium cursor-not-allowed';
    }

    // Clear selected order
    window.selectedOrder = null;
    window.selectedPaymentMethod = null;
}

function initializeWalletFields() {
    const walletNumberInput = document.getElementById('walletNumber');
    const scanWalletBtn = document.getElementById('scanWalletBtn');
    const walletBalanceDisplay = document.getElementById('walletBalanceDisplay');
    const walletBalance = document.getElementById('walletBalance');

    // Wallet number input handler
    if (walletNumberInput) {
        walletNumberInput.addEventListener('input', function() {
            // Format wallet number as XXXX-XXXX-XXXX-XXXX
            let value = this.value.replace(/\D/g, '');
            if (value.length > 16) {
                value = value.substring(0, 16);
            }

            const formatted = value.replace(/(\w{4})(?=\w)/g, '$1-');
            this.value = formatted;

            // Check wallet balance if number is complete
            if (formatted.length === 19) {
                checkWalletBalance(formatted);
            } else {
                walletBalanceDisplay.classList.add('hidden');
            }
        });
    }

    // Scan wallet QR code button
    if (scanWalletBtn) {
        scanWalletBtn.addEventListener('click', function() {
            // For now, just show a placeholder
            // In a real implementation, this would open a QR scanner
            alert('QR Scanner functionality will be implemented here');
        });
    }
}

function initializeKhaltiFields() {
    const khaltiTransactionId = document.getElementById('khaltiTransactionId');
    const khaltiQrCode = document.getElementById('khaltiQrCode');

    // Generate QR code for Khalti payment
    if (khaltiQrCode && window.selectedOrder) {
        generateKhaltiQRCode();
    }

    // Transaction ID input handler
    if (khaltiTransactionId) {
        khaltiTransactionId.addEventListener('input', function() {
            // Validate Khalti transaction ID format
            const value = this.value.trim();
            if (value && !/^[A-Za-z0-9]{10,}$/.test(value)) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
    }
}

async function checkWalletBalance(walletNumber) {
    try {
        const branchId = new URLSearchParams(window.location.search).get('branch');
        const response = await fetch(`/api/wallet/balance?wallet_number=${walletNumber}&branch_id=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        if (response.ok) {
            const data = await response.json();
            const walletBalance = document.getElementById('walletBalance');
            const walletBalanceDisplay = document.getElementById('walletBalanceDisplay');

            if (data.success && walletBalance && walletBalanceDisplay) {
                walletBalance.textContent = `Rs ${parseFloat(data.balance).toFixed(2)}`;
                walletBalanceDisplay.classList.remove('hidden');
            }
        }
    } catch (error) {
        console.error('Error checking wallet balance:', error);
    }
}

function generateKhaltiQRCode() {
    const khaltiQrCode = document.getElementById('khaltiQrCode');
    if (!khaltiQrCode || !window.selectedOrder) return;

    // Generate QR code data for Khalti payment
    const qrData = {
        type: 'khalti_payment',
        order_id: window.selectedOrder.id,
        amount: window.selectedOrder.total_amount,
        merchant_id: 'your_merchant_id', // Replace with actual merchant ID
        timestamp: new Date().toISOString()
    };

    // Clear previous QR code
    khaltiQrCode.innerHTML = '';

    // Generate new QR code using QRCode.js library
    if (typeof QRCode !== 'undefined') {
        new QRCode(khaltiQrCode, {
            text: JSON.stringify(qrData),
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        // Fallback if QRCode library is not loaded
        khaltiQrCode.innerHTML = `
            <div class="text-center text-gray-500">
                <i class="fas fa-qrcode text-4xl mb-2"></i>
                <p>QR Code: ${JSON.stringify(qrData)}</p>
            </div>
        `;
    }
}

// Export functions to global scope
window.selectOrder = selectOrder;
window.resetPaymentPanel = resetPaymentPanel;
window.initializePaymentMethods = initializePaymentMethods;
window.initializeCashDenominations = initializeCashDenominations;
window.checkWalletBalance = checkWalletBalance;
window.generateKhaltiQRCode = generateKhaltiQRCode;
