// Notification and modal functions

function showSuccessModal(title, message) {
    // Play success sound
    playNotification();

    const modal = document.getElementById('successModal');
    const modalTitle = document.getElementById('successModalTitle');
    const modalMessage = document.getElementById('successModalMessage');
    if (modal && modalTitle && modalMessage) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.classList.remove('hidden');

        // Add X close button if not already present
        if (!modal.querySelector('.success-modal-close-btn')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'success-modal-close-btn';
            closeBtn.setAttribute('onclick', 'closeSuccessModal()');
            closeBtn.style.cssText = 'position:absolute;top:10px;right:10px;background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;line-height:1;';
            closeBtn.innerHTML = '&times;';
            const inner = modal.querySelector('div');
            if (inner && inner.style.position !== 'relative') inner.style.position = 'relative';
            if (inner) inner.appendChild(closeBtn);
        }

        // Auto-hide after 3 seconds (can be cancelled by X button)
        clearTimeout(modal._autoHideTimer);
        modal._autoHideTimer = setTimeout(() => {
            modal.classList.add('hidden');
        }, 3000);
    }
}
window.showSuccessModal = showSuccessModal;

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        clearTimeout(modal._autoHideTimer);
        modal.classList.add('hidden');
    }
}
window.closeSuccessModal = closeSuccessModal;

function showErrorModal(title, message) {
    // Play warning sound
    playWarning();

    const modal = document.getElementById('errorModal');
    const modalTitle = document.getElementById('errorModalTitle');
    const modalMessage = document.getElementById('errorModalMessage');
    if (modal && modalTitle && modalMessage) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.classList.remove('hidden');
    }
}
window.showErrorModal = showErrorModal;

function closeErrorModal() {
    const modal = document.getElementById('errorModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}
window.closeErrorModal = closeErrorModal;

// Wire up error modal close button
document.addEventListener('DOMContentLoaded', function() {
    const errorModalClose = document.getElementById('errorModalClose');
    if (errorModalClose) {
        errorModalClose.addEventListener('click', closeErrorModal);
    }
});

// New Order Notification System
function showNewOrderNotification(newOrders) {
    // Filter for all new orders (online, dining, takeaway) that are still pending
    const newPendingOrders = newOrders.filter(order =>
        order.status === 'pending'
    );

    if (newPendingOrders.length === 0) {
        return; // No pending orders to notify about
    }

    // Play notification sound
    if (soundManager) {
        soundManager.playSound('orderReceived');
    }

    // Create centered modal notification
    const notification = document.createElement('div');
    notification.id = 'newOrderNotification';
    notification.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    notification.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="newOrderModalContent" onclick="event.stopPropagation()">
            <!-- Header with gradient background -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">New Order${newPendingOrders.length > 1 ? 's' : ''}!</h3>
                            <p class="text-green-100 text-sm">${newPendingOrders.length} new order${newPendingOrders.length > 1 ? 's' : ''} received</p>
                        </div>
                    </div>
                    <button onclick="closeNewOrderNotification()" class="text-white hover:text-green-200 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div class="space-y-4">
                    ${newPendingOrders.map(order => `
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow" data-order-id="${order.id}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            #${order.order_number}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${order.type === 'online' ? 'bg-green-100 text-green-800' : order.type === 'dining' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'} mr-2">
                                            ${order.type.charAt(0).toUpperCase() + order.type.slice(1)}
                                        </span>
                                        <span class="text-sm text-gray-500">${new Date(order.created_at).toLocaleTimeString()}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Total Amount</p>
                                            <p class="text-lg font-bold text-green-600">Rs. ${order.total_amount.toFixed(2)}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Items</p>
                                            <p class="text-sm text-gray-600">${order.items.length} item${order.items.length > 1 ? 's' : ''}</p>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <p class="text-sm font-medium text-gray-900 mb-1">Order Items:</p>
                                        <div class="text-sm text-gray-600">
                                            ${order.items.slice(0, 3).map(item => `${item.item_name} x${item.quantity}`).join(', ')}
                                            ${order.items.length > 3 ? ` and ${order.items.length - 3} more...` : ''}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 action-buttons ml-4">
                                    ${order.type === 'online' ? `
                                        <button onclick="acceptOrder(${order.id})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Accept
                                        </button>
                                        <button onclick="declineOrder(${order.id})" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors shadow-sm flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Decline
                                        </button>
                                    ` : `
                                        <button onclick="closeNewOrderNotification(); selectOrder(${order.id})" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View Order
                                        </button>
                                    `}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Auto-close in 30s
                        </span>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="closeNewOrderNotification()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors font-medium">
                            Dismiss
                        </button>
                        <button onclick="viewAllOrders()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm">
                            View All Orders
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Animate modal in
    setTimeout(() => {
        const modalContent = document.getElementById('newOrderModalContent');
        if (modalContent) {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }
    }, 10);

    // Auto-close after 30 seconds
    setTimeout(() => {
        if (document.getElementById('newOrderNotification')) {
            closeNewOrderNotification();
        }
    }, 30000);
}

function closeNewOrderNotification() {
    const notification = document.getElementById('newOrderNotification');
    const modalContent = document.getElementById('newOrderModalContent');

    if (notification && modalContent) {
        // Animate modal out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        // Remove from DOM after animation
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    } else if (notification) {
        // Fallback if modalContent not found
        notification.remove();
    }
}
window.closeNewOrderNotification = closeNewOrderNotification;

// Toast notification system
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-in-out translate-x-full`;

    // Set colors based on type
    let bgColor = 'bg-blue-50';
    let textColor = 'text-blue-800';
    let iconColor = 'text-blue-400';
    let icon = 'ℹ️';

    switch (type) {
        case 'success':
            bgColor = 'bg-green-50';
            textColor = 'text-green-800';
            iconColor = 'text-green-400';
            icon = '✅';
            break;
        case 'error':
            bgColor = 'bg-red-50';
            textColor = 'text-red-800';
            iconColor = 'text-red-400';
            icon = '❌';
            break;
        case 'warning':
            bgColor = 'bg-yellow-50';
            textColor = 'text-yellow-800';
            iconColor = 'text-yellow-400';
            icon = '⚠️';
            break;
    }

    toast.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <span class="text-lg">${icon}</span>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium ${textColor}">
                        ${message}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="bg-white rounded-md inline-flex ${textColor} hover:${textColor.replace('800', '600')} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add to container
    toastContainer.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}
