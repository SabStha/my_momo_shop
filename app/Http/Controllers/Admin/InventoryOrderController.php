<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryOrder;
use App\Models\InventoryOrderItem;
use App\Models\InventoryItem;
use App\Models\InventorySupplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InventoryOrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = $branchId ? Branch::findOrFail($branchId) : null;

        $query = InventoryOrder::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->with(['items', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.inventory.orders.index', compact('orders', 'branch'));
    }

    public function create(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = $branchId ? Branch::findOrFail($branchId) : null;

        $items = InventoryItem::when($branchId, function ($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->orderBy('name')->get();

        // Get all suppliers without branch filtering
        $suppliers = InventorySupplier::orderBy('name')->get();

        return view('admin.inventory.orders.create', compact('items', 'suppliers', 'branch'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:inventory_suppliers,id',
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            // Create the order
            $order = InventoryOrder::create([
                'order_number' => 'INV-' . strtoupper(Str::random(8)),
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'order_date' => now(),
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'status' => 'pending',
                'notes' => $validated['notes'],
            ]);

            // Create order items
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $orderItem = InventoryOrderItem::create([
                    'inventory_order_id' => $order->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'branch_id' => $validated['branch_id'],
                ]);

                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()
                ->route('admin.inventory.orders.index', ['branch' => $validated['branch_id']])
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function show(InventoryOrder $order)
    {
        $order->load(['supplier', 'items.item']);
        return view('admin.inventory.orders.show', compact('order'));
    }

    public function edit(InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()
                ->route('admin.inventory.orders.index', ['branch' => $order->branch_id])
                ->with('error', 'Only pending orders can be edited.');
        }

        $order->load(['supplier', 'items.item']);
        $items = InventoryItem::where('branch_id', $order->branch_id)->orderBy('name')->get();
        $suppliers = InventorySupplier::orderBy('name')->get();

        return view('admin.inventory.orders.edit', compact('order', 'items', 'suppliers'));
    }

    public function update(Request $request, InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()
                ->route('admin.inventory.index')
                ->with('error', 'Only pending orders can be edited.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:inventory_suppliers,id',
            'expected_delivery_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update order details
            $order->update([
                'supplier_id' => $validated['supplier_id'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing order items
            $order->items()->delete();

            // Create new order items
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $orderItem = InventoryOrderItem::create([
                    'inventory_order_id' => $order->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'branch_id' => $order->branch_id,
                ]);

                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()
                ->route('admin.inventory.index')
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    public function destroy(InventoryOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()
                ->route('admin.inventory.index')
                ->with('error', 'Only pending orders can be deleted.');
        }

        try {
            DB::beginTransaction();
            $order->items()->delete();
            $order->delete();
            DB::commit();

            return redirect()
                ->route('admin.inventory.index')
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting order: ' . $e->getMessage());
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

    public function history(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = $branchId ? Branch::findOrFail($branchId) : null;

        $query = InventoryOrder::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->with(['items.item', 'supplier', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.inventory.orders.history', compact('orders', 'branch'));
    }
} 