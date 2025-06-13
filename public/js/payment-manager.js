// Payment method change handler
document.getElementById('paymentMethod')?.addEventListener('change', function() {
    const method = this.value;
    const cashFields = document.getElementById('cashPaymentFields');
    const cardFields = document.getElementById('cardPaymentFields');
    const walletFields = document.getElementById('walletPaymentFields');
    const khaltiFields = document.getElementById('khaltiPaymentFields');
    const currencyDenominationsContainer = document.getElementById('currencyDenominationsContainer');
    const amountReceived = document.getElementById('amountReceived');
    const changeAmount = document.getElementById('changeAmount');

    // Hide all fields first
    cashFields.classList.add('hidden');
    cardFields.classList.add('hidden');
    walletFields.classList.add('hidden');
    khaltiFields.classList.add('hidden');
    currencyDenominationsContainer.classList.add('hidden');

    // Show relevant fields based on payment method
    if (method === 'cash') {
        cashFields.classList.remove('hidden');
        currencyDenominationsContainer.classList.remove('hidden');
        amountReceived.required = true;
    } else if (method === 'card') {
        cardFields.classList.remove('hidden');
        amountReceived.required = false;
    } else if (method === 'wallet') {
        walletFields.classList.remove('hidden');
        amountReceived.required = false;
    } else if (method === 'khalti') {
        khaltiFields.classList.remove('hidden');
        amountReceived.required = false;
    }

    // Reset change amount
    changeAmount.value = '';
});

// QR Scanner functionality
let qrScanner = null;

document.getElementById('scanWalletBtn')?.addEventListener('click', async function() {
    const modal = document.getElementById('qrScannerModal');
    modal.classList.remove('hidden');
    
    try {
        // Request camera permission first
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        stream.getTracks().forEach(track => track.stop()); // Stop the stream immediately
        
        // Initialize QR scanner
        if (!qrScanner) {
            qrScanner = new Html5Qrcode("qrScanner");
            qrScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanFailure
            ).catch(error => {
                console.error('Failed to start QR scanner:', error);
                showErrorModal('Error', 'Failed to start camera. Please ensure you have granted camera permissions.');
                modal.classList.add('hidden');
            });
        }
    } catch (error) {
        console.error('Camera access error:', error);
        showErrorModal('Error', 'Camera access denied. Please ensure you have granted camera permissions and are using a secure connection (HTTPS or localhost).');
        modal.classList.add('hidden');
    }
});

document.getElementById('closeQrScanner')?.addEventListener('click', function() {
    const modal = document.getElementById('qrScannerModal');
    modal.classList.add('hidden');
    
    // Stop QR scanner
    if (qrScanner) {
        qrScanner.stop().then(() => {
            qrScanner = null;
        });
    }
});

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanner
    qrScanner.stop().then(() => {
        qrScanner = null;
    });
    
    // Close modal
    document.getElementById('qrScannerModal').classList.add('hidden');
    
    // Set wallet number
    document.getElementById('walletNumber').value = decodedText;
    
    // Fetch wallet balance
    fetchWalletBalance(decodedText);
}

function onScanFailure(error) {
    // Handle scan failure silently
    console.warn(`QR scan failed: ${error}`);
}

// Update wallet balance when wallet number changes
document.getElementById('walletNumber')?.addEventListener('change', function() {
    const walletNumber = this.value.trim();
    if (walletNumber) {
        // Validate wallet number format (XXXX-XXXX-XXXX-XXXX)
        const walletNumberRegex = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
        if (!walletNumberRegex.test(walletNumber)) {
            alert('Please enter a valid wallet number in the format: XXXX-XXXX-XXXX-XXXX');
            this.value = '';
            return;
        }
        fetchWalletBalance(walletNumber);
    }
});

// Modal functions
function showSuccessModal(title, message) {
    const modal = document.getElementById('successModal');
    const modalTitle = document.getElementById('successModalTitle');
    const modalMessage = document.getElementById('successModalMessage');
    
    if (modal && modalTitle && modalMessage) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.classList.remove('hidden');
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 3000);
    }
}

function showErrorModal(title, message) {
    const modal = document.getElementById('errorModal');
    const modalTitle = document.getElementById('errorModalTitle');
    const modalMessage = document.getElementById('errorModalMessage');
    
    if (modal && modalTitle && modalMessage) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.classList.remove('hidden');
    }
}

