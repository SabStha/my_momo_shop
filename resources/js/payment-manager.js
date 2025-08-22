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
        // Only accept messages from the same origin
        if (event.origin !== window.location.origin) {
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
                console.log('Unknown message type from viewer:', event.data.type);
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
            showSuccessModal('Success', action === 'open' ? 'Drawer opened.' : 'Drawer closed.');
            if (typeof updateDrawerButtonState === 'function') {
                updateDrawerButtonState();
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
            
            if (data.is_open) {
                if (openButton) openButton.style.display = 'none';
                if (closeButton) closeButton.style.display = 'inline-flex';
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
        } catch (error) {
            console.error('Error updating drawer button state:', error);
        }
    }
    updateDrawerButtonState();
    // Optionally, poll or call updateDrawerButtonState after open/close actions

    // Start order polling
    startOrderPolling();
    
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
let filteredOrders = {
    takeaway: { all: [], paid: [], unpaid: [] },
    dinein: { all: [], paid: [], unpaid: [] },
    online: { all: [], paid: [], unpaid: [] }
};
let currentFilters = {
    takeaway: 'unpaid',
    dinein: 'unpaid',
    online: 'unpaid'
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

async function fetchOrders() {
    try {
        const paymentApp = document.getElementById('paymentApp');
        if (!paymentApp) {
            console.error('PaymentApp element not found');
            return;
        }
        
        const branchId = paymentApp.dataset.branchId;
        const response = await fetch(`/api/orders?branch=${branchId}`, {
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
        
        if (data.success) {
            populateOrderGrids(data.orders);
        } else {
            console.error('Failed to fetch orders:', data.message);
        }
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
}

function populateOrderGrids(orders) {
    // Store all orders
    allOrders = orders;
    
    // Separate orders by type and payment status
    const takeawayOrders = orders.filter(order => order.type === 'takeaway');
    const dineInOrders = orders.filter(order => order.type === 'dine_in');
    const onlineOrders = orders.filter(order => order.type === 'online');
    
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
    card.className = 'bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer';
    card.onclick = () => selectOrder(order);
    
    const statusColor = getStatusColor(order.status);
    const paymentStatusColor = getPaymentStatusColor(order.payment_status);
    
    const itemsList = order.items.map(item => 
        `${item.item_name} x${item.quantity}`
    ).join(', ');
    
    card.innerHTML = `
        <div class="flex justify-between items-start mb-2">
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900">${order.order_number}</h4>
                <p class="text-sm text-gray-600">${order.table ? `Table ${order.table.name}` : 'No table'}</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${statusColor}">${order.status}</span>
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${paymentStatusColor} ml-1">${order.payment_status}</span>
            </div>
        </div>
        <div class="mb-2">
            <p class="text-sm text-gray-700 line-clamp-2">${itemsList}</p>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-lg font-bold text-gray-900">Rs ${parseFloat(order.total_amount).toFixed(2)}</span>
            <span class="text-xs text-gray-500">${formatTime(order.created_at)}</span>
        </div>
    `;
    
    return card;
}

function getStatusColor(status) {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
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
    
    // Remove previous selection
    document.querySelectorAll('.order-card-selected').forEach(card => {
        card.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500');
    });
    
    // Add selection to clicked card
    event.currentTarget.classList.add('order-card-selected', 'ring-2', 'ring-blue-500');
    
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
        return;
    }
    
    console.log('Selected order:', order);
    
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
        processPaymentBtn.addEventListener('click', processPayment);
    }
    
    // Cancel payment button
    const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
    if (cancelPaymentBtn) {
        cancelPaymentBtn.addEventListener('click', () => {
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
            calculateCashTotals();
        });
    });
    
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

async function processPayment() {
    if (!window.selectedOrder || !window.selectedPaymentMethod) {
        showErrorModal('Error', 'Please select an order and payment method');
        return;
    }
    
    if (window.selectedOrder.payment_status === 'paid') {
        showErrorModal('Error', 'This order has already been paid');
        return;
    }
    
    try {
        const paymentData = {
            payment_method: window.selectedPaymentMethod,
            amount: window.selectedOrder.total_amount,
            notes: document.getElementById('paymentNotes').value || '',
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
            
            paymentData.cash_denominations = cashDenominations;
            paymentData.amount_received = parseFloat(document.getElementById('denominationTotal').textContent);
        } else if (window.selectedPaymentMethod === 'card') {
            paymentData.reference_number = document.getElementById('cardReferenceNumber').value || '';
        } else if (window.selectedPaymentMethod === 'wallet') {
            paymentData.wallet_number = document.getElementById('walletNumber').value || '';
            paymentData.reference_number = `WALLET-${Date.now()}`;
        } else if (window.selectedPaymentMethod === 'khalti') {
            paymentData.transaction_id = document.getElementById('khaltiTransactionId').value || '';
            paymentData.reference_number = `KHALTI-${Date.now()}`;
        } else if (window.selectedPaymentMethod === 'mobile') {
            paymentData.reference_number = document.getElementById('mobileReferenceNumber').value || '';
        }
        
        const response = await fetch(`/api/orders/${window.selectedOrder.id}/process-payment`, {
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
            // Play success sound
            playPaymentSuccess();
            
            showSuccessModal('Success', 'Payment processed successfully!');
            
            // Refresh orders to update status
            fetchOrders();
            
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
    const icon = document.getElementById('dineInSectionIcon');
    
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
        this.initializeAudioContext();
        this.loadUserPreferences();
    }

    initializeAudioContext() {
        try {
            // Initialize Web Audio API
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
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
        if (this.isMuted || !this.audioContext) {
            return;
        }

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

// Initialize sound manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
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

// Sound control functions
function playPaymentSuccess() {
    if (soundManager) {
        // Ensure audio context is resumed if suspended
        if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
            soundManager.audioContext.resume();
        }
        soundManager.playSound('paymentSuccess');
    }
}

function playPaymentFailed() {
    if (soundManager) {
        // Ensure audio context is resumed if suspended
        if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
            soundManager.audioContext.resume();
        }
        soundManager.playSound('paymentFailed');
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