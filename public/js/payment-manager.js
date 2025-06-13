// Payment method change handler
document.getElementById('paymentMethod')?.addEventListener('change', function() {
    const method = this.value;
    const cashFields = document.getElementById('cashPaymentFields');
    const cardFields = document.getElementById('cardPaymentFields');
    const walletFields = document.getElementById('walletPaymentFields');
    const currencyDenominationsContainer = document.getElementById('currencyDenominationsContainer');
    const amountReceived = document.getElementById('amountReceived');
    const changeAmount = document.getElementById('changeAmount');

    // Hide all fields first
    cashFields.classList.add('hidden');
    cardFields.classList.add('hidden');
    walletFields.classList.add('hidden');
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

// Update order selection handler
function selectOrder(orderId) {
    console.log('Selecting order:', orderId); // Debug log
    
    // Get branch ID from URL
    const branchId = new URLSearchParams(window.location.search).get('branch');
    if (!branchId) {
        showErrorModal('Error', 'Branch ID is required');
        return;
    }

    // Set the order ID in the form
    const selectedOrderIdInput = document.getElementById('selectedOrderId');
    if (selectedOrderIdInput) {
        selectedOrderIdInput.value = orderId;
    }

    // Fetch order details first
    fetch(`/api/admin/orders/${orderId}?branch=${branchId}&include=items,order_items,products`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Authorization': `Bearer ${document.querySelector('meta[name="auth-token"]')?.getAttribute('content')}`,
            'X-Branch-ID': branchId
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to fetch order details');
        }
        return response.json();
    })
    .then(data => {
        console.log('Full order details:', data); // Debug log for full data
        console.log('Order items:', data.items || data.order_items); // Debug log for items
        const firstItem = data.items?.[0] || data.order_items?.[0];
        console.log('First item details:', firstItem); // Debug log for first item
        console.log('First item product details:', firstItem?.product); // Debug log for product details

        // Update the order row if it exists
        const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (orderRow) {
            // Remove selected class from all rows
            document.querySelectorAll('tr[data-order-id]').forEach(row => {
                row.classList.remove('selected');
            });
            // Add selected class to clicked row
            orderRow.classList.add('selected');
        }

        // Update payment amount
        const amountInput = document.getElementById('paymentAmount');
        const amountDisplay = document.getElementById('paymentAmountDisplay');
        if (amountInput && amountDisplay) {
            const total = data.total || data.grand_total || 0;
            amountInput.value = total;
            amountDisplay.value = formatCurrency(total);
            updateTotals();
        }

        // Update order details display with more comprehensive information
        const itemsTableBody = document.getElementById('itemsTableBody');
        if (itemsTableBody) {
            // Get items from either items or order_items array
            const orderItems = data.items || data.order_items || [];
            console.log('Processing items:', orderItems); // Debug log for items being processed

            // Format items list as table rows
            const itemsList = orderItems.map(item => {
                console.log('Processing item:', item); // Debug log for individual item
                console.log('Item product:', item.product); // Debug log for item's product
                
                // Get item details
                const product = item.product || {};
                const itemName = product.name || 'Unknown Item';
                const quantity = item.quantity || 0;
                const price = item.price || 0;
                const subtotal = item.subtotal || 0;

                return `
                    <tr>
                        <td class="px-4 py-2">${itemName}</td>
                        <td class="px-4 py-2 text-right">${quantity}</td>
                        <td class="px-4 py-2 text-right">${formatCurrency(price)}</td>
                        <td class="px-4 py-2 text-right">${formatCurrency(subtotal)}</td>
                    </tr>
                `;
            }).join('');

            // Update table body
            itemsTableBody.innerHTML = itemsList || '<tr><td colspan="4" class="px-4 py-2 text-center text-gray-500">No items found</td></tr>';

            // Update totals
            const subtotalElement = document.getElementById('subtotal');
            const totalElement = document.getElementById('total');
            if (subtotalElement) {
                subtotalElement.textContent = formatCurrency(data.subtotal || 0);
            }
            if (totalElement) {
                totalElement.textContent = formatCurrency(data.total || data.grand_total || 0);
            }
        }

        // Show payment modal
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.classList.remove('hidden');
        }

        // Update modal content
        const modalOrderNumber = document.getElementById('modalOrderNumber');
        const modalTotal = document.getElementById('modalTotal');
        if (modalOrderNumber) modalOrderNumber.textContent = data.order_number;
        if (modalTotal) modalTotal.textContent = formatCurrency(data.total || data.grand_total || 0);
    })
    .catch(error => {
        console.error('Error fetching order details:', error);
        showErrorModal('Error', 'Failed to fetch order details. Please try again.');
    });
}

// Add event listeners when the document is ready
document.addEventListener('DOMContentLoaded', function() {
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
            const cashFields = document.getElementById('cashPaymentFields');
            const cardFields = document.getElementById('cardPaymentFields');
            const walletFields = document.getElementById('walletPaymentFields');
            const currencyDenominationsContainer = document.getElementById('currencyDenominationsContainer');
            const amountReceived = document.getElementById('amountReceived');
            const changeAmount = document.getElementById('changeAmount');

            // Hide all fields first
            cashFields.classList.add('hidden');
            cardFields.classList.add('hidden');
            walletFields.classList.add('hidden');
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
            }

            // Reset change amount
            changeAmount.value = '';
        });
    }
});

// Add form submission handler
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const paymentMethod = formData.get('payment_method');
    const orderId = document.getElementById('selectedOrderId').value;
    const amount = formData.get('amount');
    const branchId = new URLSearchParams(window.location.search).get('branch');

    if (!branchId) {
        showErrorModal('Error', 'Branch ID is required. Please ensure you are accessing the payment manager through the correct URL.');
        return;
    }

    if (!orderId) {
        showErrorModal('Error', 'No order selected. Please select an order first.');
        return;
    }

    // Create data object with only the necessary fields
    const data = {
        order_id: orderId,
        payment_method: paymentMethod,
        amount: parseFloat(amount),
        notes: formData.get('notes')
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
        const response = await fetch('/admin/payments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${document.querySelector('meta[name="auth-token"]').getAttribute('content')}`,
                'X-Branch-ID': branchId
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Payment processing failed');
        }

        const result = await response.json();
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
        
        // Close payment modal
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.classList.add('hidden');
        }
        
    } catch (error) {
        console.error('Payment processing error:', error);
        showErrorModal('Error', error.message);
    }
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