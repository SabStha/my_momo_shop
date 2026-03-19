// Payment panel module — speed-optimised cashier UI

// ─── Payment viewer helpers ──────────────────────────────────────────────────
const updatePaymentViewer = (orderId) => {
    if (!window.paymentViewerWindow || window.paymentViewerWindow.closed) {
        // Don't try to auto-open here — just skip if window isn't ready
        return;
    }
    window.paymentViewerWindow.postMessage({ type: 'UPDATE_ORDER', orderId }, window.location.origin);
};

const updatePaymentViewerMethod = (method) => {
    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        window.paymentViewerWindow.postMessage({ type: 'UPDATE_PAYMENT_METHOD', method }, window.location.origin);
    }
};

const updatePaymentViewerAmount = (amount) => {
    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        window.paymentViewerWindow.postMessage({ type: 'UPDATE_PAYMENT_AMOUNT', amount }, window.location.origin);
    }
};

// Tell the viewer to return to idle/ads (used when cashier clicks a paid order)
const resetPaymentViewer = () => {
    // Hide "Return to Ads" button in payment panel and cancel auto-reset timer
    if (window._viewerResetTimer) { clearTimeout(window._viewerResetTimer); window._viewerResetTimer = null; }

    if (window.paymentViewerWindow && !window.paymentViewerWindow.closed) {
        window.paymentViewerWindow.postMessage({ type: 'RESET_TO_IDLE' }, window.location.origin);
    }
    // Also broadcast via Pusher so physical viewer displays reset
    const branchId = window.currentBranchId
        || document.getElementById('paymentApp')?.dataset?.branchId;
    if (!branchId) return;
    fetch('/admin/payments/broadcast-method', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ order_id: null, method: 'reset', amount: 0, branch_id: branchId }),
    }).catch(() => {});
};

