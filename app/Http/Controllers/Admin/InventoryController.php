<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\InventoryCRUDController;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * InventoryController
 * 
 * This controller now serves as a pass-through for InventoryCRUDController
 * and maintains legacy Inventory model support.
 */
class InventoryController extends InventoryCRUDController
{
    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
    public function indexInventory()
    {
        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        $inventory = Inventory::where('branch_id', $branch->id)
            ->with(['product'])
            ->paginate(10);
        $branches = \App\Models\Branch::orderBy('name')->get();
        return view('admin.inventory.index', compact('inventory', 'branches', 'branch'));
    }

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
    public function createInventory()
    {
        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        $products = Product::where('branch_id', $branch->id)->get();
        return view('admin.inventory.create', compact('products', 'branch'));
    }

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
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

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
    public function showInventory(Inventory $inventory)
    {
        $inventory->load(['product', 'branch', 'history']);
        return view('admin.inventory.show', compact('inventory'));
    }

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
    public function editInventory(Inventory $inventory)
    {
        $inventory->load('product');
        return view('admin.inventory.edit', compact('inventory'));
    }

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
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

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
    public function destroyInventory(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
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

    /**
     * @deprecated - Legacy Inventory model, use InventoryItem via InventoryCRUDController instead.
     */
    public function historyInventory(Inventory $inventory)
    {
        $history = $inventory->history()
            ->with('createdBy')
            ->latest()
            ->paginate(10);

        return view('admin.inventory.history', compact('inventory', 'history'));
    }
}