// Close modal functions
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function closeErrorModal() {
    const modal = document.getElementById('errorModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Update fetchWalletBalance function
async function fetchWalletBalance(walletNumber = null) {
    const walletBalanceElement = document.getElementById('walletBalance');
    const walletNumberInput = document.getElementById('walletNumber');
    const branchId = new URLSearchParams(window.location.search).get('branch');

    if (!branchId) {
        console.error('Branch ID is required');
        if (walletBalanceElement) {
            walletBalanceElement.textContent = 'Branch ID required';
        }
        return;
    }

    try {
        const walletNumberToUse = walletNumber || walletNumberInput?.value;
        if (!walletNumberToUse) {
            console.error('Wallet number is required');
            if (walletBalanceElement) {
                walletBalanceElement.textContent = 'Enter wallet number';
            }
            return;
        }

        // Show loading state
        if (walletBalanceElement) {
            walletBalanceElement.textContent = 'Loading...';
        }

        const response = await fetch(`/api/admin/wallets/${walletNumberToUse}/balance?branch=${branchId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Authorization': `Bearer ${document.querySelector('meta[name="auth-token"]')?.getAttribute('content')}`,
                'X-Branch-ID': branchId
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || `Failed to fetch wallet balance: ${response.status} ${response.statusText}`);
        }

        if (walletBalanceElement) {
            if (data.balance !== undefined) {
                walletBalanceElement.textContent = formatCurrency(data.balance);
                // Add user name if available
                if (data.user_name && data.user_name !== 'N/A') {
                    walletBalanceElement.textContent += ` (${data.user_name})`;
                }
            } else {
                throw new Error('Invalid wallet balance data received');
            }
        }
    } catch (error) {
        console.error('Error fetching wallet balance:', error);
        if (walletBalanceElement) {
            walletBalanceElement.textContent = 'Error fetching balance';
        }
        showErrorModal('Error', error.message || 'Failed to fetch wallet balance. Please try again.');
    }
}

// Add event listeners for wallet number input
document.addEventListener('DOMContentLoaded', function() {
    const walletNumberInput = document.getElementById('walletNumber');
    if (walletNumberInput) {
        // Add debounce to prevent too many API calls
        let debounceTimer;
        walletNumberInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (this.value) {
                    fetchWalletBalance(this.value);
                }
            }, 500); // Wait 500ms after user stops typing
        });
    }

    // Add event listeners for modal close buttons
    const successModalClose = document.getElementById('successModalClose');
    const errorModalClose = document.getElementById('errorModalClose');
    
    if (successModalClose) {
        successModalClose.addEventListener('click', closeSuccessModal);
    }
    
    if (errorModalClose) {
        errorModalClose.addEventListener('click', closeErrorModal);
    }
});

// Initialize state
const state = {
    selectedOrderId: null,
    isPolling: false,
    branchId: new URLSearchParams(window.location.search).get('branch')
};

// Start polling for updates
function startPolling() {
    if (!state.isPolling) {
        state.isPolling = true;
        window.poller = setInterval(checkPaymentStatus, 5000);
    }
}

// Stop polling
function stopPolling() {
    if (state.isPolling) {
        state.isPolling = false;
        clearInterval(window.poller);
    }
}

// Check payment status
async function checkPaymentStatus() {
    try {
        const orderId = state.selectedOrderId || document.getElementById('selectedOrderId')?.value;
        if (!orderId) return;

        const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
        if (!authToken) {
            console.error('No auth token found');
            return;
        }

        const response = await fetch(`/api/admin/orders/${orderId}?branch=${state.branchId}&include=items,order_items,products`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${authToken}`,
                'X-Branch-ID': state.branchId
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Failed to fetch order status');
        }

        const order = await response.json();
        updateOrderStatus(order);
    } catch (error) {
        console.error('Error checking payment status:', error);
    }
}

// Update order status in UI
function updateOrderStatus(order) {
    const orderRow = document.querySelector(`tr[data-order-id="${order.id}"]`);
    if (orderRow) {
        const statusCell = orderRow.querySelector('.payment-status');
        if (statusCell) {
            statusCell.textContent = order.payment_status;
            statusCell.className = `payment-status ${order.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'}`;
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Function to open customer view
    const openCustomerView = () => {
        console.log('Opening payment viewer...');
        
        if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
            window.paymentViewerWindow.close();
        }

        // Get branch ID and order ID
        const urlParams = new URLSearchParams(window.location.search);
        const branchId = urlParams.get('branch');
        const orderId = document.getElementById('selectedOrderId')?.value;
        
        if (!branchId) {
            console.error('Branch ID not found in URL');
            alert('Error: Branch ID not found. Please refresh the page and try again.');
            return;
        }

        if (!orderId) {
            console.error('No order selected');
            alert('Error: Please select an order first.');
            return;
        }

        // Calculate window size based on screen size
        const width = Math.min(400, window.innerWidth * 0.9);
        const height = Math.min(700, window.innerHeight * 0.9);
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;

        // Open payment viewer in a new window with both order and branch IDs
        const viewerUrl = `/payment-viewer?order=${orderId}&branch=${branchId}`;
        console.log('Opening payment viewer URL:', viewerUrl);

        window.paymentViewerWindow = window.open(
            viewerUrl,
            'Payment Viewer',
            `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
        );

        if (!window.paymentViewerWindow) {
            alert('Please allow popups for this website to view the payment screen.');
            return;
        }

        // Focus the window
        window.paymentViewerWindow.focus();

        // Add event listener for window close
        window.paymentViewerWindow.addEventListener('beforeunload', () => {
            console.log('Payment viewer window closed');
            window.paymentViewerWindow = null;
        });
    };

    // Add event listener for open customer view button
    const openCustomerViewBtn = document.getElementById('openCustomerView');
    if (openCustomerViewBtn) {
        openCustomerViewBtn.addEventListener('click', openCustomerView);
    }

    // Add click event listeners to all order rows
    document.querySelectorAll('tr[data-order-id]').forEach(row => {
        row.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            if (orderId) {
                selectOrder(orderId);
            }
        });
    });

    // Add event listener for payment amount changes
    const paymentAmountInput = document.getElementById('paymentAmount');
    if (paymentAmountInput) {
        paymentAmountInput.addEventListener('input', updateTotals);
    }

    // Add event listener for payment method changes
    const paymentMethodSelect = document.getElementById('paymentMethod');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const method = this.value;
            // Hide all payment fields first
            document.getElementById('cashPaymentFields').classList.add('hidden');
            document.getElementById('cardPaymentFields').classList.add('hidden');
            document.getElementById('walletPaymentFields').classList.add('hidden');
            document.getElementById('khaltiPaymentFields').classList.add('hidden');
            
            // Show relevant fields based on selected method
            if (method === 'cash') {
                document.getElementById('cashPaymentFields').classList.remove('hidden');
            } else if (method === 'card') {
                document.getElementById('cardPaymentFields').classList.remove('hidden');
            } else if (method === 'wallet') {
                document.getElementById('walletPaymentFields').classList.remove('hidden');
            } else if (method === 'khalti') {
                document.getElementById('khaltiPaymentFields').classList.remove('hidden');
            }
        });
    }

    // Add event listener for share payment link button
    const sharePaymentLinkBtn = document.getElementById('sharePaymentLink');
    if (sharePaymentLinkBtn) {
        sharePaymentLinkBtn.addEventListener('click', function() {
            const orderId = document.getElementById('selectedOrderId').value;
            const branchId = new URLSearchParams(window.location.search).get('branch');
            
            if (!orderId) {
                showErrorModal('Error', 'No order selected');
                return;
            }

            const paymentViewerUrl = `${window.location.origin}/payment-viewer?order_id=${orderId}&branch=${branchId}`;
            
            // Copy to clipboard
            navigator.clipboard.writeText(paymentViewerUrl).then(() => {
                showSuccessModal('Success', 'Payment link copied to clipboard');
            }).catch(() => {
                // Fallback for browsers that don't support clipboard API
                const tempInput = document.createElement('input');
                tempInput.value = paymentViewerUrl;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showSuccessModal('Success', 'Payment link copied to clipboard');
            });
        });
    }

    // Modify the payment form submission to automatically open customer view
    document.getElementById('paymentForm').addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const formData = new FormData(this);
        const orderId = formData.get('order_id');
        const paymentMethod = formData.get('payment_method');
        const amount = formData.get('amount');
        const branchId = new URLSearchParams(window.location.search).get('branch');

        if (!orderId || !paymentMethod || !amount) {
            showErrorModal('Error', 'Please fill in all required fields');
            return;
        }

        // Handle Khalti payment separately
        if (paymentMethod === 'khalti') {
            try {
                const authToken = document.querySelector('meta[name="auth-token"]').getAttribute('content');
                const response = await fetch('/admin/payments/khalti/initiate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Authorization': `Bearer ${authToken}`,
                        'X-Branch-ID': branchId
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        amount: parseFloat(amount)
                    })
                });

                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show QR code or redirect to Khalti payment page
                    if (data.payment_url) {
                        window.location.href = data.payment_url;
                    } else if (data.qr_code) {
                        // Handle QR code display
                        const qrCodeContainer = document.getElementById('khaltiQrCode');
                        if (qrCodeContainer) {
                            qrCodeContainer.innerHTML = '';
                            new QRCode(qrCodeContainer, data.qr_code);
                        }
                    }
                } else {
                    throw new Error(data.message || 'Failed to initiate Khalti payment');
                }
            } catch (error) {
                console.error('Khalti payment error:', error);
                showErrorModal('Error', error.message);
            }
            return;
        }

        // Handle other payment methods
        const data = {
            order_id: orderId,
            payment_method: paymentMethod,
            amount: parseFloat(amount),
            notes: formData.get('notes'),
            payment_status: 'paid'
        };

        // Add payment method specific fields
        if (paymentMethod === 'cash') {
            data.amount_received = parseFloat(formData.get('amount_received') || 0);
        } else if (paymentMethod === 'card') {
            data.reference_number = formData.get('reference_number');
        } else if (paymentMethod === 'wallet') {
            data.wallet_number = formData.get('wallet_number');
        }

        try {
            const authToken = document.querySelector('meta[name="auth-token"]').getAttribute('content');
            const paymentResponse = await fetch(`/api/orders/${orderId}/process-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${authToken}`,
                    'X-Branch-ID': branchId
                },
                body: JSON.stringify(data)
            });

            const result = await paymentResponse.json();
            
            if (!paymentResponse.ok) {
                throw new Error(result.message || 'Payment processing failed');
            }

            // Handle successful payment
            showSuccessModal('Success', 'Payment processed successfully');
            
            // Reset form
            this.reset();
            
            // Clear selected order
            document.getElementById('selectedOrderId').value = '';
            document.getElementById('paymentAmount').value = '';
            document.getElementById('itemsTableBody').innerHTML = '';
            document.getElementById('subtotal').textContent = 'Rs 0.00';
            document.getElementById('total').textContent = 'Rs 0.00';
            
            // Hide all payment fields
            document.getElementById('cashPaymentFields').classList.add('hidden');
            document.getElementById('cardPaymentFields').classList.add('hidden');
            document.getElementById('walletPaymentFields').classList.add('hidden');
            document.getElementById('khaltiPaymentFields').classList.add('hidden');

            // Refresh tables after successful payment
            const tablesResponse = await fetch(`/api/pos/tables?branch_id=${branchId}`);
            const tablesData = await tablesResponse.json();
            console.log('Tables API Response:', {
                status: tablesResponse.status,
                data: tablesData,
                tables: tablesData.data
            });
            
            if (tablesData.data) {
                const updatedTables = tablesData.data;
                console.log('Updated Tables Data:', {
                    count: updatedTables.length,
                    tables: updatedTables.map(t => ({
                        id: t.id,
                        status: t.status,
                        is_occupied: t.is_occupied,
                        branch_id: t.branch_id
                    }))
                });
                updateTablesList(updatedTables);
            }

            // Update orders list without reloading
            try {
                const ordersResponse = await fetch('/api/admin/orders?branch=' + branchId + '&type=pos&include=items,order_items,products', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Authorization': `Bearer ${authToken}`,
                        'X-Branch-ID': branchId
                    },
                    credentials: 'same-origin'
                });

                if (!ordersResponse.ok) {
                    throw new Error('Failed to reload orders');
                }

                const ordersData = await ordersResponse.json();
                console.log('Orders updated successfully:', ordersData);

                // Ensure ordersData.orders exists and is an array
                if (!ordersData || !Array.isArray(ordersData.orders)) {
                    console.error('Invalid orders data structure:', ordersData);
                    throw new Error('Invalid orders data received from server');
                }

                // Update POS orders list
                const posOrdersList = document.getElementById('posOrdersList');
                if (posOrdersList) {
                    const posOrders = ordersData.orders.filter(order => order.order_type === 'pos');
                    posOrdersList.innerHTML = posOrders.length ? posOrders.map(order => `
                        <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="${order.id}">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium">Order #${order.id}</p>
                                    <p class="text-xs text-gray-500">
                                        ${order.table ? 
                                            `<span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium">
                                                Table ${order.table.name} (${order.table.capacity} seats)
                                            </span>` :
                                            `<span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-medium">
                                                ${order.order_type}
                                            </span>`
                                        }
                                    </p>
                                    <p class="text-sm text-gray-600">Rs. ${formatCurrency(order.total)}</p>
                                </div>
                                <span class="px-2 py-1 text-sm rounded ${order.payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'}">
                                    ${order.payment_status}
                                </span>
                            </div>
                        </div>
                    `).join('') : '<p class="text-gray-500">No POS orders.</p>';
                }

                // Update online orders list
                const onlineOrdersList = document.getElementById('onlineOrdersList');
                if (onlineOrdersList) {
                    const onlineOrders = ordersData.orders.filter(order => order.order_type === 'online');
                    onlineOrdersList.innerHTML = onlineOrders.length ? onlineOrders.map(order => `
                        <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="${order.id}">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium">Order #${order.id}</p>
                                    <p class="text-xs text-gray-500">${order.user?.name || 'Guest'}</p>
                                    <p class="text-sm text-gray-600">Rs. ${formatCurrency(order.total)}</p>
                                </div>
                                <span class="px-2 py-1 text-sm rounded ${order.payment_status === 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'}">
                                    ${order.payment_status}
                                </span>
                            </div>
                        </div>
                    `).join('') : '<p class="text-gray-500">No online orders.</p>';
                }

                // Update order history
                const orderHistoryList = document.getElementById('orderHistoryList');
                if (orderHistoryList) {
                    const paidOrders = ordersData.orders.filter(order => order.payment_status === 'paid');
                    orderHistoryList.innerHTML = paidOrders.length ? paidOrders.map(order => `
                        <div class="p-3 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer" data-order-id="${order.id}">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium">Order #${order.id}</p>
                                    <p class="text-xs text-gray-500">
                                        ${order.table ? 
                                            `<span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-medium">
                                                Table ${order.table.name} (${order.table.capacity} seats)
                                            </span>` :
                                            `<span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-medium">
                                                ${order.order_type}
                                            </span>`
                                        }
                                    </p>
                                    <p class="text-sm text-gray-600">Rs. ${formatCurrency(order.total)}</p>
                                </div>
                                <span class="px-2 py-1 text-sm rounded bg-green-200 text-green-800">
                                    ${order.payment_status}
                                </span>
                            </div>
                        </div>
                    `).join('') : '<p class="text-gray-500">No order history.</p>';
                }

                // Reattach click handlers to new order elements
                document.querySelectorAll('[data-order-id]').forEach(element => {
                    element.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        const order = ordersData.orders.find(o => o.id === parseInt(orderId));
                        if (order) {
                            // Update order details
                            document.getElementById('selectedOrderId').value = order.id;
                            document.getElementById('paymentAmount').value = order.total;
                            
                            // Update items table
                            const itemsTableBody = document.getElementById('itemsTableBody');
                            itemsTableBody.innerHTML = order.items.map(item => `
                                <tr>
                                    <td class="border px-4 py-2">${item.name}</td>
                                    <td class="border px-4 py-2 text-right">${item.pivot.quantity}</td>
                                    <td class="border px-4 py-2 text-right">${formatCurrency(item.pivot.price)}</td>
                                    <td class="border px-4 py-2 text-right">${formatCurrency(item.pivot.quantity * item.pivot.price)}</td>
                                </tr>
                            `).join('');

                            // Update totals
                            document.getElementById('subtotal').textContent = formatCurrency(order.total);
                            document.getElementById('total').textContent = formatCurrency(order.total * 1.13); // Add 13% VAT
                        }
                    });
                });
            } catch (error) {
                console.error('Error updating orders:', error);
                showErrorModal('Error', 'Failed to update orders. Please try again.');
            }
        } catch (error) {
            console.error('Payment processing error:', error);
            showErrorModal('Error', error.message);
        }
    });
});

function updateTotals() {
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const vatRate = 0.13; // 13% VAT for Nepal

    if (subtotalElement && totalElement) {
        // Calculate subtotal (payment amount)
        const subtotal = paymentAmount;
        subtotalElement.textContent = formatCurrency(subtotal);

        // Calculate total with VAT
        const total = subtotal * (1 + vatRate);
        totalElement.textContent = formatCurrency(total);
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-NP', {
        style: 'currency',
        currency: 'NPR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount).replace('NPR', 'Rs');
}

// Update the currency display in the table
document.addEventListener('DOMContentLoaded', function() {
    // Update all currency displays in the table
    const currencyCells = document.querySelectorAll('td[data-currency]');
    currencyCells.forEach(cell => {
        const amount = parseFloat(cell.getAttribute('data-currency'));
        if (!isNaN(amount)) {
            cell.textContent = formatCurrency(amount);
        }
    });
}); 