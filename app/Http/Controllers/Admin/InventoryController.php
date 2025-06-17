<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryOrder;
use App\Models\BranchInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch' => $branch]);
        }

        $query = InventoryItem::query();
        
        if ($branchId) {
            // When viewing a specific branch, show only its items
            $query->where('branch_id', $branchId);
        } else {
            // When viewing main inventory, show items from all branches
            $query->whereNull('branch_id')
                  ->orWhereHas('branch', function($q) {
                      $q->where('is_main', true);
                  });
        }

        $items = $query->with(['category', 'supplier', 'branch'])
            ->orderBy('name')
            ->paginate(10);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $lowStockCount = $query->whereRaw('current_stock <= reorder_point')->count();
        $branches = Branch::orderBy('name')->get();
        
        // Get orders for the current branch with supplier relationship and paginate
        $orders = InventoryOrder::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->with(['items', 'branch', 'supplier'])
          ->orderBy('ordered_at', 'desc')
          ->paginate(10);
        
        return view('admin.inventory.index', compact('items', 'categories', 'lowStockCount', 'branches', 'branch', 'orders'));
    }

    public function create(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch' => $branch]);
        } else {
            $branch = session('selected_branch');
            if (!$branch) {
                return redirect()->route('admin.branches.index')
                    ->with('error', 'Please select a branch first.');
            }
        }

        $categories = InventoryCategory::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.inventory.create', compact('categories', 'suppliers', 'branch'));
    }

    public function store(Request $request)
    {
        try {
            $branchId = $request->input('branch_id');
            $branch = null;
            
            if ($branchId) {
                $branch = Branch::findOrFail($branchId);
                session(['selected_branch' => $branch]);
            } else {
                $branch = session('selected_branch');
                if (!$branch) {
                    // If no branch is selected, create the item in the main branch
                    $mainBranch = Branch::where('is_main', true)->first();
                    if (!$mainBranch) {
                        return redirect()->route('admin.branches.index')
                            ->with('error', 'Main branch not found. Please set up a main branch first.');
                    }
                    $branch = $mainBranch;
                    session(['selected_branch' => $branch]);
                }
                $branchId = $branch->id;
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:50|unique:inventory_items',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:inventory_categories,id',
                'unit' => 'required|string|max:50',
                'unit_price' => 'required|numeric|min:0',
                'reorder_point' => 'required|numeric|min:0',
                'current_stock' => 'required|numeric|min:0',
                'supplier_id' => 'nullable|exists:suppliers,id'
            ]);

            // Add branch_id to validated data
            $validated['branch_id'] = $branchId;

            DB::beginTransaction();

            // Set default status
            $validated['status'] = 'active';

            $item = InventoryItem::create($validated);

            // Create initial transaction if quantity is provided
            if ($validated['current_stock'] > 0) {
                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'purchase',
                    'quantity' => $validated['current_stock'],
                    'unit_price' => $validated['unit_price'],
                    'total_amount' => $validated['current_stock'] * $validated['unit_price'],
                    'notes' => 'Initial stock',
                    'user_id' => auth()->id(),
                ]);
            }

            ActivityLogService::logInventoryActivity(
                'create',
                'Created inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'sku' => $item->sku,
                    'initial_stock' => $validated['current_stock'],
                    'branch_id' => $branchId
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.inventory.index', ['branch' => $branchId])
                ->with('success', 'Inventory item created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating inventory item: ' . $e->getMessage());
            return back()
                ->with('error', 'Error creating inventory item. Please try again.')
                ->withInput();
        }
    }

    public function show(InventoryItem $item)
    {
        $transactions = $item->transactions()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.inventory.show', compact('item', 'transactions'));
    }

    public function edit(InventoryItem $item)
    {
        $categories = InventoryCategory::all();
        $suppliers = Supplier::orderBy('name')->get();
        return view('admin.inventory.edit', compact('item', 'categories', 'suppliers'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:inventory_items,sku,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:inventory_categories,id',
            'unit_price' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);

        try {
            $oldData = $item->toArray();
            $item->update($validated);

            ActivityLogService::logInventoryActivity(
                'update',
                'Updated inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'old_data' => $oldData,
                    'new_data' => $validated
                ]
            );

            return redirect()
                ->route('admin.inventory.edit', $item)
                ->with('success', 'Inventory item updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating inventory item: ' . $e->getMessage());
            return back()->with('error', 'Error updating inventory item. Please try again.');
        }
    }

    public function adjust(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'type' => 'required|in:purchase,return,sale,waste,adjustment',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldStock = $item->current_stock;
            $item->updateQuantity(
                $validated['quantity'],
                $validated['type'],
                $validated['notes']
            );

            ActivityLogService::logInventoryActivity(
                'adjust',
                'Adjusted inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'type' => $validated['type'],
                    'quantity' => $validated['quantity'],
                    'old_stock' => $oldStock,
                    'new_stock' => $item->current_stock,
                    'notes' => $validated['notes']
                ]
            );

            DB::commit();
            return redirect()->route('admin.inventory.show', $item)
                ->with('success', 'Inventory adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adjusting inventory: ' . $e->getMessage());
            return back()->with('error', 'Error adjusting inventory. Please try again.');
        }
    }

    public function destroy(InventoryItem $item)
    {
        try {
            $itemData = $item->toArray();
            $item->delete();

            ActivityLogService::logInventoryActivity(
                'delete',
                'Deleted inventory item: ' . $itemData['name'],
                [
                    'item_id' => $itemData['id'],
                    'sku' => $itemData['sku'],
                    'final_stock' => $itemData['current_stock']
                ]
            );

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory item deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting inventory item: ' . $e->getMessage());
            return back()->with('error', 'Error deleting inventory item. Please try again.');
        }
    }

    public function categories(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch' => $branch]);
        }

        $query = InventoryCategory::query();
        
        if ($branchId) {
            // When viewing a specific branch, show all categories but count only items in this branch
            $query->withCount(['items' => function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }]);
        } else {
            $query->withCount('items');
        }

        $categories = $query->orderBy('name')->get();

        return view('admin.inventory.categories', compact('categories', 'branch'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:inventory_categories',
            'description' => 'nullable|string',
        ]);

        try {
            $category = InventoryCategory::create([
                ...$validated,
                'is_active' => true
            ]);

            $branchId = $request->query('branch') ?? session('selected_branch_id');
            if ($branchId) {
                return redirect()->route('admin.inventory.categories', ['branch' => $branchId])
                    ->with('success', 'Category created successfully.');
            }

            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return back()->with('error', 'Error creating category. Please try again.');
        }
    }

    public function updateCategory(Request $request, InventoryCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:inventory_categories,code,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            $category->update($validated);

            $branchId = $request->query('branch') ?? session('selected_branch_id');
            if ($branchId) {
                return redirect()->route('admin.inventory.categories', ['branch' => $branchId])
                    ->with('success', 'Category updated successfully.');
            }

            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return back()->with('error', 'Error updating category. Please try again.');
        }
    }

    public function deleteCategory(InventoryCategory $category)
    {
        try {
            if ($category->items()->count() > 0) {
                return back()->with('error', 'Cannot delete category with associated items.');
            }

            $category->delete();

            $branchId = request()->query('branch') ?? session('selected_branch_id');
            if ($branchId) {
                return redirect()->route('admin.inventory.categories', ['branch' => $branchId])
                    ->with('success', 'Category deleted successfully.');
            }

            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return back()->with('error', 'Error deleting category. Please try again.');
        }
    }

    public function toggleLock(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->update([
            'is_locked' => $request->is_locked
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item lock status updated successfully'
        ]);
    }

    public function manage(Request $request)
    {
        $query = Inventory::with(['category', 'branch']);

        if ($request->has('branch')) {
            $branch = Branch::findOrFail($request->branch);
            $query->where('branch_id', $branch->id);
        }

        $items = $query->orderBy('name')->paginate(10);
        $branches = Branch::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

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
                    'sku' => $item->sku
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
                    'sku' => $item->sku
                ]
            );

            return response()->json(['message' => 'Item unlocked successfully']);
        } catch (\Exception $e) {
            Log::error('Error unlocking inventory item: ' . $e->getMessage());
            return response()->json(['message' => 'Error unlocking item'], 500);
        }
    }

    public function orderLockedItems(Request $request)
    {
        $lockedItems = \App\Models\InventoryItem::where('is_locked', true)->get();
        if ($lockedItems->isEmpty()) {
            return redirect()->back()->with('error', 'No locked items to order.');
        }
        $supplierGroups = $lockedItems->groupBy('supplier_id');
        foreach ($supplierGroups as $supplierId => $items) {
            $order = new \App\Models\InventoryOrder();
            $order->supplier_id = $supplierId;
            $order->order_number = $order->generateOrderNumber();
            $order->status = 'pending';
            $order->ordered_at = now();
            $order->total_amount = 0;
            $order->save();
            $total = 0;
            foreach ($items as $item) {
                $qty = $item->reorder_point > 0 ? $item->reorder_point : 1;
                $itemTotal = $qty * $item->unit_price;
                $order->items()->create([
                    'inventory_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $item->unit_price,
                    'total_price' => $itemTotal
                ]);
                $total += $itemTotal;
                $item->update(['is_locked' => false]);
            }
            $order->update(['total_amount' => $total]);
        }
        return redirect()->route('admin.supply.orders.index')->with('success', 'Supply orders created for all suppliers.');
    }

    public function dailyCheck(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch' => $branch]);
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

        $branch = session('selected_branch');
        
        foreach ($request->items as $item) {
            $inventory = BranchInventory::find($item['id']);
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

    public function bulkOrder(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch' => $branch]);
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

        $branch = session('selected_branch');
        
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

    public function lockInventory(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
            session(['selected_branch' => $branch]);
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

        \Log::info('Item locked:', [
            'item_id' => $item->id,
            'branch_id' => $branchId,
            'is_locked' => $item->is_locked
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

    public function indexInventory()
    {
        $branch = session('selected_branch');
        $inventory = Inventory::where('branch_id', $branch->id)
            ->with(['product'])
            ->paginate(10);
        $branches = \App\Models\Branch::orderBy('name')->get();
        return view('admin.inventory.index', compact('inventory', 'branches', 'branch'));
    }

    public function createInventory()
    {
        $branch = session('selected_branch');
        $products = Product::where('branch_id', $branch->id)->get();
        return view('admin.inventory.create', compact('products', 'branch'));
    }

    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $branch = session('selected_branch');
        $validated['branch_id'] = $branch->id;
        $validated['created_by'] = Auth::id();

        $inventory = Inventory::create($validated);

        return redirect()->route('admin.inventory.show', $inventory)
            ->with('success', 'Inventory item created successfully.');
    }

    public function showInventory(Inventory $inventory)
    {
        $inventory->load(['product', 'branch', 'history']);
        return view('admin.inventory.show', compact('inventory'));
    }

    public function editInventory(Inventory $inventory)
    {
        $inventory->load('product');
        return view('admin.inventory.edit', compact('inventory'));
    }

    public function updateInventory(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'reorder_level' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $inventory->update($validated);

        return redirect()->route('admin.inventory.show', $inventory)
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroyInventory(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function adjustStock(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'reason' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($inventory, $validated) {
            $oldQuantity = $inventory->quantity;
            $inventory->quantity += $validated['quantity'];
            $inventory->save();

            $inventory->history()->create([
                'old_quantity' => $oldQuantity,
                'new_quantity' => $inventory->quantity,
                'adjustment' => $validated['quantity'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id()
            ]);
        });

        return redirect()->route('admin.inventory.show', $inventory)
            ->with('success', 'Stock adjusted successfully.');
    }

    public function historyInventory(Inventory $inventory)
    {
        $history = $inventory->history()
            ->with('createdBy')
            ->latest()
            ->paginate(10);

        return view('admin.inventory.history', compact('inventory', 'history'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'update_field' => 'required|in:price,quantity,status',
            'update_value' => 'required'
        ]);

        $query = Inventory::query();

} 
}