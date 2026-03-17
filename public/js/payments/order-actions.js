// Order action functions module

// Store order IDs for confirmation modals
let pendingReadyOrderId = null;
let pendingPreparingOrderId = null;

function updateOrderCardButton(orderId, state) {
    // Find the order card in the order grids by looking for the specific order ID in the buttons
    const orderCards = document.querySelectorAll('.order-card');
    let targetCard = null;

    orderCards.forEach(card => {
        if (card.innerHTML.includes(`acceptOrder(${orderId})`) ||
            card.innerHTML.includes(`declineOrder(${orderId})`)) {
            targetCard = card;
        }
    });

    if (!targetCard) return;

    const actionButtons = targetCard.querySelector('.mt-3.pt-3.border-t.border-gray-200');
    if (!actionButtons) return;

    switch (state) {
        case 'accepting':
            actionButtons.innerHTML = `
                <div class="flex gap-2">
                    <button disabled class="flex-1 px-3 py-2 bg-green-100 text-green-600 text-sm font-medium rounded-md cursor-not-allowed">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Accepting...
                    </button>
                    <button disabled class="flex-1 px-3 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-md cursor-not-allowed">
                        <i class="fas fa-times mr-1"></i> Decline
                    </button>
                </div>
            `;
            break;

        case 'declining':
            actionButtons.innerHTML = `
                <div class="flex gap-2">
                    <button disabled class="flex-1 px-3 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-md cursor-not-allowed">
                        <i class="fas fa-check mr-1"></i> Accept
                    </button>
                    <button disabled class="flex-1 px-3 py-2 bg-red-100 text-red-600 text-sm font-medium rounded-md cursor-not-allowed">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Declining...
                    </button>
                </div>
            `;
            break;
    }
}

function updateOrderActionState(orderId, state, errorMessage = null) {
    const notification = document.getElementById('newOrderNotification');
    if (!notification) return;

    // Find the order card within the notification
    const orderCard = notification.querySelector(`[data-order-id="${orderId}"]`);
    if (!orderCard) return;

    const actionButtons = orderCard.querySelector('.action-buttons');
    if (!actionButtons) return;

    switch (state) {
        case 'accepting':
            actionButtons.innerHTML = `
                <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-green-100 rounded-lg">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-600"></div>
                    <span class="text-sm text-green-600">Accepting...</span>
                </div>
            `;
            break;

        case 'accepted':
            actionButtons.innerHTML = `
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-green-600 font-medium">Accepted!</span>
                    </div>
                    <button onclick="closeNewOrderNotification()" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                        OK
                    </button>
                </div>
            `;
            break;

        case 'declining':
            actionButtons.innerHTML = `
                <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-red-100 rounded-lg">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-600"></div>
                    <span class="text-sm text-red-600">Declining...</span>
                </div>
            `;
            break;

        case 'declined':
            actionButtons.innerHTML = `
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-red-100 rounded-lg">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-sm text-red-600 font-medium">Declined</span>
                    </div>
                    <button onclick="closeNewOrderNotification()" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                        OK
                    </button>
                </div>
            `;
            break;

        case 'preparing':
            actionButtons.innerHTML = `
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-blue-600 font-medium">Preparing!</span>
                    </div>
                    <button onclick="closeNewOrderNotification()" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                        OK
                    </button>
                </div>
            `;
            break;

        case 'error':
            actionButtons.innerHTML = `
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-red-100 rounded-lg">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-red-600">Error: ${errorMessage || 'Unknown error'}</span>
                    </div>
                    <button onclick="closeNewOrderNotification()" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                        Close
                    </button>
                </div>
            `;
            break;
    }
}

