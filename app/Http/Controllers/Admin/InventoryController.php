<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with(['category', 'supplier'])
            ->orderBy('name')
            ->paginate(10);
            
        $categories = InventoryCategory::all();
        $lowStockCount = InventoryItem::whereRaw('quantity <= reorder_point')->count();
        
        return view('desktop.admin.inventory.index', compact('items', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = InventoryCategory::all();
        $suppliers = Supplier::orderBy('name')->get();
        return view('desktop.admin.inventory.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:inventory_items',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'status' => 'required|in:active,inactive,discontinued',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        try {
            DB::beginTransaction();

            // Find or create the category
            $category = InventoryCategory::firstOrCreate(
                ['code' => $validated['category']],
                [
                    'name' => ucwords(str_replace('-', ' ', $validated['category'])),
                    'description' => 'Auto-created category'
                ]
            );

            // Update category code to use the actual category code
            $validated['category_code'] = $category->code;
            unset($validated['category']); // Remove the original category field

            $item = InventoryItem::create($validated);

            // Create initial transaction if quantity is provided
            if ($validated['quantity'] > 0) {
                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'purchase',
                    'quantity' => $validated['quantity'],
                    'unit_price' => $validated['unit_price'],
                    'total_amount' => $validated['quantity'] * $validated['unit_price'],
                    'notes' => 'Initial stock',
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory item created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating inventory item: ' . $e->getMessage());
            return back()->with('error', 'Error creating inventory item. Please try again.');
        }
    }

    public function show(InventoryItem $item)
    {
        $transactions = $item->transactions()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('desktop.admin.inventory.show', compact('item', 'transactions'));
    }

    public function edit(InventoryItem $item)
    {
        $categories = InventoryCategory::all();
        $suppliers = Supplier::orderBy('name')->get();
        return view('desktop.admin.inventory.edit', compact('item', 'categories', 'suppliers'));
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
            $item->update($validated);
            return redirect()
                ->route('admin.inventory.edit', $item)
                ->with('success', 'Inventory item updated successfully.')
                ->with('show_links', true);
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

            $item->updateQuantity(
                $validated['quantity'],
                $validated['type'],
                $validated['notes']
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
            $item->delete();
            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory item deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting inventory item: ' . $e->getMessage());
            return back()->with('error', 'Error deleting inventory item. Please try again.');
        }
    }

    public function categories()
    {
        $categories = InventoryCategory::withCount('items')->get();
        return view('desktop.admin.inventory.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:inventory_categories',
            'description' => 'nullable|string',
        ]);

        try {
            InventoryCategory::create($validated);
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
        ]);

        try {
            $category->update($validated);
            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return back()->with('error', 'Error updating category. Please try again.');
        }
    }

    public function deleteCategory(InventoryCategory $category)
    {
        if ($category->items()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated items.');
        }

        try {
            $category->delete();
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

    public function manage()
    {
        $items = InventoryItem::with(['category', 'supplier'])
            ->orderBy('name')
            ->paginate(10);

        $lockedItems = InventoryItem::with('supplier')
            ->where('is_locked', true)
            ->get();
            
        $hasLockedItems = $lockedItems->isNotEmpty();

        $supplierGroups = [];
        if ($hasLockedItems) {
            $supplierGroups = $lockedItems->groupBy('supplier_id')->map(function ($items, $supplierId) {
                return [
                    'supplier' => $items->first()->supplier,
                    'items' => $items
                ];
            });
        }

        return view('desktop.admin.inventory.manage', compact('items', 'hasLockedItems', 'supplierGroups'));
    }

    public function lock(InventoryItem $item)
    {
        $item->update(['is_locked' => true]);
        return response()->json([
            'success' => true,
            'message' => 'Item locked successfully.'
        ]);
    }

    public function unlock(InventoryItem $item)
    {
        $item->update(['is_locked' => false]);
        return response()->json([
            'success' => true,
            'message' => 'Item unlocked successfully.'
        ]);
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
} 