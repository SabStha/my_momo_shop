<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\DailyStockCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryCheckController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch') ?? session('current_branch_id');
        
        if (!$branchId) {
            return redirect()->route('admin.branch-inventories.index')
                ->with('error', 'Please select a branch first.');
        }

        $items = InventoryItem::where('branch_id', $branchId)
            ->with(['dailyChecks' => function ($query) {
                $query->whereDate('check_date', today());
            }])
            ->get();

        return view('admin.inventory.checks.index', compact('items', 'branchId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:0',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string',
            'item_ids' => 'required|array',
            'item_ids.*' => 'required|exists:inventory_items,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->item_ids as $index => $itemId) {
                if (isset($request->quantities[$itemId])) {
                    $item = InventoryItem::find($itemId);
                    $quantityChecked = $request->quantities[$itemId];
                    
                    DailyStockCheck::updateOrCreate(
                        [
                            'inventory_item_id' => $itemId,
                            'branch_id' => $request->branch_id ?? session('current_branch_id'),
                            'check_date' => today(),
                        ],
                        [
                            'checked_by' => auth()->id(),
                            'opening_stock' => $item->current_stock,
                            'closing_stock' => $quantityChecked,
                            'wastage' => max(0, $item->current_stock - $quantityChecked),
                            'notes' => $request->notes[$itemId] ?? null
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Stock checks recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record stock checks. Please try again.');
        }
    }
} 