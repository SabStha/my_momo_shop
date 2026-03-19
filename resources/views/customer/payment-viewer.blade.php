<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Viewer - Amako Momo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body style="height:100vh;overflow:hidden;margin:0;background:#eff6ff;">
<div style="height:100vh;display:flex;flex-direction:column;overflow:hidden;">

    {{-- ── HEADER (flex-shrink:0, 80px) ──────────────────────────────────── --}}
    <div style="flex-shrink:0;height:80px;background:linear-gradient(135deg,#2563eb,#4338ca);color:white;padding:0 20px;display:flex;align-items:center;position:relative;z-index:30;">
        <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-utensils" style="font-size:20px;"></i>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;line-height:1.2;">Amako Momo</div>
                    <div style="font-size:12px;opacity:0.8;">Payment Terminal</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                {{-- Sound controls --}}
                <div style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);border-radius:8px;padding:4px 10px;">
                    <button id="soundMuteBtn" onclick="toggleSoundMute()" style="color:white;background:none;border:none;cursor:pointer;" title="Mute sounds">
                        <i class="fas fa-volume-up"></i>
                    </button>
                    <input type="range" id="soundVolumeSlider" min="0" max="100" value="70"
                           onchange="setSoundVolume(this.value / 100)"
                           style="width:60px;cursor:pointer;">
                    <span style="font-size:11px;opacity:0.8;">Sound</span>
                    <button onclick="playPaymentSuccess()" style="font-size:11px;color:rgba(134,239,172,1);background:none;border:none;cursor:pointer;" title="Test success">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="playPaymentFailed()" style="font-size:11px;color:rgba(252,165,165,1);background:none;border:none;cursor:pointer;" title="Test fail">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;opacity:0.7;">Branch #{{ request('branch', 'N/A') }}</div>
                    <div style="font-size:20px;font-weight:700;" id="currentTime"></div>
                    <div style="font-size:11px;opacity:0.7;" id="currentDate"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MAIN AREA (flex:1, position:relative — layers stack absolutely) ── --}}
    <div style="flex:1;position:relative;overflow:hidden;min-height:0;">

        {{-- ── LAYER 1: Ad player — full-width fullscreen when idle ───────── --}}
        {{-- position:fixed so it truly spans viewport between header/footer --}}
        <div id="adPlayer"
             style="position:fixed;top:80px;left:0;right:0;bottom:64px;
                    width:100%;background:black;overflow:hidden;
                    z-index:10;display:none;"></div>

        {{-- ── LAYER 2: Idle welcome screen — shown when no ads loaded ───── --}}
        <div id="idleState"
             style="position:absolute;inset:0;background:white;z-index:5;
                    display:flex;flex-direction:column;align-items:center;
                    justify-content:center;padding:40px 20px;">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,#f87171,#dc2626);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:20px;box-shadow:0 8px 25px rgba(220,38,38,0.3);">
                <i class="fas fa-utensils" style="font-size:32px;color:white;"></i>
            </div>
            <h2 style="font-size:32px;font-weight:800;color:#1f2937;margin-bottom:10px;">Welcome!</h2>
            <p style="font-size:16px;color:#6b7280;margin-bottom:28px;text-align:center;max-width:380px;">
                Your order will appear here shortly.<br>Get ready for an amazing culinary experience!
            </p>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:380px;margin-bottom:24px;">
                <div style="text-align:center;">
                    <div style="width:52px;height:52px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                        <i class="fas fa-clock" style="color:#ef4444;font-size:20px;"></i>
                    </div>
                    <p style="font-size:12px;color:#6b7280;font-weight:500;">Quick Service</p>
                </div>
                <div style="text-align:center;">
                    <div style="width:52px;height:52px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                        <i class="fas fa-leaf" style="color:#22c55e;font-size:20px;"></i>
                    </div>
                    <p style="font-size:12px;color:#6b7280;font-weight:500;">Fresh Food</p>
                </div>
                <div style="text-align:center;">
                    <div style="width:52px;height:52px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                        <i class="fas fa-credit-card" style="color:#3b82f6;font-size:20px;"></i>
                    </div>
                    <p style="font-size:12px;color:#6b7280;font-weight:500;">Easy Payment</p>
                </div>
            </div>
            <div style="width:12px;height:12px;background:#3b82f6;border-radius:50%;animation:pulse 1.5s infinite;"></div>
        </div>

        {{-- ── LAYER 3: Active order — split 55/45 layout ─────────────────── --}}
        {{-- Shown when an order is selected. Contains BOTH panels.           --}}
        <div id="orderContent"
             style="position:absolute;inset:0;z-index:20;
                    display:none;flex-direction:row;">

            {{-- Left 55%: order details --}}
            <div style="width:55%;height:100%;overflow-y:auto;
                        padding:16px;background:white;
                        border-right:2px solid #e5e7eb;box-sizing:border-box;">
                {{-- Order sub-header --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #e5e7eb;">
                    <div>
                        <span style="font-size:20px;font-weight:800;color:#111827;">Order #<span id="orderNumber">-</span></span>
                        <span style="font-size:13px;color:#9ca3af;margin-left:10px;" id="orderTime">-</span>
                    </div>
                    <div id="paymentStatus"></div>
                </div>
                {{-- Items list --}}
                <h4 style="font-size:15px;font-weight:700;color:#374151;margin-bottom:10px;">Order Items</h4>
                <div id="orderItems" style="display:flex;flex-direction:column;gap:8px;"></div>
                {{-- Totals --}}
                <div style="margin-top:12px;padding-top:10px;border-top:1px solid #e5e7eb;">
                    <div style="display:flex;justify-content:space-between;font-size:14px;color:#6b7280;margin-bottom:4px;">
                        <span>Subtotal</span><span id="subtotal">Rs 0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;color:#6b7280;margin-bottom:4px;">
                        <span>Tax</span><span id="tax">Rs 0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:800;color:#111827;border-top:1px solid #e5e7eb;padding-top:6px;margin-top:4px;">
                        <span>Total</span><span id="totalAmount">Rs 0.00</span>
                    </div>
                </div>
            </div>

            {{-- Right 45%: payment methods --}}
            <div style="width:45%;height:100%;overflow-y:auto;
                        padding:16px;background:#f9fafb;box-sizing:border-box;">
                <div style="background:linear-gradient(135deg,#f9fafb,#eff6ff);border-radius:14px;border:1px solid #e5e7eb;padding:14px;">
                    <h4 style="font-size:15px;font-weight:700;color:#374151;margin-bottom:12px;">Select Payment Method</h4>

                    {{-- Payment method buttons: 2×3 grid --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px;">
                        <button id="method-cash" class="payment-method-option" data-method="cash"
                                onclick="selectMethodFromViewer('cash')"
                                style="padding:12px 6px;border-radius:12px;border:2px solid #e5e7eb;background:white;cursor:pointer;text-align:center;transition:all 0.2s;">
                            <i class="fas fa-money-bill-wave" style="font-size:22px;color:#22c55e;display:block;margin-bottom:5px;"></i>
                            <div style="font-size:12px;font-weight:600;color:#374151;">Cash</div>
                        </button>
                        <button id="method-card" class="payment-method-option" data-method="card"
                                onclick="selectMethodFromViewer('card')"
                                style="padding:12px 6px;border-radius:12px;border:2px solid #e5e7eb;background:white;cursor:pointer;text-align:center;transition:all 0.2s;">
                            <i class="fas fa-credit-card" style="font-size:22px;color:#3b82f6;display:block;margin-bottom:5px;"></i>
                            <div style="font-size:12px;font-weight:600;color:#374151;">Card</div>
                        </button>
                        <button id="method-khalti" class="payment-method-option" data-method="khalti"
                                onclick="selectMethodFromViewer('khalti')"
                                style="padding:12px 6px;border-radius:12px;border:2px solid #e5e7eb;background:white;cursor:pointer;text-align:center;transition:all 0.2s;">
                            <i class="fas fa-qrcode" style="font-size:22px;color:#a855f7;display:block;margin-bottom:5px;"></i>
                            <div style="font-size:12px;font-weight:600;color:#374151;">Khalti</div>
                        </button>
                        <button id="method-mobile" class="payment-method-option" data-method="mobile"
                                onclick="selectMethodFromViewer('mobile')"
                                style="padding:12px 6px;border-radius:12px;border:2px solid #e5e7eb;background:white;cursor:pointer;text-align:center;transition:all 0.2s;">
                            <i class="fas fa-mobile-alt" style="font-size:22px;color:#0891b2;display:block;margin-bottom:5px;"></i>
                            <div style="font-size:12px;font-weight:600;color:#374151;">Mobile</div>
                        </button>
                        <button id="method-wallet" class="payment-method-option" data-method="wallet"
                                onclick="selectMethodFromViewer('wallet')"
                                style="padding:12px 6px;border-radius:12px;border:2px solid #e5e7eb;background:white;cursor:pointer;text-align:center;transition:all 0.2s;">
                            <i class="fas fa-wallet" style="font-size:22px;color:#dc2626;display:block;margin-bottom:5px;"></i>
                            <div style="font-size:12px;font-weight:600;color:#374151;">MA:MO Credits</div>
                        </button>
                        <button id="method-esewa" class="payment-method-option" data-method="esewa"
                                onclick="selectMethodFromViewer('esewa')"
                                style="padding:12px 6px;border-radius:12px;border:2px solid #e5e7eb;background:white;cursor:pointer;text-align:center;transition:all 0.2s;">
                            <i class="fas fa-leaf" style="font-size:22px;color:#00a650;display:block;margin-bottom:5px;"></i>
                            <div style="font-size:12px;font-weight:600;color:#374151;">eSewa</div>
                        </button>
                    </div>

                    {{-- Payment detail rows --}}
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;background:white;border-radius:10px;padding:8px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                            <span style="font-size:13px;color:#6b7280;">Method</span>
                            <span id="paymentMethodDisplay" style="font-weight:700;font-size:15px;color:#111827;">-</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;background:white;border-radius:10px;padding:8px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                            <span style="font-size:13px;color:#6b7280;">Amount</span>
                            <span id="paymentAmountDisplay" style="font-weight:700;font-size:15px;color:#111827;">-</span>
                        </div>
                        <div id="cashPaymentDetails" style="display:none;flex-direction:column;gap:6px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;background:white;border-radius:10px;padding:8px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                                <span style="font-size:13px;color:#6b7280;">Received</span>
                                <span id="amountReceivedDisplay" style="font-weight:700;font-size:15px;color:#16a34a;">-</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;background:white;border-radius:10px;padding:8px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                                <span style="font-size:13px;color:#6b7280;">Change</span>
                                <span id="changeAmountDisplay" style="font-weight:700;font-size:15px;color:#2563eb;">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Instructions --}}
                    <div id="paymentInstructions" style="display:none;margin-top:10px;padding:10px 12px;background:#eff6ff;border-radius:10px;">
                        <p id="instructionText" style="font-size:13px;color:#1d4ed8;margin:0;"></p>
                    </div>
                </div>
            </div>{{-- /right 45% --}}

        </div>{{-- /orderContent --}}

    </div>{{-- /main area --}}

    {{-- ── TOTAL BAR (flex-shrink:0, 64px, dark bg) ────────────────────── --}}
    <div style="flex-shrink:0;height:64px;background:#1e1b4b;color:white;padding:0 24px;display:flex;justify-content:space-between;align-items:center;position:relative;z-index:30;">
        <span style="font-size:16px;font-weight:600;opacity:0.85;">Total Amount</span>
        <span style="font-size:28px;font-weight:900;" id="total">Rs 0.00</span>
    </div>

</div>{{-- /outer flex --}}


{{-- ── QR popup overlay (pre-existing, hidden by default) ─────────────── --}}
<div id="viewerQRPopup" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.75);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:20px;padding:32px;max-width:400px;width:90%;
        text-align:center;box-shadow:0 30px 60px rgba(0,0,0,0.4);position:relative;">
        <button onclick="closeViewerPopup()"
            style="position:absolute;top:12px;right:16px;background:none;border:none;
            font-size:24px;cursor:pointer;color:#9ca3af;line-height:1;">&times;</button>
        <div id="viewerPopupIcon" style="font-size:48px;margin-bottom:10px;"></div>
        <h2 id="viewerPopupTitle" style="margin:0 0 16px;font-size:22px;"></h2>
        <div id="viewerPopupBody"></div>
        <div id="viewerPopupAmount" style="font-size:36px;font-weight:900;margin:12px 0;"></div>
        <p id="viewerPopupNote" style="color:#6b7280;font-size:14px;margin:0;"></p>
    </div>
</div>

<style>
@keyframes fade-in {
  from { opacity: 0; transform: scale(0.95); }
  to   { opacity: 1; transform: scale(1); }
}
@keyframes pulse {
  0%,100% { opacity:1; }
  50%      { opacity:0.5; }
}
@keyframes pop {
  0%   { transform:scale(0.5); opacity:0; }
  80%  { transform:scale(1.1); }
  100% { transform:scale(1);   opacity:1; }
}
.animate-fade-in { animation: fade-in 0.4s ease; }
::-webkit-scrollbar       { width: 6px; }
::-webkit-scrollbar-track { background:#f1f1f1; border-radius:4px; }
::-webkit-scrollbar-thumb { background:#c1c1c1; border-radius:4px; }
::-webkit-scrollbar-thumb:hover { background:#a8a8a8; }
</style>

<script>
    // ── State ────────────────────────────────────────────────────────────────
    const state = {
        orderId:    null,
        branchId:   null,
        pollInterval: null,
        lastUpdate: null
    };

    // ── Clock ────────────────────────────────────────────────────────────────
    function updateTime() {
        const now = new Date();
        const timeEl = document.getElementById('currentTime');
        const dateEl = document.getElementById('currentDate');
        if (timeEl) timeEl.textContent = now.toLocaleTimeString('en-US', { hour12:true, hour:'2-digit', minute:'2-digit' });
        if (dateEl) dateEl.textContent = now.toLocaleDateString('en-NP', { weekday:'short', day:'2-digit', month:'short', year:'numeric' });
    }
    setInterval(updateTime, 1000);
    updateTime();

    // ── Helpers ──────────────────────────────────────────────────────────────
    function formatCurrency(amount) {
        return 'Rs ' + parseFloat(amount || 0).toFixed(2);
    }

    function _pusherMethodColor(method) {
        const colors = { cash:'#16a34a', card:'#2563eb', khalti:'#7c3aed', wallet:'#dc2626', mobile:'#0891b2', esewa:'#00a650' };
        return colors[method] || '#6b7280';
    }
    function _pusherMethodBg(method) {
        const bgs = { cash:'#f0fdf4', card:'#eff6ff', khalti:'#f5f3ff', wallet:'#fef2f2', mobile:'#ecfeff', esewa:'#f0fdf4' };
        return bgs[method] || '#f9fafb';
    }

    function highlightMethodButton(method) {
        document.querySelectorAll('.payment-method-option').forEach(btn => {
            const sel = btn.dataset.method === method;
            btn.style.borderColor = sel ? _pusherMethodColor(method) : '#e5e7eb';
            btn.style.background  = sel ? _pusherMethodBg(method)    : 'white';
            btn.style.boxShadow   = sel ? `0 2px 10px ${_pusherMethodColor(method)}55` : 'none';
        });
    }

    function showPaymentInstructions(method) {
        const div  = document.getElementById('paymentInstructions');
        const text = document.getElementById('instructionText');
        if (!div || !text) return;
        const map = {
            cash:   'Please hand over the exact amount to the cashier. Change will be provided if needed.',
            card:   'Please insert or tap your card on the card reader.',
            wallet: 'Please provide your MA:MO account number to the cashier.',
            khalti: 'Please scan the QR code with your Khalti app to complete the payment.',
            esewa:  'Please scan the QR code with your eSewa app to complete the payment.',
            mobile: 'Transfer to the account shown and tell the cashier your reference ID.'
        };
        text.textContent  = map[method] || '';
        div.style.display = 'block';
    }

    // ── FIX 1: Viewer → Manager broadcast ────────────────────────────────────
    function selectMethodFromViewer(method) {
        playSelectSound();
        highlightMethodButton(method);

        const display = document.getElementById('paymentMethodDisplay');
        if (display) display.textContent = method.toUpperCase();

        showPaymentInstructions(method);

        // Show/hide cash change rows
        const cashDetails = document.getElementById('cashPaymentDetails');
        if (cashDetails) cashDetails.style.display = method === 'cash' ? 'flex' : 'none';

        // Show QR popup for digital methods
        if (['khalti', 'esewa', 'wallet', 'mobile'].includes(method)) {
            const raw = document.getElementById('total')?.textContent?.replace('Rs ', '') || '0';
            const amount = parseFloat(raw) || 0;
            showCustomerQRPopup(method, amount, state.orderId);
            playScanSound();
        } else {
            closeViewerPopup();
        }

        // Notify opener via postMessage (popup window fallback)
        if (window.opener && !window.opener.closed) {
            window.opener.postMessage({ type: 'UPDATE_PAYMENT_METHOD', method }, window.location.origin);
        }

        // Broadcast via Pusher through the backend (viewer → manager real-time sync)
        if (!state.orderId || !state.branchId) return;
        const rawTotal = parseFloat(document.getElementById('total')?.textContent?.replace('Rs ', '') || 0);
        fetch('/payment/method-selected', {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                order_id:  state.orderId,
                method:    method,
                amount:    rawTotal,
                branch_id: state.branchId
            })
        }).catch(e => console.warn('[Viewer→Manager] Broadcast failed:', e));
    }

    // ── postMessage listener (opener fallback) ────────────────────────────────
    window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin) return;
        switch (event.data.type) {
            case 'UPDATE_ORDER':
                state.orderId = event.data.orderId;
                pollOrderUpdates();
                break;
            case 'UPDATE_PAYMENT_METHOD':
                handleMethodSelected({ method: event.data.method, amount: 0, orderId: state.orderId });
                break;
            case 'UPDATE_PAYMENT_AMOUNT':
                const el = document.getElementById('paymentAmountDisplay');
                if (el) el.textContent = formatCurrency(event.data.amount);
                break;
            case 'RESET_TO_IDLE':
                returnToIdleMode();
                break;
        }
    });

    // ── Order display ─────────────────────────────────────────────────────────
    function updateOrderDisplay(order) {
        if (!order) return;
        try {
            const key = JSON.stringify({
                id: order.id, total: order.total,
                payment_status: order.payment_status,
                updated_at: order.updated_at,
                payment_method: order.payment_method,
                amount_received: order.amount_received,
                change_amount: order.change_amount
            });
            if (key === state.lastUpdate) return;
            state.lastUpdate = key;

            // Switch panels — hide ads + idle, show split order layout
            hideAds();
            document.getElementById('idleState').style.display    = 'none';
            document.getElementById('orderContent').style.display = 'flex';

            // Header
            const orderNumber = document.getElementById('orderNumber');
            const orderTime   = document.getElementById('orderTime');
            if (orderNumber) orderNumber.textContent = order.order_number || order.id;
            if (orderTime)   orderTime.textContent   = new Date().toLocaleTimeString('en-US', { hour12:true });

            // Items
            const itemsContainer = document.getElementById('orderItems');
            if (itemsContainer) {
                const items = order.items || order.order_items || [];
                if (items.length > 0) {
                    itemsContainer.innerHTML = items.map(item => {
                        const name = item.product?.name || item.item_name || item.name || 'Unknown Item';
                        return `<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#f9fafb;border-radius:10px;">
                            <div>
                                <div style="font-weight:700;font-size:15px;color:#1f2937;">${name}</div>
                                <div style="font-size:13px;color:#6b7280;">${item.quantity} × ${formatCurrency(item.price)}</div>
                            </div>
                            <div style="font-weight:700;font-size:15px;color:#1f2937;">${formatCurrency(item.subtotal)}</div>
                        </div>`;
                    }).join('');
                } else {
                    itemsContainer.innerHTML = '<div style="text-align:center;color:#9ca3af;padding:20px;">No items found</div>';
                }
            }

            // Totals
            const subtotalEl    = document.getElementById('subtotal');
            const totalEl       = document.getElementById('total');
            const totalAmountEl = document.getElementById('totalAmount');
            const taxEl         = document.getElementById('tax');
            if (subtotalEl)    subtotalEl.textContent    = formatCurrency(order.subtotal);
            if (totalEl)       totalEl.textContent       = formatCurrency(order.total);
            if (totalAmountEl) totalAmountEl.textContent = formatCurrency(order.total);
            const tax = (order.total - order.subtotal) || (order.total * 0.1);
            if (taxEl) taxEl.textContent = formatCurrency(tax);

            // Payment status badge
            const paymentStatus = document.getElementById('paymentStatus');
            if (paymentStatus) {
                const isPaid    = order.payment_status === 'paid';
                const color     = isPaid ? '#16a34a' : '#d97706';
                const bgColor   = isPaid ? '#f0fdf4'  : '#fffbeb';
                const icon      = isPaid ? 'fa-check-circle' : 'fa-clock';
                const statusTxt = isPaid ? 'Paid' : 'Awaiting';
                paymentStatus.innerHTML = `<span style="display:inline-flex;align-items:center;gap:6px;background:${bgColor};color:${color};border-radius:20px;padding:4px 12px;font-size:13px;font-weight:700;">
                    <i class="fas ${icon}"></i> ${statusTxt}</span>`;
            }

            // Payment method highlight + cash rows
            const methodDisp = document.getElementById('paymentMethodDisplay');
            const amountDisp = document.getElementById('paymentAmountDisplay');
            const cashDetails = document.getElementById('cashPaymentDetails');

            if (order.payment_method) {
                highlightMethodButton(order.payment_method);
                if (methodDisp) methodDisp.textContent = order.payment_method.toUpperCase();
                showPaymentInstructions(order.payment_method);
            }
            if (amountDisp) amountDisp.textContent = formatCurrency(order.total);

            if (cashDetails) {
                if (order.payment_method === 'cash') {
                    cashDetails.style.display = 'flex';
                    const rcvd   = document.getElementById('amountReceivedDisplay');
                    const change = document.getElementById('changeAmountDisplay');
                    if (rcvd   && order.amount_received) rcvd.textContent   = formatCurrency(order.amount_received);
                    if (change && order.change_amount)   change.textContent = formatCurrency(order.change_amount);
                } else {
                    cashDetails.style.display = 'none';
                }
            }

            // Payment complete → success screen
            if (order.payment_status === 'paid') {
                playPaymentSuccess();
                closeViewerPopup();
                showViewerPaymentSuccess(order.payment_method || '', order.total || 0);
            }

        } catch (error) {
            playErrorSound();
            console.error('Error updating order display:', error);
        }
    }

    // ── Polling ───────────────────────────────────────────────────────────────
    async function pollOrderUpdates() {
        try {
            if (!state.orderId) return;
            const response = await fetch(`/api/customer/active-order?order=${state.orderId}&branch=${state.branchId}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            if (data.order) {
                updateOrderDisplay(data.order);
            } else if (!state.orderId) {
                document.getElementById('idleState').style.display    = 'flex';
                document.getElementById('orderContent').style.display = 'none';
            }
        } catch (error) {
            console.error('Poll error:', error);
        }
    }

    async function initializeOrderDisplay() {
        const urlParams   = new URLSearchParams(window.location.search);
        state.orderId     = urlParams.get('order');
        state.branchId    = urlParams.get('branch');
        if (!state.branchId) {
            const orderItems = document.getElementById('orderItems');
            if (orderItems) orderItems.innerHTML = '<div style="color:#ef4444;text-align:center;padding:20px;">Error: Branch ID required</div>';
            return;
        }
        await pollOrderUpdates();
        state.pollInterval = setInterval(pollOrderUpdates, 5000);
    }

    window.addEventListener('beforeunload', () => {
        if (state.pollInterval) clearInterval(state.pollInterval);
    });

    document.addEventListener('DOMContentLoaded', function() {
        initializeOrderDisplay();
        initPusher();
        loadAds();
    });

    // ── Pusher real-time listener ─────────────────────────────────────────────
    function initPusher() {
        const pusherKey     = '{{ config("broadcasting.connections.pusher.key") }}';
        const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster", "mt1") }}';
        if (!pusherKey) {
            console.log('[Pusher] No key configured — polling only.');
            return;
        }
        const pusher   = new Pusher(pusherKey, { cluster: pusherCluster });
        const branchId = new URLSearchParams(window.location.search).get('branch');
        const channel  = pusher.subscribe('payment.' + branchId);

        channel.bind('App\\Events\\PaymentMethodSelected', handleMethodSelected);
        channel.bind('method.selected',                    handleMethodSelected);
        channel.bind('App\\Events\\PaymentCompleted',      handlePaymentCompleted);
        channel.bind('payment.completed',                  handlePaymentCompleted);

        pusher.connection.bind('connected', () => console.log('[Pusher] Connected — branch', branchId));
        pusher.connection.bind('error',     e  => console.warn('[Pusher] Error', e));
    }

    function handleMethodSelected(data) {
        // Manager broadcast when a paid order was clicked — return to idle
        if (data.method === 'reset') {
            returnToIdleMode();
            return;
        }
        if (state.orderId && parseInt(data.orderId) !== parseInt(state.orderId)) return;
        const method = data.method || '';
        const amount = data.amount || 0;

        highlightMethodButton(method);
        const display      = document.getElementById('paymentMethodDisplay');
        const amountDisplay = document.getElementById('paymentAmountDisplay');
        if (display)      display.textContent      = method.toUpperCase();
        if (amountDisplay) amountDisplay.textContent = formatCurrency(amount);
        showPaymentInstructions(method);

        if (['khalti', 'esewa', 'wallet', 'mobile'].includes(method)) {
            showCustomerQRPopup(method, amount, data.orderId);
        } else {
            closeViewerPopup();
        }
    }

    function handlePaymentCompleted(data) {
        if (state.orderId && parseInt(data.orderId) !== parseInt(state.orderId)) return;
        closeViewerPopup();
        playPaymentSuccess();
        showViewerPaymentSuccess(data.method || '', data.amount || 0);
    }

    // ── FIX 3: QR popup using #viewerQRPopup ─────────────────────────────────
    function showCustomerQRPopup(method, amount, orderId) {
        const icons  = { khalti:'🟣', esewa:'🟢', wallet:'🥟', mobile:'📲' };
        const titles = { khalti:'Scan to Pay — Khalti', esewa:'Scan to Pay — eSewa',
                         wallet:'MA:MO Credits',         mobile:'Mobile Banking' };
        const colors = { khalti:'#5c2d91', esewa:'#00a650', wallet:'#dc2626', mobile:'#0891b2' };

        document.getElementById('viewerPopupIcon').textContent   = icons[method]  || '💳';
        document.getElementById('viewerPopupTitle').textContent  = titles[method] || method.toUpperCase();
        document.getElementById('viewerPopupTitle').style.color  = colors[method] || '#374151';
        document.getElementById('viewerPopupAmount').textContent = formatCurrency(amount);
        document.getElementById('viewerPopupAmount').style.color = colors[method] || '#374151';

        const notes = {
            wallet: 'Please provide your MA:MO account number to the cashier.',
            mobile: 'Transfer to the account above and tell the cashier your reference ID.',
            khalti: 'Scan the QR code with your Khalti app to pay.',
            esewa:  'Scan the QR code with your eSewa app to pay.'
        };
        document.getElementById('viewerPopupNote').textContent = notes[method] || '';

        let bodyHtml = '';
        if (['khalti', 'esewa'].includes(method)) {
            bodyHtml = `<div id="viewerQrCanvas"
                style="width:200px;height:200px;margin:0 auto 14px;background:#f3f4f6;
                border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#999;font-size:13px;">Generating QR…</span>
            </div>`;
        } else if (method === 'mobile') {
            bodyHtml = `<div style="background:#f0f9ff;border-radius:10px;padding:12px;
                text-align:left;font-size:14px;margin:0 auto 12px;width:260px;">
                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #e0f2fe;">
                    <span style="color:#666;">Bank</span><strong id="vBankName">…</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #e0f2fe;">
                    <span style="color:#666;">Account</span><strong id="vBankAcct">…</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:4px 0;">
                    <span style="color:#666;">Name</span><strong id="vBankName2">…</strong>
                </div>
            </div>`;
        }
        document.getElementById('viewerPopupBody').innerHTML = bodyHtml;

        const popup = document.getElementById('viewerQRPopup');
        popup.style.display = 'flex';

        // Generate QR after DOM insert
        if (['khalti', 'esewa'].includes(method)) {
            setTimeout(() => {
                const el = document.getElementById('viewerQrCanvas');
                if (el && typeof QRCode !== 'undefined') {
                    el.innerHTML = '';
                    const qrText = method === 'khalti'
                        ? `khalti://pay?amount=${amount}&ref=${orderId}`
                        : `esewa://pay?amount=${amount}&ref=${orderId}`;
                    new QRCode(el, { text: qrText, width: 200, height: 200,
                                     colorDark: '#000', colorLight: '#fff',
                                     correctLevel: QRCode.CorrectLevel.H });
                }
            }, 50);
        } else if (method === 'mobile') {
            fetch('/admin/payments/payment-info')
                .then(r => r.json())
                .then(info => {
                    const n  = document.getElementById('vBankName');
                    const a  = document.getElementById('vBankAcct');
                    const n2 = document.getElementById('vBankName2');
                    if (n)  n.textContent  = info.bank_name         || '—';
                    if (a)  a.textContent  = info.bank_account      || '—';
                    if (n2) n2.textContent = info.bank_account_name || '—';
                }).catch(() => {});
        }
    }

    function closeViewerPopup() {
        const popup = document.getElementById('viewerQRPopup');
        if (popup) popup.style.display = 'none';
    }

    function showViewerPaymentSuccess(method, amount) {
        closeViewerPopup();
        const screen = document.createElement('div');
        screen.style.cssText = `position:fixed;top:0;left:0;width:100%;height:100%;
            background:#f0fdf4;z-index:999999;
            display:flex;flex-direction:column;align-items:center;justify-content:center;`;
        screen.innerHTML = `
            <div style="font-size:90px;margin-bottom:20px;animation:pop 0.4s ease;">✅</div>
            <h1 style="color:#16a34a;font-size:40px;margin:0 0 10px;font-weight:900;">Payment Successful!</h1>
            <p style="color:#166534;font-size:28px;font-weight:700;margin:0 0 6px;">${formatCurrency(amount)}</p>
            <p style="color:#166534;font-size:18px;margin:0 0 30px;">Thank you for dining with us!</p>`;
        document.body.appendChild(screen);
        setTimeout(() => {
            screen.remove();
            document.getElementById('orderContent').style.display = 'none';
            state.orderId    = null;
            state.lastUpdate = null;
            document.getElementById('orderContent').style.display = 'none';
            // Return to ads if available, otherwise idle welcome screen
            showAds(); // showAds() falls back to idleState when _ads is empty
        }, 12000);
    }

    // ── Ad Player ────────────────────────────────────────────────────────────
    let _ads = [];
    let _adIndex = 0;
    let _adActive = false;
    let _adTimeout = null;

    async function loadAds() {
        const branchId = {{ request('branch', 0) }};
        if (!branchId) return;
        try {
            const res = await fetch(`/api/display-ads/${branchId}`);
            if (!res.ok) return;
            _ads = await res.json();
            if (_ads.length > 0) showAds();
        } catch (e) {
            console.log('[Ads] Load failed:', e);
        }
    }

    function showAds() {
        if (_ads.length === 0) {
            // No ads — fall back to idle welcome screen
            document.getElementById('idleState').style.display    = 'flex';
            document.getElementById('orderContent').style.display = 'none';
            document.getElementById('adPlayer').style.display     = 'none';
            return;
        }
        _adActive = true;
        document.getElementById('adPlayer').style.display     = 'block';
        document.getElementById('idleState').style.display    = 'none';
        document.getElementById('orderContent').style.display = 'none';
        _showCurrentAd();
    }

    function hideAds() {
        _adActive = false;
        if (_adTimeout) clearTimeout(_adTimeout);
        const ap = document.getElementById('adPlayer');
        if (ap) {
            ap.innerHTML     = '';
            ap.style.display = 'none';
        }
    }

    // Return the viewer to idle/ads — called by button or RESET_TO_IDLE message
    function returnToIdleMode() {
        closeViewerPopup();
        state.orderId    = null;
        state.lastUpdate = null;
        if (state.pollInterval) clearInterval(state.pollInterval);
        document.getElementById('orderContent').style.display = 'none';
        const totalEl = document.getElementById('total');
        if (totalEl) totalEl.textContent = 'Rs 0.00';
        showAds(); // falls back to idleState when no ads
    }

    function _showCurrentAd() {
        if (!_adActive || _ads.length === 0) return;
        const ad = _ads[_adIndex];
        const ap = document.getElementById('adPlayer');
        if (!ap) return;

        if (_adTimeout) clearTimeout(_adTimeout);

        if (ad.type === 'upload' && ad.file_path) {
            ap.innerHTML = `<video autoplay muted playsinline
                style="width:100%;height:100%;object-fit:cover;display:block;pointer-events:none;"
                onended="_nextAd()">
                <source src="${ad.file_path}" type="video/mp4">
            </video>`;
        } else if (ad.embed_url) {
            // Extract clean base URL (strip any params the model accessor added)
            const baseUrl = ad.embed_url.split('?')[0];
            const isYT    = baseUrl.includes('youtube.com');

            let src;
            if (isYT) {
                // Extract video ID from path: .../embed/VIDEO_ID
                const videoId = baseUrl.match(/\/embed\/([^/?]+)/)?.[1] || '';
                src = `${baseUrl}?autoplay=1&mute=1&controls=0&loop=1` +
                      `&playlist=${videoId}&modestbranding=1&showinfo=0` +
                      `&rel=0&iv_load_policy=3&disablekb=1&fs=0&playsinline=1`;
            } else {
                // Vimeo
                src = `${baseUrl}?autoplay=1&muted=1&loop=1&background=1`;
            }

            ap.innerHTML = `<iframe src="${src}"
                style="width:100%;height:100%;border:none;display:block;pointer-events:none;"
                allow="autoplay;encrypted-media"
                allowfullscreen></iframe>`;
            // Advance after 30 s (can't detect onended cross-origin)
            _adTimeout = setTimeout(_nextAd, 30000);
        }
    }

    function _nextAd() {
        _adIndex = (_adIndex + 1) % _ads.length;
        _showCurrentAd();
    }

    // Override updateOrderDisplay to hide ads when order arrives
    const _origUpdateOrderDisplay = updateOrderDisplay;
    // (patched below — we use a wrapper approach)

    // ── End Ad Player ─────────────────────────────────────────────────────────

    // ── SoundManager ─────────────────────────────────────────────────────────
    class SoundManager {
        constructor() {
            this.audioContext = null;
            this.isMuted = false;
            this.volume  = 0.7;
            this.initializeAudioContext();
            this.loadUserPreferences();
        }
        initializeAudioContext() {
            try { this.audioContext = new (window.AudioContext || window.webkitAudioContext)(); }
            catch (e) { console.log('Web Audio not supported:', e); }
        }
        loadUserPreferences() {
            const sv = localStorage.getItem('paymentViewerVolume');
            const sm = localStorage.getItem('paymentViewerMuted');
            if (sv !== null) this.volume  = parseFloat(sv);
            if (sm !== null) this.isMuted = JSON.parse(sm);
        }
        saveUserPreferences() {
            localStorage.setItem('paymentViewerVolume', this.volume.toString());
            localStorage.setItem('paymentViewerMuted',  this.isMuted.toString());
        }
        playTone(frequency, duration, type = 'sine') {
            if (this.isMuted || !this.audioContext) return;
            try {
                const osc  = this.audioContext.createOscillator();
                const gain = this.audioContext.createGain();
                osc.connect(gain);
                gain.connect(this.audioContext.destination);
                osc.frequency.setValueAtTime(frequency, this.audioContext.currentTime);
                osc.type = type;
                gain.gain.setValueAtTime(0, this.audioContext.currentTime);
                gain.gain.linearRampToValueAtTime(this.volume * 0.3, this.audioContext.currentTime + 0.01);
                gain.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + duration);
                osc.start(this.audioContext.currentTime);
                osc.stop(this.audioContext.currentTime + duration);
            } catch (e) { console.log('Tone failed:', e); }
        }
        playSound(name) {
            if (this.isMuted) return;
            switch (name) {
                case 'paymentSuccess':
                    this.playTone(523.25, 0.2, 'sine');
                    setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100);
                    setTimeout(() => this.playTone(783.99, 0.3, 'sine'), 200);
                    break;
                case 'paymentFailed':
                    this.playTone(783.99, 0.2, 'sine');
                    setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100);
                    setTimeout(() => this.playTone(523.25, 0.3, 'sine'), 200);
                    break;
                case 'select': this.playTone(660, 0.08, 'triangle'); break;
                case 'scan':   this.playTone(880, 0.1,  'square');   break;
                case 'error':  this.playTone(220, 0.15, 'sawtooth'); break;
            }
        }
        setVolume(v) { this.volume = Math.max(0, Math.min(1, v)); this.saveUserPreferences(); }
        toggleMute() {
            this.isMuted = !this.isMuted;
            this.saveUserPreferences();
            this.updateMuteButton();
        }
        updateMuteButton() {
            const btn = document.getElementById('soundMuteBtn');
            if (!btn) return;
            btn.querySelector('i').className = this.isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
            btn.title = this.isMuted ? 'Unmute sounds' : 'Mute sounds';
        }
    }

    let soundManager;
    document.addEventListener('DOMContentLoaded', function() {
        soundManager = new SoundManager();
        soundManager.updateMuteButton();
        document.getElementById('soundVolumeSlider').value = Math.round(soundManager.volume * 100);
        document.addEventListener('click', function initAudio() {
            if (soundManager?.audioContext?.state === 'suspended') soundManager.audioContext.resume();
            document.removeEventListener('click', initAudio);
        }, { once: true });
    });

    function _resumeAudio() {
        if (soundManager?.audioContext?.state === 'suspended') soundManager.audioContext.resume();
    }
    function playPaymentSuccess() { _resumeAudio(); soundManager?.playSound('paymentSuccess'); }
    function playPaymentFailed()  { _resumeAudio(); soundManager?.playSound('paymentFailed');  }
    function playSelectSound()    { _resumeAudio(); soundManager?.playSound('select');          }
    function playScanSound()      { _resumeAudio(); soundManager?.playSound('scan');            }
    function playErrorSound()     { _resumeAudio(); soundManager?.playSound('error');           }
    function toggleSoundMute()    { soundManager?.toggleMute();       }
    function setSoundVolume(v)    { soundManager?.setVolume(v);       }
    // ── End SoundManager ──────────────────────────────────────────────────────

    // ── Fullscreen + cursor hiding ────────────────────────────────────────────
    function _enterFullscreen() {
        const el = document.documentElement;
        const fn = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
        if (fn) fn.call(el).catch(() => {});
    }

    // First click: enter fullscreen AND resume audio context
    document.addEventListener('click', function onFirstClick() {
        _enterFullscreen();
        document.removeEventListener('click', onFirstClick);

        // After fullscreen, re-attach a click handler that re-enters if user ever exits
        document.addEventListener('click', function() {
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                _enterFullscreen();
            }
        });
    }, { once: true });

    // Auto-attempt fullscreen 1 second after load (works if page was opened via user gesture)
    setTimeout(_enterFullscreen, 1000);

    // Hide cursor after 3 seconds of inactivity
    let _cursorTimer = null;
    document.addEventListener('mousemove', function() {
        document.body.style.cursor = 'default';
        clearTimeout(_cursorTimer);
        _cursorTimer = setTimeout(function() {
            document.body.style.cursor = 'none';
        }, 3000);
    });
</script>
</body>
</html>
