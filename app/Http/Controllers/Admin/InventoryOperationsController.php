<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\InventoryOrder;
use App\Models\BranchInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\InventoryService;
use App\Services\ActivityLogService;

class InventoryOperationsController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function manage(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $items = $query->orderBy('name')->paginate(10);
        $branches = Branch::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.inventory.manage', compact('items', 'branches', 'categories'));
    }

    public function lock(Request $request, InventoryItem $item)
    {
        try {
            $item->update(['is_locked' => true]);

            ActivityLogService::logInventoryActivity(
                'lock',
                'Locked inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'sku' => $item->code
                ]
            );

            return response()->json(['message' => 'Item locked successfully']);
        } catch (\Exception $e) {
            Log::error('Error locking inventory item: ' . $e->getMessage());
            return response()->json(['message' => 'Error locking item'], 500);
        }
    }

    public function unlock(Request $request, InventoryItem $item)
    {
        try {
            $item->update(['is_locked' => false]);

            ActivityLogService::logInventoryActivity(
                'unlock',
                'Unlocked inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'sku' => $item->code
                ]
            );

            return response()->json(['message' => 'Item unlocked successfully']);
        } catch (\Exception $e) {
            Log::error('Error unlocking inventory item: ' . $e->getMessage());
            return response()->json(['message' => 'Error unlocking item'], 500);
        }
    }

    public function toggleLock(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->is_locked = !$item->is_locked;
        $item->save();

        return response()->json(['success' => true, 'is_locked' => $item->is_locked]);
    }

    public function lockInventory(Request $request)
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
            ->where('is_locked', false)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.lock', compact('items', 'branch'));
    }

    public function submitLockInventory(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id'
        ]);

        $branchId = session('selected_branch_id') ?? $request->query('branch');
        
        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'Branch ID is required.'
            ], 400);
        }

        $item = InventoryItem::where('id', $request->item_id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in this branch.'
            ], 404);
        }

        $item->update([
            'is_locked' => true,
            'locked_by' => auth()->id(),
            'locked_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item has been locked successfully.'
        ]);
    }

    public function unlockInventory(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id'
        ]);

        $item = InventoryItem::findOrFail($request->item_id);
        $item->update([
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item has been unlocked successfully.'
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:inventory_categories,id',
            'update_field' => 'required|in:price,quantity,status',
            'update_value' => 'required'
        ]);

        $query = InventoryItem::query();

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->get();
        $updatedCount = 0;

        foreach ($items as $item) {
            switch ($request->update_field) {
                case 'price':
                    $item->update(['unit_price' => $request->update_value]);
                    break;
                case 'quantity':
                    $item->update(['current_stock' => $request->update_value]);
                    break;
                case 'status':
                    $item->update(['status' => $request->update_value]);
                    break;
            }
            $updatedCount++;
        }

        return redirect()->back()->with('success', "Successfully updated {$updatedCount} items.");
    }

    public function bulkOrder(Request $request)
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
            ->whereRaw('current_stock <= reorder_point')
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.inventory.bulk-order', compact('items', 'suppliers', 'branch'));
    }

    public function submitBulkOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:branch_inventories,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:1000'
        ]);

        $branch = Branch::find(session('selected_branch_id'));
        
        DB::beginTransaction();
        try {
            $order = InventoryOrder::create([
                'branch_id' => $branch->id,
                'supplier_id' => $request->supplier_id,
                'status' => 'pending',
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);

            foreach ($request->items as $item) {
                $inventory = BranchInventory::find($item['id']);
                if ($inventory && $inventory->branch_id === $branch->id) {
                    $order->items()->create([
                        'branch_inventory_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.inventory.orders.show', $order)
                ->with('success', 'Bulk order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create bulk order. Please try again.');
        }
    }

    public function orderLockedItems(Request $request)
    {
        $count = $this->inventoryService->orderLockedItems();
        
        if ($count === 0) {
            return redirect()->back()->with('error', 'No locked items to order.');
        }

        return redirect()->route('admin.supply.orders.index')->with('success', "Supply orders created for {$count} suppliers.");
    }

    public function export(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:all,low_stock,category',
            'category_id' => 'nullable|exists:inventory_categories,id'
        ]);

        $items = $this->inventoryService->getExportData($request->export_type, $request->category_id);

        $filename = 'inventory_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'Name', 'Category', 'Supplier', 'Branch', 'Current Stock', 'Unit Price', 'Reorder Point', 'Status', 'Unit']);
            
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->code,
                    $item->name,
                    $item->category->name ?? 'N/A',
                    $item->supplier->name ?? 'N/A',
                    $item->branch->name ?? 'N/A',
                    $item->current_stock,
                    $item->unit_price,
                    $item->reorder_point,
                    $item->status,
                    $item->unit
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:2048',
            'update_existing' => 'boolean'
        ]);

        try {
            $result = $this->inventoryService->processImport(
                $request->file('import_file')->getPathname(),
                $request->boolean('update_existing')
            );

            $message = "Import completed. Imported: {$result['imported']}, Updated: {$result['updated']}";
            if (!empty($result['errors'])) {
                $message .= ". Errors: " . implode(', ', array_slice($result['errors'], 0, 5));
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function adjust(Request $request, InventoryItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'adjustment_type' => 'required|in:add,subtract',
            'reason' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $result = $this->inventoryService->adjustStock(
                $item,
                $request->quantity,
                $request->adjustment_type,
                $request->reason,
                $request->notes
            );

            return response()->json([
                'message' => 'Stock adjusted successfully',
                'new_stock' => $result['new_stock']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
