@php /* Payment Panel Partial — Speed-optimised cashier UI */ @endphp
<div id="paymentPanel" class="w-full bg-white shadow-lg border-l border-gray-200 flex flex-col"
     style="height:calc(100vh - 56px);overflow:hidden;">

  {{-- ── TOP BAR ──────────────────────────────────────────────────────────── --}}
  <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-2">
      <button id="backToOrdersBtn" onclick="backToOrdersList()"
              class="md:hidden text-gray-500 hover:text-gray-700 hidden text-sm">
        <i class="fas fa-arrow-left mr-1"></i> Orders
      </button>
      <span class="text-sm font-semibold text-gray-700">Payment</span>
    </div>
    <div class="flex items-center gap-2">
      <button id="returnToDisplayBtn"
              onclick="if(typeof resetPaymentViewer==='function') resetPaymentViewer()"
              style="padding:4px 10px;background:#6366f1;color:white;border:none;
                     border-radius:6px;font-size:12px;cursor:pointer;white-space:nowrap;">
        📺 Ads
      </button>
      <button id="openPaymentViewerBtn"
              class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-medium">
        <i class="fas fa-external-link-alt mr-1"></i> Viewer
      </button>
      <button id="closePaymentPanel" class="text-gray-400 hover:text-gray-600 text-lg leading-none">×</button>
    </div>
  </div>

  {{-- ── NO ORDER SELECTED ──────────────────────────────────────────────── --}}
  <div id="noOrderSelected"
       class="flex-1 flex flex-col items-center justify-center text-gray-400 select-none">
    <div class="text-5xl mb-3">👈</div>
    <p class="text-base">Select an order to process payment</p>
  </div>

  {{-- ── ORDER PANEL (shown by JS when order selected) ─────────────────── --}}
  {{-- paymentMethodsSection: JS sets display:flex / display:none           --}}
  <div id="paymentMethodsSection"
       style="display:none;height:100%;flex-direction:column;overflow:hidden;">

    {{-- A: Order header ─────────────────────────────────────────────────── --}}
    <div id="orderSummary" style="flex-shrink:0;padding:10px 16px;border-bottom:1px solid #e5e7eb;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <span id="orderNumber" style="font-size:14px;font-weight:700;color:#111827;">#---</span>
            <span id="orderTableBadge" style="font-size:12px;color:#6b7280;"></span>
            <span id="orderStatusBadge"
                  style="font-size:11px;padding:2px 6px;border-radius:4px;background:#f3f4f6;color:#6b7280;"></span>
          </div>
          <p id="orderItemsList"
             style="font-size:11px;color:#9ca3af;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;"></p>
        </div>
        <div style="text-align:right;flex-shrink:0;">
          <div id="orderTotal" style="font-size:22px;font-weight:900;color:#111827;line-height:1.1;">Rs 0</div>
          <div id="paymentStatusBadge" style="font-size:11px;margin-top:2px;"></div>
        </div>
      </div>
    </div>

    {{-- Order workflow actions (accept / mark-ready etc.) ────────────────── --}}
    <div id="orderActions" class="hidden"
         style="flex-shrink:0;padding:8px 16px;border-bottom:1px solid #f3f4f6;"></div>

    {{-- B: Order items detail — fills available space, scrolls internally ─── --}}
    <div id="orderDetailSection"
         style="flex:1;min-height:0;overflow-y:auto;
                padding:8px 16px;border-bottom:1px solid #e5e7eb;
                font-size:12px;color:#374151;">
    </div>

    {{-- C: Payment method buttons ───────────────────────────────────────── --}}
    <div style="flex-shrink:0;padding:12px 16px;">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">

        <button type="button" id="tab-cash" onclick="selectPaymentMethod('cash')"
                style="padding:8px 6px;border-radius:10px;border:2px solid #e5e7eb;min-height:60px;
                       background:white;cursor:pointer;text-align:center;transition:all 0.2s;
                       display:flex;flex-direction:column;align-items:center;gap:3px;">
          <span style="font-size:22px">💵</span>
          <span style="font-size:13px;font-weight:700;color:#374151;">Cash</span>
        </button>

        <button type="button" id="tab-card" onclick="selectPaymentMethod('card')"
                style="padding:8px 6px;border-radius:10px;border:2px solid #e5e7eb;min-height:60px;
                       background:white;cursor:pointer;text-align:center;transition:all 0.2s;
                       display:flex;flex-direction:column;align-items:center;gap:3px;">
          <span style="font-size:22px">💳</span>
          <span style="font-size:13px;font-weight:700;color:#374151;">Card</span>
        </button>

        <button type="button" id="tab-khalti" onclick="selectPaymentMethod('khalti')"
                style="padding:8px 6px;border-radius:10px;border:2px solid #e5e7eb;min-height:60px;
                       background:white;cursor:pointer;text-align:center;transition:all 0.2s;
                       display:flex;flex-direction:column;align-items:center;gap:3px;">
          <span style="font-size:22px">🟣</span>
          <span style="font-size:13px;font-weight:700;color:#374151;">Khalti</span>
        </button>

        <button type="button" id="tab-wallet" onclick="selectPaymentMethod('wallet')"
                style="padding:8px 6px;border-radius:10px;border:2px solid #e5e7eb;min-height:60px;
                       background:white;cursor:pointer;text-align:center;transition:all 0.2s;
                       display:flex;flex-direction:column;align-items:center;gap:3px;">
          <span style="font-size:22px">🥟</span>
          <span style="font-size:13px;font-weight:700;color:#374151;">MA:MO Credits</span>
        </button>

        <button type="button" id="tab-mobile" onclick="selectPaymentMethod('mobile')"
                style="padding:10px 8px;border-radius:10px;border:2px solid #e5e7eb;
                       background:white;cursor:pointer;text-align:center;transition:all 0.2s;
                       display:flex;flex-direction:column;align-items:center;gap:4px;
                       grid-column:span 2;min-height:60px;">
          <span style="font-size:22px">📲</span>
          <span style="font-size:13px;font-weight:700;color:#374151;">Mobile Banking</span>
        </button>

      </div>

      {{-- Inline fields for non-cash methods (shown when method selected) --}}

      {{-- cashFields: kept in DOM so cashAmountInput exists for processPaymentManager --}}
      <div id="cashFields" class="hidden">
        <input type="number" id="cashAmountInput" value="0" style="position:absolute;opacity:0;pointer-events:none;" tabindex="-1">
      </div>

      {{-- ── CARD ──────────────────────────────────────────────────────────── --}}
      <div id="cardFields" class="hidden" style="margin-top:10px;">
        <input type="text" id="cardReferenceNumber"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-400"
               placeholder="Card reference / approval code (optional)">
      </div>

      {{-- ── WALLET ────────────────────────────────────────────────────────── --}}
      <div id="walletFields" class="hidden" style="margin-top:10px;">
        <div class="flex gap-2">
          <input type="text" id="walletNumber"
                 class="flex-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                        focus:outline-none focus:ring-2 focus:ring-blue-400"
                 placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19">
          <button type="button" id="scanWalletBtn"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm">
            <i class="fas fa-qrcode"></i>
          </button>
        </div>
        <div id="walletBalanceDisplay" class="hidden" style="margin-top:6px;">
          <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-sm text-green-800">
            Balance: <span id="walletBalance" class="font-bold">Rs 0.00</span>
          </div>
        </div>
      </div>

      {{-- ── KHALTI ────────────────────────────────────────────────────────── --}}
      <div id="khaltiFields" class="hidden" style="margin-top:10px;">
        <input type="text" id="khaltiTransactionId"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:ring-2 focus:ring-purple-400"
               placeholder="Khalti transaction ID">
      </div>

      {{-- ── MOBILE ────────────────────────────────────────────────────────── --}}
      <div id="mobileFields" class="hidden" style="margin-top:10px;">
        <input type="text" id="mobileReferenceNumber"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-400"
               placeholder="Mobile transaction reference">
      </div>

    </div>{{-- /payment methods C --}}

    {{-- Add note link ────────────────────────────────────────────────────── --}}
    <div style="flex-shrink:0;padding:4px 16px;">
      <button id="addNoteBtn" onclick="openNotePopup()"
              style="background:none;border:none;cursor:pointer;font-size:13px;
                     color:#6b7280;padding:2px 0;">
        + Add note
      </button>
    </div>

    {{-- D: Process button — pinned to bottom ────────────────────────────── --}}
    <div style="flex-shrink:0;padding:12px 16px;background:white;border-top:1px solid #e5e7eb;">
      <button type="button" id="processPaymentBtn"
              style="width:100%;padding:14px;font-size:16px;font-weight:700;
                     background:#d1d5db;color:white;border:none;border-radius:10px;
                     cursor:not-allowed;transition:all 0.2s;"
              disabled>
        ✓ Process Payment · Rs <span id="paymentTotal">0</span>
      </button>
    </div>

  </div>{{-- /paymentMethodsSection --}}

</div>{{-- /paymentPanel --}}

{{-- ── NOTE POPUP ──────────────────────────────────────────────────────────── --}}
<div id="notePopup" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
     background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:12px;padding:24px;max-width:400px;width:90%;">
    <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;color:#111827;">Add Payment Note</h3>
    <textarea id="noteTextarea" rows="4"
      style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:8px;
             font-size:14px;resize:none;box-sizing:border-box;"
      placeholder="Optional note about this payment..."></textarea>
    <div style="display:flex;gap:8px;margin-top:12px;">
      <button onclick="saveNote()"
              style="flex:1;padding:10px;background:#4f46e5;color:white;border:none;
                     border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;">
        Save Note</button>
      <button onclick="closeNotePopup()"
              style="flex:1;padding:10px;background:#f3f4f6;border:none;
                     border-radius:8px;cursor:pointer;font-size:14px;">
        Cancel</button>
    </div>
  </div>
</div>