function acceptOrder(orderId) {
    // Check if this is called from popup or order card
    const notification = document.getElementById('newOrderNotification');
    const isFromPopup = notification && notification.querySelector(`[data-order-id="${orderId}"]`);

    if (isFromPopup) {
        // Update the popup to show "Accepting..." state
        updateOrderActionState(orderId, 'accepting');
    } else {
        // Update order card button to show loading state
        updateOrderCardButton(orderId, 'accepting');
    }

    // Update order status to confirmed
    fetch(`/admin/orders/${orderId}/accept`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isFromPopup) {
                // Update popup to show "Accepted" state
                updateOrderActionState(orderId, 'accepted');
            } else {
                // Show success message for order card
                showToast('Order accepted successfully!', 'success');
            }

            // Print to kitchen
            printKitchenOrder(orderId);

            // Refresh orders after a short delay
            setTimeout(() => {
                fetchOrders();
            }, 2000);
        } else {
            if (isFromPopup) {
                // Show error state in popup
                updateOrderActionState(orderId, 'error', data.message);
            } else {
                // Show error message for order card
                showToast(data.message || 'Failed to accept order', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error accepting order:', error);
        if (isFromPopup) {
            updateOrderActionState(orderId, 'error', 'Failed to accept order');
        } else {
            showToast('Failed to accept order. Please try again.', 'error');
        }
    });
}

function declineOrder(orderId) {
    // Check if this is called from popup or order card
    const notification = document.getElementById('newOrderNotification');
    const isFromPopup = notification && notification.querySelector(`[data-order-id="${orderId}"]`);

    if (isFromPopup) {
        // Update the popup to show "Declining..." state
        updateOrderActionState(orderId, 'declining');
    } else {
        // Update order card button to show loading state
        updateOrderCardButton(orderId, 'declining');
    }

    // Update order status to declined
    fetch(`/admin/orders/${orderId}/decline`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isFromPopup) {
                // Update popup to show "Declined" state
                updateOrderActionState(orderId, 'declined');
            } else {
                // Show success message for order card
                showToast('Order declined successfully!', 'success');
            }

            // Refresh orders after a short delay
            setTimeout(() => {
                fetchOrders();
            }, 2000);
        } else {
            if (isFromPopup) {
                // Show error state in popup
                updateOrderActionState(orderId, 'error', data.message);
            } else {
                // Show error message for order card
                showToast(data.message || 'Failed to decline order', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error declining order:', error);
        if (isFromPopup) {
            updateOrderActionState(orderId, 'error', 'Failed to decline order');
        } else {
            showToast('Failed to decline order. Please try again.', 'error');
        }
    });
}

function markOrderAsReady(orderId) {
    // Store the order ID
    pendingReadyOrderId = orderId;

    // Find the order to get details
    const order = allOrders.find(o => o.id === orderId);

    if (order) {
        // Update modal with order details
        const orderNumberEl = document.getElementById('markReadyOrderNumber');
        const orderAmountEl = document.getElementById('markReadyOrderAmount');

        if (orderNumberEl) orderNumberEl.textContent = order.order_number || `#${order.id}`;
        if (orderAmountEl) orderAmountEl.textContent = `Rs ${parseFloat(order.total_amount || 0).toFixed(2)}`;
    }

    // Show the beautiful modal
    const modal = document.getElementById('markReadyModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Add fade-in animation
        setTimeout(() => {
            modal.querySelector('.bg-white').classList.add('animate-scale-in');
        }, 10);

        // Add keyboard event listener for ESC key
        document.addEventListener('keydown', handleMarkReadyModalKeydown);

        // Add click outside to close
        modal.addEventListener('click', handleMarkReadyModalBackdropClick);
    }
}

function handleMarkReadyModalKeydown(e) {
    if (e.key === 'Escape') {
        closeMarkReadyModal();
    }
}

function handleMarkReadyModalBackdropClick(e) {
    if (e.target.id === 'markReadyModal') {
        closeMarkReadyModal();
    }
}

