<?php

namespace App\Http\Controllers;

use App\Models\SupplyOrder;
use App\Models\Supplier;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplyOrderController extends Controller
{
    public function index()
    {
        $orders = SupplyOrder::with(['supplier', 'items'])
            ->latest()
            ->paginate(10);

        return view('admin.supply.orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $items = InventoryItem::all();
        return view('admin.supply.orders.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = SupplyOrder::create([
            'order_number' => 'SO-' . strtoupper(Str::random(8)),
            'supplier_id' => $validated['supplier_id'],
            'expected_delivery_date' => $validated['expected_delivery_date'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        return redirect()
            ->route('admin.supply.orders.show', $order)
            ->with('success', 'Supply order created successfully.');
    }

    public function show(SupplyOrder $order)
    {
        $order->load(['supplier', 'items.inventoryItem']);
        return view('admin.supply.orders.show', compact('order'));
    }

    public function edit(SupplyOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()
                ->route('admin.supply.orders.show', $order)
                ->with('error', 'Only pending orders can be edited.');
        }

        $suppliers = Supplier::all();
        $items = InventoryItem::all();
        $order->load('items');
        
        return view('admin.supply.orders.edit', compact('order', 'suppliers', 'items'));
    }

    public function update(Request $request, SupplyOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()
                ->route('admin.supply.orders.show', $order)
                ->with('error', 'Only pending orders can be updated.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order->update([
            'supplier_id' => $validated['supplier_id'],
            'expected_delivery_date' => $validated['expected_delivery_date'],
            'notes' => $validated['notes'],
        ]);

        // Delete existing items
        $order->items()->delete();

        // Create new items
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        return redirect()
            ->route('admin.supply.orders.show', $order)
            ->with('success', 'Supply order updated successfully.');
    }

    public function destroy(SupplyOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()
                ->route('admin.supply.orders.show', $order)
                ->with('error', 'Only pending orders can be deleted.');
        }

        $order->items()->delete();
        $order->delete();

        return redirect()
            ->route('admin.supply.orders.index')
            ->with('success', 'Supply order deleted successfully.');
    }
} 