<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\OrderAmendment;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PosTableGrid extends Component
{
    // ── Core ─────────────────────────────────────────────────────────────────

    public $branchId;
    public $tables = [];

    // ── New-order selection (available table click) ───────────────────────────

    public $selectedTableId   = null;
    public $selectedTableName = null;

    // ── Modal shared ─────────────────────────────────────────────────────────

    public $showModal             = false;
    public $activeTableId         = null;
    public $activeTableName       = null;
    public $activeOrderId         = null;
    public $activeOrderTotal      = null;
    public $activeOrderItemsCount = 0;

    /**
     * Current modal sub-screen.
     * Occupied flow:  'main' | 'transfer' | 'split' | 'merge' | 'amend' | 'history'
     * Available flow: 'available_action' | 'manage_available' | 'pre_split' | 'pre_rename' | 'pre_combine'
     * Clear flow:     'clear_table'
     */
    public $modalMode = 'main';

    // ── Transfer ─────────────────────────────────────────────────────────────

    public $availableTablesForAction = [];

    // ── Post-order Split ─────────────────────────────────────────────────────

    public $splitItems       = [];
    public $splitQtys        = [];
    public $splitDestTableId = null;

    // ── Post-order Merge ──────────────────────────────────────────────────────

    public $occupiedTablesForMerge = [];

    // ── Amend ─────────────────────────────────────────────────────────────────

    public $amendCurrentItems  = [];
    public $amendRemovals      = [];
    public $amendAdditions     = [];
    public $amendReason        = '';
    public $amendProductSearch = '';
    public $amendSearchResults = [];

    // ── History ───────────────────────────────────────────────────────────────

    public $amendHistoryItems = [];

    // ── Pre-order table management (available tables) ─────────────────────────

    /** Sub-table name inputs for pre-order split. */
    public $preSplitNameA = '';
    public $preSplitNameB = '';

    /** Rename input. */
    public $preRenameValue = '';

    /** Available tables list for combine picker. */
    public $combineAvailableTables = [];

    // ── Clear table / restore parent ─────────────────────────────────────────

    public $clearTableParentId   = null;
    public $clearTableParentName = '';
    public $clearTableCanRestore = false;

    // ── Grid collapse (Fix 2) ─────────────────────────────────────────────

    /** True when a table has been selected and the grid collapses to save space. */
    public $gridCollapsed = false;

    // ── Listeners ────────────────────────────────────────────────────────────

    protected $listeners = [
        'posOrderCreated'  => 'onOrderCreated',
        'refreshTableGrid' => 'loadTables',
    ];

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->branchId = session('selected_branch_id');
        $this->loadTables();
    }

    public function onOrderCreated(): void
    {
        $this->selectedTableId   = null;
        $this->selectedTableName = null;
        $this->gridCollapsed     = false;
        $this->loadTables();
    }

    // ── Capacity inline edit (Fix 1) ──────────────────────────────────────────

    public function updateCapacity(int $tableId, int $capacity): void
    {
        $capacity = max(1, min(30, (int) $capacity));
        Table::where('id', $tableId)
            ->where('branch_id', $this->branchId) // safety: own branch only
            ->update(['capacity' => $capacity]);
        $this->loadTables();
    }

    // ── Table data ────────────────────────────────────────────────────────────

    public function loadTables(): void
    {
        $allRows = Table::where('branch_id', $this->branchId)
            ->orderBy('number')
            ->get();

        // IDs of tables that are parents of ACTIVE sub-tables (pre-order split parents).
        $splitParentIds = $allRows
            ->where('is_active', true)
            ->whereNotNull('parent_table_id')
            ->pluck('parent_table_id')
            ->unique();

        // Names of inactive tables absorbed (combined) into an active parent.
        // Grouped by the active parent's ID → shown as "+T2, T3" badge on that parent.
        $combinedNames = $allRows
            ->where('is_active', false)
            ->whereNotNull('parent_table_id')
            ->filter(fn($t) => $splitParentIds->doesntContain($t->parent_table_id))
            ->groupBy('parent_table_id')
            ->map(fn($group) => $group->pluck('name')->implode(', '));

        // Show:  active tables  +  inactive split-parents (they become group headers).
        // Skip:  inactive combined/absorbed tables (badges on their active parent instead).
        $rows = $allRows->filter(function (Table $t) use ($splitParentIds) {
            if ($t->is_active) return true;
            return $splitParentIds->contains($t->id);
        });

        $this->tables = $rows->map(function (Table $table) use ($combinedNames) {
            $order = $this->resolveActiveOrder($table);
            return [
                'id'                => $table->id,
                'name'              => $table->name,
                'number'            => $table->number,
                'capacity'          => $table->capacity,
                'status'            => $table->status ?? 'available',
                'is_active'         => (bool) $table->is_active,
                'parent_table_id'   => $table->parent_table_id,
                'combined_names'    => $combinedNames[$table->id] ?? null,
                'current_order_id'  => $order?->id,
                'order_total'       => $order
                    ? number_format((float) ($order->total ?? $order->grand_total ?? 0), 2)
                    : null,
                'order_items_count' => $order ? $order->items()->count() : 0,
            ];
        })->values()->toArray();
    }

    private function resolveActiveOrder(Table $table): ?Order
    {
        if ($table->current_order_id) {
            $order = Order::find($table->current_order_id);
            if ($order) return $order;
        }
        if ($table->is_occupied) {
            return Order::where('table_id', $table->id)
                ->whereIn('status', ['pending', 'processing', 'ready', 'preparing'])
                ->latest()
                ->first();
        }
        return null;
    }

    // ── Tile interaction ──────────────────────────────────────────────────────

    public function handleTableClick(int $tableId): void
    {
        $table = collect($this->tables)->firstWhere('id', $tableId);
        if (!$table || !$table['is_active']) return;

        if ($table['status'] === 'occupied') {
            $this->openOccupiedActionModal($tableId);
        } elseif ($table['status'] === 'needs_cleaning') {
            $this->openCleaningModal($tableId);
        } else {
            // Available (or reserved) → show available-action modal
            $this->openAvailableModal($tableId);
        }
    }

    public function clearSelection(): void
    {
        $this->selectedTableId   = null;
        $this->selectedTableName = null;
        $this->gridCollapsed     = false;
        $this->dispatchBrowserEvent('tableDeselected');
    }

    /** Re-expand the grid so the user can change their table selection. */
    public function expandGrid(): void
    {
        $this->gridCollapsed = false;
    }

    // ── Occupied / needs-cleaning modal ───────────────────────────────────────

    public function openModal(int $tableId): void
    {
        $table = collect($this->tables)->firstWhere('id', $tableId);
        if (!$table) return;

        $this->resetModalState();
        $this->activeTableId         = $tableId;
        $this->activeTableName       = $table['name'] . ' — Table ' . $table['number'];
        $this->activeOrderId         = $table['current_order_id'];
        $this->activeOrderTotal      = $table['order_total'];
        $this->activeOrderItemsCount = $table['order_items_count'];
        $this->showModal             = true;
        $this->modalMode             = 'main';
    }

    // ── Available table modal ─────────────────────────────────────────────────

    public function openAvailableModal(int $tableId): void
    {
        $table = collect($this->tables)->firstWhere('id', $tableId);
        if (!$table) return;

        $this->resetModalState();
        $this->activeTableId   = $tableId;
        $this->activeTableName = $table['name'];
        $this->showModal       = true;
        $this->modalMode       = 'available_action';
    }

    // ── Occupied table — two-choice modal ─────────────────────────────────────

    public function openOccupiedActionModal(int $tableId): void
    {
        $table = collect($this->tables)->firstWhere('id', $tableId);
        if (!$table) return;

        $this->resetModalState();
        $this->activeTableId         = $tableId;
        $this->activeTableName       = $table['name'] . ' — Table ' . $table['number'];
        $this->activeOrderId         = $table['current_order_id'];
        $this->activeOrderTotal      = $table['order_total'];
        $this->activeOrderItemsCount = $table['order_items_count'];
        $this->showModal             = true;
        $this->modalMode             = 'occupied_action';
    }

    /** User chose "Add to Current Order" — dispatch browser event to POS JS. */
    public function startContinueOrder(): void
    {
        if (!$this->activeTableId || !$this->activeOrderId) return;

        $table = collect($this->tables)->firstWhere('id', $this->activeTableId);
        if (!$table) return;

        $order = Order::find($this->activeOrderId);
        if (!$order) return;

        $dispatchTableId   = (int) $this->activeTableId;
        $dispatchTableName = $table['name'];
        $dispatchOrderId   = (int) $this->activeOrderId;
        $dispatchOrderNum  = $order->order_number ?? ('#' . $this->activeOrderId);

        $this->selectedTableId   = $this->activeTableId;
        $this->selectedTableName = $table['name'];
        $this->gridCollapsed     = true;

        $this->closeModal();

        $this->dispatchBrowserEvent('continueOrder', [
            'tableId'     => $dispatchTableId,
            'tableName'   => $dispatchTableName,
            'orderId'     => $dispatchOrderId,
            'orderNumber' => $dispatchOrderNum,
        ]);
    }

    /** Switch from occupied_action to the full management modal (View/Manage). */
    public function openFullManageModal(): void
    {
        $this->modalMode = 'main';
    }

    // ── Needs-cleaning — quick clean modal ────────────────────────────────────

    public function openCleaningModal(int $tableId): void
    {
        $table = collect($this->tables)->firstWhere('id', $tableId);
        if (!$table) return;

        $this->resetModalState();
        $this->activeTableId   = $tableId;
        $this->activeTableName = $table['name'] . ' — Table ' . $table['number'];
        $this->showModal       = true;
        $this->modalMode       = 'needs_cleaning_action';
    }

    /** User chose "Start Order" from the available-action modal. */
    public function startOrder(): void
    {
        if (!$this->activeTableId) return;

        $table = collect($this->tables)->firstWhere('id', $this->activeTableId);
        if (!$table) return;

        $this->selectedTableId   = $this->activeTableId;
        $this->selectedTableName = $table['name'];
        $this->gridCollapsed     = true;

        // Capture before closeModal() wipes activeTableId via resetModalState()
        $dispatchTableId   = (int) $this->activeTableId;
        $dispatchTableName = $table['name'];

        $this->closeModal();

        $this->dispatchBrowserEvent('tableSelected', [
            'tableId'   => $dispatchTableId,
            'tableName' => $dispatchTableName,
        ]);
    }

    /** User chose "Manage Table" from the available-action modal. */
    public function initiateManageAvailable(): void
    {
        $this->modalMode = 'manage_available';
    }

    // ── PRE-ORDER SPLIT ───────────────────────────────────────────────────────

    public function initiatePreSplit(): void
    {
        $table = collect($this->tables)->firstWhere('id', $this->activeTableId);
        if (!$table) return;

        $this->preSplitNameA = $table['name'] . 'A';
        $this->preSplitNameB = $table['name'] . 'B';
        $this->modalMode     = 'pre_split';
    }

    public function confirmPreSplit(): void
    {
        $nameA = trim($this->preSplitNameA);
        $nameB = trim($this->preSplitNameB);

        if (!$nameA || !$nameB) {
            $this->notify('error', 'Both sub-table names are required.');
            return;
        }
        if ($nameA === $nameB) {
            $this->notify('error', 'Sub-table names must be different.');
            return;
        }

        try {
            DB::transaction(function () use ($nameA, $nameB) {
                $original = Table::findOrFail($this->activeTableId);
                $maxNum   = Table::where('branch_id', $this->branchId)->max('number') ?? $original->number;

                Table::create([
                    'branch_id'       => $this->branchId,
                    'name'            => $nameA,
                    'number'          => $maxNum + 1,
                    'capacity'        => $original->capacity,
                    'is_active'       => true,
                    'is_occupied'     => false,
                    'status'          => 'available',
                    'parent_table_id' => $original->id,
                ]);

                Table::create([
                    'branch_id'       => $this->branchId,
                    'name'            => $nameB,
                    'number'          => $maxNum + 2,
                    'capacity'        => $original->capacity,
                    'is_active'       => true,
                    'is_occupied'     => false,
                    'status'          => 'available',
                    'parent_table_id' => $original->id,
                ]);

                $original->update(['is_active' => false]);
            });

            $this->notify('success', "Split into '{$nameA}' and '{$nameB}'.");
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: pre-split failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Split failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    // ── PRE-ORDER RENAME ──────────────────────────────────────────────────────

    public function initiatePreRename(): void
    {
        $table = collect($this->tables)->firstWhere('id', $this->activeTableId);
        $this->preRenameValue = $table ? $table['name'] : '';
        $this->modalMode      = 'pre_rename';
    }

    public function confirmPreRename(): void
    {
        $name = trim($this->preRenameValue);
        if (!$name) {
            $this->notify('error', 'Table name cannot be empty.');
            return;
        }

        Table::where('id', $this->activeTableId)->update(['name' => $name]);
        $this->notify('success', "Renamed to '{$name}'.");
        $this->closeModal();
        $this->loadTables();
    }

    // ── PRE-ORDER COMBINE ─────────────────────────────────────────────────────

    public function initiatePreCombine(): void
    {
        $this->combineAvailableTables = collect($this->tables)
            ->filter(fn($t) => $t['is_active']
                && $t['status'] === 'available'
                && $t['id'] !== $this->activeTableId
                && !$t['parent_table_id'])
            ->values()
            ->toArray();

        $this->modalMode = 'pre_combine';
    }

    /** Absorb $targetTableId into the active table (mark target inactive). */
    public function confirmPreCombine(int $targetTableId): void
    {
        try {
            $target = Table::findOrFail($targetTableId);
            $target->update([
                'is_active'       => false,
                'parent_table_id' => $this->activeTableId,
            ]);

            $targetName  = $target->name;
            $activeName  = collect($this->tables)->firstWhere('id', $this->activeTableId)['name'] ?? '';
            $this->notify('success', "'{$targetName}' combined into '{$activeName}'.");
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: combine failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Combine failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    // ── CLEAR TABLE (occupied → needs_cleaning + optional restore) ────────────

    /**
     * Called from the occupied-table main modal.
     * Marks the table as needs_cleaning. If it is a pre-order split sub-table,
     * switches to the 'clear_table' modal mode so the user can optionally restore
     * the original parent table.
     */
    public function clearTable(int $tableId): void
    {
        try {
            $table = Table::findOrFail($tableId);
            $table->update([
                'status'           => 'needs_cleaning',
                'is_occupied'      => false,
                'current_order_id' => null,
            ]);

            if ($table->parent_table_id) {
                $parent   = Table::find($table->parent_table_id);
                $siblings = Table::where('parent_table_id', $table->parent_table_id)
                    ->where('id', '!=', $tableId)
                    ->get();

                $this->clearTableParentId   = $table->parent_table_id;
                $this->clearTableParentName = $parent?->name ?? 'Original table';

                // Restore is only safe when no sibling still has an active order.
                $this->clearTableCanRestore = $siblings->every(
                    fn($s) => !$s->current_order_id && $s->status !== 'occupied'
                );

                $this->loadTables();
                $this->modalMode = 'clear_table';
                return;
            }
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: clearTable failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
        $this->dispatchBrowserEvent('tableStatusChanged');
    }

    /** Restore parent: delete all sub-tables, reactivate original. */
    public function confirmRestoreParent(): void
    {
        try {
            DB::transaction(function () {
                $subTables = Table::where('parent_table_id', $this->clearTableParentId)->get();

                foreach ($subTables as $sub) {
                    if ($sub->current_order_id) {
                        throw new \RuntimeException("'{$sub->name}' still has an active order.");
                    }
                }

                Table::where('parent_table_id', $this->clearTableParentId)->delete();

                Table::findOrFail($this->clearTableParentId)->update([
                    'is_active'   => true,
                    'status'      => 'needs_cleaning',
                    'is_occupied' => false,
                ]);
            });

            $this->notify('success', "'{$this->clearTableParentName}' restored.");
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: restoreParent failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Restore failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    /** Keep sub-tables as-is; just close the modal. */
    public function skipRestore(): void
    {
        $this->closeModal();
        $this->loadTables();
        $this->dispatchBrowserEvent('tableStatusChanged');
    }

    // ── Mark clean (needs_cleaning → available) ───────────────────────────────

    public function markClean(int $tableId): void
    {
        $table = Table::find($tableId);
        if ($table) {
            $table->update([
                'status'           => 'available',
                'is_occupied'      => false,
                'current_order_id' => null,
            ]);
        }
        $this->closeModal();
        $this->loadTables();
        $this->dispatchBrowserEvent('tableStatusChanged');
    }

    // ── Modal helpers ─────────────────────────────────────────────────────────

    public function closeModal(): void
    {
        $this->resetModalState();
        $this->showModal = false;
    }

    /**
     * Navigate back to the previous logical modal screen.
     * Used by the Back button in the modal header.
     */
    public function backToMain(): void
    {
        static $backMap = [
            // Occupied flow
            'transfer'         => 'main',
            'split'            => 'main',
            'merge'            => 'main',
            'amend'            => 'main',
            'history'          => 'main',
            // Available management flow
            'manage_available' => 'available_action',
            'pre_split'        => 'manage_available',
            'pre_rename'       => 'manage_available',
            'pre_combine'      => 'manage_available',
        ];

        $target = $backMap[$this->modalMode] ?? 'main';
        $this->clearSubModeState();
        $this->modalMode = $target;

        if ($target === 'main') {
            $this->loadTables();
            $freshTable = collect($this->tables)->firstWhere('id', $this->activeTableId);
            if ($freshTable) {
                $this->activeOrderId         = $freshTable['current_order_id'];
                $this->activeOrderTotal      = $freshTable['order_total'];
                $this->activeOrderItemsCount = $freshTable['order_items_count'];
            }
        }
    }

    private function resetModalState(): void
    {
        $this->activeTableId         = null;
        $this->activeTableName       = null;
        $this->activeOrderId         = null;
        $this->activeOrderTotal      = null;
        $this->activeOrderItemsCount = 0;
        $this->clearSubModeState();
        $this->modalMode = 'main';
    }

    private function clearSubModeState(): void
    {
        // Occupied flow
        $this->availableTablesForAction = [];
        $this->splitItems               = [];
        $this->splitQtys                = [];
        $this->splitDestTableId         = null;
        $this->occupiedTablesForMerge   = [];
        $this->amendCurrentItems        = [];
        $this->amendRemovals            = [];
        $this->amendAdditions           = [];
        $this->amendReason              = '';
        $this->amendProductSearch       = '';
        $this->amendSearchResults       = [];
        $this->amendHistoryItems        = [];
        // Available flow
        $this->preSplitNameA            = '';
        $this->preSplitNameB            = '';
        $this->preRenameValue           = '';
        $this->combineAvailableTables   = [];
        // Clear flow
        $this->clearTableParentId       = null;
        $this->clearTableParentName     = '';
        $this->clearTableCanRestore     = false;
    }

    // ── TRANSFER ─────────────────────────────────────────────────────────────

    public function initiateTransfer(): void
    {
        $this->availableTablesForAction = collect($this->tables)
            ->filter(fn($t) => $t['is_active'] && $t['status'] === 'available')
            ->values()
            ->toArray();

        $this->modalMode = 'transfer';
    }

    public function confirmTransfer(int $destTableId): void
    {
        if (!$this->activeOrderId || !$this->activeTableId) return;

        try {
            DB::transaction(function () use ($destTableId) {
                $order       = Order::findOrFail($this->activeOrderId);
                $sourceTable = Table::findOrFail($this->activeTableId);
                $destTable   = Table::findOrFail($destTableId);

                $order->update(['table_id' => $destTableId]);

                $sourceTable->update([
                    'status'           => 'needs_cleaning',
                    'is_occupied'      => false,
                    'current_order_id' => null,
                ]);

                $destTable->update([
                    'status'           => 'occupied',
                    'is_occupied'      => true,
                    'current_order_id' => $order->id,
                ]);
            });

            $this->notify('success', 'Order transferred successfully.');
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: transfer failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Transfer failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    // ── POST-ORDER SPLIT ──────────────────────────────────────────────────────

    public function initiateSplit(): void
    {
        if (!$this->activeOrderId) return;

        $order = Order::with('items')->find($this->activeOrderId);
        if (!$order || $order->items->isEmpty()) {
            $this->notify('error', 'No items on this order to split.');
            return;
        }

        $this->splitItems = $order->items->map(fn($item) => [
            'id'        => $item->id,
            'item_name' => $item->item_name,
            'quantity'  => (int) $item->quantity,
            'price'     => (float) $item->price,
        ])->toArray();

        $this->splitQtys = collect($this->splitItems)
            ->mapWithKeys(fn($item) => [$item['id'] => 0])
            ->toArray();

        $this->availableTablesForAction = collect($this->tables)
            ->filter(fn($t) => $t['is_active']
                && $t['status'] === 'available'
                && $t['id'] !== $this->activeTableId)
            ->values()
            ->toArray();

        $this->splitDestTableId = null;
        $this->modalMode        = 'split';
    }

    public function setSplitQty(int $itemId, int $qty): void
    {
        $item = collect($this->splitItems)->firstWhere('id', $itemId);
        if (!$item) return;
        $this->splitQtys[$itemId] = max(0, min((int) $item['quantity'], $qty));
    }

    public function setSplitDest(int $tableId): void
    {
        $this->splitDestTableId = ($this->splitDestTableId === $tableId) ? null : $tableId;
    }

    public function createTempTableForSplit(): void
    {
        $lastTempNumber = Table::where('branch_id', $this->branchId)
            ->where('name', 'like', 'Temp-%')
            ->max('number') ?? 100;

        $tempTable = Table::create([
            'branch_id'   => $this->branchId,
            'name'        => 'Temp-' . date('Hi'),
            'number'      => $lastTempNumber + 1,
            'capacity'    => 10,
            'is_active'   => true,
            'is_occupied' => false,
            'status'      => 'available',
        ]);

        $newEntry = [
            'id'                => $tempTable->id,
            'name'              => $tempTable->name,
            'number'            => $tempTable->number,
            'capacity'          => $tempTable->capacity,
            'status'            => 'available',
            'is_active'         => true,
            'parent_table_id'   => null,
            'combined_names'    => null,
            'current_order_id'  => null,
            'order_total'       => null,
            'order_items_count' => 0,
        ];

        $this->availableTablesForAction[] = $newEntry;
        $this->tables[]                   = $newEntry;

        $this->notify('success', "Temp table '{$tempTable->name}' created.");
    }

    public function confirmSplit(): void
    {
        if (!$this->activeOrderId || !$this->splitDestTableId) {
            $this->notify('error', 'Choose a destination table first.');
            return;
        }

        $itemsToMove = collect($this->splitQtys)->filter(fn($q) => $q > 0)->all();

        if (empty($itemsToMove)) {
            $this->notify('error', 'Select at least one item to split.');
            return;
        }

        foreach ($itemsToMove as $itemId => $moveQty) {
            $item = collect($this->splitItems)->firstWhere('id', (int) $itemId);
            if ($item && $moveQty > $item['quantity']) {
                $this->notify('error', "Can't move more than {$item['quantity']} × {$item['item_name']}.");
                return;
            }
        }

        try {
            DB::transaction(function () use ($itemsToMove) {
                $sourceOrder = Order::with('items')->findOrFail($this->activeOrderId);
                $sourceTable = Table::findOrFail($this->activeTableId);
                $destTable   = Table::findOrFail($this->splitDestTableId);

                $childOrder = Order::create([
                    'branch_id'       => $sourceOrder->branch_id,
                    'user_id'         => $sourceOrder->user_id,
                    'created_by'      => auth()->id(),
                    'table_id'        => $this->splitDestTableId,
                    'parent_table_id' => $sourceTable->id,
                    'order_type'      => 'dine_in',
                    'status'          => 'pending',
                    'payment_status'  => 'unpaid',
                    'order_number'    => 'SPL-' . strtoupper(uniqid()),
                    'subtotal'        => 0,
                    'tax'             => 0,
                    'total'           => 0,
                ]);

                foreach ($itemsToMove as $itemId => $moveQty) {
                    $sourceItem = OrderItem::findOrFail((int) $itemId);
                    $moveQty    = (int) $moveQty;

                    if ($moveQty >= $sourceItem->quantity) {
                        $sourceItem->update(['order_id' => $childOrder->id]);
                    } else {
                        $remainQty = $sourceItem->quantity - $moveQty;
                        $sourceItem->update([
                            'quantity' => $remainQty,
                            'subtotal' => round($remainQty * $sourceItem->price, 2),
                        ]);
                        OrderItem::create([
                            'order_id'   => $childOrder->id,
                            'product_id' => $sourceItem->product_id,
                            'item_name'  => $sourceItem->item_name,
                            'category'   => $sourceItem->category ?? null,
                            'quantity'   => $moveQty,
                            'price'      => $sourceItem->price,
                            'subtotal'   => round($moveQty * $sourceItem->price, 2),
                        ]);
                    }
                }

                $this->recalcOrderTotals($sourceOrder);
                $this->recalcOrderTotals($childOrder);

                $destTable->update([
                    'status'           => 'occupied',
                    'is_occupied'      => true,
                    'current_order_id' => $childOrder->id,
                ]);

                if ($sourceOrder->fresh()->items()->count() === 0) {
                    $sourceTable->update([
                        'status'           => 'needs_cleaning',
                        'is_occupied'      => false,
                        'current_order_id' => null,
                    ]);
                } else {
                    $sourceTable->update(['current_order_id' => $sourceOrder->id]);
                }
            });

            $this->notify('success', 'Table split successfully.');
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: split failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Split failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    // ── MERGE ─────────────────────────────────────────────────────────────────

    public function initiateMerge(): void
    {
        $this->occupiedTablesForMerge = collect($this->tables)
            ->filter(fn($t) => $t['is_active']
                && $t['status'] === 'occupied'
                && $t['id'] !== $this->activeTableId
                && !empty($t['current_order_id']))
            ->values()
            ->toArray();

        $this->modalMode = 'merge';
    }

    public function confirmMerge(int $sourceTableId): void
    {
        if (!$this->activeOrderId || !$this->activeTableId) return;

        try {
            DB::transaction(function () use ($sourceTableId) {
                $targetOrder      = Order::with('items')->findOrFail($this->activeOrderId);
                $sourceTableData  = collect($this->tables)->firstWhere('id', $sourceTableId);
                $sourceOrderId    = $sourceTableData['current_order_id'] ?? null;

                if (!$sourceOrderId) {
                    throw new \RuntimeException('No active order on source table.');
                }

                $sourceOrder      = Order::with('items')->findOrFail($sourceOrderId);
                $sourceTableModel = Table::findOrFail($sourceTableId);

                foreach ($sourceOrder->items as $item) {
                    $existing = $targetOrder->items()
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($existing) {
                        $newQty = $existing->quantity + $item->quantity;
                        $existing->update([
                            'quantity' => $newQty,
                            'subtotal' => round($newQty * $existing->price, 2),
                        ]);
                        $item->delete();
                    } else {
                        $item->update(['order_id' => $targetOrder->id]);
                    }
                }

                $this->recalcOrderTotals($targetOrder);

                $sourceOrder->update([
                    'status' => 'cancelled',
                    'notes'  => trim(($sourceOrder->notes ?? '') . ' [Merged → Order #' . $targetOrder->id . ']'),
                ]);

                $sourceTableModel->update([
                    'status'           => 'needs_cleaning',
                    'is_occupied'      => false,
                    'current_order_id' => null,
                ]);
            });

            $this->notify('success', 'Tables merged successfully.');
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: merge failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Merge failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    // ── AMEND ─────────────────────────────────────────────────────────────────

    public function initiateAmend(): void
    {
        if (!$this->activeOrderId) return;

        $order = Order::with('items')->find($this->activeOrderId);
        if (!$order) return;

        $this->amendCurrentItems = $order->items->map(fn($item) => [
            'id'        => $item->id,
            'item_name' => $item->item_name,
            'quantity'  => (int) $item->quantity,
            'price'     => (float) $item->price,
            'subtotal'  => (float) $item->subtotal,
        ])->toArray();

        $this->amendRemovals = collect($this->amendCurrentItems)
            ->mapWithKeys(fn($item) => [$item['id'] => 0])
            ->toArray();

        $this->amendAdditions     = [];
        $this->amendReason        = '';
        $this->amendProductSearch = '';
        $this->amendSearchResults = [];
        $this->modalMode          = 'amend';
    }

    public function setAmendRemoval(int $itemId, int $qty): void
    {
        $item = collect($this->amendCurrentItems)->firstWhere('id', $itemId);
        if (!$item) return;
        $this->amendRemovals[$itemId] = max(0, min((int) $item['quantity'], $qty));
    }

    public function updatedAmendProductSearch(): void
    {
        $q = trim($this->amendProductSearch);
        if (strlen($q) < 2) {
            $this->amendSearchResults = [];
            return;
        }

        $alreadyAdded = collect($this->amendAdditions)->pluck('product_id')->all();

        $this->amendSearchResults = Product::where('branch_id', $this->branchId)
            ->where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->whereNotIn('id', $alreadyAdded)
            ->select('id', 'name', 'price', 'category')
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'price'    => (float) $p->price,
                'category' => $p->category ?? '',
            ])
            ->toArray();
    }

    public function addToAmendAdditions(int $productId): void
    {
        if (collect($this->amendAdditions)->firstWhere('product_id', $productId)) return;

        $product = Product::select('id', 'name', 'price')->find($productId);
        if (!$product) return;

        $this->amendAdditions[] = [
            'product_id' => $product->id,
            'name'       => $product->name,
            'price'      => (float) $product->price,
            'qty'        => 1,
        ];

        $this->amendSearchResults = collect($this->amendSearchResults)
            ->filter(fn($r) => $r['id'] !== $productId)
            ->values()
            ->toArray();
        $this->amendProductSearch = '';
    }

    public function setAdditionQty(int $productId, int $qty): void
    {
        $this->amendAdditions = collect($this->amendAdditions)
            ->map(function ($item) use ($productId, $qty) {
                if ($item['product_id'] === $productId) {
                    $item['qty'] = max(1, $qty);
                }
                return $item;
            })
            ->toArray();
    }

    public function removeFromAdditions(int $productId): void
    {
        $this->amendAdditions = collect($this->amendAdditions)
            ->filter(fn($item) => $item['product_id'] !== $productId)
            ->values()
            ->toArray();
    }

    public function confirmAmend(): void
    {
        $hasRemovals  = collect($this->amendRemovals)->sum() > 0;
        $hasAdditions = !empty($this->amendAdditions);

        if (!$hasRemovals && !$hasAdditions) {
            $this->notify('error', 'No changes to apply.');
            return;
        }

        try {
            DB::transaction(function () use ($hasRemovals, $hasAdditions) {
                $order        = Order::with('items')->findOrFail($this->activeOrderId);
                $amountBefore = (float) ($order->total ?? 0);
                $itemsRemoved = [];
                $itemsAdded   = [];

                if ($hasRemovals) {
                    foreach ($this->amendRemovals as $itemId => $removeQty) {
                        if ($removeQty <= 0) continue;
                        $item = OrderItem::findOrFail((int) $itemId);

                        $itemsRemoved[] = [
                            'item_name' => $item->item_name,
                            'qty'       => (int) $removeQty,
                            'price'     => (float) $item->price,
                        ];

                        if ($removeQty >= $item->quantity) {
                            $item->delete();
                        } else {
                            $newQty = $item->quantity - $removeQty;
                            $item->update([
                                'quantity' => $newQty,
                                'subtotal' => round($newQty * $item->price, 2),
                            ]);
                        }
                    }
                }

                if ($hasAdditions) {
                    foreach ($this->amendAdditions as $addition) {
                        $product  = Product::findOrFail($addition['product_id']);
                        $qty      = (int) $addition['qty'];
                        $price    = (float) $addition['price'];
                        $existing = $order->items()->where('product_id', $product->id)->first();

                        if ($existing) {
                            $newQty = $existing->quantity + $qty;
                            $existing->update([
                                'quantity' => $newQty,
                                'subtotal' => round($newQty * $existing->price, 2),
                            ]);
                        } else {
                            OrderItem::create([
                                'order_id'   => $order->id,
                                'product_id' => $product->id,
                                'item_name'  => $product->name,
                                'quantity'   => $qty,
                                'price'      => $price,
                                'subtotal'   => round($qty * $price, 2),
                            ]);
                        }

                        $itemsAdded[] = [
                            'item_name' => $product->name,
                            'qty'       => $qty,
                            'price'     => $price,
                        ];
                    }
                }

                $this->recalcOrderTotals($order);
                $order->refresh();
                $amountChange = round((float) ($order->total ?? 0) - $amountBefore, 2);

                OrderAmendment::create([
                    'order_id'      => $order->id,
                    'amended_by'    => auth()->id(),
                    'items_removed' => $itemsRemoved ?: null,
                    'items_added'   => $itemsAdded   ?: null,
                    'amount_change' => $amountChange,
                    'reason'        => $this->amendReason ?: null,
                ]);

                Table::where('id', $this->activeTableId)
                    ->update(['current_order_id' => $order->id]);
            });

            $this->notify('success', 'Order amended and recorded.');
        } catch (\Throwable $e) {
            \Log::error('PosTableGrid: amend failed', ['error' => $e->getMessage()]);
            $this->notify('error', 'Amendment failed: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->loadTables();
    }

    // ── HISTORY ───────────────────────────────────────────────────────────────

    public function showHistory(): void
    {
        if (!$this->activeOrderId) return;

        $this->amendHistoryItems = OrderAmendment::with('amendedBy')
            ->where('order_id', $this->activeOrderId)
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id'            => $a->id,
                'amended_by'    => $a->amendedBy?->name ?? 'Unknown',
                'items_removed' => $a->items_removed ?? [],
                'items_added'   => $a->items_added ?? [],
                'amount_change' => (float) $a->amount_change,
                'reason'        => $a->reason ?? '',
                'created_at'    => $a->created_at->format('M j, g:i A'),
            ])
            ->toArray();

        $this->modalMode = 'history';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function recalcOrderTotals(Order $order): void
    {
        $order->refresh();
        $subtotal = (float) $order->items()->sum('subtotal');
        $taxRate  = ($order->subtotal > 0 && $order->tax > 0)
            ? ($order->tax / $order->subtotal)
            : 0.0;
        $tax   = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $tax, 2);

        $order->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $total,
        ]);
    }

    private function notify(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('posNotify', [
            'type'    => $type,
            'message' => $message,
        ]);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.pos-table-grid');
    }
}