function closeMarkReadyModal() {
    const modal = document.getElementById('markReadyModal');
    if (modal) {
        // Add fade-out animation
        const modalContent = modal.querySelector('.bg-white');
        modalContent.classList.add('animate-scale-out');

        setTimeout(() => {
            modal.classList.add('hidden');
            modalContent.classList.remove('animate-scale-in', 'animate-scale-out');
        }, 200);

        // Remove event listeners to prevent memory leaks
        document.removeEventListener('keydown', handleMarkReadyModalKeydown);
        modal.removeEventListener('click', handleMarkReadyModalBackdropClick);
    }
    pendingReadyOrderId = null;
}

function confirmMarkAsReady() {
    if (!pendingReadyOrderId) {
        console.error('No order ID stored for marking as ready');
        return;
    }

    const orderId = pendingReadyOrderId;

    // Close the modal
    closeMarkReadyModal();

    // Update order card button to show loading state
    const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
    if (orderCard) {
        const button = orderCard.querySelector('button[onclick*="markOrderAsReady"]');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Marking...';
        }
    }

    // Update order status to ready
    fetch(`/admin/orders/${orderId}/mark-as-ready`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const order = allOrders.find(o => o.id === orderId);
            showOrderReadySuccess(order ? (order.order_number || `#${orderId}`) : `#${orderId}`);

            // Immediately refresh orders and update payment panel
            fetchOrders();
            if (window.selectedOrder && window.selectedOrder.id === orderId) {
                window.selectedOrder.status = 'ready';
                populatePaymentPanelContent(window.selectedOrder);
            }
        } else {
            showToast(data.message || 'Failed to mark order as ready', 'error');
            // Restore button state
            if (orderCard) {
                const button = orderCard.querySelector('button[onclick*="markOrderAsReady"]');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Mark as Ready';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error marking order as ready:', error);
        showToast('❌ Failed to mark order as ready. Please try again.', 'error');
        // Restore button state
        if (orderCard) {
            const button = orderCard.querySelector('button[onclick*="markOrderAsReady"]');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Mark as Ready';
            }
        }
    });
}

// Export modal functions to global scope
window.markOrderAsReady = markOrderAsReady;
window.closeMarkReadyModal = closeMarkReadyModal;
window.confirmMarkAsReady = confirmMarkAsReady;

function printKitchenOrder(orderId) {
    // Open print window for kitchen order
    const printWindow = window.open(`/admin/orders/${orderId}/kitchen-print`, '_blank', 'width=800,height=600');
    if (printWindow) {
        printWindow.focus();
    }
}
window.printKitchenOrder = printKitchenOrder;

// Reset order status to pending (for re-accept/decline)
function resetOrderStatus(orderId, previousStatus) {
    if (!confirm(`Are you sure you want to reset this order from "${previousStatus}" back to "pending"? This will allow you to accept or decline it again.`)) {
        return;
    }

    fetch(`/admin/orders/${orderId}/reset-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            previous_status: previousStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast(`Order status reset to pending successfully!`, 'success');

            // Refresh orders to show updated status
            fetchOrders();
        } else {
            // Show error message
            showToast(data.message || 'Failed to reset order status', 'error');
        }
    })
    .catch(error => {
        console.error('Error resetting order status:', error);
        showToast('Failed to reset order status. Please try again.', 'error');
    });
}
window.resetOrderStatus = resetOrderStatus;

function markAsPreparing(orderId) {
    pendingPreparingOrderId = orderId;

    // Find the order to populate modal details
    const order = allOrders.find(o => o.id === orderId);

    const modal = document.getElementById('markPreparingModal');
    if (modal) {
        const numEl = document.getElementById('markPreparingOrderNumber');
        const tableEl = document.getElementById('markPreparingTableName');
        if (numEl) numEl.textContent = order ? (order.order_number || `#${order.id}`) : `#${orderId}`;
        const tableName = order
            ? (order.table_name || (order.table && typeof order.table === 'object' ? order.table.name : order.table) || 'N/A')
            : 'N/A';
        if (tableEl) tableEl.textContent = tableName;
        modal.classList.remove('hidden');
        document.addEventListener('keydown', handleMarkPreparingModalKeydown);
        modal.addEventListener('click', handleMarkPreparingModalBackdropClick);
    }
}

