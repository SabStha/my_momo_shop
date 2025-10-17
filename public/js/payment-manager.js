// --- Payment Viewer Communication Functions (top-level scope) ---
const attemptAutoOpen = () => {
    console.log('Attempting to auto-open payment viewer...');
    // Get branch ID
    const urlParams = new URLSearchParams(window.location.search);
    const branchId = urlParams.get('branch');
    if (!branchId) {
        console.error('Branch ID not found in URL');
        return;
    }
    // Check if payment viewer is already open
    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        console.log('Payment viewer already open');
        return;
    }
    // Calculate window size
    const width = Math.min(400, window.innerWidth * 0.9);
    const height = Math.min(700, window.innerHeight * 0.9);
    const left = (window.innerWidth - width) / 2;
    const top = (window.innerHeight - height) / 2;
    // Open payment viewer
    const viewerUrl = `/customer/payment-viewer?branch=${branchId}`;
    console.log('Opening payment viewer URL:', viewerUrl);
    // Test the URL first
    fetch(viewerUrl, { method: 'HEAD' })
        .then(response => {
            console.log('Payment viewer route test response:', response.status);
            if (response.ok) {
                // Route is accessible, open the window
                try {
                    window.paymentViewerWindow = window.open(
                        viewerUrl,
                        'Payment Viewer',
                        `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
                    );
                    if (!window.paymentViewerWindow) {
                        console.warn('Popup blocked. Payment viewer will not open automatically.');
                        return;
                    }
                    // Focus the window
                    window.paymentViewerWindow.focus();
                    // Add event listener for window close
                    window.paymentViewerWindow.addEventListener('beforeunload', () => {
                        console.log('Payment viewer window closed');
                        window.paymentViewerWindow = null;
                    });
                    console.log('Payment viewer opened successfully');
                } catch (error) {
                    console.error('Error opening payment viewer:', error);
                }
            } else {
                console.error('Payment viewer route not accessible:', response.status);
            }
        })
        .catch(error => {
            console.error('Error testing payment viewer route:', error);
        });
};

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

// --- End top-level functions ---

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the payments page
    const paymentApp = document.getElementById('paymentApp');
    if (!paymentApp) {
        console.log('PaymentApp element not found - not on payments page, skipping payment manager initialization');
        return;
    }

    // Payment Manager JavaScript
    console.log('=== PAYMENT MANAGER JS LOADED ===');
    
    // Prevent form submission
    const paymentForm = document.getElementById('paymentPanelForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission prevented - using JavaScript handling');
        });
    }
    
    // Auto-authenticate for payment access if admin, then start order polling
    authenticatePaymentAccess().then(() => {
        console.log('Payment authentication successful, starting order polling');
        
        // Hide loading state and show orders
        const loadingState = document.getElementById('ordersLoadingState');
        const ordersSections = document.getElementById('ordersSections');
        
        if (loadingState) loadingState.style.display = 'none';
        if (ordersSections) ordersSections.classList.remove('hidden');
        
        // Start order polling
        startOrderPolling();
    }).catch((error) => {
        console.error('Payment authentication failed:', error);
        
        // Show error state
        const loadingState = document.getElementById('ordersLoadingState');
        if (loadingState) {
            loadingState.innerHTML = `
                <div class="text-center">
                    <div class="text-red-500 text-4xl mb-4">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p class="text-red-600 text-sm font-medium">Authentication Failed</p>
                    <p class="text-gray-400 text-xs mt-1">Please refresh the page or contact administrator</p>
                </div>
            `;
        }
        
        showErrorModal('Authentication Error', 'Failed to authenticate payment access. Please refresh the page.');
    });

    // Auto-open payment viewer functionality
    const manualOpenPaymentViewer = () => {
        console.log('Manually opening payment viewer...');
        
        // Get branch ID
        const urlParams = new URLSearchParams(window.location.search);
        const branchId = urlParams.get('branch');
        
        if (!branchId) {
            showErrorModal('Error', 'Branch ID not found. Please refresh the page and try again.');
            return;
        }

        // Calculate window size based on screen size
        const width = Math.min(400, window.innerWidth * 0.9);
        const height = Math.min(700, window.innerHeight * 0.9);
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;

        // Open payment viewer
        const viewerUrl = `/customer/payment-viewer?branch=${branchId}`;
        console.log('Manually opening payment viewer URL:', viewerUrl);

        try {
            window.paymentViewerWindow = window.open(
                viewerUrl,
                'Payment Viewer',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
            );

            if (!window.paymentViewerWindow) {
                showErrorModal('Popup Blocked', 'Please allow popups for this website to view the payment screen.');
                return;
            }

            // Focus the window
            window.paymentViewerWindow.focus();

            // Add event listener for window close
            window.paymentViewerWindow.addEventListener('beforeunload', () => {
                console.log('Payment viewer window closed');
                window.paymentViewerWindow = null;
            });

            console.log('Payment viewer opened successfully');
        } catch (error) {
            console.error('Error opening payment viewer:', error);
            showErrorModal('Error', 'Failed to open payment viewer. Please try again.');
        }
    };

    // Start auto-open after page loads
    console.log('Payment manager loaded, will attempt to open payment viewer...');
    console.log('Current URL:', window.location.href);
    console.log('Branch ID from URL:', new URLSearchParams(window.location.search).get('branch'));
    
    // Wait for DOM to be fully loaded and then wait a bit more for any async operations
    const initializeAutoOpen = () => {
        console.log('Initializing auto-open payment viewer...');
        console.log('DOM ready state:', document.readyState);
        setTimeout(() => {
            console.log('About to attempt auto-open...');
            attemptAutoOpen();
        }, 2000); // Wait 2 seconds for everything to load
    };
    
    if (document.readyState === 'loading') {
        console.log('DOM still loading, adding event listener...');
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOMContentLoaded fired');
            initializeAutoOpen();
        });
    } else {
        console.log('DOM already loaded, starting auto-open...');
        // If DOM is already loaded, wait a bit more
        setTimeout(initializeAutoOpen, 1000);
    }

    // Add event listener for manual payment viewer button
    const openPaymentViewerBtn = document.getElementById('openPaymentViewerBtn');
    if (openPaymentViewerBtn) {
        openPaymentViewerBtn.addEventListener('click', manualOpenPaymentViewer);
    }

    // Listen for messages from payment viewer
    window.addEventListener('message', function(event) {
        try {
            // Only accept messages from the same origin
            if (event.origin !== window.location.origin) {
                return;
            }

            // Validate message data
            if (!event.data || typeof event.data !== 'object') {
                console.log('Received invalid message data:', event.data);
                return;
            }

            console.log('Payment manager received message from viewer:', event.data);

            switch (event.data.type) {
            case 'VIEWER_PAYMENT_METHOD_SELECTED':
                console.log('Viewer selected payment method:', event.data.method);
                
                // Update payment method selection in manager
                const paymentMethodBtns = document.querySelectorAll('.payment-method-btn');
                paymentMethodBtns.forEach(btn => {
                    btn.classList.remove('ring-2', 'ring-blue-500');
                    if (btn.getAttribute('data-method') === event.data.method) {
                        btn.classList.add('ring-2', 'ring-blue-500');
                    }
                });
                
                // Store selected payment method
                window.selectedPaymentMethod = event.data.method;
                
                // Show/hide relevant fields
                const cashFields = document.getElementById('cashFields');
                const cardMobileFields = document.getElementById('cardMobileFields');
                
                if (event.data.method === 'cash') {
                    cashFields.classList.remove('hidden');
                    cardMobileFields.classList.add('hidden');
                    if (typeof initializeCashDenominations === 'function') {
                        initializeCashDenominations();
                    }
                } else {
                    cashFields.classList.add('hidden');
                    cardMobileFields.classList.remove('hidden');
                }
                break;
                
            default:
                // Handle unknown message types gracefully
                if (event.data && typeof event.data === 'object') {
                    console.log('Unknown message type from viewer:', event.data.type || 'undefined');
                } else {
                    console.log('Received non-object message from viewer:', typeof event.data);
                }
        }
        } catch (error) {
            console.error('Error handling message from viewer:', error);
        }
    });

    // ... existing code from <script> block in index.blade.php ...
    // (All JS logic moved here)
    // Cash Drawer Modal Functions
    function showCashDrawerModal(action = 'open') {
        const modal = document.getElementById('cashDrawerModal');
        const title = document.getElementById('modalTitle');
        const confirmBtn = document.getElementById('confirmModalBtn');
        const confirmBtnText = document.getElementById('confirmBtnText');
        const modalDrawerStatus = document.getElementById('modalDrawerStatus');
        if (modal && title && confirmBtn && confirmBtnText && modalDrawerStatus) {
            modal.classList.remove('hidden');
            if (action === 'open') {
                title.textContent = 'Open Cash Drawer';
                confirmBtnText.textContent = 'Open Drawer';
                modalDrawerStatus.textContent = 'Set starting denominations and notes, then open the drawer.';
                // Set default denominations
                const defaultDenoms = {1000: 0, 500: 5, 100: 20, 50: 20, 20: 50, 10: 50, 5: 50, 2: 200, 1: 200};
                Object.keys(defaultDenoms).forEach(denom => {
                    const input = document.getElementById('denom_' + denom);
                    if (input) input.value = defaultDenoms[denom];
                    const totalSpan = document.getElementById('total_' + denom);
                    if (totalSpan) totalSpan.textContent = defaultDenoms[denom] * denom;
                });
                // Update total cash amount
                let total = 0;
                Object.entries(defaultDenoms).forEach(([denom, count]) => {
                    total += parseInt(denom) * count;
                });
                const totalCashAmount = document.getElementById('totalCashAmount');
                if (totalCashAmount) totalCashAmount.textContent = 'Rs ' + total;
            } else {
                title.textContent = 'Close Cash Drawer';
                confirmBtnText.textContent = 'Close Drawer';
                modalDrawerStatus.textContent = 'Review denominations and notes, then close the drawer.';
                // Fetch current denominations from backend
                const branchId = document.getElementById('paymentApp').dataset.branchId;
                fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`)
                    .then(res => res.json())
                    .then(data => {
                        const denoms = data.denominations || {};
                        Object.keys(denoms).forEach(denom => {
                            const input = document.getElementById('denom_' + denom);
                            if (input) input.value = denoms[denom];
                            const totalSpan = document.getElementById('total_' + denom);
                            if (totalSpan) totalSpan.textContent = denoms[denom] * denom;
                        });
                        // Update total cash amount
                        let total = 0;
                        Object.entries(denoms).forEach(([denom, count]) => {
                            total += parseInt(denom) * count;
                        });
                        const totalCashAmount = document.getElementById('totalCashAmount');
                        if (totalCashAmount) totalCashAmount.textContent = 'Rs ' + total;
                    });
            }
            confirmBtn.onclick = () => handleDrawerAction(action);
        }
    }
    window.showCashDrawerModal = showCashDrawerModal;

    function hideCashDrawerModal() {
        const modal = document.getElementById('cashDrawerModal');
        if (modal) modal.classList.add('hidden');
    }
    window.hideCashDrawerModal = hideCashDrawerModal;

    // Clean up declined orders from UI when drawer is closed
    function cleanupDeclinedOrdersFromUI() {
        try {
            // Find all declined order cards and remove them from the UI
            const orderCards = document.querySelectorAll('.order-card');
            let removedCount = 0;
            
            orderCards.forEach(card => {
                // Check if this card contains a declined order
                const statusBadge = card.querySelector('.bg-red-100.text-red-800');
                if (statusBadge && statusBadge.textContent.trim().toLowerCase() === 'declined') {
                    // Remove the card with a fade-out animation
                    card.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        card.remove();
                        removedCount++;
                    }, 300);
                }
            });
            
            if (removedCount > 0) {
                console.log(`Cleaned up ${removedCount} declined orders from UI`);
                
                // Update order counts
                setTimeout(() => {
                    const onlineCountElement = document.getElementById('onlineCount');
                    if (onlineCountElement) {
                        const currentCount = parseInt(onlineCountElement.textContent) || 0;
                        onlineCountElement.textContent = Math.max(0, currentCount - removedCount);
                    }
                }, 350);
            }
        } catch (error) {
            console.error('Error cleaning up declined orders from UI:', error);
        }
    }

    async function handleDrawerAction(action) {
        const branchId = document.getElementById('paymentApp').dataset.branchId;
        const notes = document.getElementById('drawerNotes').value;
        // Calculate opening balance and denominations
        let openingBalance = 0;
        let openingDenominations = {};
        let closingDenominations = {};
        [1000,500,100,50,20,10,5,2,1].forEach(denom => {
            const input = document.getElementById('denom_' + denom);
            if (input) {
                const count = parseInt(input.value) || 0;
                openingBalance += denom * count;
                openingDenominations[denom] = count;
                closingDenominations[denom] = count;
            }
        });
        const url = action === 'open'
            ? '/admin/cash-drawer/open'
            : '/admin/cash-drawer/close';
        const method = 'POST';

        // Build request body
        const body = action === 'open'
            ? { branch_id: branchId, notes, opening_balance: openingBalance, opening_denominations: openingDenominations }
            : { branch_id: branchId, notes, closing_denominations: closingDenominations };

        // Show loading spinner and disable confirm button
        const confirmBtn = document.getElementById('confirmModalBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        if (confirmBtn) confirmBtn.disabled = true;
        if (loadingSpinner) loadingSpinner.classList.remove('hidden');

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(body),
                credentials: 'same-origin'
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || data.error || 'Drawer action failed');
            hideCashDrawerModal();
            
            // If closing drawer, clean up declined orders from UI
            if (action === 'close') {
                cleanupDeclinedOrdersFromUI();
            }
            
            showSuccessModal('Success', action === 'open' ? 'Drawer opened.' : 'Drawer closed.');
            if (typeof updateDrawerButtonState === 'function') {
                updateDrawerButtonState();
            }
            
            // Handle orders based on drawer action
            if (action === 'open') {
                // When opening drawer, start with clean slate
                currentSessionStartTime = new Date(); // Set session start time
                hideAllOrders();
                hideDrawerClosedMessage();
                
                // Show new session message
                showNewSessionMessage();
                
                // Small delay to show the new session message, then start fetching orders
                setTimeout(() => {
                    fetchOrders();
                }, 1000);
            } else {
                // When closing drawer, clear session and refresh to hide orders
                currentSessionStartTime = null;
                fetchOrders();
            }
        } catch (error) {
            showErrorModal('Error', error.message);
        } finally {
            // Hide loading spinner and re-enable confirm button
            if (confirmBtn) confirmBtn.disabled = false;
            if (loadingSpinner) loadingSpinner.classList.add('hidden');
        }
    }
    window.handleDrawerAction = handleDrawerAction;

    // Wire up modal close/cancel button
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideCashDrawerModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', hideCashDrawerModal);

    // Disable 'Open Drawer' button if drawer is already open
    async function updateDrawerButtonState() {
        try {
            const paymentApp = document.getElementById('paymentApp');
            if (!paymentApp) {
                console.error('PaymentApp element not found');
                return;
            }
            
            const branchId = paymentApp.dataset.branchId;
            const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch drawer status');
            }
            
            const data = await response.json();
            
            const openButton = document.getElementById('openDrawerBtn');
            const closeButton = document.getElementById('closeDrawerBtn');
            const statusIndicator = document.getElementById('drawerStatusIndicator');
            
            // Update pending orders warning
            updatePendingOrdersWarning(data.pending_unpaid_orders || 0);
            
            if (data.is_open) {
                // If drawer is already open when page loads, DON'T set session start time
                // This prevents filtering out existing orders when you reload the page
                // Session start time is only set when YOU actually open the drawer
                if (!currentSessionStartTime) {
                    console.log('💡 Drawer was already open - showing ALL orders (no session time filtering)');
                }
                
                if (openButton) openButton.style.display = 'none';
                if (closeButton) {
                    closeButton.style.display = 'inline-flex';
                    // Disable close button if there are pending unpaid orders
                    if (data.can_close === false) {
                        closeButton.disabled = true;
                        closeButton.title = `Cannot close drawer. ${data.pending_unpaid_orders} pending unpaid orders need to be handled first.`;
                        closeButton.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        closeButton.disabled = false;
                        closeButton.title = 'Close cash drawer';
                        closeButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
                if (statusIndicator) {
                    statusIndicator.textContent = 'Open';
                    statusIndicator.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                }
            } else {
                if (openButton) openButton.style.display = 'inline-flex';
                if (closeButton) closeButton.style.display = 'none';
                if (statusIndicator) {
                    statusIndicator.textContent = 'Closed';
                    statusIndicator.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                }
            }
            
            // Refresh orders to show/hide based on drawer status
            fetchOrders();
        } catch (error) {
            console.error('Error updating drawer button state:', error);
        }
    }

    function updatePendingOrdersWarning(pendingCount) {
        // Find or create the warning element
        let warningElement = document.getElementById('pendingOrdersWarning');
        
        if (pendingCount > 0) {
            if (!warningElement) {
                // Create warning element
                warningElement = document.createElement('div');
                warningElement.id = 'pendingOrdersWarning';
                warningElement.className = 'bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4';
                
                // Insert after the status bar
                const statusBar = document.querySelector('.status-bar');
                if (statusBar) {
                    statusBar.insertAdjacentElement('afterend', warningElement);
                }
            }
            
            warningElement.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Warning:</strong> There ${pendingCount === 1 ? 'is' : 'are'} ${pendingCount} pending unpaid online order${pendingCount === 1 ? '' : 's'} that need to be handled before closing the cash drawer.
                        </p>
                    </div>
                </div>
            `;
            warningElement.style.display = 'block';
        } else if (warningElement) {
            warningElement.style.display = 'none';
        }
    }

    updateDrawerButtonState();
    // Optionally, poll or call updateDrawerButtonState after open/close actions

    // Order polling will start after authentication is complete
    
    // Initialize password validation
    initializePasswordValidation();
    
    // Initialize order filter buttons
    initializeOrderFilters();
    
    // Initialize payment methods
    initializePaymentMethods();

    // Initialize sound manager
    soundManager = new SoundManager();
    soundManager.updateMuteButton();
    
    // Add click handler to initialize audio context on first user interaction
    document.addEventListener('click', function initAudio() {
        if (soundManager && soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
            soundManager.audioContext.resume();
        }
        document.removeEventListener('click', initAudio);
    }, { once: true });
});

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
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 3000);
    }
}
window.showSuccessModal = showSuccessModal;

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

async function showPhysicalDrawerDenominationsModal() {
    const branchId = document.getElementById('paymentApp').dataset.branchId;
    try {
        const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. Please check authentication.');
        }
        
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || data.error || 'Failed to fetch denominations');
        
        // Populate starting denominations
        const startingDenoms = data.session ? data.session.opening_denominations : {};
        const currentDenoms = data.denominations || {};
        const alerts = (data.alerts && data.alerts.alerts) ? data.alerts.alerts : [];
        const alertMap = {};
        alerts.forEach(a => {
            if (!alertMap[a.denomination]) alertMap[a.denomination] = [];
            alertMap[a.denomination].push(a);
        });
        const denomOrder = [1000,500,100,50,20,10,5,2,1];
        let startingHtml = '';
        let currentHtml = '';
        let totalStarting = 0;
        let totalCurrent = 0;
        denomOrder.forEach(denom => {
            const startCount = startingDenoms[denom] || 0;
            const currCount = currentDenoms[denom] || 0;
            startingHtml += `<div class='flex justify-between'><span>Rs ${denom}</span><span>${startCount}</span></div>`;
            // Editable input for current
            currentHtml += `<div class='mb-2'>
                <div class='flex justify-between items-center'>
                    <span>Rs ${denom}</span>
                    <input type='number' id='edit_current_${denom}' class='w-20 px-2 py-1 border border-gray-300 rounded text-right' min='0' value='${currCount}' onchange='recalcCurrentDenomsTotal()'>
                </div>`;
            // Alerts for this denom
            if (alertMap[denom]) {
                currentHtml += `<ul class='ml-2 mt-1 space-y-1'>`;
                alertMap[denom].forEach(alert => {
                    const color = alert.status === 'low' ? 'text-yellow-700' : 'text-blue-700';
                    const icon = alert.status === 'low' ? 'fa-arrow-down' : 'fa-arrow-up';
                    currentHtml += `<li class='flex items-center text-xs ${color}'><i class='fas ${icon} mr-1'></i>${alert.message}</li>`;
                });
                currentHtml += `</ul>`;
            }
            currentHtml += `</div>`;
            totalStarting += startCount*denom;
            totalCurrent += currCount*denom;
        });
        document.getElementById('startingDenomsList').innerHTML = startingHtml;
        document.getElementById('currentDenomsList').innerHTML = currentHtml;
        document.getElementById('totalStartingDenoms').textContent = 'Rs ' + totalStarting;
        document.getElementById('totalCurrentDenoms').textContent = 'Rs ' + totalCurrent;
        // Show modal
        document.getElementById('physicalDrawerDenominationsModal').classList.remove('hidden');
        // Initialize password validation for save functionality only
        initializePasswordValidation();
    } catch (error) {
        console.error('Error fetching denominations:', error);
        showErrorModal('Error', 'Failed to fetch denominations: ' + error.message);
    }
}
window.showPhysicalDrawerDenominationsModal = showPhysicalDrawerDenominationsModal;

// Recalculate total when editing current denominations
window.recalcCurrentDenomsTotal = function() {
    const denomOrder = [1000,500,100,50,20,10,5,2,1];
    let total = 0;
    denomOrder.forEach(denom => {
        const input = document.getElementById('edit_current_' + denom);
        if (input) {
            const val = parseInt(input.value) || 0;
            total += val * denom;
        }
    });
    document.getElementById('totalCurrentDenoms').textContent = 'Rs ' + total;
};

// Function to open cash adjustment modal for a specific denomination
window.adjustDenominationFromAlert = function(denomination) {
    // Show the cash adjustment modal
    if (typeof showCashAdjustmentModal === 'function') {
        showCashAdjustmentModal();
        // Prefill only the selected denomination for adjustment, others to 0
        [1000,500,100,50,20,10,5,2,1].forEach(denom => {
            const input = document.getElementById('adjust_' + denom);
            if (input) input.value = denom === denomination ? '' : 0;
        });
        // Focus the input for the selected denomination
        const selectedInput = document.getElementById('adjust_' + denomination);
        if (selectedInput) selectedInput.focus();
    }
};

// Wire up close buttons for the modal
function wirePhysicalDrawerDenominationsModalClose() {
    const closeBtns = [
        document.getElementById('closePhysicalDrawerDenominationsModalBtn'),
        document.getElementById('closePhysicalDrawerDenominationsModalBtn2')
    ];
    closeBtns.forEach(btn => {
        if (btn) btn.onclick = () => {
            document.getElementById('physicalDrawerDenominationsModal').classList.add('hidden');
        };
    });
}
document.addEventListener('DOMContentLoaded', wirePhysicalDrawerDenominationsModalClose);

// Update openPhysicalCashDrawer to just open the drawer without password
async function openPhysicalCashDrawer() {
    const branchId = document.getElementById('paymentApp').dataset.branchId;
    try {
        const response = await fetch(`/admin/cash-drawer/open-physical`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ branch_id: branchId }),
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || data.error || 'Failed to open cash drawer');
        showSuccessModal('Success', 'Physical cash drawer opened!');
        showPhysicalDrawerDenominationsModal();
    } catch (error) {
        showErrorModal('Error', error.message);
    }
}
window.openPhysicalCashDrawer = openPhysicalCashDrawer;

function showCashAdjustmentModal() {
    const modal = document.getElementById('cashAdjustmentModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Reset all adjustment fields to 0
        [1000,500,100,50,20,10,5,2,1].forEach(denom => {
            const input = document.getElementById('adjust_' + denom);
            if (input) input.value = 0;
        });
        // Optionally reset password and reason fields
        const pwd = document.getElementById('adjustmentPassword');
        if (pwd) pwd.value = '';
        const reason = document.getElementById('adjustmentReason');
        if (reason) reason.value = '';
    }
}
window.showCashAdjustmentModal = showCashAdjustmentModal;

// Alert Settings Modal Logic
function showAlertSettingsModal() {
    const modal = document.getElementById('alertSettingsModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Fetch current thresholds from backend
        const branchId = document.getElementById('paymentApp').dataset.branchId;
        fetch(`/api/admin/cash-drawer/alerts?branch_id=${branchId}`)
            .then(res => res.json())
            .then(data => {
                const alerts = data.alerts || [];
                const alertMap = {};
                alerts.forEach(a => alertMap[a.denomination] = a);
                [1000,500,100,50,20,10,5,2,1].forEach(denom => {
                    const low = document.getElementById('low_' + denom);
                    const high = document.getElementById('high_' + denom);
                    if (low) low.value = alertMap[denom]?.low_threshold ?? '';
                    if (high) high.value = alertMap[denom]?.high_threshold ?? '';
                });
            });
    }
}
window.showAlertSettingsModal = showAlertSettingsModal;

function hideAlertSettingsModal() {
    const modal = document.getElementById('alertSettingsModal');
    if (modal) modal.classList.add('hidden');
}
window.hideAlertSettingsModal = hideAlertSettingsModal;

document.getElementById('closeAlertSettingsModalBtn')?.addEventListener('click', hideAlertSettingsModal);
document.getElementById('alertSettingsForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const branchId = document.getElementById('paymentApp').dataset.branchId;
    const spinner = document.getElementById('alertSettingsLoadingSpinner');
    if (spinner) spinner.classList.remove('hidden');
    try {
        const updates = [1000,500,100,50,20,10,5,2,1].map(denom => ({
            denomination: denom,
            low_threshold: document.getElementById('low_' + denom)?.value || null,
            high_threshold: document.getElementById('high_' + denom)?.value || null
        }));
        const response = await fetch('/admin/cash-drawer/alerts/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ branch_id: branchId, updates }),
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || data.error || 'Failed to update alert settings');
        showSuccessModal('Success', 'Alert settings updated!');
        hideAlertSettingsModal();
    } catch (error) {
        showErrorModal('Error', error.message);
    } finally {
        if (spinner) spinner.classList.add('hidden');
    }
});

// Password validation for denominations
let isPasswordValidated = false;

// Function to initialize password validation
function initializePasswordValidation() {
    const validateBtn = document.getElementById('validatePasswordBtn');
    const saveBtn = document.getElementById('saveDenominationsBtn');
    const closeBtn1 = document.getElementById('closePhysicalDrawerDenominationsModalBtn');
    const closeBtn2 = document.getElementById('closePhysicalDrawerDenominationsModalBtn2');

    if (validateBtn && !validateBtn.hasAttribute('data-initialized')) {
        validateBtn.setAttribute('data-initialized', 'true');
        validateBtn.addEventListener('click', function() {
            const password = document.getElementById('denominationPassword').value;
            if (password === '333122') {
                isPasswordValidated = true;
                document.getElementById('saveDenominationsBtn').disabled = false;
                document.getElementById('passwordError').classList.add('hidden');
                document.getElementById('denominationPassword').disabled = true;
                this.disabled = true;
                this.textContent = 'Validated ✓';
                this.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
                this.classList.add('bg-green-500');
            } else {
                document.getElementById('passwordError').classList.remove('hidden');
                document.getElementById('denominationPassword').value = '';
            }
        });
    }

    if (saveBtn && !saveBtn.hasAttribute('data-initialized')) {
        saveBtn.setAttribute('data-initialized', 'true');
        saveBtn.addEventListener('click', async function() {
            console.log('Save button clicked');
            
            if (!isPasswordValidated) {
                alert('Please validate your password first.');
                return;
            }

            // Collect current denominations
            const denominations = {};
            document.querySelectorAll('#currentDenomsList input[type="number"]').forEach(input => {
                const denom = input.id.replace('edit_current_', '');
                denominations[denom] = parseInt(input.value) || 0;
            });
            
            console.log('Collected denominations:', denominations);

            try {
                const requestBody = {
                    branch_id: document.getElementById('paymentApp').dataset.branchId,
                    password: document.getElementById('denominationPassword').value,
                    adjustments: denominations,
                    reason: 'Manual denomination update'
                };
                
                console.log('Sending request:', requestBody);
                
                const response = await fetch('/admin/cash-drawer/update-denominations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(requestBody),
                    credentials: 'same-origin'
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.log('Response text:', text.substring(0, 200));
                    throw new Error('Server returned non-JSON response. Please check authentication.');
                }

                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    console.log('Success! Showing success modal');
                    // Show success message
                    showSuccessModal('Success', 'Denominations updated successfully');
                    // Close the modal
                    document.getElementById('closePhysicalDrawerDenominationsModalBtn').click();
                    // Reset password validation state
                    resetDenominationPasswordValidation();
                } else {
                    console.log('Error from server:', data.message);
                    showErrorModal('Error', data.message || 'Failed to update denominations');
                }
            } catch (error) {
                console.error('Error updating denominations:', error);
                showErrorModal('Error', 'An error occurred while updating denominations: ' + error.message);
            }
        });
    }

    if (closeBtn1 && !closeBtn1.hasAttribute('data-initialized')) {
        closeBtn1.setAttribute('data-initialized', 'true');
        closeBtn1.addEventListener('click', resetDenominationPasswordValidation);
    }

    if (closeBtn2 && !closeBtn2.hasAttribute('data-initialized')) {
        closeBtn2.setAttribute('data-initialized', 'true');
        closeBtn2.addEventListener('click', resetDenominationPasswordValidation);
    }
}

// Reset password validation when modal is closed
function resetDenominationPasswordValidation() {
    isPasswordValidated = false;
    const passwordInput = document.getElementById('denominationPassword');
    const validateBtn = document.getElementById('validatePasswordBtn');
    const saveBtn = document.getElementById('saveDenominationsBtn');
    const errorMsg = document.getElementById('passwordError');
    
    if (passwordInput) passwordInput.value = '';
    if (passwordInput) passwordInput.disabled = false;
    if (validateBtn) {
        validateBtn.disabled = false;
        validateBtn.textContent = 'Validate';
        validateBtn.classList.remove('bg-green-500');
        validateBtn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
    }
    if (saveBtn) saveBtn.disabled = true;
    if (errorMsg) errorMsg.classList.add('hidden');
}

// Make functions globally available
window.initializePasswordValidation = initializePasswordValidation;
window.resetDenominationPasswordValidation = resetDenominationPasswordValidation;

// Test function to check authentication
async function testAuthentication() {
    try {
        const response = await fetch('/api/admin/cash-drawer/status?branch_id=1', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.log('Response text:', text.substring(0, 200));
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        return data;
    } catch (error) {
        console.error('Authentication test failed:', error);
        throw error;
    }
}

// Add this to the global scope for testing
window.testAuthentication = testAuthentication;

// Order Management Functions
let orderPollingInterval;
let allOrders = []; // Store all orders
let currentSessionStartTime = null; // Track when current session started
let filteredOrders = {
    takeaway: { all: [], paid: [], unpaid: [] },
    dinein: { all: [], paid: [], unpaid: [] },
    online: { all: [], paid: [], unpaid: [] }
};
let currentFilters = {
    takeaway: 'unpaid',
    dinein: 'unpaid',
    online: 'all'  // Show all online orders by default (includes paid Amako Credits orders)
};

function startOrderPolling() {
    // Fetch orders immediately
    fetchOrders();
    
    // Then poll every 10 seconds
    orderPollingInterval = setInterval(fetchOrders, 10000);
}

function stopOrderPolling() {
    if (orderPollingInterval) {
        clearInterval(orderPollingInterval);
        orderPollingInterval = null;
    }
}

// Check cash drawer status
async function checkCashDrawerStatus(branchId) {
    try {
        const response = await fetch(`/api/business/status/${branchId}`);
        const data = await response.json();
        return {
            isOpen: data.is_open,
            message: data.message
        };
    } catch (error) {
        console.error('Error checking cash drawer status:', error);
        return { isOpen: false, message: 'Unable to check drawer status' };
    }
}

// Hide all orders when drawer is closed
function hideAllOrders() {
    // Clear all order grids
    const dineinGrid = document.getElementById('dineinOrdersGrid');
    const takeawayGrid = document.getElementById('takeawayOrdersGrid');
    const onlineGrid = document.getElementById('onlineOrdersGrid');
    
    if (dineinGrid) dineinGrid.innerHTML = '';
    if (takeawayGrid) takeawayGrid.innerHTML = '';
    if (onlineGrid) onlineGrid.innerHTML = '';
    
    // Clear order counts
    const dineinCount = document.getElementById('dineinCount');
    const takeawayCount = document.getElementById('takeawayCount');
    const onlineCount = document.getElementById('onlineCount');
    
    if (dineinCount) dineinCount.textContent = '(0)';
    if (takeawayCount) takeawayCount.textContent = '(0)';
    if (onlineCount) onlineCount.textContent = '(0)';
    
    // Clear stored orders
    allOrders = [];
    window.previousOrderIds = new Set();
}

// Show new session started message
function showNewSessionMessage() {
    const dineinGrid = document.getElementById('dineinOrdersGrid');
    const takeawayGrid = document.getElementById('takeawayOrdersGrid');
    const onlineGrid = document.getElementById('onlineOrdersGrid');
    
    const newSessionMessage = `
        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                    <i class="fas fa-store text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">New Session Started</h3>
                <p class="text-gray-600 mb-4">Cash drawer is open and ready for new orders.</p>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="font-medium text-blue-800">Fresh Start</span>
                </div>
                <p class="text-sm text-blue-700">
                    All previous order history has been cleared. New orders will appear here as they come in.
                </p>
            </div>
            
            <div class="text-xs text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                Session started at ${new Date().toLocaleTimeString()}
            </div>
        </div>
    `;
    
    if (dineinGrid) dineinGrid.innerHTML = newSessionMessage;
    if (takeawayGrid) takeawayGrid.innerHTML = newSessionMessage;
    if (onlineGrid) onlineGrid.innerHTML = newSessionMessage;
}

// Show drawer closed message
function showDrawerClosedMessage() {
    // Show the banner
    const banner = document.getElementById('drawerStatusBanner');
    if (banner) {
        banner.classList.remove('hidden');
    }
    
    const dineinGrid = document.getElementById('dineinOrdersGrid');
    const takeawayGrid = document.getElementById('takeawayOrdersGrid');
    const onlineGrid = document.getElementById('onlineOrdersGrid');
    
    const closedMessage = `
        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-3">
                    <i class="fas fa-store-slash text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Cash Drawer Closed</h3>
                <p class="text-gray-600 mb-4">Order history is hidden while the cash drawer is closed.</p>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 max-w-sm">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="font-medium text-blue-800">How to View Orders</span>
                </div>
                <p class="text-sm text-blue-700">
                    Open the cash drawer to view current order history and start a fresh session.
                </p>
            </div>
            
            <div class="text-xs text-gray-500">
                <i class="fas fa-database mr-1"></i>
                All order data is safely stored in the database
            </div>
        </div>
    `;
    
    if (dineinGrid) dineinGrid.innerHTML = closedMessage;
    if (takeawayGrid) takeawayGrid.innerHTML = closedMessage;
    if (onlineGrid) onlineGrid.innerHTML = closedMessage;
}

// Hide drawer closed message
function hideDrawerClosedMessage() {
    // Hide the banner
    const banner = document.getElementById('drawerStatusBanner');
    if (banner) {
        banner.classList.add('hidden');
    }
    
    // The populateOrderGrids function will replace the content with actual orders
}

async function fetchOrders() {
    console.log('🔄 fetchOrders() called - fetching orders from backend...');
    try {
        const paymentApp = document.getElementById('paymentApp');
        if (!paymentApp) {
            console.error('❌ PaymentApp element not found');
            return;
        }
        
        const branchId = paymentApp.dataset.branchId;
        console.log('🏪 Branch ID:', branchId);
        
        // First check if cash drawer is open
        const drawerStatus = await checkCashDrawerStatus(branchId);
        console.log('💰 Cash drawer status:', drawerStatus.isOpen ? 'OPEN' : 'CLOSED');
        
        if (!drawerStatus.isOpen) {
            // Drawer is closed - show warning but still fetch orders for testing
            showDrawerClosedMessage();
            console.warn('⚠️ Cash drawer is closed - orders shown for testing purposes');
            // Don't return - continue to fetch orders
        } else {
            // Drawer is open - hide the closed message
            hideDrawerClosedMessage();
        }
        
        // Fetch and show orders regardless of drawer status (for testing)
        console.log('📡 Fetching orders from API...');
        const response = await fetch(`/admin/orders/json?branch=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch orders');
        }
        
        const data = await response.json();
        console.log('📦 API response received:', data);
        
        if (data.success) {
            console.log(`📊 Total orders from API: ${data.orders?.length || 0}`);
            
            // Filter orders to only show those created after current session started
            // But if no session is active, show ALL orders (for testing/flexibility)
            let filteredOrders = data.orders;
            if (currentSessionStartTime) {
                filteredOrders = data.orders.filter(order => {
                    const orderDate = new Date(order.created_at);
                    return orderDate >= currentSessionStartTime;
                });
                console.log(`✅ Session active - showing ${filteredOrders.length} orders from session (started at ${currentSessionStartTime.toLocaleTimeString()})`);
            } else {
                // No active session - show ALL orders (don't filter by date)
                filteredOrders = data.orders;
                console.log(`✅ No active session - showing ALL ${filteredOrders.length} orders`);
            }
            
            console.log('📋 Orders to display:', filteredOrders);
            
            // Check for new orders by comparing order IDs (only from filtered orders)
            const previousOrderIds = window.previousOrderIds || new Set();
            const currentOrderIds = new Set(filteredOrders.map(order => order.id));
            
            // Find truly new orders (not just count-based)
            const newOrderIds = [...currentOrderIds].filter(id => !previousOrderIds.has(id));
            if (newOrderIds.length > 0) {
                const newOrders = filteredOrders.filter(order => newOrderIds.includes(order.id));
                console.log('🆕 New orders detected:', newOrders);
                showNewOrderNotification(newOrders);
            }
            
            window.previousOrderIds = currentOrderIds;
            console.log('🎨 Populating order grids...');
            populateOrderGrids(filteredOrders);
            console.log('✅ Orders populated successfully!');
        } else {
            console.error('❌ Failed to fetch orders:', data.message);
        }
    } catch (error) {
        console.error('❌ Error fetching orders:', error);
    }
}

