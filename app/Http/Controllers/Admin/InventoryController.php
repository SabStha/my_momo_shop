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
use App\Services\StockCheckService;

class InventoryController extends Controller
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
        
        // Show "Main Branch" as the supplier option for all branches
        $mainBranch = Branch::where('is_main', true)->first();
        $suppliers = collect();
        if ($mainBranch) {
            $mainBranchSupplier = new \stdClass();
            $mainBranchSupplier->id = $mainBranch->id;
            $mainBranchSupplier->name = 'Main Branch';
            $suppliers->push($mainBranchSupplier);
        }
        
        $lowStockCount = $query->whereRaw('current_stock <= reorder_point')->count();
        $branches = Branch::orderBy('name')->get();
        
        // Get orders for the current branch with supplier relationship and paginate
        $orders = InventoryOrder::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->with(['items', 'branch', 'supplier'])
          ->orderBy('created_at', 'desc')
          ->paginate(10);
        
        return view('admin.inventory.index', compact('items', 'categories', 'lowStockCount', 'branches', 'branch', 'orders'));
    }

    public function create(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
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
        
        // Show "Main Branch" as the supplier option for all branches
        $mainBranch = Branch::where('is_main', true)->first();
        $suppliers = collect();
        
        if ($mainBranch) {
            // Create a virtual supplier object for Main Branch
            $mainBranchSupplier = new \stdClass();
            $mainBranchSupplier->id = $mainBranch->id;
            $mainBranchSupplier->name = 'Main Branch';
            $suppliers->push($mainBranchSupplier);
        }
        
        $lowStockCount = $query->whereRaw('current_stock <= reorder_point')->count();
        $branches = Branch::orderBy('name')->get();
        
        // Get orders for the current branch with supplier relationship and paginate
        $orders = InventoryOrder::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->with('supplier')->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.inventory.create', compact('items', 'categories', 'suppliers', 'branch', 'lowStockCount', 'branches', 'orders'));
    }

    public function store(Request $request)
    {
        try {
            $branchId = $request->input('branch_id');
            $branch = null;
            
            if ($branchId) {
                $branch = Branch::findOrFail($branchId);
            } else {
                $branch = \App\Models\Branch::find(session('selected_branch_id'));
                if (!$branch) {
                    // If no branch is selected, create the item in the main branch
                    $mainBranch = Branch::where('is_main', true)->first();
                    if (!$mainBranch) {
                        return redirect()->route('admin.branches.index')
                            ->with('error', 'Main branch not found. Please set up a main branch first.');
                    }
                    $branch = $mainBranch;
                }
                $branchId = $branch->id;
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:inventory_items',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'unit' => 'required|string|max:50',
                'unit_price' => 'required|numeric|min:0',
                'reorder_point' => 'required|numeric|min:0',
                'current_stock' => 'required|numeric|min:0',
                'supplier_id' => 'nullable|exists:suppliers,id'
            ]);

            // Add branch_id to validated data
            $validated['branch_id'] = $branchId;

            // Get the main branch for supplier assignment
            $mainBranch = Branch::where('is_main', true)->first();

            // If supplier_id is the main branch ID, assign to a default supplier from main branch
            if ($validated['supplier_id'] && $mainBranch && $validated['supplier_id'] == $mainBranch->id) {
                $defaultSupplier = Supplier::where('branch_id', $mainBranch->id)->first();
                if ($defaultSupplier) {
                    $validated['supplier_id'] = $defaultSupplier->id;
                } else {
                    // If no suppliers exist in main branch, create a default one
                    $defaultSupplier = Supplier::create([
                        'name' => 'Default Supplier',
                        'code' => Str::random(8),
                        'contact_person' => 'Main Branch',
                        'email' => 'main@momoshop.com',
                        'phone' => '1234567890',
                        'address' => 'Main Branch Address',
                        'branch_id' => $mainBranch->id
                    ]);
                    $validated['supplier_id'] = $defaultSupplier->id;
                }
            }

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
                    'sku' => $item->code,
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
        $categories = Category::all();
        $suppliers = Supplier::orderBy('name')->get();
        return view('admin.inventory.edit', compact('item', 'categories', 'suppliers'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:inventory_items,code,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
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
                    'sku' => $itemData['code'],
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

        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        
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
        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        $inventory = Inventory::where('branch_id', $branch->id)
            ->with(['product'])
            ->paginate(10);
        $branches = \App\Models\Branch::orderBy('name')->get();
        return view('admin.inventory.index', compact('inventory', 'branches', 'branch'));
    }

    public function createInventory()
    {
        $branch = \App\Models\Branch::find(session('selected_branch_id'));
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

        $branch = \App\Models\Branch::find(session('selected_branch_id'));
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

    public function export(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:all,low_stock,category',
            'category_id' => 'nullable|exists:inventory_categories,id'
        ]);

        $query = InventoryItem::with(['category', 'supplier', 'branch']);

        switch ($request->export_type) {
            case 'low_stock':
                $query->whereRaw('current_stock <= reorder_point');
                break;
            case 'category':
                if ($request->category_id) {
                    $query->where('category_id', $request->category_id);
                }
                break;
        }

        $items = $query->get();

        $filename = 'inventory_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['SKU', 'Name', 'Category', 'Supplier', 'Branch', 'Current Stock', 'Unit Price', 'Reorder Point', 'Status', 'Unit']);
            
            // CSV data
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
            $file = $request->file('import_file');
            $updateExisting = $request->boolean('update_existing');
            $importedCount = 0;
            $updatedCount = 0;
            $errors = [];

            if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
                // Skip header row
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) >= 6) {
                        $sku = $data[0];
                        $name = $data[1];
                        $categoryName = $data[2];
                        $supplierName = $data[3];
                        $currentStock = $data[4];
                        $unitPrice = $data[5];
                        
                        // Find or create category
                        $category = InventoryCategory::firstOrCreate(
                            ['name' => $categoryName],
                            ['slug' => Str::slug($categoryName)]
                        );
                        
                        // Find or create supplier
                        $supplier = null;
                        if ($supplierName && $supplierName !== 'N/A') {
                            // Get the main branch
                            $mainBranch = Branch::where('is_main', true)->first();
                            
                            // If no main branch exists, create one
                            if (!$mainBranch) {
                                $mainBranch = Branch::create([
                                    'name' => 'Main Branch',
                                    'code' => 'MB001',
                                    'address' => 'Main Branch Address',
                                    'contact_person' => 'Main Branch Contact',
                                    'email' => 'main@momoshop.com',
                                    'phone' => '1234567890',
                                    'is_active' => true,
                                    'is_main' => true
                                ]);
                            }
                            
                            $supplier = Supplier::firstOrCreate(
                                ['name' => $supplierName],
                                [
                                    'code' => Str::random(8),
                                    'branch_id' => $mainBranch->id
                                ]
                            );
                        }
                        
                        $itemData = [
                            'name' => $name,
                            'code' => $sku,
                            'category_id' => $category->id,
                            'supplier_id' => $supplier ? $supplier->id : null,
                            'current_stock' => $currentStock,
                            'unit_price' => $unitPrice,
                            'unit' => 'pcs',
                            'reorder_point' => 10,
                            'status' => 'active'
                        ];
                        
                        $existingItem = InventoryItem::where('code', $sku)->first();
                        
                        if ($existingItem && $updateExisting) {
                            $existingItem->update($itemData);
                            $updatedCount++;
                        } elseif (!$existingItem) {
                            InventoryItem::create($itemData);
                            $importedCount++;
                        } else {
                            $errors[] = "SKU {$sku} already exists and update_existing is false";
                        }
                    }
                }
                fclose($handle);
            }

            $message = "Import completed. Imported: {$importedCount}, Updated: {$updatedCount}";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', array_slice($errors, 0, 5));
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function stockCheck(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        if ($branchId) {
            $branch = Branch::find($branchId);
        }
        $universal = !$branchId;

        $service = new \App\Services\StockCheckService();
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

        // Get categories for filtering
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

                    // Handle image upload
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

        // Get categories for filtering
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

                    // Handle image upload
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