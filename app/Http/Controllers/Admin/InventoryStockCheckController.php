<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\StockCheckService;

class InventoryStockCheckController extends Controller
{
    public function dailyCheck(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = InventoryItem::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $items = $query->with(['category', 'supplier'])
            ->orderBy('name')
            ->get();

        return view('admin.inventory.daily-check', compact('items', 'branch'));
    }

    public function submitDailyCheck(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:branch_inventories,id',
            'items.*.counted_quantity' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255'
        ]);

        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        
        foreach ($request->items as $item) {
            $inventory = \App\Models\BranchInventory::find($item['id']);
            if ($inventory && $inventory->branch_id === $branch->id) {
                $inventory->update([
                    'counted_quantity' => $item['counted_quantity'],
                    'counted_at' => now(),
                    'counted_by' => auth()->id(),
                    'count_notes' => $item['notes'] ?? null
                ]);
            }
        }

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Daily check completed successfully.');
    }

    public function stockCheck(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        if ($branchId) {
            $branch = Branch::find($branchId);
        }
        $universal = !$branchId;

        $service = new StockCheckService();
        $daily = $service->performDailyCheck($branchId);
        $weekly = $service->performWeeklyCheck($branchId);
        $monthly = $service->performMonthlyCheck($branchId);

        return view('admin.inventory.stock-check', compact('branch', 'universal', 'daily', 'weekly', 'monthly'));
    }

    public function weeklyChecks(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = InventoryItem::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $items = $query->with(['category', 'supplier', 'weeklyChecks' => function ($query) {
            $query->whereDate('checked_at', now()->startOfWeek());
        }])
        ->orderBy('name')
        ->get();

        $categories = InventoryCategory::orderBy('name')->get();

        return view('admin.inventory.weekly-checks.index', compact('items', 'branch', 'categories'));
    }

    public function storeWeeklyChecks(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:0',
            'audit_notes' => 'nullable|array',
            'audit_notes.*' => 'nullable|string',
            'is_damaged' => 'nullable|array',
            'is_damaged.*' => 'boolean',
            'is_missing' => 'nullable|array',
            'is_missing.*' => 'boolean',
            'item_ids' => 'required|array',
            'item_ids.*' => 'required|exists:inventory_items,id',
            'branch_id' => 'nullable|exists:branches,id',
            'audit_session_id' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $auditSessionId = $request->audit_session_id ?? Str::uuid();
            $branchId = $request->branch_id;

            foreach ($request->item_ids as $index => $itemId) {
                if (isset($request->quantities[$itemId])) {
                    $item = InventoryItem::find($itemId);
                    $systemStock = $item->current_stock;
                    $actualCount = $request->quantities[$itemId];
                    $discrepancyAmount = $actualCount - $systemStock;
                    $discrepancyValue = $discrepancyAmount * $item->unit_price;

                    $imagePath = null;
                    if (isset($request->images[$itemId]) && $request->images[$itemId]->isValid()) {
                        $imagePath = $request->images[$itemId]->store('audit-images/weekly', 'public');
                    }

                    \App\Models\WeeklyStockCheck::updateOrCreate(
                        [
                            'inventory_item_id' => $itemId,
                            'checked_at' => now()->startOfWeek(),
                        ],
                        [
                            'user_id' => auth()->id(),
                            'branch_id' => $branchId,
                            'quantity_checked' => $actualCount,
                            'system_stock' => $systemStock,
                            'discrepancy_amount' => $discrepancyAmount,
                            'discrepancy_value' => $discrepancyValue,
                            'audit_notes' => $request->audit_notes[$itemId] ?? null,
                            'is_damaged' => isset($request->is_damaged[$itemId]),
                            'is_missing' => isset($request->is_missing[$itemId]),
                            'image_path' => $imagePath,
                            'audit_session_id' => $auditSessionId,
                            'audit_started_at' => now(),
                            'audit_completed_at' => now()
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Weekly stock checks recorded successfully with advanced audit trail.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record weekly stock checks. Please try again.');
        }
    }

    public function monthlyChecks(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = InventoryItem::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $items = $query->with(['category', 'supplier', 'monthlyChecks' => function ($query) {
            $query->whereDate('checked_at', now()->startOfMonth());
        }])
        ->orderBy('name')
        ->get();

        $categories = InventoryCategory::orderBy('name')->get();

        return view('admin.inventory.monthly-checks.index', compact('items', 'branch', 'categories'));
    }

    public function storeMonthlyChecks(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:0',
            'audit_notes' => 'nullable|array',
            'audit_notes.*' => 'nullable|string',
            'is_damaged' => 'nullable|array',
            'is_damaged.*' => 'boolean',
            'is_missing' => 'nullable|array',
            'is_missing.*' => 'boolean',
            'item_ids' => 'required|array',
            'item_ids.*' => 'required|exists:inventory_items,id',
            'branch_id' => 'nullable|exists:branches,id',
            'audit_session_id' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $auditSessionId = $request->audit_session_id ?? Str::uuid();
            $branchId = $request->branch_id;

            foreach ($request->item_ids as $index => $itemId) {
                if (isset($request->quantities[$itemId])) {
                    $item = InventoryItem::find($itemId);
                    $systemStock = $item->current_stock;
                    $actualCount = $request->quantities[$itemId];
                    $discrepancyAmount = $actualCount - $systemStock;
                    $discrepancyValue = $discrepancyAmount * $item->unit_price;

                    $imagePath = null;
                    if (isset($request->images[$itemId]) && $request->images[$itemId]->isValid()) {
                        $imagePath = $request->images[$itemId]->store('audit-images/monthly', 'public');
                    }

                    \App\Models\MonthlyStockCheck::updateOrCreate(
                        [
                            'inventory_item_id' => $itemId,
                            'checked_at' => now()->startOfMonth(),
                        ],
                        [
                            'user_id' => auth()->id(),
                            'branch_id' => $branchId,
                            'quantity_checked' => $actualCount,
                            'system_stock' => $systemStock,
                            'discrepancy_amount' => $discrepancyAmount,
                            'discrepancy_value' => $discrepancyValue,
                            'audit_notes' => $request->audit_notes[$itemId] ?? null,
                            'is_damaged' => isset($request->is_damaged[$itemId]),
                            'is_missing' => isset($request->is_missing[$itemId]),
                            'image_path' => $imagePath,
                            'audit_session_id' => $auditSessionId,
                            'audit_started_at' => now(),
                            'audit_completed_at' => now()
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Monthly stock checks recorded successfully with advanced audit trail.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record monthly stock checks. Please try again.');
        }
    }
}
