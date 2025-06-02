<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryOrder;
use Illuminate\Http\Request;

class InventoryOrderController extends Controller
{
    public function index()
    {
        $orders = InventoryOrder::with(['items', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('desktop.admin.inventory.orders.index', compact('orders'));
    }

    public function create()
    {
        return view('desktop.admin.inventory.orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order = InventoryOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return redirect()->route('admin.inventory.orders.index')
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating order. Please try again.');
        }
    }

    public function show(InventoryOrder $order)
    {
        $order->load(['items.inventoryItem', 'supplier', 'user']);
        return view('desktop.admin.inventory.orders.show', compact('order'));
    }

    public function edit(InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->route('admin.inventory.orders.index')
                ->with('error', 'Only pending orders can be edited.');
        }

        $order->load(['items.inventoryItem', 'supplier']);
        return view('desktop.admin.inventory.orders.edit', compact('order'));
    }

    public function update(Request $request, InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->route('admin.inventory.orders.index')
                ->with('error', 'Only pending orders can be updated.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing items
            $order->items()->delete();

            // Create new items
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return redirect()->route('admin.inventory.orders.index')
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating order. Please try again.');
        }
    }

    public function destroy(InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->route('admin.inventory.orders.index')
                ->with('error', 'Only pending orders can be deleted.');
        }

        try {
            $order->items()->delete();
            $order->delete();
            return redirect()->route('admin.inventory.orders.index')
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting order. Please try again.');
        }
    }

    public function confirm(InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->route('admin.inventory.orders.index')
                ->with('error', 'Only pending orders can be confirmed.');
        }

        try {
            $order->update(['status' => 'confirmed']);
            return redirect()->route('admin.inventory.orders.index')
                ->with('success', 'Order confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error confirming order. Please try again.');
        }
    }

    public function cancel(InventoryOrder $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('admin.inventory.orders.index')
                ->with('error', 'Only pending or confirmed orders can be cancelled.');
        }

        try {
            $order->update(['status' => 'cancelled']);
            return redirect()->route('admin.inventory.orders.index')
                ->with('success', 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling order. Please try again.');
        }
    }

    public function export()
    {
        $orders = InventoryOrder::with(['items.inventoryItem', 'supplier', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Order ID', 'Supplier', 'Order Date', 'Expected Delivery', 'Status', 'Total Items', 'Total Amount']);
            
            // Add data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->supplier->name,
                    $order->order_date,
                    $order->expected_delivery_date,
                    $order->status,
                    $order->items->sum('quantity'),
                    $order->items->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    })
                ]);
            }
            
            fclose($file);
        }, 'inventory_orders.csv');
    }
} 