// ─── Order selection ─────────────────────────────────────────────────────────
function selectOrder(order) {
    if (typeof order === 'number' || typeof order === 'string') {
        const id = parseInt(order, 10);
        order = allOrders.find(o => o.id === id);
        if (!order) { console.error('selectOrder: order not found for id', id); return; }
    }
    console.log('[PM] selectOrder:', order.id, order.status, order.payment_status);

    if (!document.getElementById('paymentPanel')) {
        setTimeout(() => document.getElementById('paymentPanel') && selectOrder(order), 100);
        return;
    }

    playButtonClick();

    document.querySelectorAll('.order-card-selected').forEach(c =>
        c.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500'));

    if (typeof event !== 'undefined' && event && event.currentTarget) {
        event.currentTarget.classList.add('order-card-selected', 'ring-2', 'ring-blue-500');
    }

    populatePaymentPanel(order);

    // Only push unpaid/active orders to the customer viewer.
    // Clicking a completed order to review it shouldn't disturb the display.
    const isPaid = order.payment_status === 'paid' || order.status === 'completed';
    if (isPaid) {
        resetPaymentViewer(); // return viewer to idle/ads
    } else {
        updatePaymentViewer(order.id);
    }
}

function populatePaymentPanel(order) {
    const paymentPanel = document.getElementById('paymentPanel');
    if (!paymentPanel) {
        setTimeout(() => {
            const el = document.getElementById('paymentPanel');
            if (el) populatePaymentPanelContent(order, el);
        }, 100);
        return;
    }
    populatePaymentPanelContent(order, paymentPanel);
}

// ─── Receipt view ─────────────────────────────────────────────────────────────
function buildReceiptView(order) {
    const displayNum    = order.session_order_number || order.order_number || ('#' + order.id);
    const total         = parseFloat(order.total_amount || 0);
    const tax           = parseFloat(order.tax_amount || 0);
    const method        = (order.payment_method || '').toLowerCase();
    const received      = parseFloat(order.amount_received || total);
    const change        = Math.max(0, received - total);
    const tableLabel    = order.table ? `Table ${order.table.name}` : '';

    const itemsHtml = (order.items || []).map(item => `
        <div style="display:flex;justify-content:space-between;padding:3px 0;font-size:13px;">
            <span>${item.name || item.item_name} ×${item.quantity}</span>
            <span>Rs ${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}</span>
        </div>`).join('');

    const cashLines = method === 'cash' ? `
        <div style="display:flex;justify-content:space-between;margin-top:4px;font-size:13px;">
            <span>Received</span><span>Rs ${received.toFixed(2)}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:4px;font-weight:bold;color:#16a34a;font-size:13px;">
            <span>Change</span><span>Rs ${change.toFixed(2)}</span>
        </div>` : '';

    return `
    <div style="padding:16px;font-family:monospace;overflow-y:auto;max-height:100%;">
        <div style="text-align:center;margin-bottom:12px;">
            <div style="font-size:28px;">✅</div>
            <div style="font-size:16px;font-weight:bold;color:#16a34a;">Payment Completed</div>
            <div style="font-size:18px;font-weight:900;color:#374151;margin-top:2px;">${displayNum}</div>
            ${tableLabel ? `<div style="font-size:12px;color:#9ca3af;">${tableLabel}</div>` : ''}
        </div>
        <div style="border-top:1px dashed #d1d5db;border-bottom:1px dashed #d1d5db;padding:10px 0;margin:10px 0;">
            ${itemsHtml || '<div style="color:#9ca3af;font-size:12px;text-align:center;">No items</div>'}
        </div>
        ${tax > 0 ? `<div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
            <span>Tax (13%)</span><span>Rs ${tax.toFixed(2)}</span>
        </div>` : ''}
        <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:bold;margin-top:6px;padding-top:6px;border-top:2px solid #e5e7eb;">
            <span>TOTAL</span><span>Rs ${total.toFixed(2)}</span>
        </div>
        <div style="background:#f0fdf4;border-radius:8px;padding:10px;margin-top:10px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;">
                <span>Paid by</span>
                <span style="font-weight:bold;text-transform:uppercase;">${method || '—'}</span>
            </div>
            ${cashLines}
        </div>
        <button onclick="if(typeof printKitchenOrder==='function')printKitchenOrder(${order.id})"
            style="width:100%;margin-top:10px;padding:10px;background:#f9fafb;border:1px solid #d1d5db;
                   border-radius:8px;cursor:pointer;font-size:13px;font-family:monospace;">
            🖨 Print Receipt
        </button>
    </div>`;
}

// ─── Main populate function ───────────────────────────────────────────────────
function populatePaymentPanelContent(order, paymentPanel) {
    console.log('[PM] populatePaymentPanelContent:', order.id, order.status, order.payment_status);

    const noOrderSelected     = document.getElementById('noOrderSelected');
    const paymentMethodsSection = document.getElementById('paymentMethodsSection');
    const isPaid = order.payment_status === 'paid' || order.status === 'completed';

    // ── Paid order: show receipt ──────────────────────────────────────────────
    if (isPaid) {
        if (paymentMethodsSection) paymentMethodsSection.style.display = 'none';
        if (noOrderSelected) {
            noOrderSelected.innerHTML = buildReceiptView(order);
            noOrderSelected.style.display = 'block';
            noOrderSelected.style.overflow = 'auto';
        }
        window.selectedOrder = order;
        _showMobilePaymentPanel();

        // Auto-reset viewer after 30 seconds if cashier doesn't act
        if (window._viewerResetTimer) clearTimeout(window._viewerResetTimer);
        window._viewerResetTimer = setTimeout(() => {
            resetPaymentViewer();
        }, 30000);

        return;
    }

    // ── Unpaid order: clear any paid-order UI ────────────────────────────────
    // Cancel any pending auto-reset — payment is now in progress
    if (window._viewerResetTimer) { clearTimeout(window._viewerResetTimer); window._viewerResetTimer = null; }

    if (noOrderSelected) noOrderSelected.style.display = 'none';

    _showMobilePaymentPanel();

    // ── Compact order summary ─────────────────────────────────────────────────
    const total = parseFloat(order.total_amount || 0);

    const num = document.getElementById('orderNumber');
    if (num) num.textContent = order.session_order_number || order.order_number || ('#' + order.id);

    const tableBadge = document.getElementById('orderTableBadge');
    if (tableBadge) tableBadge.textContent = order.table ? `· Table ${order.table.name}` : '';

    const statusBadge = document.getElementById('orderStatusBadge');
    if (statusBadge) {
        const orderType = (order.type || order.order_type || '').toLowerCase();
        const isDineIn  = orderType === 'dine_in' || orderType === 'takeaway';
        statusBadge.textContent = isDineIn ? '' : (order.status || '');
    }

    const itemsList = document.getElementById('orderItemsList');
    if (itemsList) {
        itemsList.textContent = (order.items || []).map(i => `${i.item_name || i.name} ×${i.quantity}`).join(', ');
    }

    // ── Order detail section — items + subtotal/tax/total ─────────────────────
    const detailEl = document.getElementById('orderDetailSection');
    if (detailEl) {
        const items = order.items || order.order_items || [];
        const tax      = parseFloat(order.tax_amount || 0);
        const subtotal = total > 0 ? total - tax : 0;

        let itemsHtml = '';
        if (items.length > 0) {
            itemsHtml = items.map(item => {
                const name     = item.name || item.item_name || '—';
                const qty      = parseInt(item.quantity || 1);
                const lineTotal = parseFloat(item.subtotal || (item.price * qty) || 0);
                return `<div style="display:flex;justify-content:space-between;padding:3px 0;font-size:13px;">
                    <span style="color:#374151;">${name} × ${qty}</span>
                    <span style="color:#374151;font-weight:500;">Rs ${lineTotal.toFixed(2)}</span>
                </div>`;
            }).join('');
        } else {
            itemsHtml = `<div style="color:#9ca3af;font-size:13px;">${order.items_summary || 'No items'}</div>`;
        }

        detailEl.innerHTML = `
            <div style="padding:4px 0;">
                ${itemsHtml}
                <div style="border-top:1px dashed #e5e7eb;margin-top:6px;padding-top:6px;">
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;padding:2px 0;">
                        <span>Subtotal</span><span>Rs ${subtotal.toFixed(2)}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;padding:2px 0;">
                        <span>Tax (13%)</span><span>Rs ${tax.toFixed(2)}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;padding:4px 0;">
                        <span>Total</span>
                        <span style="color:#dc2626;">Rs ${total.toFixed(2)}</span>
                    </div>
                </div>
            </div>`;
    }

    const orderTotalEl = document.getElementById('orderTotal');
    if (orderTotalEl) orderTotalEl.textContent = `Rs ${total.toFixed(2)}`;

    const payTotalEl = document.getElementById('paymentTotal');
    if (payTotalEl) payTotalEl.textContent = total.toFixed(2);

    const payStatusBadge = document.getElementById('paymentStatusBadge');
    if (payStatusBadge) {
        payStatusBadge.textContent = order.payment_status || '';
        payStatusBadge.className = `text-xs px-1.5 py-0.5 rounded ${getPaymentStatusColor(order.payment_status)}`;
    }

    // ── Action buttons + payment section visibility (owns both) ───────────────
    _populateOrderActions(order);

    // ── Store & initialise payment methods ────────────────────────────────────
    window.selectedOrder = order;
    window.selectedPaymentMethod = null;

    _resetProcessBtn();
    _resetPaymentTabs();
    _generateQuickAmounts(total);

    initializePaymentMethods();
}

function _showMobilePaymentPanel() {
    if (window.innerWidth < 768) {
        const ordersPanel  = document.getElementById('ordersListPanel');
        const detailsPanel = document.getElementById('paymentDetailsPanel');
        if (ordersPanel)  ordersPanel.classList.add('hidden');
        if (detailsPanel) { detailsPanel.classList.remove('hidden'); detailsPanel.style.display = 'flex'; }
        const backBtn = document.getElementById('backToOrdersBtn');
        if (backBtn) backBtn.classList.remove('hidden');
    }
}

function _populateOrderActions(order) {
    const actionsEl         = document.getElementById('orderActions');
    const paymentSection    = document.getElementById('paymentMethodsSection');
    const noOrderEl         = document.getElementById('noOrderSelected');

    const status    = (order.status || '').toLowerCase();
    const payStatus = (order.payment_status || '').toLowerCase();
    const orderType = (order.type || order.order_type || '').toLowerCase();
    const isOnline  = orderType === 'online' || orderType === 'delivery';

    let actionsHtml = '';
    let showPayment = false;

    if (isOnline) {
        // Online orders: show workflow action buttons at each stage
        if (status === 'pending') {
            actionsHtml = `
                <div class="flex gap-2 mb-2">
                    <button type="button" onclick="acceptOrder(${order.id})"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm">
                        ✓ Accept Order
                    </button>
                    <button type="button" onclick="declineOrder(${order.id})"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm">
                        ✗ Decline
                    </button>
                </div>`;
        } else if (['confirmed','accepted','preparing','processing'].includes(status)) {
            actionsHtml = `
                <button type="button" onclick="markOrderAsReady(${order.id})"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm mb-2">
                    ✓ Mark as Ready
                </button>`;
        } else if (status === 'ready') {
            actionsHtml = `
                <button type="button" onclick="markOutForDelivery(${order.id})"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm mb-2">
                    🛵 Send for Delivery
                </button>`;
        } else if (status === 'out_for_delivery') {
            actionsHtml = `
                <button type="button" onclick="markAsDelivered(${order.id})"
                    class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2.5 rounded-lg font-medium text-sm mb-2">
                    🏠 Mark as Delivered
                </button>`;
        }
        // Show payment methods for any online order that's past the early workflow stages
        // and hasn't been paid yet (covers ready, out_for_delivery, completed COD orders)
        const earlyStatuses = ['pending', 'confirmed', 'accepted', 'preparing', 'processing'];
        if (payStatus !== 'paid' && !earlyStatuses.includes(status)) {
            showPayment = true;
        }
    } else {
        // Dine-in and takeaway: POS cashier goes straight to payment
        // Kitchen handles prep on their own screen — no workflow buttons here
        showPayment = payStatus !== 'paid';
    }

    // Set actions bar
    if (actionsEl) {
        actionsEl.innerHTML = actionsHtml;
        actionsEl.style.display = actionsHtml ? 'block' : 'none';
        actionsEl.classList.toggle('hidden', !actionsHtml);
    }

    // Show/hide payment methods section
    if (paymentSection) {
        paymentSection.style.display = showPayment ? 'flex' : 'none';
    }

    // When no payment and no actions (online mid-workflow), show a subtle waiting state
    if (!showPayment && !actionsHtml && noOrderEl) {
        noOrderEl.style.display = 'none';
    }
}

// ─── Quick amounts ────────────────────────────────────────────────────────────
function _generateQuickAmounts(total) {
    const container = document.getElementById('quickAmounts');
    if (!container) return;
    container.innerHTML = '';

    const amounts = [];
    const n100 = Math.ceil(total / 100) * 100;
    if (n100 > total) amounts.push(n100);
    const n500 = Math.ceil(total / 500) * 500;
    if (n500 !== n100 && n500 > total) amounts.push(n500);
    [1000, 2000, 5000, 10000].forEach(a => {
        if (a > total && amounts.length < 4) amounts.push(a);
    });
    const unique = [...new Set(amounts)].slice(0, 4);

    unique.forEach(amt => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = `Rs ${amt.toLocaleString()}`;
        btn.style.cssText = 'padding:10px 18px;font-size:15px;font-weight:700;border-radius:8px;background:#dbeafe;color:#1d4ed8;border:none;cursor:pointer;flex:1;';
        btn.addEventListener('click', () => {
            const input = document.getElementById('cashAmountInput');
            if (input) {
                input.value = amt;
                input.dispatchEvent(new Event('input'));
            }
        });
        container.appendChild(btn);
    });
}

