<div class="w-full" wire:poll.10000ms="loadTables">

    {{-- ── Animations & tile styles ───────────────────────────────────────── --}}
    <style>
        @keyframes cleaning-pulse {
            0%, 100% { background-color: #fefce8; }
            50%       { background-color: #fef9c3; }
        }
        .tile-cleaning { animation: cleaning-pulse 2.8s ease-in-out infinite; }

        /* Prevent number-input spinners (capacity field) */
        .cap-input::-webkit-outer-spin-button,
        .cap-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .cap-input { -moz-appearance: textfield; }
    </style>

    {{-- ══════════════════════════════════════════════════════════
         GROUPING (computed once, used by grid & sub-grids)
    ══════════════════════════════════════════════════════════════ --}}
    @php
        $rootTables  = collect($tables)->filter(fn($t) => !$t['parent_table_id'])->values();
        $childGroups = collect($tables)->filter(fn($t) => $t['parent_table_id'])->groupBy('parent_table_id');

        /**
         * Returns [ tileClasses, extraCssClass, dotClasses ]
         * for a single table row based on status / active / selected state.
         */
        $tileStyle = function(array $t, bool $sel): array {
            $s = $t['status'] ?? 'available';
            $ring = $sel ? 'ring-2 ring-offset-1 ring-blue-500' : '';

            if (!$t['is_active']) {
                return [
                    "bg-gray-100 border border-gray-200 opacity-50 cursor-not-allowed {$ring}",
                    '',
                    'bg-gray-300',
                ];
            }
            if ($s === 'occupied') {
                return [
                    "bg-red-50 border border-red-100 border-l-[4px] border-l-red-500 shadow-md cursor-pointer {$ring}",
                    '',
                    'bg-red-500 animate-pulse',
                ];
            }
            if ($s === 'needs_cleaning') {
                return [
                    "bg-yellow-50 border border-yellow-100 border-l-[4px] border-l-orange-400 cursor-pointer {$ring}",
                    'tile-cleaning',
                    'bg-orange-400 animate-pulse',
                ];
            }
            // available / reserved
            return [
                "bg-white border border-gray-100 border-l-[4px] border-l-green-500 cursor-pointer hover:bg-green-50 {$ring}",
                '',
                'bg-green-500',
            ];
        };
    @endphp


    {{-- ══════════════════════════════════════════════════════════
         COLLAPSIBLE GRID WRAPPER  (Fix 2)
         Alpine's `collapsed` is entangled with Livewire $gridCollapsed.
         The grid slides up/down via max-height transition.
    ══════════════════════════════════════════════════════════════ --}}
    <div
        x-data="{ collapsed: @entangle('gridCollapsed') }"
        x-show="!collapsed"
        x-transition:enter="transition-all ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition-all ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    >

        {{-- ── 3-column tile grid ─────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-2 p-2">

            @forelse($rootTables as $table)
                @php
                    $children      = $childGroups->get($table['id'], collect());
                    $isSplitParent = !$table['is_active'] && $children->isNotEmpty();
                @endphp

                {{-- ────── Split-group block (col-span-3) ────────────────── --}}
                @if($isSplitParent)
                    <div class="col-span-3 rounded-xl overflow-hidden border border-amber-200"
                         wire:key="group-{{ $table['id'] }}">

                        <div class="bg-amber-50 px-3 py-1.5 flex items-center gap-2 border-b border-amber-200">
                            <i class="fas fa-code-branch text-amber-500 text-[10px]"></i>
                            <span class="text-xs font-bold text-amber-700 uppercase tracking-wide">{{ $table['name'] }}</span>
                            <span class="ml-auto text-[10px] text-amber-400">split group</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 p-2 bg-amber-50/40">
                            @foreach($children->sortBy('number') as $child)
                                @php
                                    $cSel = $selectedTableId == $child['id'];
                                    [$cBase, $cExtra, $cDot] = $tileStyle($child, $cSel);
                                    $cs = $child['status'];
                                @endphp

                                {{-- Sub-tile --}}
                                <div
                                    x-data="{ editing: false, cap: {{ (int)$child['capacity'] }} }"
                                    wire:key="tile-{{ $child['id'] }}"
                                    @if($child['is_active']) wire:click="handleTableClick({{ $child['id'] }})" @endif
                                    class="relative flex flex-col justify-between p-2.5 rounded-xl select-none {{ $cBase }} {{ $cExtra }}"
                                    style="min-height:82px; border-radius:12px;"
                                >
                                    {{-- Name + dot --}}
                                    <div class="flex items-start justify-between gap-1">
                                        <span class="font-bold text-gray-900 leading-tight" style="font-size:16px;">
                                            {{ $child['name'] }}
                                        </span>
                                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-0.5 {{ $cDot }}"></span>
                                    </div>

                                    {{-- Capacity with inline edit --}}
                                    <div class="flex items-center gap-1 mt-1" @click.stop="">
                                        <template x-if="!editing">
                                            <span class="text-gray-400" style="font-size:11px;">
                                                <span x-text="cap + ' seats'"></span>
                                            </span>
                                        </template>
                                        <template x-if="editing">
                                            <div class="flex items-center gap-1">
                                                <input
                                                    type="number" x-model.number="cap"
                                                    min="1" max="30"
                                                    class="cap-input w-10 text-center border border-gray-300 rounded px-1 bg-white text-gray-800"
                                                    style="font-size:11px; height:20px;"
                                                    @click.stop=""
                                                    @keydown.enter.stop="$wire.updateCapacity({{ $child['id'] }}, cap); editing = false"
                                                >
                                                <button
                                                    @click.stop="$wire.updateCapacity({{ $child['id'] }}, cap); editing = false"
                                                    class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-check text-white" style="font-size:8px;"></i>
                                                </button>
                                            </div>
                                        </template>
                                        <button
                                            @click.stop="editing = !editing"
                                            class="ml-0.5 text-gray-300 hover:text-gray-500 leading-none flex-shrink-0">
                                            <i x-show="!editing" class="fas fa-pencil-alt" style="font-size:9px;"></i>
                                            <i x-show="editing"  class="fas fa-times text-red-400" style="font-size:9px;"></i>
                                        </button>
                                    </div>

                                    {{-- Bottom: total or broom --}}
                                    <div class="flex items-end justify-end mt-1">
                                        @if($cs === 'occupied' && $child['order_total'])
                                            <span class="font-bold text-red-600" style="font-size:13px;">Rs {{ $child['order_total'] }}</span>
                                        @elseif($cs === 'needs_cleaning')
                                            <i class="fas fa-broom text-orange-400" style="font-size:13px;"></i>
                                        @endif
                                    </div>

                                    @if($cSel)
                                        <span class="absolute top-1.5 left-1.5 text-blue-500" style="font-size:10px;">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                            @php $fill = (3 - ($children->count() % 3)) % 3; @endphp
                            @for($i = 0; $i < $fill; $i++) <div></div> @endfor
                        </div>
                    </div>

                {{-- ────── Normal tile ──────────────────────────────────────── --}}
                @else
                    @php
                        $sel = $selectedTableId == $table['id'];
                        [$tBase, $tExtra, $tDot] = $tileStyle($table, $sel);
                        $status = $table['status'];
                    @endphp

                    <div
                        x-data="{ editing: false, cap: {{ (int)$table['capacity'] }} }"
                        wire:key="tile-{{ $table['id'] }}"
                        @if($table['is_active']) wire:click="handleTableClick({{ $table['id'] }})" @endif
                        class="relative flex flex-col justify-between p-3 rounded-xl select-none {{ $tBase }} {{ $tExtra }}"
                        style="min-height:90px; border-radius:12px;"
                    >
                        {{-- Name + dot --}}
                        <div class="flex items-start justify-between gap-1">
                            <span class="font-bold text-gray-900 leading-tight" style="font-size:18px;">
                                {{ $table['name'] }}
                            </span>
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1 {{ $tDot }}"></span>
                        </div>

                        {{-- Capacity with inline edit --}}
                        <div class="flex items-center gap-1 mt-1" @click.stop="">
                            <template x-if="!editing">
                                <span class="text-gray-400" style="font-size:12px;">
                                    <span x-text="cap + ' seats'"></span>
                                </span>
                            </template>
                            <template x-if="editing">
                                <div class="flex items-center gap-1">
                                    <input
                                        type="number" x-model.number="cap"
                                        min="1" max="30"
                                        class="cap-input w-12 text-center border border-gray-300 rounded px-1 bg-white text-gray-800"
                                        style="font-size:12px; height:22px;"
                                        @click.stop=""
                                        @keydown.enter.stop="$wire.updateCapacity({{ $table['id'] }}, cap); editing = false"
                                    >
                                    <button
                                        @click.stop="$wire.updateCapacity({{ $table['id'] }}, cap); editing = false"
                                        class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check text-white" style="font-size:8px;"></i>
                                    </button>
                                </div>
                            </template>
                            <button
                                @click.stop="editing = !editing"
                                class="ml-0.5 text-gray-300 hover:text-gray-500 leading-none flex-shrink-0">
                                <i x-show="!editing" class="fas fa-pencil-alt" style="font-size:9px;"></i>
                                <i x-show="editing"  class="fas fa-times text-red-400" style="font-size:9px;"></i>
                            </button>
                        </div>

                        {{-- Bottom: total, broom, or combined badge --}}
                        <div class="flex items-end justify-between mt-1">
                            @if($table['combined_names'])
                                <span class="text-blue-500 font-medium" style="font-size:10px;">
                                    +{{ $table['combined_names'] }}
                                </span>
                            @else
                                <span></span>
                            @endif

                            @if($status === 'occupied' && $table['order_total'])
                                <span class="font-bold text-red-600" style="font-size:14px;">Rs {{ $table['order_total'] }}</span>
                            @elseif($status === 'needs_cleaning')
                                <i class="fas fa-broom text-orange-400" style="font-size:14px;"></i>
                            @endif
                        </div>

                        @if($sel)
                            <span class="absolute top-2 left-2 text-blue-500" style="font-size:11px;">
                                <i class="fas fa-check"></i>
                            </span>
                        @endif
                    </div>
                @endif

            @empty
                <div class="col-span-3 py-6 text-center text-sm text-gray-400">
                    No tables configured for this branch.
                </div>
            @endforelse

        </div>

        {{-- ── Legend ─────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-center gap-4 pb-2 pt-0.5" style="font-size:11px; color:#9ca3af;">
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span>Free</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>Busy</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-orange-400"></span>Clean</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400"></span>Split</span>
        </div>

    </div>
    {{-- end collapsible wrapper --}}


    {{-- ══════════════════════════════════════════════════════════
         SELECTED TABLE BANNER  (Fix 2)
         Shown at all times when a table is active.
         Replaces the grid visually once collapsed.
    ══════════════════════════════════════════════════════════════ --}}
    @if($selectedTableId)
        @php
            $selTable = collect($tables)->firstWhere('id', $selectedTableId);
            $selCap   = $selTable ? $selTable['capacity'] : null;
        @endphp
        <div class="mx-2 mb-2 rounded-xl overflow-hidden shadow-lg" style="background:#16a34a;">
            <div class="flex items-center px-3 py-2.5 gap-3">

                {{-- Checkmark circle --}}
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-white text-lg"></i>
                </div>

                {{-- Table info --}}
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-white leading-tight truncate" style="font-size:16px;">
                        {{ $selectedTableName }}
                    </p>
                    <p class="text-green-100" style="font-size:11px;">
                        @if($selCap) {{ $selCap }} seats &middot; @endif
                        Ready for order
                    </p>
                </div>

                {{-- Change Table link --}}
                <button wire:click="expandGrid"
                        class="text-green-100 hover:text-white border border-green-300 hover:border-white
                               rounded-lg px-2.5 py-1.5 flex-shrink-0 transition-colors"
                        style="font-size:12px; font-weight:600; white-space:nowrap;">
                    Change Table
                </button>

                {{-- Deselect X --}}
                <button wire:click="clearSelection"
                        class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0 transition-colors">
                    <i class="fas fa-times text-white text-sm"></i>
                </button>

            </div>
        </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════
         MODAL OVERLAY
    ══════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="fixed inset-0 z-[999] flex items-center justify-center"
         style="background:rgba(0,0,0,0.55);"
         wire:click="closeModal">

        <div class="bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden"
             style="width:420px; max-width:95vw; max-height:88vh;"
             wire:click.stop>

            {{-- Modal header --}}
            <div class="flex items-start justify-between px-5 py-3.5 bg-gray-50 border-b flex-shrink-0">
                <div class="flex-1 min-w-0 mr-3">
                    @if(!in_array($modalMode, ['main', 'available_action', 'clear_table', 'occupied_action', 'needs_cleaning_action']))
                        <button wire:click="backToMain"
                                class="text-xs text-indigo-600 hover:text-indigo-800 mb-1 flex items-center gap-1">
                            <i class="fas fa-chevron-left text-[10px]"></i> Back
                        </button>
                    @endif
                    <h3 class="font-bold text-gray-900 text-sm leading-tight truncate">
                        @if($modalMode === 'main')                      {{ $activeTableName }}
                        @elseif($modalMode === 'occupied_action')       {{ $activeTableName }}
                        @elseif($modalMode === 'needs_cleaning_action') 🧹 {{ $activeTableName }}
                        @elseif($modalMode === 'available_action')      {{ $activeTableName }}
                        @elseif($modalMode === 'manage_available')      Manage: {{ $activeTableName }}
                        @elseif($modalMode === 'pre_split')         Split Table
                        @elseif($modalMode === 'pre_rename')        Rename Table
                        @elseif($modalMode === 'pre_combine')       Combine Tables
                        @elseif($modalMode === 'clear_table')       Table Cleared
                        @elseif($modalMode === 'transfer')          Transfer Order
                        @elseif($modalMode === 'split')             Split Order
                        @elseif($modalMode === 'merge')             Merge Tables
                        @elseif($modalMode === 'amend')             Amend Order
                        @elseif($modalMode === 'history')           Amendment History
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if($modalMode === 'occupied_action')    Table is occupied — what would you like to do?
                        @elseif($modalMode === 'needs_cleaning_action') Table needs cleaning
                        @elseif($modalMode === 'main')
                            @if($activeOrderId)
                                Order #{{ $activeOrderId }} &middot;
                                {{ $activeOrderItemsCount }} item{{ $activeOrderItemsCount !== 1 ? 's' : '' }}
                                @if($activeOrderTotal) &middot; Rs {{ $activeOrderTotal }} @endif
                            @else
                                No active order linked
                            @endif
                        @elseif($modalMode === 'available_action')  What would you like to do?
                        @elseif($modalMode === 'manage_available')  Set up this table before ordering
                        @elseif($modalMode === 'pre_split')         Two sub-tables will be created
                        @elseif($modalMode === 'pre_rename')        Rename for this session
                        @elseif($modalMode === 'pre_combine')       Pick a table to absorb into this one
                        @elseif($modalMode === 'clear_table')       Marked for cleaning
                        @elseif($modalMode === 'transfer')          Select a free table to move this order to
                        @elseif($modalMode === 'split')             Choose items &amp; destination
                        @elseif($modalMode === 'merge')             Pull another table's order into this one
                        @elseif($modalMode === 'amend')             Add or remove items from Order #{{ $activeOrderId }}
                        @elseif($modalMode === 'history')           All edits to Order #{{ $activeOrderId }}
                        @endif
                    </p>
                </div>
                <button wire:click="closeModal"
                        class="text-gray-400 hover:text-gray-700 p-1 rounded transition flex-shrink-0 mt-0.5">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Scrollable body --}}
            <div class="overflow-y-auto flex-1">

                {{-- ── Occupied action (add to order vs manage) ──────────── --}}
                @if($modalMode === 'occupied_action')
                    <div class="p-5 space-y-3">
                        @if($activeOrderId)
                            <div class="px-3 py-2 bg-red-50 border border-red-100 rounded-xl flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600">Current Total</span>
                                <span class="text-lg font-bold text-red-700">Rs {{ $activeOrderTotal }}</span>
                            </div>
                            <button wire:click="startContinueOrder"
                                    class="flex items-center gap-3 w-full px-4 py-3.5
                                           bg-green-500 hover:bg-green-600 text-white rounded-xl transition shadow-sm">
                                <span class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-plus text-sm"></i>
                                </span>
                                <div class="text-left">
                                    <p class="font-semibold text-sm">Add to Current Order</p>
                                    <p class="text-xs text-green-100">Order #{{ $activeOrderId }} &middot; {{ $activeOrderItemsCount }} item{{ $activeOrderItemsCount !== 1 ? 's' : '' }}</p>
                                </div>
                                <i class="fas fa-chevron-right ml-auto text-white/60 text-xs"></i>
                            </button>
                        @endif
                        <button wire:click="openFullManageModal"
                                class="flex items-center gap-3 w-full px-4 py-3.5
                                       bg-blue-50 hover:bg-blue-100 border border-blue-200
                                       text-blue-800 rounded-xl transition">
                            <span class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-sliders-h text-blue-600 text-sm"></i>
                            </span>
                            <div class="text-left">
                                <p class="font-semibold text-sm">View / Manage Table</p>
                                <p class="text-xs text-blue-500">Transfer, split, amend or clear</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-blue-300 text-xs"></i>
                        </button>
                    </div>

                {{-- ── Needs-cleaning action ──────────────────────────────── --}}
                @elseif($modalMode === 'needs_cleaning_action')
                    <div class="p-5 space-y-3">
                        <div class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                            <span class="text-3xl">🧹</span>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900">This table needs cleaning</p>
                                <p class="text-xs text-yellow-700">Mark it as available once cleaned.</p>
                            </div>
                        </div>
                        <button wire:click="markClean({{ $activeTableId }})"
                                wire:loading.attr="disabled" wire:target="markClean({{ $activeTableId }})"
                                class="flex items-center justify-center gap-2 w-full px-4 py-4
                                       bg-green-500 hover:bg-green-600 text-white text-base font-bold rounded-xl transition shadow">
                            <i class="fas fa-check-circle text-lg"
                               wire:loading.remove wire:target="markClean({{ $activeTableId }})"></i>
                            <i class="fas fa-spinner fa-spin text-lg"
                               wire:loading wire:target="markClean({{ $activeTableId }})"></i>
                            <span wire:loading.remove wire:target="markClean({{ $activeTableId }})">✓ Mark as Clean & Available</span>
                            <span wire:loading wire:target="markClean({{ $activeTableId }})">Updating…</span>
                        </button>
                        <button wire:click="closeModal"
                                class="w-full px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition text-center">
                            Dismiss
                        </button>
                    </div>

                {{-- ── Available action ──────────────────────────────────── --}}
                @elseif($modalMode === 'available_action')
                    <div class="p-5 space-y-3">
                        <button wire:click="startOrder"
                                class="flex items-center gap-3 w-full px-4 py-3.5
                                       bg-green-500 hover:bg-green-600 text-white rounded-xl transition shadow-sm">
                            <span class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-utensils text-sm"></i>
                            </span>
                            <div class="text-left">
                                <p class="font-semibold text-sm">Start Order</p>
                                <p class="text-xs text-green-100">Select this table for a new order</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-white/60 text-xs"></i>
                        </button>
                        <button wire:click="initiateManageAvailable"
                                class="flex items-center gap-3 w-full px-4 py-3.5
                                       bg-blue-50 hover:bg-blue-100 border border-blue-200
                                       text-blue-800 rounded-xl transition">
                            <span class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-sliders-h text-blue-600 text-sm"></i>
                            </span>
                            <div class="text-left">
                                <p class="font-semibold text-sm">Manage Table</p>
                                <p class="text-xs text-blue-500">Split, rename, or combine before ordering</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-blue-300 text-xs"></i>
                        </button>
                    </div>

                {{-- ── Manage available ────────────────────────────────────── --}}
                @elseif($modalMode === 'manage_available')
                    <div class="p-5 space-y-2.5">
                        <button wire:click="initiatePreSplit"
                                class="flex items-center gap-3 w-full px-4 py-3
                                       bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 rounded-xl transition">
                            <span class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-code-branch text-amber-600"></i>
                            </span>
                            <div class="text-left">
                                <p class="font-semibold text-sm">Split Table</p>
                                <p class="text-xs text-amber-600">Divide into two sub-tables (e.g. 1A + 1B)</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-amber-300 text-xs"></i>
                        </button>
                        <button wire:click="initiatePreRename"
                                class="flex items-center gap-3 w-full px-4 py-3
                                       bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-900 rounded-xl transition">
                            <span class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-pen text-indigo-600"></i>
                            </span>
                            <div class="text-left">
                                <p class="font-semibold text-sm">Rename Table</p>
                                <p class="text-xs text-indigo-500">Change the display name for this session</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-indigo-300 text-xs"></i>
                        </button>
                        <button wire:click="initiatePreCombine"
                                class="flex items-center gap-3 w-full px-4 py-3
                                       bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-900 rounded-xl transition">
                            <span class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-compress-arrows-alt text-purple-600"></i>
                            </span>
                            <div class="text-left">
                                <p class="font-semibold text-sm">Combine Tables</p>
                                <p class="text-xs text-purple-500">Mark another table as joined to this one</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-purple-300 text-xs"></i>
                        </button>
                    </div>

                {{-- ── Pre-split ────────────────────────────────────────────── --}}
                @elseif($modalMode === 'pre_split')
                    <div class="p-5 space-y-4">
                        <p class="text-xs text-gray-500">
                            The original table will be deactivated. Both sub-tables appear as available (green).
                        </p>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sub-table A name</label>
                            <input type="text" wire:model.defer="preSplitNameA" placeholder="e.g. Table 1A"
                                   class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sub-table B name</label>
                            <input type="text" wire:model.defer="preSplitNameB" placeholder="e.g. Table 1B"
                                   class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <button wire:click="confirmPreSplit" wire:loading.attr="disabled" wire:target="confirmPreSplit"
                                class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                                       bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition">
                            <i class="fas fa-code-branch" wire:loading.remove wire:target="confirmPreSplit"></i>
                            <i class="fas fa-spinner fa-spin" wire:loading wire:target="confirmPreSplit"></i>
                            <span wire:loading.remove wire:target="confirmPreSplit">Create Sub-tables</span>
                            <span wire:loading wire:target="confirmPreSplit">Creating…</span>
                        </button>
                    </div>

                {{-- ── Pre-rename ────────────────────────────────────────────── --}}
                @elseif($modalMode === 'pre_rename')
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">New name</label>
                            <input type="text" wire:model.defer="preRenameValue"
                                   class="w-full px-3 py-2 border border-indigo-300 rounded-lg text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <button wire:click="confirmPreRename" wire:loading.attr="disabled" wire:target="confirmPreRename"
                                class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                                       bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                            <i class="fas fa-check" wire:loading.remove wire:target="confirmPreRename"></i>
                            <i class="fas fa-spinner fa-spin" wire:loading wire:target="confirmPreRename"></i>
                            <span wire:loading.remove wire:target="confirmPreRename">Save Name</span>
                            <span wire:loading wire:target="confirmPreRename">Saving…</span>
                        </button>
                    </div>

                {{-- ── Pre-combine ───────────────────────────────────────────── --}}
                @elseif($modalMode === 'pre_combine')
                    @if(empty($combineAvailableTables))
                        <div class="px-5 py-8 text-center text-gray-500 text-sm">
                            <i class="fas fa-compress-arrows-alt text-3xl text-gray-300 mb-3 block"></i>
                            No other free tables to combine with.
                        </div>
                    @else
                        <p class="px-5 pt-4 pb-2 text-xs text-gray-500">
                            The selected table will be absorbed into
                            <strong>{{ $activeTableName }}</strong> and hidden from the grid.
                        </p>
                        <div class="grid grid-cols-3 gap-2 px-4 pb-5">
                            @foreach($combineAvailableTables as $t)
                                <button wire:click="confirmPreCombine({{ $t['id'] }})"
                                        wire:key="combine-pick-{{ $t['id'] }}"
                                        wire:loading.attr="disabled" wire:target="confirmPreCombine({{ $t['id'] }})"
                                        class="flex flex-col items-center justify-center gap-1 p-3
                                               bg-green-50 hover:bg-purple-50 border-2 border-green-400
                                               hover:border-purple-400 text-green-900 hover:text-purple-900
                                               rounded-xl transition cursor-pointer active:scale-95 text-xs font-medium">
                                    <i class="fas fa-chair text-lg text-green-600"
                                       wire:loading.remove wire:target="confirmPreCombine({{ $t['id'] }})"></i>
                                    <i class="fas fa-spinner fa-spin text-lg text-purple-500"
                                       wire:loading wire:target="confirmPreCombine({{ $t['id'] }})"></i>
                                    <span>T{{ $t['number'] }}</span>
                                    <span class="text-[10px] opacity-70 truncate max-w-full">{{ $t['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                {{-- ── Clear table ───────────────────────────────────────────── --}}
                @elseif($modalMode === 'clear_table')
                    <div class="p-5 space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                            <div class="w-9 h-9 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-broom text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900">Marked for cleaning</p>
                                <p class="text-xs text-yellow-700">Table is now flagged as needs cleaning.</p>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <p class="text-sm font-semibold text-amber-900 mb-1">
                                <i class="fas fa-code-branch mr-1.5 text-amber-600"></i>
                                Part of the <strong>{{ $clearTableParentName }}</strong> split group
                            </p>
                            <p class="text-xs text-amber-700">
                                Would you like to restore the original table?
                                This will delete all sub-tables in this group.
                            </p>
                            @if(!$clearTableCanRestore)
                                <div class="mt-2 flex items-start gap-1.5 text-xs text-red-600">
                                    <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                                    <span>Some sibling tables are still occupied — clear them first.</span>
                                </div>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="confirmRestoreParent"
                                    wire:loading.attr="disabled" wire:target="confirmRestoreParent"
                                    @if(!$clearTableCanRestore) disabled @endif
                                    class="flex items-center justify-center gap-1.5 px-3 py-2.5
                                           text-sm font-semibold rounded-xl transition
                                           {{ $clearTableCanRestore ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                                <i class="fas fa-undo text-xs" wire:loading.remove wire:target="confirmRestoreParent"></i>
                                <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="confirmRestoreParent"></i>
                                <span wire:loading.remove wire:target="confirmRestoreParent">Restore {{ $clearTableParentName }}</span>
                                <span wire:loading wire:target="confirmRestoreParent">Restoring…</span>
                            </button>
                            <button wire:click="skipRestore"
                                    class="flex items-center justify-center gap-1.5 px-3 py-2.5
                                           bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                                <i class="fas fa-times text-xs"></i>
                                Keep Separate
                            </button>
                        </div>
                    </div>

                {{-- ── Occupied main ─────────────────────────────────────────── --}}
                @elseif($modalMode === 'main')

                    @if($activeOrderTotal)
                        <div class="px-5 py-3 bg-red-50 border-b flex items-center justify-between">
                            <span class="text-sm text-gray-600">Current Total</span>
                            <span class="text-xl font-bold text-red-700">Rs {{ $activeOrderTotal }}</span>
                        </div>
                    @endif

                    <div class="p-5 space-y-2.5">
                        @if($activeOrderId)
                            <a href="{{ route('admin.orders.show.details', $activeOrderId) }}" target="_blank"
                               class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                                      bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition">
                                <i class="fas fa-receipt"></i> View Full Order
                            </a>
                        @endif

                        @if($activeOrderId && $activeOrderItemsCount > 0)
                            <button wire:click="initiateAmend"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                                           bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition">
                                <i class="fas fa-edit"></i> Amend Order
                            </button>
                        @endif

                        @php
                            $activeTableData = collect($tables)->firstWhere('id', $activeTableId);
                            $currentStatus   = $activeTableData['status'] ?? 'occupied';
                        @endphp

                        @if($currentStatus === 'needs_cleaning')
                            <button wire:click="markClean({{ $activeTableId }})" wire:loading.attr="disabled"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                                           bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition">
                                <i class="fas fa-check-circle"
                                   wire:loading.remove wire:target="markClean({{ $activeTableId }})"></i>
                                <i class="fas fa-spinner fa-spin"
                                   wire:loading wire:target="markClean({{ $activeTableId }})"></i>
                                <span wire:loading.remove wire:target="markClean({{ $activeTableId }})">Mark as Available</span>
                                <span wire:loading wire:target="markClean({{ $activeTableId }})">Updating…</span>
                            </button>
                        @else
                            <button wire:click="clearTable({{ $activeTableId }})" wire:loading.attr="disabled"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5
                                           bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-xl transition">
                                <i class="fas fa-broom"
                                   wire:loading.remove wire:target="clearTable({{ $activeTableId }})"></i>
                                <i class="fas fa-spinner fa-spin"
                                   wire:loading wire:target="clearTable({{ $activeTableId }})"></i>
                                <span wire:loading.remove wire:target="clearTable({{ $activeTableId }})">Clear Table</span>
                                <span wire:loading wire:target="clearTable({{ $activeTableId }})">Clearing…</span>
                            </button>
                        @endif

                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <button wire:click="initiateTransfer"
                                    class="flex flex-col items-center justify-center gap-1.5 px-2 py-3
                                           bg-indigo-50 hover:bg-indigo-100 border border-indigo-200
                                           text-indigo-700 text-xs font-medium rounded-xl transition">
                                <i class="fas fa-exchange-alt text-base"></i>Transfer
                            </button>
                            @if($activeOrderId && $activeOrderItemsCount > 0)
                                <button wire:click="initiateSplit"
                                        class="flex flex-col items-center justify-center gap-1.5 px-2 py-3
                                               bg-amber-50 hover:bg-amber-100 border border-amber-200
                                               text-amber-700 text-xs font-medium rounded-xl transition">
                                    <i class="fas fa-code-branch text-base"></i>Split
                                </button>
                            @else
                                <button disabled
                                        class="flex flex-col items-center justify-center gap-1.5 px-2 py-3
                                               bg-gray-100 border border-gray-200 text-gray-400
                                               text-xs font-medium rounded-xl cursor-not-allowed">
                                    <i class="fas fa-code-branch text-base"></i>Split
                                </button>
                            @endif
                            <button wire:click="initiateMerge"
                                    class="flex flex-col items-center justify-center gap-1.5 px-2 py-3
                                           bg-purple-50 hover:bg-purple-100 border border-purple-200
                                           text-purple-700 text-xs font-medium rounded-xl transition">
                                <i class="fas fa-compress-arrows-alt text-base"></i>Merge
                            </button>
                        </div>

                        @if($activeOrderId)
                            <button wire:click="showHistory"
                                    class="flex items-center justify-center gap-1.5 w-full py-1.5
                                           text-xs text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-history text-[11px]"></i> View amendment history
                            </button>
                        @endif
                    </div>

                {{-- ── Transfer ──────────────────────────────────────────────── --}}
                @elseif($modalMode === 'transfer')
                    @if(empty($availableTablesForAction))
                        <div class="px-5 py-8 text-center text-gray-500 text-sm">
                            <i class="fas fa-chair text-3xl text-gray-300 mb-3 block"></i>
                            No free tables available right now.
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-2 p-4">
                            @foreach($availableTablesForAction as $t)
                                <button wire:click="confirmTransfer({{ $t['id'] }})"
                                        wire:key="transfer-dest-{{ $t['id'] }}"
                                        wire:loading.attr="disabled" wire:target="confirmTransfer({{ $t['id'] }})"
                                        class="flex flex-col items-center justify-center gap-1 p-3
                                               bg-green-50 hover:bg-green-100 border-2 border-green-400
                                               text-green-900 rounded-xl transition cursor-pointer active:scale-95 text-xs font-medium">
                                    <i class="fas fa-chair text-lg text-green-600"
                                       wire:loading.remove wire:target="confirmTransfer({{ $t['id'] }})"></i>
                                    <i class="fas fa-spinner fa-spin text-lg text-green-600"
                                       wire:loading wire:target="confirmTransfer({{ $t['id'] }})"></i>
                                    <span>T{{ $t['number'] }}</span>
                                    <span class="text-[10px] opacity-70">{{ $t['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                {{-- ── Post-order split ──────────────────────────────────────── --}}
                @elseif($modalMode === 'split')
                    <div class="px-5 pt-4 pb-2">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Step 1 — Qty to move</p>
                        @foreach($splitItems as $item)
                            @php $mv = (int)($splitQtys[$item['id']] ?? 0); @endphp
                            <div class="flex items-center gap-3 py-2 border-b last:border-0"
                                 wire:key="split-item-{{ $item['id'] }}">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item['item_name'] }}</p>
                                    <p class="text-xs text-gray-400">Rs {{ number_format($item['price'], 2) }} &times; {{ $item['quantity'] }}</p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button wire:click="setSplitQty({{ $item['id'] }}, {{ $mv - 1 }})"
                                            @if($mv <= 0) disabled @endif
                                            class="w-7 h-7 rounded-full border flex items-center justify-center
                                                   {{ $mv > 0 ? 'border-gray-300 hover:bg-gray-100 text-gray-700' : 'border-gray-200 text-gray-300 cursor-not-allowed' }}">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <span class="w-5 text-center text-sm font-bold {{ $mv > 0 ? 'text-amber-700' : 'text-gray-300' }}">{{ $mv }}</span>
                                    <button wire:click="setSplitQty({{ $item['id'] }}, {{ $mv + 1 }})"
                                            @if($mv >= $item['quantity']) disabled @endif
                                            class="w-7 h-7 rounded-full border flex items-center justify-center
                                                   {{ $mv < $item['quantity'] ? 'border-gray-300 hover:bg-gray-100 text-gray-700' : 'border-gray-200 text-gray-300 cursor-not-allowed' }}">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-5 pt-3 pb-3 border-t bg-gray-50">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Step 2 — Destination table</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($availableTablesForAction as $t)
                                <button wire:click="setSplitDest({{ $t['id'] }})" wire:key="split-dest-{{ $t['id'] }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border-2 transition
                                               {{ $splitDestTableId == $t['id'] ? 'bg-amber-500 border-amber-500 text-white' : 'bg-white border-green-400 text-green-800 hover:bg-green-50' }}">
                                    T{{ $t['number'] }} — {{ $t['name'] }}
                                </button>
                            @endforeach
                            <button wire:click="createTempTableForSplit"
                                    wire:loading.attr="disabled" wire:target="createTempTableForSplit"
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border-2 border-dashed
                                           border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition flex items-center gap-1">
                                <i class="fas fa-plus text-[10px]"
                                   wire:loading.remove wire:target="createTempTableForSplit"></i>
                                <i class="fas fa-spinner fa-spin text-[10px]"
                                   wire:loading wire:target="createTempTableForSplit"></i>
                                Temp table
                            </button>
                        </div>
                        @if(empty($availableTablesForAction))
                            <p class="text-xs text-gray-400 mt-1.5">No free tables — create a temp table above.</p>
                        @endif
                    </div>
                    <div class="px-5 pt-3 pb-5">
                        @php $anyMv = collect($splitQtys)->sum() > 0; $hasDst = !empty($splitDestTableId); @endphp
                        <button wire:click="confirmSplit" wire:loading.attr="disabled" wire:target="confirmSplit"
                                @if(!$anyMv || !$hasDst) disabled @endif
                                class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold rounded-xl transition
                                       {{ ($anyMv && $hasDst) ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            <i class="fas fa-code-branch" wire:loading.remove wire:target="confirmSplit"></i>
                            <i class="fas fa-spinner fa-spin" wire:loading wire:target="confirmSplit"></i>
                            <span wire:loading.remove wire:target="confirmSplit">Confirm Split</span>
                            <span wire:loading wire:target="confirmSplit">Splitting…</span>
                        </button>
                    </div>

                {{-- ── Merge ──────────────────────────────────────────────────── --}}
                @elseif($modalMode === 'merge')
                    @if(empty($occupiedTablesForMerge))
                        <div class="px-5 py-8 text-center text-gray-500 text-sm">
                            <i class="fas fa-compress-arrows-alt text-3xl text-gray-300 mb-3 block"></i>
                            No other occupied tables to merge with.
                        </div>
                    @else
                        <p class="px-5 pt-4 pb-2 text-xs text-gray-500">
                            All items from the chosen table move into <strong>{{ $activeTableName }}</strong>.
                        </p>
                        <div class="px-5 pb-5 space-y-2 pt-2">
                            @foreach($occupiedTablesForMerge as $t)
                                <button wire:click="confirmMerge({{ $t['id'] }})"
                                        wire:key="merge-src-{{ $t['id'] }}"
                                        wire:loading.attr="disabled" wire:target="confirmMerge({{ $t['id'] }})"
                                        class="flex items-center justify-between w-full px-4 py-3
                                               bg-white hover:bg-purple-50 border-2 border-red-300
                                               hover:border-purple-400 rounded-xl transition group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-chair text-red-500 text-sm"
                                               wire:loading.remove wire:target="confirmMerge({{ $t['id'] }})"></i>
                                            <i class="fas fa-spinner fa-spin text-red-500 text-sm"
                                               wire:loading wire:target="confirmMerge({{ $t['id'] }})"></i>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-semibold text-gray-900">T{{ $t['number'] }} — {{ $t['name'] }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $t['order_items_count'] }} items
                                                @if($t['order_total']) &middot; Rs {{ $t['order_total'] }} @endif
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-purple-600 group-hover:text-purple-800 font-medium flex items-center gap-1">
                                        Merge in <i class="fas fa-chevron-right text-[10px]"></i>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                {{-- ── Amend ──────────────────────────────────────────────────── --}}
                @elseif($modalMode === 'amend')
                    <div class="px-5 pt-4 pb-1">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Remove items</p>
                        @forelse($amendCurrentItems as $item)
                            @php $rm = (int)($amendRemovals[$item['id']] ?? 0); $allGone = $rm >= $item['quantity']; @endphp
                            <div class="flex items-center gap-3 py-2 border-b last:border-0"
                                 wire:key="amend-rm-{{ $item['id'] }}">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate {{ $allGone ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                        {{ $item['item_name'] }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Rs {{ number_format($item['price'], 2) }} &times; {{ $item['quantity'] }}
                                        @if($rm > 0)<span class="text-red-500 font-medium">— remove {{ $rm }}</span>@endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button wire:click="setAmendRemoval({{ $item['id'] }}, {{ $rm - 1 }})"
                                            @if($rm <= 0) disabled @endif
                                            class="w-7 h-7 rounded-full border flex items-center justify-center
                                                   {{ $rm > 0 ? 'border-red-300 hover:bg-red-50 text-red-600' : 'border-gray-200 text-gray-300 cursor-not-allowed' }}">
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <span class="w-5 text-center text-sm font-bold {{ $rm > 0 ? 'text-red-600' : 'text-gray-300' }}">{{ $rm }}</span>
                                    <button wire:click="setAmendRemoval({{ $item['id'] }}, {{ $rm + 1 }})"
                                            @if($rm >= $item['quantity']) disabled @endif
                                            class="w-7 h-7 rounded-full border flex items-center justify-center
                                                   {{ $rm < $item['quantity'] ? 'border-red-300 hover:bg-red-50 text-red-600' : 'border-gray-200 text-gray-300 cursor-not-allowed' }}">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 py-2">No items on this order.</p>
                        @endforelse
                    </div>
                    <div class="px-5 pt-3 pb-2 border-t">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Add items</p>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" wire:model.debounce.300ms="amendProductSearch"
                                   placeholder="Search menu…"
                                   class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg
                                          focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent">
                        </div>
                        @if(!empty($amendSearchResults))
                            <div class="mt-1.5 border border-gray-100 rounded-lg overflow-hidden divide-y">
                                @foreach($amendSearchResults as $result)
                                    <button wire:click="addToAmendAdditions({{ $result['id'] }})"
                                            wire:key="search-r-{{ $result['id'] }}"
                                            class="flex items-center justify-between w-full px-3 py-2 hover:bg-orange-50 transition text-left">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $result['name'] }}</p>
                                            @if($result['category'])<p class="text-[10px] text-gray-400">{{ $result['category'] }}</p>@endif
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                            <span class="text-xs font-semibold text-gray-700">Rs {{ number_format($result['price'], 2) }}</span>
                                            <span class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center">
                                                <i class="fas fa-plus text-orange-600 text-[10px]"></i>
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @elseif(strlen(trim($amendProductSearch)) >= 2)
                            <p class="text-xs text-gray-400 mt-2 text-center">No products found.</p>
                        @endif
                        @if(!empty($amendAdditions))
                            <div class="mt-3 space-y-1.5">
                                <p class="text-[11px] text-gray-400 font-medium">Adding:</p>
                                @foreach($amendAdditions as $addition)
                                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-1.5"
                                         wire:key="addition-{{ $addition['product_id'] }}">
                                        <span class="flex-1 text-sm font-medium text-green-900 truncate">{{ $addition['name'] }}</span>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button wire:click="setAdditionQty({{ $addition['product_id'] }}, {{ $addition['qty'] - 1 }})"
                                                    @if($addition['qty'] <= 1) disabled @endif
                                                    class="w-6 h-6 rounded-full border border-green-300 flex items-center justify-center
                                                           {{ $addition['qty'] > 1 ? 'hover:bg-green-100 text-green-700' : 'text-gray-300 cursor-not-allowed' }}">
                                                <i class="fas fa-minus text-[9px]"></i>
                                            </button>
                                            <span class="w-5 text-center text-sm font-bold text-green-800">{{ $addition['qty'] }}</span>
                                            <button wire:click="setAdditionQty({{ $addition['product_id'] }}, {{ $addition['qty'] + 1 }})"
                                                    class="w-6 h-6 rounded-full border border-green-300 hover:bg-green-100 flex items-center justify-center text-green-700">
                                                <i class="fas fa-plus text-[9px]"></i>
                                            </button>
                                        </div>
                                        <span class="text-xs text-green-700 font-medium w-16 text-right flex-shrink-0">
                                            Rs {{ number_format($addition['price'] * $addition['qty'], 2) }}
                                        </span>
                                        <button wire:click="removeFromAdditions({{ $addition['product_id'] }})"
                                                class="text-red-400 hover:text-red-600 ml-1 flex-shrink-0">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="px-5 pt-3 pb-5 border-t">
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">
                            Reason <span class="font-normal normal-case">(optional)</span>
                        </label>
                        <input type="text" wire:model.defer="amendReason" placeholder="e.g. Customer changed mind…"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg mb-3
                                      focus:outline-none focus:ring-2 focus:ring-orange-300">
                        @php
                            $removeTotal = collect($amendCurrentItems)->sum(fn($item) => ($amendRemovals[$item['id']] ?? 0) * $item['price']);
                            $addTotal    = collect($amendAdditions)->sum(fn($a) => $a['qty'] * $a['price']);
                            $netChange   = $addTotal - $removeTotal;
                            $hasChanges  = collect($amendRemovals)->sum() > 0 || !empty($amendAdditions);
                        @endphp
                        @if($hasChanges)
                            <div class="text-xs text-gray-500 mb-3 space-y-0.5">
                                @if($removeTotal > 0)<p class="text-red-600"><i class="fas fa-minus-circle mr-1"></i>Removing Rs {{ number_format($removeTotal, 2) }}</p>@endif
                                @if($addTotal > 0)<p class="text-green-600"><i class="fas fa-plus-circle mr-1"></i>Adding Rs {{ number_format($addTotal, 2) }}</p>@endif
                                <p class="font-semibold {{ $netChange >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    Net change: {{ $netChange >= 0 ? '+' : '' }}Rs {{ number_format($netChange, 2) }}
                                </p>
                            </div>
                        @endif
                        <button wire:click="confirmAmend" wire:loading.attr="disabled" wire:target="confirmAmend"
                                @if(!$hasChanges) disabled @endif
                                class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold rounded-xl transition
                                       {{ $hasChanges ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                            <i class="fas fa-check" wire:loading.remove wire:target="confirmAmend"></i>
                            <i class="fas fa-spinner fa-spin" wire:loading wire:target="confirmAmend"></i>
                            <span wire:loading.remove wire:target="confirmAmend">Confirm Amendment</span>
                            <span wire:loading wire:target="confirmAmend">Saving…</span>
                        </button>
                    </div>

                {{-- ── History ────────────────────────────────────────────────── --}}
                @elseif($modalMode === 'history')
                    @if(empty($amendHistoryItems))
                        <div class="px-5 py-8 text-center text-gray-500 text-sm">
                            <i class="fas fa-history text-3xl text-gray-200 mb-3 block"></i>
                            No amendments recorded for this order.
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach($amendHistoryItems as $entry)
                                <div class="px-5 py-4" wire:key="hist-{{ $entry['id'] }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold text-gray-700">{{ $entry['amended_by'] }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $entry['created_at'] }}</p>
                                    </div>
                                    @if(!empty($entry['items_removed']))
                                        <div class="mb-1.5">
                                            @foreach($entry['items_removed'] as $item)
                                                <p class="text-xs text-red-600 flex items-center gap-1.5">
                                                    <i class="fas fa-minus-circle text-[10px]"></i>
                                                    {{ $item['qty'] }} &times; {{ $item['item_name'] }}
                                                    <span class="text-red-400">(Rs {{ number_format($item['price'] * $item['qty'], 2) }})</span>
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($entry['items_added']))
                                        <div class="mb-1.5">
                                            @foreach($entry['items_added'] as $item)
                                                <p class="text-xs text-green-700 flex items-center gap-1.5">
                                                    <i class="fas fa-plus-circle text-[10px]"></i>
                                                    {{ $item['qty'] }} &times; {{ $item['item_name'] }}
                                                    <span class="text-green-500">(Rs {{ number_format($item['price'] * $item['qty'], 2) }})</span>
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-xs font-semibold {{ $entry['amount_change'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                            {{ $entry['amount_change'] >= 0 ? '+' : '' }}Rs {{ number_format($entry['amount_change'], 2) }}
                                        </span>
                                        @if($entry['reason'])
                                            <span class="text-[10px] text-gray-400 italic truncate max-w-[200px]">"{{ $entry['reason'] }}"</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                @endif {{-- end modalMode --}}
            </div>

        </div>
    </div>
    @endif

</div>
