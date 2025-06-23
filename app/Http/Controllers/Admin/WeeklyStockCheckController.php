<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\WeeklyStockCheck;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeeklyStockCheckController extends Controller
{
    public function index(Request $request)
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

        return view('admin.inventory.weekly-checks.index', compact('items', 'branch'));
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
                    WeeklyStockCheck::updateOrCreate(
                        [
                            'inventory_item_id' => $itemId,
                            'checked_at' => now()->startOfWeek(),
                        ],
                        [
                            'user_id' => auth()->id(),
                            'quantity_checked' => $request->quantities[$itemId],
                            'notes' => $request->notes[$itemId] ?? null
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Weekly stock checks recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record weekly stock checks. Please try again.');
        }
    }
} 