// ─── Payment method selection (called via onclick) ───────────────────────────
const _tabColors = {
    cash:   { border: '#16a34a', bg: '#f0fdf4', text: '#15803d' },
    card:   { border: '#2563eb', bg: '#eff6ff', text: '#1d4ed8' },
    khalti: { border: '#7c3aed', bg: '#f5f3ff', text: '#6d28d9' },
    wallet: { border: '#dc2626', bg: '#fef2f2', text: '#b91c1c' },
    mobile: { border: '#0891b2', bg: '#ecfeff', text: '#0e7490' },
};
const _allMethods       = ['cash','card','khalti','wallet','mobile'];
const _allFieldSections = ['cashFields','cardFields','walletFields','khaltiFields','mobileFields'];
const _fieldMap = { cash:'cashFields', card:'cardFields', wallet:'walletFields', khalti:'khaltiFields', mobile:'mobileFields' };

window.selectPaymentMethod = function (method) {
    playButtonClick();

    // Reset all tabs
    _allMethods.forEach(m => {
        const tab = document.getElementById(`tab-${m}`);
        if (!tab) return;
        tab.style.borderColor = '#e5e7eb';
        tab.style.background  = 'white';
        tab.style.boxShadow   = 'none';
        const label = tab.querySelector('span:last-child');
        if (label) label.style.color = '#374151';
    });

    // Highlight selected
    const sel = document.getElementById(`tab-${method}`);
    const c   = _tabColors[method];
    if (sel && c) {
        sel.style.borderColor = c.border;
        sel.style.background  = c.bg;
        sel.style.boxShadow   = `0 2px 8px ${c.border}33`;
        const label = sel.querySelector('span:last-child');
        if (label) label.style.color = c.text;
    }

    // Hide all field sections; cash uses popup so never shown inline
    _allFieldSections.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
    if (method !== 'cash') {
        const sectionEl = document.getElementById(_fieldMap[method]);
        if (sectionEl) sectionEl.classList.remove('hidden');
    }

    window.selectedPaymentMethod = method;

    // Method-specific init
    if (method === 'cash') {
        showCashPopup(window.selectedOrder);
    } else if (method === 'wallet') {
        initializeWalletFields();
        _enableProcessBtn();
    } else if (method === 'khalti') {
        initializeKhaltiFields();
        _enableProcessBtn();
    } else {
        _enableProcessBtn();
    }

    updatePaymentViewerMethod(method);

    // Broadcast selection to customer viewer via Pusher (fire-and-forget)
    if (window.selectedOrder) {
        const branchId = parseInt(document.getElementById('paymentApp').dataset.branchId);
        fetch('/admin/payments/broadcast-method', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                order_id:  window.selectedOrder.id,
                method:    method,
                amount:    window.selectedOrder.total_amount,
                branch_id: branchId,
            }),
        }).catch(() => {});
    }

    // Show payment popup for QR/digital methods
    if (['khalti', 'esewa', 'wallet', 'mobile'].includes(method) && window.selectedOrder) {
        showPaymentPopup(method, window.selectedOrder);
    }
};

