// --- Payment Viewer open helper (must be called from a user-gesture context) ---
function openPaymentViewerTab() {
    const branchId = new URLSearchParams(window.location.search).get('branch');
    if (!branchId) return;

    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        window.paymentViewerWindow.focus();
        return;
    }

    const viewerUrl = `/customer/payment-viewer?branch=${branchId}`;
    window.paymentViewerWindow = window.open(viewerUrl, 'PaymentViewer');

    if (!window.paymentViewerWindow) {
        // Browser blocked it — keep the banner visible so user can click it manually
        _showViewerBanner(branchId);
        return;
    }

    window.paymentViewerWindow.focus();
    window.paymentViewerWindow.addEventListener('beforeunload', () => {
        window.paymentViewerWindow = null;
        // Re-show banner so user can reopen it
        const branchId = new URLSearchParams(window.location.search).get('branch');
        _showViewerBanner(branchId);
    });
    _hideViewerBanner();
}

function _showViewerBanner(branchId) {
    let banner = document.getElementById('_viewerBanner');
    if (!banner) {
        banner = document.createElement('div');
        banner.id = '_viewerBanner';
        banner.style.cssText = [
            'position:fixed;bottom:24px;right:24px;z-index:99999',
            'background:#1e40af;color:white;padding:14px 20px',
            'border-radius:12px;font-size:14px;font-weight:700',
            'cursor:pointer;box-shadow:0 6px 24px rgba(0,0,0,0.35)',
            'display:flex;align-items:center;gap:10px',
            'animation:_bannerPulse 2s infinite',
        ].join(';');
        banner.innerHTML = '<span style="font-size:20px">📺</span> Open Customer Display';
        banner.onclick = openPaymentViewerTab;
        document.body.appendChild(banner);

        // Pulse animation
        if (!document.getElementById('_viewerBannerStyle')) {
            const s = document.createElement('style');
            s.id = '_viewerBannerStyle';
            s.textContent = '@keyframes _bannerPulse{0%,100%{box-shadow:0 6px 24px rgba(30,64,175,0.5)}50%{box-shadow:0 6px 32px rgba(30,64,175,0.9)}}';
            document.head.appendChild(s);
        }
    }
    banner.style.display = 'flex';
}