function populateOrderGrids(orders) {
    // Store all orders
    allOrders = orders;
    
    // Separate orders by type and payment status
    const takeawayOrders = orders.filter(order => order.type === 'takeaway');
    const dineInOrders = orders.filter(order => order.type === 'dine_in');
    const onlineOrders = orders.filter(order => order.type === 'online' || order.type === 'delivery');
    
    // Store filtered orders
    filteredOrders.takeaway.all = takeawayOrders;
    filteredOrders.takeaway.paid = takeawayOrders.filter(order => order.payment_status === 'paid');
    filteredOrders.takeaway.unpaid = takeawayOrders.filter(order => order.payment_status !== 'paid');
    
    filteredOrders.dinein.all = dineInOrders;
    filteredOrders.dinein.paid = dineInOrders.filter(order => order.payment_status === 'paid');
    filteredOrders.dinein.unpaid = dineInOrders.filter(order => order.payment_status !== 'paid');
    
    filteredOrders.online.all = onlineOrders;
    filteredOrders.online.paid = onlineOrders.filter(order => order.payment_status === 'paid');
    filteredOrders.online.unpaid = onlineOrders.filter(order => order.payment_status !== 'paid');
    
    // Update counts (show total counts)
    document.getElementById('takeawayCount').textContent = takeawayOrders.length;
    document.getElementById('dineinCount').textContent = dineInOrders.length;
    document.getElementById('onlineCount').textContent = onlineOrders.length;
    
    // Apply current filters and populate grids
    applyFilters();
}