// ─── Payment method tabs — wire up process button only (tab clicks use onclick) ─
function initializePaymentMethods() {
    // Process payment button — deduplicate listener via clone
    const processBtn = document.getElementById('processPaymentBtn');
    if (processBtn) {
        const freshBtn = processBtn.cloneNode(true);
        processBtn.parentNode.replaceChild(freshBtn, processBtn);
        document.getElementById('processPaymentBtn').addEventListener('click', () => {
            playPaymentProcessing();
            if (typeof window.processPaymentManager === 'function') {
                window.processPaymentManager();
            } else {
                showErrorModal('Error', 'Payment function unavailable. Refresh the page.');
            }
        });
    }

    // Cancel button
    const cancelBtn = document.getElementById('cancelPaymentBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            playButtonClick();
            document.querySelectorAll('.order-card-selected').forEach(c =>
                c.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500'));
            resetPaymentPanel();
        });
    }
}

// ─── Cash denomination helpers ────────────────────────────────────────────────
function initializeCashDenominations() {
    const orderTotal = window.selectedOrder ? parseFloat(window.selectedOrder.total_amount) : 0;
    const cashInput  = document.getElementById('cashAmountInput');
    const changeDisp = document.getElementById('changeDisplay');
    const changeAmt  = document.getElementById('changeAmountDisplay');
    const denomTotal = document.getElementById('denominationTotal');
    const denomChange = document.getElementById('changeAmount');

    if (cashInput) {
        // Remove stale listeners
        const fresh = cashInput.cloneNode(true);
        cashInput.parentNode.replaceChild(fresh, cashInput);

        document.getElementById('cashAmountInput').addEventListener('input', function () {
            const received = parseFloat(this.value) || 0;
            const change   = received - orderTotal;

            if (received > 0 && change >= 0) {
                if (changeDisp) changeDisp.classList.remove('hidden');
                if (changeAmt)  changeAmt.textContent = `Rs ${change.toFixed(2)}`;
                if (denomTotal) denomTotal.textContent = received.toFixed(2);
                if (denomChange) denomChange.textContent = change.toFixed(2);
                calculateChangeDenominations(change);
                _updateProcessBtnForCash();
            } else {
                if (changeDisp) changeDisp.classList.add('hidden');
                _updateProcessBtnForCash();
            }
        });
    }

    // Denomination row inputs still work (inside details)
    document.querySelectorAll('.denomination-input').forEach(input => {
        input.addEventListener('input', function () {
            let total = 0;
            document.querySelectorAll('.denomination-input').forEach(i => {
                total += (parseInt(i.value) || 0) * parseInt(i.dataset.value);
            });
            const change = Math.max(0, total - orderTotal);
            if (denomTotal)  denomTotal.textContent  = total.toFixed(2);
            if (denomChange) denomChange.textContent = change.toFixed(2);
            // Sync to main input
            const inp = document.getElementById('cashAmountInput');
            if (inp) { inp.value = total; inp.dispatchEvent(new Event('input')); }
            calculateChangeDenominations(change);
        });
    });
}

function calculateChangeDenominations(changeAmount) {
    const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
    const changeInputs  = document.querySelectorAll('.change-given-input');
    let remaining = Math.round(changeAmount);
    denominations.forEach((denom, i) => {
        const input = changeInputs[i];
        if (input) {
            const count = Math.floor(remaining / denom);
            input.value = count;
            remaining  -= count * denom;
        }
    });
}

// ─── Process button state ─────────────────────────────────────────────────────
function _resetProcessBtn() {
    const btn  = document.getElementById('processPaymentBtn');
    const span = document.getElementById('paymentTotal');
    const total = window.selectedOrder ? parseFloat(window.selectedOrder.total_amount) : 0;
    if (span) span.textContent = total.toFixed(2);
    if (btn) {
        btn.disabled = true;
        btn.style.cssText = 'width:100%;padding:18px;font-size:20px;font-weight:700;background:#d1d5db;color:white;border:none;border-radius:12px;cursor:not-allowed;letter-spacing:0.3px;transition:all 0.2s;';
    }
}

function _enableProcessBtn() {
    const btn = document.getElementById('processPaymentBtn');
    if (btn) {
        btn.disabled = false;
        btn.style.cssText = 'width:100%;padding:18px;font-size:20px;font-weight:700;background:#16a34a;color:white;border:none;border-radius:12px;cursor:pointer;letter-spacing:0.3px;transition:all 0.2s;box-shadow:0 4px 12px rgba(22,163,74,0.3);';
    }
}

function _updateProcessBtnForCash() {
    const input = document.getElementById('cashAmountInput');
    const total = window.selectedOrder ? parseFloat(window.selectedOrder.total_amount) : 0;
    const received = parseFloat(input ? input.value : 0) || 0;
    if (received >= total && total > 0) {
        _enableProcessBtn();
    } else {
        _resetProcessBtn();
    }
}

function _resetPaymentTabs() {
    _allMethods.forEach(m => {
        const tab = document.getElementById(`tab-${m}`);
        if (!tab) return;
        tab.style.borderColor = '#e5e7eb';
        tab.style.background  = 'white';
        tab.style.boxShadow   = 'none';
        const label = tab.querySelector('span:last-child');
        if (label) label.style.color = '#374151';
    });
    _allFieldSections.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
}