function _hideViewerBanner() {
    const b = document.getElementById('_viewerBanner');
    if (b) b.style.display = 'none';
}

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

    // ── Payment viewer auto-open ─────────────────────────────────────────────
    // Browsers block window.open() unless called from a direct user gesture.
    // Strategy: show a pulsing banner immediately, open viewer on first click
    // anywhere on the page (which counts as a user gesture).
    const branchIdForViewer = new URLSearchParams(window.location.search).get('branch');
    if (branchIdForViewer) {
        _showViewerBanner(branchIdForViewer);

        const openOnFirstInteraction = () => {
            openPaymentViewerTab();
            document.removeEventListener('click',   openOnFirstInteraction);
            document.removeEventListener('keydown', openOnFirstInteraction);
        };
        document.addEventListener('click',   openOnFirstInteraction);
        document.addEventListener('keydown', openOnFirstInteraction);
    }

    // "Viewer" button in the payment panel header also opens/focuses the tab
    const openPaymentViewerBtn = document.getElementById('openPaymentViewerBtn');
    if (openPaymentViewerBtn) {
        openPaymentViewerBtn.addEventListener('click', openPaymentViewerTab);
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
            case 'UPDATE_PAYMENT_METHOD':
            case 'VIEWER_PAYMENT_METHOD_SELECTED':
                if (event.data.method && typeof selectPaymentMethod === 'function') {
                    selectPaymentMethod(event.data.method);
                }
                break;

            case 'UPDATE_ORDER':
                // Viewer is telling us which order it is displaying
                break;

            case 'UPDATE_PAYMENT_AMOUNT':
                // Viewer echoing amount — no action needed on manager side
                break;

            default:
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
            // Reset error banner and summary on each open
            const errDiv = document.getElementById('drawerErrorMsg');
            if (errDiv) errDiv.style.display = 'none';
            const summary = document.getElementById('drawerClosingSummary');

            if (action === 'open') {
                title.textContent = 'Open Cash Drawer';
                confirmBtnText.textContent = 'Open Drawer';
                modalDrawerStatus.textContent = 'Set starting denominations and notes, then open the drawer.';
                if (summary) summary.style.display = 'none';
                // Set default denominations
                const defaultDenoms = {1000: 0, 500: 5, 100: 20, 50: 20, 20: 50, 10: 50, 5: 50, 2: 200, 1: 200};
                Object.keys(defaultDenoms).forEach(denom => {
                    const input = document.getElementById('denom_' + denom);
                    if (input) input.value = defaultDenoms[denom];
                    const totalSpan = document.getElementById('total_' + denom);
                    if (totalSpan) totalSpan.textContent = defaultDenoms[denom] * denom;
                });
                window._drawerExpectedClose = 0;
                updateDrawerDenomTotal();
            } else {
                title.textContent = 'Close Cash Drawer';
                confirmBtnText.textContent = 'Close Drawer';
                modalDrawerStatus.textContent = 'Count denominations and notes, then close the drawer.';
                // Fetch current denominations + expected balance
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
                        // Store expected balance for live diff calculation
                        window._drawerExpectedClose = parseFloat(data.total_balance || 0);
                        const expectedEl = document.getElementById('expectedTotal');
                        if (expectedEl) expectedEl.textContent = 'Rs ' + window._drawerExpectedClose.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
                        if (summary) summary.style.display = 'block';
                        updateDrawerDenomTotal();
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
                window.currentSessionStartTime = new Date(); // Set session start time (shared with order-list.js)
                localStorage.setItem('pmSessionStartTime', window.currentSessionStartTime.toISOString());
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
                window.currentSessionStartTime = null;
                localStorage.removeItem('pmSessionStartTime');
                fetchOrders();
            }
        } catch (error) {
            // Show error inside the modal (error modal is behind cash drawer modal)
            const errDiv = document.getElementById('drawerErrorMsg');
            if (errDiv) {
                errDiv.textContent = error.message;
                errDiv.style.display = 'block';
                setTimeout(() => { errDiv.style.display = 'none'; }, 10000);
            } else {
                showErrorModal('Error', error.message);
            }
        } finally {
            // Hide loading spinner and re-enable confirm button
            if (confirmBtn) confirmBtn.disabled = false;
            if (loadingSpinner) loadingSpinner.classList.add('hidden');
        }
    }
    window.handleDrawerAction = handleDrawerAction;

    // Live denomination recalc — called by oninput on each denom input
    function updateDrawerDenomTotal() {
        let grandTotal = 0;
        [1000,500,100,50,20,10,5,2,1].forEach(denom => {
            const input = document.getElementById('denom_' + denom);
            const count = parseInt(input ? input.value : 0) || 0;
            const val   = count * denom;
            grandTotal += val;
            const span = document.getElementById('total_' + denom);
            if (span) span.textContent = val;
        });
        const totalEl = document.getElementById('totalCashAmount');
        if (totalEl) totalEl.textContent = 'Rs ' + grandTotal.toLocaleString();

        // Update closing summary if visible
        const summary = document.getElementById('drawerClosingSummary');
        if (summary && summary.style.display !== 'none') {
            const expected = window._drawerExpectedClose || 0;
            const countedEl = document.getElementById('closingTotalCash');
            if (countedEl) countedEl.textContent = 'Rs ' + grandTotal.toLocaleString();
            const diff = grandTotal - expected;
            const diffEl = document.getElementById('closingDifference');
            if (diffEl) {
                if (diff === 0) {
                    diffEl.textContent = 'Rs 0 (balanced)';
                    diffEl.style.color = '#16a34a';
                } else {
                    const abs = Math.abs(diff).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
                    diffEl.textContent = (diff > 0 ? '+' : '-') + ' Rs ' + abs + (diff > 0 ? ' (surplus)' : ' (shortage)');
                    diffEl.style.color = diff > 0 ? '#16a34a' : '#dc2626';
                }
            }
        }
    }
    window.updateDrawerDenomTotal = updateDrawerDenomTotal;

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

            // Update balance display
            const balanceEl = document.getElementById('drawerBalance');
            if (balanceEl && data.total_balance !== undefined) {
                balanceEl.textContent = parseFloat(data.total_balance).toFixed(2);
            }

            // Update pending orders warning
            updatePendingOrdersWarning(data.pending_unpaid_orders || 0);

            if (data.is_open) {
                // If drawer is already open when page loads, DON'T set session start time
                // This prevents filtering out existing orders when you reload the page
                // Session start time is only set when YOU actually open the drawer
                if (!window.currentSessionStartTime) {
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
                    statusIndicator.style.background = '#dcfce7';
                    statusIndicator.style.color = '#16a34a';
                }
            } else {
                if (openButton) openButton.style.display = 'inline-flex';
                if (closeButton) closeButton.style.display = 'none';
                if (statusIndicator) {
                    statusIndicator.textContent = 'Closed';
                    statusIndicator.style.background = '#fee2e2';
                    statusIndicator.style.color = '#dc2626';
                }
            }

            // Refresh orders to show/hide based on drawer status
            fetchOrders();
        } catch (error) {
            console.error('Error updating drawer button state:', error);
        }
    }
    // Export so payment-panel.js can call it after a cash payment
    window.updateDrawerButtonState = updateDrawerButtonState;

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

function viewAllOrders() {
    closeNewOrderNotification();
    // Scroll to orders section or focus on it
    const ordersSection = document.querySelector('.orders-section');
    if (ordersSection) {
        ordersSection.scrollIntoView({ behavior: 'smooth' });
    }
}

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

// Auto-authenticate for payment access
async function authenticatePaymentAccess() {
    try {
        const response = await fetch('/admin/payment/quick-auth', {
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

// ── Header hamburger menu ─────────────────────────────────────────────────────
function toggleHeaderMenu() {
    const menu = document.getElementById('headerMenu');
    if (!menu) return;
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
window.toggleHeaderMenu = toggleHeaderMenu;

document.addEventListener('click', function(e) {
    if (!e.target.closest('#headerMenu') &&
        !e.target.closest('[onclick*="toggleHeaderMenu"]')) {
        const menu = document.getElementById('headerMenu');
        if (menu) menu.style.display = 'none';
    }
});