function applyFilters() {
    // Apply current filter for each section
    const takeawayFiltered = filteredOrders.takeaway[currentFilters.takeaway];
    const dineinFiltered = filteredOrders.dinein[currentFilters.dinein];
    const onlineFiltered = filteredOrders.online[currentFilters.online];
    
    // Populate grids with filtered orders
    populateOrderGrid('takeawayOrdersGrid', takeawayFiltered);
    populateOrderGrid('dineInOrdersGrid', dineinFiltered);
    populateOrderGrid('onlineOrdersGrid', onlineFiltered);
}

function setFilter(section, filter) {
    currentFilters[section] = filter;
    
    // Update button states
    updateFilterButtonStates(section, filter);
    
    // Apply the filter
    applyFilters();
}

function updateFilterButtonStates(section, activeFilter) {
    // Remove active class from all buttons in this section
    document.querySelectorAll(`[data-section="${section}"].order-filter-btn`).forEach(btn => {
        btn.classList.remove('bg-blue-100', 'text-blue-800');
        btn.classList.add('bg-gray-100', 'text-gray-600');
    });
    
    // Add active class to the clicked button
    const activeBtn = document.querySelector(`[data-section="${section}"][data-filter="${activeFilter}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-100', 'text-gray-600');
        activeBtn.classList.add('bg-blue-100', 'text-blue-800');
    }
}

function populateOrderGrid(gridId, orders) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    
    grid.innerHTML = '';
    
    if (orders.length === 0) {
        grid.innerHTML = '<div class="text-center text-gray-500 py-8">No orders</div>';
        return;
    }
    
    orders.forEach(order => {
        const orderCard = createOrderCard(order);
        grid.appendChild(orderCard);
    });
}

function createOrderCard(order) {
    const card = document.createElement('div');
    
    // Add special styling for paid orders (especially Amako Credits)
    const isPaid = order.payment_status === 'paid';
    const isAmakoCredits = order.payment_method === 'amako_credits';
    const borderClass = isPaid && isAmakoCredits 
        ? 'border-l-4 border-l-green-500 bg-green-50' 
        : isPaid 
        ? 'border-l-4 border-l-green-400 bg-white' 
        : 'border-gray-200 bg-white';
    
    card.className = `order-card ${borderClass} border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer`;
    card.onclick = () => selectOrder(order);
    
    const statusColor = getStatusColor(order.status);
    const paymentStatusColor = getPaymentStatusColor(order.payment_status);
    
    const itemsList = order.items.map(item => 
        `${item.item_name} x${item.quantity}`
    ).join(', ');
    
    // Add action buttons for online orders
    let actionButtons = '';
    if (order.type === 'online') {
        if (order.status === 'pending') {
            // Show accept/decline buttons for pending orders
            actionButtons = `
                <div class="mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                    <div class="flex gap-2">
                        <button onclick="acceptOrder(${order.id})" 
                                class="flex-1 px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-1"></i> Accept
                        </button>
                        <button onclick="declineOrder(${order.id})" 
                                class="flex-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-1"></i> Decline
                        </button>
                    </div>
                </div>
            `;
        } else if (order.status === 'confirmed' || order.status === 'preparing') {
            // Show mark as ready button for confirmed/preparing orders
            actionButtons = `
                <div class="mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                    <button onclick="markOrderAsReady(${order.id})" 
                            class="w-full px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check-circle mr-1"></i> Mark as Ready
                    </button>
                </div>
            `;
        } else if (order.status === 'declined') {
            // Show reset button for declined orders
            actionButtons = `
                <div class="mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                    <button onclick="resetOrderStatus(${order.id}, '${order.status}')" 
                            class="w-full px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-undo mr-1"></i> Reset to Pending
                    </button>
                </div>
            `;
        }
    }
    
    // Add payment method badge for Amako Credits
    const paymentMethodBadge = isAmakoCredits && isPaid 
        ? `<span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 ml-1">
            <i class="fas fa-wallet mr-1"></i>Amako Credits
           </span>`
        : '';
    
    card.innerHTML = `
        <div class="flex justify-between items-start mb-2">
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900">${order.order_number}</h4>
                <p class="text-sm text-gray-600">${order.table ? `Table ${order.table.name}` : 'No table'}</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${statusColor}">${order.status}</span>
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${paymentStatusColor} ml-1">${order.payment_status}</span>
                ${paymentMethodBadge}
            </div>
        </div>
        <div class="mb-2">
            <p class="text-sm text-gray-700 line-clamp-2">${itemsList}</p>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-lg font-bold text-gray-900">Rs ${parseFloat(order.total_amount).toFixed(2)}</span>
            <span class="text-xs text-gray-500">${formatTime(order.created_at)}</span>
        </div>
        ${actionButtons}
    `;
    
    return card;
}

function getStatusColor(status) {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'declined': 'bg-red-100 text-red-800',
        'preparing': 'bg-blue-100 text-blue-800',
        'ready': 'bg-green-100 text-green-800',
        'completed': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getPaymentStatusColor(paymentStatus) {
    const colors = {
        'unpaid': 'bg-red-100 text-red-800',
        'paid': 'bg-green-100 text-green-800',
        'partial': 'bg-yellow-100 text-yellow-800'
    };
    return colors[paymentStatus] || 'bg-gray-100 text-gray-800';
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
}

function selectOrder(order) {
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
    // Update order summary
    const orderDetails = document.getElementById('orderDetails');
    if (orderDetails) {
        const itemsList = order.items.map(item => 
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
                    <p><strong>Type:</strong> ${order.type.replace('_', ' ').toUpperCase()}</p>
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
}

function resetPaymentPanel() {
    // Reset order details
    const orderDetails = document.getElementById('orderDetails');
    if (orderDetails) {
        orderDetails.innerHTML = '<p class="text-gray-500">Select an order to process payment</p>';
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

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    stopOrderPolling();
});

// Initialize order filter buttons
function initializeOrderFilters() {
    // Add click event listeners to all filter buttons
    document.querySelectorAll('.order-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            const filter = this.getAttribute('data-filter');
            setFilter(section, filter);
        });
    });
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

// Order section toggle functions
function toggleDineInSection() {
    const content = document.getElementById('dineInSectionContent');
    const icon = document.getElementById('dineinSectionIcon');
    
    if (!content || !icon) {
        console.warn('toggleDineInSection: Required elements not found');
        return;
    }
    
    // Dinein section starts open (no hidden class, chevron-up icon)
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function toggleTakeawaySection() {
    const content = document.getElementById('takeawaySectionContent');
    const icon = document.getElementById('takeawaySectionIcon');
    
    if (!content || !icon) {
        console.warn('toggleTakeawaySection: Required elements not found');
        return;
    }
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function toggleOnlineSection() {
    const content = document.getElementById('onlineSectionContent');
    const icon = document.getElementById('onlineSectionIcon');
    
    if (!content || !icon) {
        console.warn('toggleOnlineSection: Required elements not found');
        return;
    }
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Sound Management System
class SoundManager {
    constructor() {
        this.audioContext = null;
        this.isMuted = false;
        this.volume = 0.7;
        this.audioContextInitialized = false;
        this.loadUserPreferences();
        this.setupUserInteractionHandler();
    }

    setupUserInteractionHandler() {
        // Add event listeners for user interactions to initialize AudioContext
        const initAudio = () => {
            if (!this.audioContextInitialized) {
                this.initializeAudioContext();
                this.audioContextInitialized = true;
            }
            // Remove listeners after first interaction
            document.removeEventListener('click', initAudio);
            document.removeEventListener('keydown', initAudio);
            document.removeEventListener('touchstart', initAudio);
        };

        document.addEventListener('click', initAudio, { once: true });
        document.addEventListener('keydown', initAudio, { once: true });
        document.addEventListener('touchstart', initAudio, { once: true });
    }

    initializeAudioContext() {
        try {
            // Initialize Web Audio API only after user interaction
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            console.log('AudioContext initialized successfully');
        } catch (error) {
            console.log('Web Audio API not supported:', error);
        }
    }

    loadUserPreferences() {
        // Load user preferences from localStorage
        const savedVolume = localStorage.getItem('paymentManagerVolume');
        const savedMuted = localStorage.getItem('paymentManagerMuted');
        
        if (savedVolume !== null) {
            this.volume = parseFloat(savedVolume);
        }
        
        if (savedMuted !== null) {
            this.isMuted = JSON.parse(savedMuted);
        }
    }

    saveUserPreferences() {
        localStorage.setItem('paymentManagerVolume', this.volume.toString());
        localStorage.setItem('paymentManagerMuted', this.isMuted.toString());
    }

    playTone(frequency, duration, type = 'sine') {
        if (this.isMuted) {
            return;
        }

        // Initialize AudioContext if not already done
        if (!this.audioContextInitialized) {
            this.initializeAudioContext();
            this.audioContextInitialized = true;
        }

        if (!this.audioContext) {
            console.log('AudioContext not available');
            return;
        }

        try {
            // Resume AudioContext if suspended (required by some browsers)
            if (this.audioContext.state === 'suspended') {
                this.audioContext.resume().then(() => {
                    this.playToneInternal(frequency, duration, type);
                }).catch(error => {
                    console.log('Failed to resume AudioContext:', error);
                });
            } else {
                this.playToneInternal(frequency, duration, type);
            }
        } catch (error) {
            console.log('Tone generation failed:', error);
        }
    }

    playToneInternal(frequency, duration, type = 'sine') {
        try {
            // Create oscillator
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            // Connect nodes
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            // Set oscillator properties
            oscillator.frequency.setValueAtTime(frequency, this.audioContext.currentTime);
            oscillator.type = type;

            // Set gain (volume)
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(this.volume * 0.3, this.audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + duration);

            // Start and stop oscillator
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + duration);

        } catch (error) {
            console.log('Tone generation failed:', error);
        }
    }

    playSound(soundName) {
        if (this.isMuted) {
            return;
        }

        switch (soundName) {
            case 'paymentSuccess':
                // Play a pleasant success sound (ascending notes)
                this.playTone(523.25, 0.2, 'sine'); // C5
                setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100); // E5
                setTimeout(() => this.playTone(783.99, 0.3, 'sine'), 200); // G5
                break;
            
            case 'paymentFailed':
                // Play a warning sound (descending notes)
                this.playTone(783.99, 0.2, 'sine'); // G5
                setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100); // E5
                setTimeout(() => this.playTone(523.25, 0.3, 'sine'), 200); // C5
                break;
            
            case 'orderReceived':
                // Play a notification sound for new orders
                this.playTone(880, 0.15, 'sine'); // A5
                setTimeout(() => this.playTone(1047, 0.15, 'sine'), 150); // C6
                setTimeout(() => this.playTone(1319, 0.2, 'sine'), 300); // E6
                break;
            
            case 'paymentProcessing':
                // Play a processing sound
                this.playTone(440, 0.1, 'square'); // A4
                setTimeout(() => this.playTone(554, 0.1, 'square'), 100); // C#5
                setTimeout(() => this.playTone(659, 0.1, 'square'), 200); // E5
                break;
            
            case 'buttonClick':
                // Play a simple button click sound
                this.playTone(800, 0.08, 'square');
                break;
            
            case 'cashRegister':
                // Play a cash register sound
                this.playTone(523, 0.1, 'square'); // C5
                setTimeout(() => this.playTone(659, 0.1, 'square'), 50); // E5
                setTimeout(() => this.playTone(784, 0.1, 'square'), 100); // G5
                setTimeout(() => this.playTone(1047, 0.2, 'square'), 150); // C6
                break;
            
            case 'cardPayment':
                // Play a card payment sound
                this.playTone(659, 0.15, 'sine'); // E5
                setTimeout(() => this.playTone(784, 0.15, 'sine'), 100); // G5
                setTimeout(() => this.playTone(880, 0.2, 'sine'), 200); // A5
                break;
            
            case 'walletPayment':
                // Play a wallet payment sound
                this.playTone(784, 0.15, 'sine'); // G5
                setTimeout(() => this.playTone(880, 0.15, 'sine'), 100); // A5
                setTimeout(() => this.playTone(1047, 0.2, 'sine'), 200); // C6
                break;
            
            case 'khaltiPayment':
                // Play a Khalti payment sound
                this.playTone(698, 0.15, 'sine'); // F5
                setTimeout(() => this.playTone(784, 0.15, 'sine'), 100); // G5
                setTimeout(() => this.playTone(932, 0.2, 'sine'), 200); // A#5
                break;
            
            case 'mobilePayment':
                // Play a mobile payment sound
                this.playTone(622, 0.15, 'sine'); // D#5
                setTimeout(() => this.playTone(740, 0.15, 'sine'), 100); // F#5
                setTimeout(() => this.playTone(880, 0.2, 'sine'), 200); // A5
                break;
            
            case 'notification':
                // Play a general notification sound
                this.playTone(1000, 0.2, 'sine');
                break;
            
            case 'warning':
                // Play a warning sound
                this.playTone(440, 0.2, 'sawtooth');
                setTimeout(() => this.playTone(440, 0.2, 'sawtooth'), 300);
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

// Initialize sound manager
let soundManager;

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
                                        <button onclick="selectOrder(${order.id})" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center">
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

// Store order ID for mark as ready modal
let pendingReadyOrderId = null;

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
            showToast('✅ Order marked as ready! Customer has been notified.', 'success');
            
            // Refresh orders to update status
            setTimeout(() => {
                fetchOrders();
            }, 1000);
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

function viewAllOrders() {
    closeNewOrderNotification();
    // Scroll to orders section or focus on it
    const ordersSection = document.querySelector('.orders-section');
    if (ordersSection) {
        ordersSection.scrollIntoView({ behavior: 'smooth' });
    }
}

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

// Initialize sound manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    soundManager = new SoundManager();
    soundManager.updateMuteButton();
});

// Auto-authenticate for payment access
async function authenticatePaymentAccess() {
    try {
        const response = await fetch('/payment/quick-auth', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                console.log('Payment access authenticated successfully');
                return Promise.resolve();
            } else {
                throw new Error('Authentication response indicates failure');
            }
        } else {
            throw new Error(`Authentication failed with status: ${response.status}`);
        }
    } catch (error) {
        console.error('Payment access authentication error:', error);
        return Promise.reject(error);
    }
}

// Sound control functions
function playPaymentSuccess() {
    if (soundManager) {
        soundManager.playSound('paymentSuccess');
    }
}

function playPaymentSuccessWithMethod(method) {
    if (soundManager) {
        switch (method) {
            case 'cash':
                soundManager.playSound('cashRegister');
                break;
            case 'card':
                soundManager.playSound('cardPayment');
                break;
            case 'wallet':
                soundManager.playSound('walletPayment');
                break;
            case 'khalti':
                soundManager.playSound('khaltiPayment');
                break;
            case 'mobile':
                soundManager.playSound('mobilePayment');
                break;
            default:
                soundManager.playSound('paymentSuccess');
        }
    }
}

function playPaymentFailed() {
    if (soundManager) {
        soundManager.playSound('paymentFailed');
    }
}

function playOrderReceived() {
    if (soundManager) {
        soundManager.playSound('orderReceived');
    }
}

function playPaymentProcessing() {
    if (soundManager) {
        soundManager.playSound('paymentProcessing');
    }
}

function playButtonClick() {
    if (soundManager) {
        soundManager.playSound('buttonClick');
    }
}

function playNotification() {
    if (soundManager) {
        soundManager.playSound('notification');
    }
}

function playWarning() {
    if (soundManager) {
        soundManager.playSound('warning');
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

// Export sound functions to global scope
window.playPaymentSuccess = playPaymentSuccess;
window.playPaymentFailed = playPaymentFailed;
window.toggleSoundMute = toggleSoundMute;
window.setSoundVolume = setSoundVolume;

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