// ─── Payment processing ───────────────────────────────────────────────────────
window.processPaymentManager = async function () {
    if (!window.selectedOrder || !window.selectedPaymentMethod) {
        showErrorModal('Error', 'Please select an order and payment method');
        return;
    }
    if (window.selectedOrder.payment_status === 'paid') {
        showErrorModal('Error', 'This order has already been paid');
        return;
    }

    try {
        const total = parseFloat(window.selectedOrder.total_amount);
        const paymentData = {
            amount:         total,
            payment_method: window.selectedPaymentMethod,
            branch_id:      parseInt(document.getElementById('paymentApp').dataset.branchId),
            reference_number: '',
        };

        if (window.selectedPaymentMethod === 'cash') {
            const inp = document.getElementById('cashAmountInput');
            const received = parseFloat(inp ? inp.value : total) || total;
            paymentData.amount_received = received;
            paymentData.change_amount   = Math.max(0, received - total);
        } else if (window.selectedPaymentMethod === 'card') {
            paymentData.reference_number = document.getElementById('cardReferenceNumber')?.value || '';
            paymentData.amount_received  = total;
            paymentData.change_amount    = 0;
        } else if (window.selectedPaymentMethod === 'wallet') {
            paymentData.reference_number = `WALLET-${Date.now()}`;
            paymentData.amount_received  = total;
            paymentData.change_amount    = 0;
        } else {
            // khalti / mobile — map to 'card' for backend
            paymentData.payment_method   = 'card';
            const refEl = window.selectedPaymentMethod === 'khalti'
                ? document.getElementById('khaltiTransactionId')
                : document.getElementById('mobileReferenceNumber');
            paymentData.reference_number = refEl?.value || `${window.selectedPaymentMethod.toUpperCase()}-${Date.now()}`;
            paymentData.amount_received  = total;
            paymentData.change_amount    = 0;
        }

        const response = await fetch(`/admin/payments/order/${window.selectedOrder.id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type':    'application/json',
                'Accept':          'application/json',
                'X-Requested-With':'XMLHttpRequest',
                'X-CSRF-TOKEN':    document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(paymentData),
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (data.success) {
            playPaymentSuccessWithMethod(window.selectedPaymentMethod);
            showSuccessModal('Success', 'Payment processed successfully!');
            fetchOrders();
            // Refresh drawer balance after every payment (cash/cod affect balance; others keep UI in sync)
            if (typeof updateDrawerButtonState === 'function') updateDrawerButtonState();
            resetPaymentPanel();
            document.querySelectorAll('.order-card-selected').forEach(c =>
                c.classList.remove('order-card-selected', 'ring-2', 'ring-blue-500'));
            // Return customer display to ads after 3 s (shows success state briefly first)
            if (window._viewerResetTimer) clearTimeout(window._viewerResetTimer);
            window._viewerResetTimer = setTimeout(resetPaymentViewer, 3000);
        } else {
            playPaymentFailed();
            showErrorModal('Error', data.message || 'Failed to process payment');
        }
    } catch (err) {
        playPaymentFailed();
        console.error('Payment processing error:', err);
        showErrorModal('Error', 'Failed to process payment. Please try again.');
    }
};

// ─── Reset panel ──────────────────────────────────────────────────────────────
function resetPaymentPanel() {
    const noOrder   = document.getElementById('noOrderSelected');
    const methods   = document.getElementById('paymentMethodsSection');

    if (noOrder) {
        noOrder.innerHTML = `
            <div class="text-5xl mb-3">👈</div>
            <p class="text-base">Select an order to process payment</p>`;
        noOrder.style.display = 'flex';
        noOrder.className = 'flex-1 flex flex-col items-center justify-center text-gray-400 select-none';
    }
    if (methods) methods.style.display = 'none';

    // Mobile: restore order list
    const backBtn = document.getElementById('backToOrdersBtn');
    if (backBtn) backBtn.classList.add('hidden');
    if (window.innerWidth < 768) {
        const op = document.getElementById('ordersListPanel');
        const pp = document.getElementById('paymentDetailsPanel');
        if (op) { op.classList.remove('hidden'); op.style.display = ''; }
        if (pp)  pp.classList.add('hidden');
    }

    // Clear order summary
    const orderDetails = document.getElementById('orderDetails');
    if (orderDetails) orderDetails.innerHTML = '<p class="text-gray-500">Select an order</p>';

    const actionsEl = document.getElementById('orderActions');
    if (actionsEl) { actionsEl.innerHTML = ''; actionsEl.style.display = 'none'; actionsEl.classList.add('hidden'); }

    // Reset tabs & fields
    _resetPaymentTabs();

    // Clear all inputs
    ['cashAmountInput','cardReferenceNumber','walletNumber',
     'khaltiTransactionId','mobileReferenceNumber','paymentNotes'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    // Hide change display
    const cd = document.getElementById('changeDisplay');
    if (cd) cd.classList.add('hidden');

    // Reset denomination inputs
    document.querySelectorAll('.denomination-input').forEach(i => { i.value = 0; });
    document.querySelectorAll('.change-given-input').forEach(i => { i.value = 0; });
    const dt = document.getElementById('denominationTotal');
    const dc = document.getElementById('changeAmount');
    if (dt) dt.textContent = '0';
    if (dc) dc.textContent = '0';

    // Hide wallet balance
    const wbd = document.getElementById('walletBalanceDisplay');
    if (wbd) wbd.classList.add('hidden');

    // Reset Khalti QR
    const kqr = document.getElementById('khaltiQrCode');
    if (kqr) kqr.innerHTML = '<div class="text-center text-gray-400"><i class="fas fa-qrcode text-3xl mb-1"></i><p class="text-xs">QR generated here</p></div>';

    // Reset process button
    _resetProcessBtn();
    window.selectedOrder         = null;
    window.selectedPaymentMethod = null;
}

// ─── Wallet ───────────────────────────────────────────────────────────────────
function initializeWalletFields() {
    const input   = document.getElementById('walletNumber');
    const scanBtn = document.getElementById('scanWalletBtn');
    const display = document.getElementById('walletBalanceDisplay');

    if (input) {
        const fresh = input.cloneNode(true);
        input.parentNode.replaceChild(fresh, input);
        document.getElementById('walletNumber').addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').substring(0, 16);
            this.value = v.replace(/(\w{4})(?=\w)/g, '$1-');
            if (this.value.length === 19) checkWalletBalance(this.value);
            else if (display) display.classList.add('hidden');
        });
    }
    if (scanBtn) scanBtn.addEventListener('click', () => alert('QR Scanner not yet implemented'));
}

async function checkWalletBalance(walletNumber) {
    try {
        const branchId = new URLSearchParams(window.location.search).get('branch');
        const res = await fetch(`/api/wallet/balance?wallet_number=${walletNumber}&branch_id=${branchId}`, {
            headers: {
                'Accept':          'application/json',
                'X-Requested-With':'XMLHttpRequest',
                'X-CSRF-TOKEN':    document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });
        if (res.ok) {
            const data = await res.json();
            const bal  = document.getElementById('walletBalance');
            const disp = document.getElementById('walletBalanceDisplay');
            if (data.success && bal && disp) {
                bal.textContent = `Rs ${parseFloat(data.balance).toFixed(2)}`;
                disp.classList.remove('hidden');
            }
        }
    } catch (e) { console.error('Wallet balance error:', e); }
}

// ─── Khalti ───────────────────────────────────────────────────────────────────
function initializeKhaltiFields() {
    const txnInput = document.getElementById('khaltiTransactionId');
    if (txnInput) {
        txnInput.addEventListener('input', function () {
            const ok = !this.value || /^[A-Za-z0-9]{10,}$/.test(this.value.trim());
            this.classList.toggle('border-red-500', !ok);
        });
    }
    generateKhaltiQRCode();
}

function generateKhaltiQRCode() {
    const el = document.getElementById('khaltiQrCode');
    if (!el || !window.selectedOrder) return;
    const qrData = { type: 'khalti_payment', order_id: window.selectedOrder.id,
                     amount: window.selectedOrder.total_amount, timestamp: new Date().toISOString() };
    el.innerHTML = '';
    if (typeof QRCode !== 'undefined') {
        new QRCode(el, { text: JSON.stringify(qrData), width: 160, height: 160,
                         colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.H });
    } else {
        el.innerHTML = '<div class="text-center text-gray-400 text-xs"><i class="fas fa-qrcode text-3xl"></i><p>QR library not loaded</p></div>';
    }
}

// ─── Mobile back / resize ─────────────────────────────────────────────────────
function backToOrdersList() {
    const op = document.getElementById('ordersListPanel');
    const dp = document.getElementById('paymentDetailsPanel');
    if (op) { op.classList.remove('hidden'); op.style.display = ''; }
    if (dp)  dp.classList.add('hidden');
    const backBtn = document.getElementById('backToOrdersBtn');
    if (backBtn) backBtn.classList.add('hidden');
}

window.addEventListener('resize', function () {
    if (window.innerWidth >= 768) {
        const op = document.getElementById('ordersListPanel');
        const dp = document.getElementById('paymentDetailsPanel');
        if (op) op.classList.remove('hidden');
        if (dp) dp.classList.remove('hidden');
    }
});

// ─── Payment popup (for digital/QR methods) ──────────────────────────────────

function showPaymentPopup(method, order) {
    document.getElementById('paymentPopup')?.remove();

    if (!order) return;
    const amount  = parseFloat(order.total_amount || 0).toFixed(2);
    const orderId = order.id;

    let content = '';

    if (method === 'khalti') {
        content = `
        <div style="text-align:center">
            <div style="font-size:40px;margin-bottom:8px">🟣</div>
            <h2 style="color:#5c2d91;margin:0 0 16px">Pay with Khalti</h2>
            <div id="popupKhaltiQr" style="width:180px;height:180px;margin:0 auto 12px;
                background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#999;font-size:13px">Generating QR…</span>
            </div>
            <div style="font-size:24px;font-weight:bold;color:#5c2d91;margin-bottom:14px">Rs ${amount}</div>
            <p style="color:#666;font-size:13px;margin-bottom:12px">Customer scans QR with Khalti app</p>
            <input type="text" id="popupKhaltiTxn" placeholder="Enter Khalti transaction ID"
                style="width:100%;padding:12px;border:2px solid #e5e7eb;border-radius:8px;
                font-size:16px;margin-bottom:4px;box-sizing:border-box;">
        </div>`;
    } else if (method === 'esewa') {
        content = `
        <div style="text-align:center">
            <div style="font-size:40px;margin-bottom:8px">🟢</div>
            <h2 style="color:#00a650;margin:0 0 16px">Pay with eSewa</h2>
            <div id="popupEsewaQr" style="width:180px;height:180px;margin:0 auto 12px;
                background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#999;font-size:13px">Generating QR…</span>
            </div>
            <div style="font-size:24px;font-weight:bold;color:#00a650;margin-bottom:14px">Rs ${amount}</div>
            <input type="text" id="popupEsewaTxn" placeholder="Enter eSewa transaction ID"
                style="width:100%;padding:12px;border:2px solid #e5e7eb;border-radius:8px;
                font-size:16px;margin-bottom:4px;box-sizing:border-box;">
        </div>`;
    } else if (method === 'wallet') {
        content = `
        <div style="text-align:center">
            <div style="font-size:40px;margin-bottom:8px">🥟</div>
            <h2 style="color:#dc2626;margin:0 0 16px">MA:MO Credits</h2>
            <div style="font-size:24px;font-weight:bold;color:#dc2626;margin-bottom:14px">Rs ${amount}</div>
            <input type="text" id="popupWalletAccount" placeholder="Customer account (XXXX-XXXX)"
                style="width:100%;padding:12px;border:2px solid #e5e7eb;border-radius:8px;
                font-size:16px;margin-bottom:6px;box-sizing:border-box;">
            <div id="popupWalletBalance" style="color:#666;font-size:13px;margin-bottom:10px;min-height:18px;"></div>
            <button onclick="checkWalletBalancePopup()"
                style="background:#fee2e2;color:#dc2626;border:none;padding:8px 16px;
                border-radius:6px;cursor:pointer;margin-bottom:10px;">Check Balance</button>
        </div>`;
    } else if (method === 'mobile') {
        content = `
        <div style="text-align:center">
            <div style="font-size:40px;margin-bottom:8px">📲</div>
            <h2 style="color:#0891b2;margin:0 0 14px">Mobile Banking</h2>
            <div id="popupBankDetails" style="background:#f0f9ff;border-radius:12px;padding:14px;
                margin-bottom:14px;text-align:left;font-size:14px;">
                <div style="display:flex;justify-content:space-between;padding:5px 0;
                    border-bottom:1px solid #e0f2fe">
                    <span style="color:#666">Bank</span>
                    <strong id="popupBankName">Loading…</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;
                    border-bottom:1px solid #e0f2fe">
                    <span style="color:#666">Account</span>
                    <strong id="popupBankAccount">—</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0">
                    <span style="color:#666">Name</span>
                    <strong id="popupBankAccountName">—</strong>
                </div>
            </div>
            <div style="font-size:24px;font-weight:bold;color:#0891b2;margin-bottom:14px">Rs ${amount}</div>
            <input type="text" id="popupMobileTxn" placeholder="Enter transaction / reference ID"
                style="width:100%;padding:12px;border:2px solid #e5e7eb;border-radius:8px;
                font-size:16px;margin-bottom:4px;box-sizing:border-box;">
        </div>`;
    }

    const modal = document.createElement('div');
    modal.id = 'paymentPopup';
    modal.style.cssText = `position:fixed;top:0;left:0;width:100%;height:100%;
        background:rgba(0,0,0,0.6);z-index:99999;
        display:flex;align-items:center;justify-content:center;`;

    modal.innerHTML = `
        <div style="background:white;border-radius:16px;padding:28px 28px 20px;
            max-width:460px;width:92%;position:relative;
            box-shadow:0 25px 50px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
            <button onclick="closePaymentPopup()"
                style="position:absolute;top:10px;right:14px;background:none;border:none;
                font-size:26px;cursor:pointer;color:#9ca3af;line-height:1;">×</button>
            ${content}
            <button onclick="confirmPopupPayment('${method}', ${orderId})"
                id="confirmPopupBtn"
                style="width:100%;padding:15px;background:#16a34a;color:white;border:none;
                border-radius:10px;font-size:17px;font-weight:bold;cursor:pointer;margin-top:10px;">
                ✓ Confirm Payment · Rs ${amount}
            </button>
            <button onclick="closePaymentPopup()"
                style="width:100%;padding:11px;background:#f3f4f6;color:#374151;border:none;
                border-radius:10px;font-size:15px;cursor:pointer;margin-top:8px;">
                Cancel
            </button>
        </div>`;

    document.body.appendChild(modal);

    // Generate QR codes after DOM insert
    if (method === 'khalti') {
        _generatePopupQR('popupKhaltiQr', `khalti://pay?amount=${amount}&ref=${orderId}`);
    } else if (method === 'esewa') {
        _generatePopupQR('popupEsewaQr', `esewa://pay?amount=${amount}&ref=${orderId}`);
    } else if (method === 'mobile') {
        // Fetch bank details
        fetch('/admin/payments/payment-info').then(r => r.json()).then(info => {
            const nameEl    = document.getElementById('popupBankName');
            const acctEl    = document.getElementById('popupBankAccount');
            const acctNameEl = document.getElementById('popupBankAccountName');
            if (nameEl)     nameEl.textContent     = info.bank_name         || '—';
            if (acctEl)     acctEl.textContent     = info.bank_account      || '—';
            if (acctNameEl) acctNameEl.textContent = info.bank_account_name || '—';
        }).catch(() => {});
    }
}

function _generatePopupQR(containerId, text) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = '';
    if (typeof QRCode !== 'undefined') {
        new QRCode(el, { text, width: 180, height: 180,
                         colorDark: '#000000', colorLight: '#ffffff',
                         correctLevel: QRCode.CorrectLevel.H });
    } else {
        el.innerHTML = '<div style="color:#9ca3af;font-size:12px;text-align:center;">QR library not loaded</div>';
    }
}

