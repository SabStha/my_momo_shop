@php
    $cashDrawer = $cashDrawer ?? null;
    $drawerOpen = $cashDrawer && $cashDrawer->status === 'open';
    $balance    = $cashDrawer ? number_format($cashDrawer->current_balance ?? 0, 2) : '0.00';
@endphp
<div class="bg-white border-b border-gray-200 px-4 py-2 flex items-center gap-4 text-sm flex-wrap flex-shrink-0">
    {{-- Status badge — JS updates textContent + className via id="drawerStatusIndicator" --}}
    <span id="drawerStatusIndicator"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $drawerOpen ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
        {{ $drawerOpen ? 'Open' : 'Closed' }}
    </span>

    <span class="font-medium text-gray-700">Cash Drawer</span>

    <span class="text-gray-400">|</span>

    <span class="text-gray-600">Balance: Rs <span id="drawerBalance">{{ $balance }}</span></span>

    @if($drawerOpen && $cashDrawer->created_at)
        <span class="text-gray-400">|</span>
        <span class="text-gray-600">Started: {{ $cashDrawer->created_at->format('h:i A') }}</span>
    @endif

    {{-- Push buttons to the right --}}
    <div class="ml-auto flex items-center gap-2">
        {{-- Open button: JS hides this when drawer is open (id="openDrawerBtn") --}}
        <button id="openDrawerBtn"
                onclick="if(typeof showCashDrawerModal === 'function') showCashDrawerModal('open')"
                style="{{ $drawerOpen ? 'display:none' : '' }}"
                class="px-3 py-1.5 bg-green-600 text-white rounded text-xs font-medium hover:bg-green-700 transition-colors">
            <i class="fas fa-lock-open mr-1"></i> Open Drawer
        </button>

        {{-- Close button: JS hides this when drawer is closed (id="closeDrawerBtn") --}}
        <button id="closeDrawerBtn"
                onclick="if(typeof showCashDrawerModal === 'function') showCashDrawerModal('close')"
                style="{{ $drawerOpen ? '' : 'display:none' }}"
                class="px-3 py-1.5 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700 transition-colors">
            <i class="fas fa-lock mr-1"></i> Close Drawer
        </button>

        @if($drawerOpen)
        <button onclick="if(typeof showCashAdjustmentModal === 'function') showCashAdjustmentModal()"
                class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition-colors">
            <i class="fas fa-money-bill-wave mr-1"></i> Adjust
        </button>
        @endif

        <button onclick="if(typeof openPhysicalCashDrawer === 'function') openPhysicalCashDrawer()"
                class="px-3 py-1.5 bg-yellow-500 text-white rounded text-xs font-medium hover:bg-yellow-600 transition-colors"
                title="Pop physical cash drawer">
            <i class="fas fa-door-open mr-1"></i> Pop
        </button>
    </div>
</div>