function handleMarkPreparingModalKeydown(e) {
    if (e.key === 'Escape') closeMarkPreparingModal();
}

function handleMarkPreparingModalBackdropClick(e) {
    if (e.target.id === 'markPreparingModal') closeMarkPreparingModal();
}

function closeMarkPreparingModal() {
    const modal = document.getElementById('markPreparingModal');
    if (modal) {
        modal.classList.add('hidden');
        document.removeEventListener('keydown', handleMarkPreparingModalKeydown);
        modal.removeEventListener('click', handleMarkPreparingModalBackdropClick);
    }
    pendingPreparingOrderId = null;
}

async function confirmMarkAsPreparing() {
    if (!pendingPreparingOrderId) return;
    const orderId = pendingPreparingOrderId;
    closeMarkPreparingModal();

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(`/admin/orders/${orderId}/mark-as-preparing`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Failed to mark order as preparing');
        showSuccessModal('Preparing', 'Order is now being prepared.');
        fetchOrders();
        if (window.selectedOrder && window.selectedOrder.id === orderId) {
            window.selectedOrder.status = 'preparing';
            populatePaymentPanelContent(window.selectedOrder);
        }
    } catch (error) {
        console.error('Error marking order as preparing:', error);
        showToast(error.message, 'error');
    }
}

window.markAsPreparing = markAsPreparing;
window.closeMarkPreparingModal = closeMarkPreparingModal;
window.confirmMarkAsPreparing = confirmMarkAsPreparing;

// FLOW 3: Mark online/delivery order as out for delivery
async function markOutForDelivery(orderId) {
    if (!confirm('Mark this order as Out for Delivery?')) return;
    try {
        const response = await fetch(`/admin/orders/${orderId}/mark-out-for-delivery`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Failed to update status');
        showSuccessModal('Out for Delivery', 'Order is on its way!');
        fetchOrders();
        if (window.selectedOrder && window.selectedOrder.id === orderId) {
            window.selectedOrder.status = 'out_for_delivery';
            populatePaymentPanelContent(window.selectedOrder);
        }
    } catch (error) {
        showErrorModal('Error', error.message);
    }
}
window.markOutForDelivery = markOutForDelivery;

// FLOW 3: Mark online/delivery order as delivered and record COD payment
async function markAsDelivered(orderId) {
    if (!confirm('Confirm delivery and collect payment?')) return;
    try {
        const response = await fetch(`/admin/orders/${orderId}/mark-as-delivered`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || data.message || 'Failed to mark as delivered');
        showOrderReadySuccess('Order delivered & payment recorded!');
        fetchOrders();
        if (window.selectedOrder && window.selectedOrder.id === orderId) {
            window.selectedOrder.status = 'completed';
            window.selectedOrder.payment_status = 'paid';
            populatePaymentPanelContent(window.selectedOrder);
        }
    } catch (error) {
        showErrorModal('Error', error.message);
    }
}
window.markAsDelivered = markAsDelivered;

function showOrderReadySuccess(orderNumber) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;';
    modal.innerHTML = `
        <div style="background:white;border-radius:16px;padding:40px;text-align:center;max-width:400px;width:90%;position:relative;">
            <button onclick="this.closest('div[style]').remove()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
            <div style="font-size:60px;margin-bottom:16px;">✅</div>
            <h2 style="color:#16a34a;font-size:24px;font-weight:bold;margin-bottom:8px;">Order Ready!</h2>
            <p style="color:#666;margin-bottom:8px;">${orderNumber}</p>
            <p style="color:#888;font-size:14px;">Customer has been notified.</p>
            <button onclick="this.closest('div[style]').remove()" style="margin-top:24px;background:#16a34a;color:white;border:none;padding:12px 32px;border-radius:8px;font-size:16px;cursor:pointer;width:100%;">OK</button>
        </div>
    `;
    document.body.appendChild(modal);
    setTimeout(() => { if (modal.parentNode) modal.remove(); }, 4000);
}