function closePaymentPopup() {
    document.getElementById('paymentPopup')?.remove();
}

function confirmPopupPayment(method, orderId) {
    const inputMap = {
        khalti: 'popupKhaltiTxn',
        esewa:  'popupEsewaTxn',
        wallet: 'popupWalletAccount',
        mobile: 'popupMobileTxn',
    };
    const inputId  = inputMap[method];
    const reference = inputId ? (document.getElementById(inputId)?.value || '') : '';

    // Sync reference into the main payment panel field before processing
    const fieldMap = {
        khalti: 'khaltiTransactionId',
        esewa:  'khaltiTransactionId',   // reuse khalti field — backend maps both to card
        mobile: 'mobileReferenceNumber',
    };
    const panelField = fieldMap[method];
    if (panelField && reference) {
        const el = document.getElementById(panelField);
        if (el) el.value = reference;
    }

    closePaymentPopup();
    if (typeof window.processPaymentManager === 'function') {
        window.processPaymentManager();
    }
}

async function checkWalletBalancePopup() {
    const input = document.getElementById('popupWalletAccount');
    const balDiv = document.getElementById('popupWalletBalance');
    if (!input || !balDiv) return;
    const walletNumber = input.value.trim();
    if (!walletNumber) return;

    try {
        const branchId = parseInt(document.getElementById('paymentApp').dataset.branchId);
        const res = await fetch(`/api/wallet/balance?wallet_number=${encodeURIComponent(walletNumber)}&branch_id=${branchId}`, {
            headers: { 'Accept': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        const data = await res.json();
        balDiv.textContent = data.success
            ? `Balance: Rs ${parseFloat(data.balance).toFixed(2)}`
            : (data.message || 'Wallet not found');
        balDiv.style.color = data.success ? '#16a34a' : '#dc2626';
    } catch (e) {
        balDiv.textContent = 'Could not check balance';
        balDiv.style.color = '#dc2626';
    }
}

window.closePaymentPopup         = closePaymentPopup;
window.confirmPopupPayment       = confirmPopupPayment;
window.checkWalletBalancePopup   = checkWalletBalancePopup;
window.showPaymentPopup          = showPaymentPopup;

// ─── Cash popup ───────────────────────────────────────────────────────────────
function showCashPopup(order) {
    document.getElementById('cashPopupModal')?.remove();
    if (!order) return;

    const total = parseFloat(order.total_amount || 0);
    const orderId = order.id;

    // Build quick-amount options (round up to nearest sensible denomination)
    const roundUps = [50, 100, 200, 500, 1000, 2000];
    const quickAmounts = [];
    for (const r of roundUps) {
        const v = Math.ceil(total / r) * r;
        if (v >= total && !quickAmounts.includes(v)) quickAmounts.push(v);
        if (quickAmounts.length >= 5) break;
    }

    const modal = document.createElement('div');
    modal.id = 'cashPopupModal';
    modal.style.cssText = `position:fixed;top:0;left:0;width:100%;height:100%;
        background:rgba(0,0,0,0.6);z-index:99999;
        display:flex;align-items:center;justify-content:center;`;

    modal.innerHTML = `
        <div style="background:white;border-radius:16px;padding:24px;
            max-width:420px;width:92%;position:relative;
            box-shadow:0 25px 50px rgba(0,0,0,0.3);">
            <button onclick="closeCashPopup()"
                style="position:absolute;top:10px;right:14px;background:none;border:none;
                font-size:26px;cursor:pointer;color:#9ca3af;line-height:1;">×</button>
            <div style="text-align:center;margin-bottom:14px;">
                <div style="font-size:32px;margin-bottom:2px;">💵</div>
                <h2 style="margin:0;font-size:17px;color:#111827;">Cash Payment</h2>
                <div style="font-size:26px;font-weight:900;color:#111827;margin-top:2px;">
                    Rs ${total.toFixed(0)}
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
                ${quickAmounts.map(a => `
                    <button onclick="setCashPopupAmount(${a})"
                        style="flex:1;min-width:56px;padding:8px 4px;border:2px solid #e5e7eb;
                        border-radius:8px;background:white;cursor:pointer;font-size:13px;
                        font-weight:600;color:#374151;">Rs ${a}</button>`).join('')}
            </div>
            <div style="display:flex;align-items:center;gap:8px;background:#eef2ff;
                border:2px solid #818cf8;border-radius:12px;padding:10px 14px;margin-bottom:10px;">
                <span style="font-size:15px;font-weight:700;color:#4f46e5;flex-shrink:0;">Rs</span>
                <input type="number" id="cashPopupInput"
                    style="flex:1;background:transparent;border:none;outline:none;
                    font-size:28px;font-weight:800;color:#312e81;width:0;min-width:0;"
                    placeholder="0" step="1" min="0" inputmode="numeric">
                <span style="font-size:11px;color:#818cf8;flex-shrink:0;">received</span>
            </div>
            <div id="cashPopupChange" style="display:none;background:#f0fdf4;border:1px solid #86efac;
                border-radius:10px;padding:10px 14px;justify-content:space-between;
                align-items:center;margin-bottom:10px;">
                <span style="font-size:14px;font-weight:600;color:#166534;">Change:</span>
                <span id="cashPopupChangeAmt" style="font-size:22px;font-weight:800;color:#15803d;">Rs 0</span>
            </div>
            <button id="cashPopupConfirmBtn" onclick="confirmCashPopup(${orderId})"
                disabled
                style="width:100%;padding:14px;background:#d1d5db;color:white;border:none;
                border-radius:10px;font-size:16px;font-weight:700;cursor:not-allowed;transition:all 0.2s;">
                ✓ Confirm Cash Payment
            </button>
            <button onclick="closeCashPopup()"
                style="width:100%;padding:10px;background:#f3f4f6;color:#374151;border:none;
                border-radius:10px;font-size:14px;cursor:pointer;margin-top:8px;">
                Cancel
            </button>
        </div>`;

    document.body.appendChild(modal);

    const input = document.getElementById('cashPopupInput');
    if (input) {
        input.focus();
        input.addEventListener('input', () => _updateCashPopupChange(total));
    }
}

function setCashPopupAmount(amount) {
    const input = document.getElementById('cashPopupInput');
    if (input) {
        input.value = amount;
        const total = window.selectedOrder ? parseFloat(window.selectedOrder.total_amount) : 0;
        _updateCashPopupChange(total);
    }
}

function _updateCashPopupChange(total) {
    const input      = document.getElementById('cashPopupInput');
    const received   = parseFloat(input ? input.value : 0) || 0;
    const change     = received - total;
    const changeDiv  = document.getElementById('cashPopupChange');
    const changeAmt  = document.getElementById('cashPopupChangeAmt');
    const confirmBtn = document.getElementById('cashPopupConfirmBtn');

    if (received > 0 && change >= 0) {
        if (changeDiv) changeDiv.style.display = 'flex';
        if (changeAmt) changeAmt.textContent = `Rs ${change.toFixed(0)}`;
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.style.background = '#16a34a';
            confirmBtn.style.cursor = 'pointer';
        }
    } else {
        if (changeDiv) changeDiv.style.display = 'none';
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.style.background = '#d1d5db';
            confirmBtn.style.cursor = 'not-allowed';
        }
    }
}

function confirmCashPopup(orderId) {
    const input    = document.getElementById('cashPopupInput');
    const received = parseFloat(input ? input.value : 0) || 0;
    // Sync to hidden cashAmountInput so processPaymentManager reads it
    const cashInput = document.getElementById('cashAmountInput');
    if (cashInput) cashInput.value = received;
    closeCashPopup();
    playPaymentProcessing();
    if (typeof window.processPaymentManager === 'function') {
        window.processPaymentManager();
    }
}

function closeCashPopup() {
    document.getElementById('cashPopupModal')?.remove();
}

window.showCashPopup     = showCashPopup;
window.setCashPopupAmount = setCashPopupAmount;
window.confirmCashPopup  = confirmCashPopup;
window.closeCashPopup    = closeCashPopup;

// ─── Note popup ───────────────────────────────────────────────────────────────
function openNotePopup() {
    const popup = document.getElementById('notePopup');
    if (popup) { popup.style.display = 'flex'; }
    const ta = document.getElementById('noteTextarea');
    if (ta) { ta.value = window.paymentNote || ''; ta.focus(); }
}
function closeNotePopup() {
    const popup = document.getElementById('notePopup');
    if (popup) popup.style.display = 'none';
}
function saveNote() {
    const ta = document.getElementById('noteTextarea');
    window.paymentNote = ta ? ta.value.trim() : '';
    closeNotePopup();
    const btn = document.getElementById('addNoteBtn');
    if (btn) {
        btn.textContent = window.paymentNote ? '✓ Note added' : '+ Add note';
        btn.style.color = window.paymentNote ? '#16a34a' : '#6b7280';
    }
}
window.openNotePopup  = openNotePopup;
window.closeNotePopup = closeNotePopup;
window.saveNote       = saveNote;

// ─── Exports ──────────────────────────────────────────────────────────────────
window.selectOrder              = selectOrder;
window.resetPaymentPanel        = resetPaymentPanel;
window.backToOrdersList         = backToOrdersList;
window.initializePaymentMethods = initializePaymentMethods;
window.initializeCashDenominations = initializeCashDenominations;
window.checkWalletBalance       = checkWalletBalance;
window.generateKhaltiQRCode     = generateKhaltiQRCode;
window.populatePaymentPanelContent = populatePaymentPanelContent;
