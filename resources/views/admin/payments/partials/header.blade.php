@php
    $cashDrawer = $cashDrawer ?? null;
    $drawerOpen = $cashDrawer && $cashDrawer->status === 'open';
    $balance    = $cashDrawer ? number_format($cashDrawer->current_balance ?? 0, 2) : '0.00';
@endphp
<header style="height:56px;display:flex;align-items:center;padding:0 16px;
               border-bottom:1px solid #e5e7eb;flex-shrink:0;gap:12px;
               background:white;overflow:visible;position:relative;z-index:100;">

    {{-- ← Dashboard --}}
    <a href="{{ route('admin.dashboard.branch', ['branch' => $branch->id ?? 1]) }}"
       style="display:flex;align-items:center;gap:6px;color:#6b7280;font-size:14px;
              text-decoration:none;white-space:nowrap;flex-shrink:0;"
       onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#6b7280'">
        <i class="fas fa-arrow-left" style="font-size:11px;"></i> Dashboard
    </a>

    <span style="color:#d1d5db;flex-shrink:0;">|</span>

    {{-- Title --}}
    <span style="font-weight:600;font-size:15px;color:#111827;white-space:nowrap;flex-shrink:0;">
        Payment Management{{ isset($branch) ? ' · ' . $branch->name : '' }}
    </span>

    {{-- Cash drawer section — fills middle --}}
    <div style="flex:1;display:flex;align-items:center;gap:10px;min-width:0;padding:0 4px;">

        <span id="drawerStatusIndicator"
              style="display:inline-flex;align-items:center;padding:2px 10px;border-radius:20px;
                     font-size:12px;font-weight:600;flex-shrink:0;
                     background:{{ $drawerOpen ? '#dcfce7' : '#fee2e2' }};
                     color:{{ $drawerOpen ? '#16a34a' : '#dc2626' }};">
            {{ $drawerOpen ? 'Open' : 'Closed' }}
        </span>

        <span style="font-size:14px;color:#374151;white-space:nowrap;flex-shrink:0;">Cash Drawer</span>

        <span style="color:#d1d5db;flex-shrink:0;">|</span>

        <span style="font-size:14px;color:#374151;white-space:nowrap;flex-shrink:0;">
            Balance: Rs <span id="drawerBalance">{{ $balance }}</span>
        </span>

        @if($drawerOpen && $cashDrawer->created_at)
        <span style="color:#d1d5db;flex-shrink:0;">|</span>
        <span style="font-size:13px;color:#9ca3af;white-space:nowrap;flex-shrink:0;">
            Since {{ $cashDrawer->created_at->format('h:i A') }}
        </span>
        @endif

        {{-- Open Drawer (shown when closed) --}}
        <button id="openDrawerBtn"
                onclick="if(typeof showCashDrawerModal==='function') showCashDrawerModal('open')"
                style="display:{{ $drawerOpen ? 'none' : 'inline-flex' }};align-items:center;gap:4px;
                       padding:4px 10px;background:#16a34a;color:white;border:none;
                       border-radius:6px;font-size:13px;cursor:pointer;flex-shrink:0;white-space:nowrap;">
            <i class="fas fa-lock-open" style="font-size:11px;"></i> Open Drawer
        </button>

        {{-- Close Drawer (shown when open) --}}
        <button id="closeDrawerBtn"
                onclick="if(typeof showCashDrawerModal==='function') showCashDrawerModal('close')"
                style="display:{{ $drawerOpen ? 'inline-flex' : 'none' }};align-items:center;gap:4px;
                       padding:4px 10px;background:#dc2626;color:white;border:none;
                       border-radius:6px;font-size:13px;cursor:pointer;flex-shrink:0;white-space:nowrap;">
            <i class="fas fa-lock" style="font-size:11px;"></i> Close Drawer
        </button>

        {{-- Adjust (shown when open) --}}
        @if($drawerOpen)
        <button onclick="if(typeof showCashAdjustmentModal==='function') showCashAdjustmentModal()"
                style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;
                       background:#3b82f6;color:white;border:none;border-radius:6px;
                       font-size:13px;cursor:pointer;flex-shrink:0;white-space:nowrap;">
            <i class="fas fa-money-bill-wave" style="font-size:11px;"></i> Adjust
        </button>
        @endif

        {{-- Pop physical drawer --}}
        <button onclick="if(typeof openPhysicalCashDrawer==='function') openPhysicalCashDrawer()"
                style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;
                       background:#f59e0b;color:white;border:none;border-radius:6px;
                       font-size:13px;cursor:pointer;flex-shrink:0;white-space:nowrap;"
                title="Pop physical cash drawer">
            <i class="fas fa-door-open" style="font-size:11px;"></i> Pop
        </button>

    </div>

    {{-- ☰ Hamburger --}}
    <div style="position:relative;flex-shrink:0;">
        <button onclick="toggleHeaderMenu()"
                style="width:36px;height:36px;border:none;background:none;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;
                       border-radius:8px;color:#6b7280;font-size:18px;"
                onmouseover="this.style.background='#f3f4f6'"
                onmouseout="this.style.background='none'">
            <i class="fas fa-bars"></i>
        </button>

        <div id="headerMenu"
             style="display:none;position:absolute;right:0;top:44px;background:white;
                    border:1px solid #e5e7eb;border-radius:10px;padding:12px;
                    min-width:240px;box-shadow:0 4px 16px rgba(0,0,0,0.12);z-index:9999;">

            {{-- Sound --}}
            <div style="padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #f3f4f6;">
                <div style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;
                            letter-spacing:0.05em;margin-bottom:8px;">Sound</div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button id="soundMuteBtn" onclick="toggleSoundMute()"
                            style="width:32px;height:32px;border:none;background:#f3f4f6;border-radius:8px;
                                   cursor:pointer;display:flex;align-items:center;justify-content:center;
                                   color:#6b7280;flex-shrink:0;">
                        <i class="fas fa-volume-up" style="font-size:13px;"></i>
                    </button>
                    <input type="range" id="soundVolumeSlider" min="0" max="100" value="70"
                           onchange="setSoundVolume(this.value / 100)"
                           style="flex:1;accent-color:#3b82f6;cursor:pointer;">
                    <button onclick="playPaymentSuccess()"
                            style="font-size:12px;color:#16a34a;background:#f0fdf4;border:none;
                                   padding:4px 8px;border-radius:6px;cursor:pointer;">✓</button>
                    <button onclick="playPaymentFailed()"
                            style="font-size:12px;color:#dc2626;background:#fef2f2;border:none;
                                   padding:4px 8px;border-radius:6px;cursor:pointer;">✕</button>
                </div>
            </div>

            {{-- User --}}
            <div style="padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #f3f4f6;">
                <div style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;
                            letter-spacing:0.05em;margin-bottom:4px;">Signed in as</div>
                <div style="font-size:14px;font-weight:500;color:#1f2937;">{{ Auth::user()->name }}</div>
            </div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('payment.logout') }}">
                @csrf
                <button type="submit"
                        style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 10px;
                               background:none;border:none;cursor:pointer;font-size:14px;
                               color:#dc2626;border-radius:8px;text-align:left;"
                        onmouseover="this.style.background='#fef2f2'"
                        onmouseout="this.style.background='none'">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout from Payment System
                </button>
            </form>
        </div>
    </div>

</header>
