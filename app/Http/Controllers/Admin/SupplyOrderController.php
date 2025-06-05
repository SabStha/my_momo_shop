<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryOrder;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Mail\SupplierOrderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Activity;

class SupplyOrderController extends Controller
{
    public function index()
    {
        $orders = InventoryOrder::with(['supplier', 'items.inventoryItem'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $ordersBySupplier = $orders->groupBy('supplier_id');
        
        return view('desktop.admin.supply.orders.list', compact('ordersBySupplier'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();
        
        return view('desktop.admin.supply.orders.create', compact('suppliers', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        if ($request->has('item_ids')) {
            // Handle order creation from selected inventory items
            $itemIds = $request->input('item_ids');
            $items = InventoryItem::whereIn('id', $itemIds)
                ->where('is_locked', true)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No locked items found to order.'
                ], 400);
            }

            // Group items by supplier
            $supplierGroups = $items->groupBy('supplier_id');

            foreach ($supplierGroups as $supplierId => $supplierItems) {
                $order = new InventoryOrder();
                $order->supplier_id = $supplierId;
                $order->order_number = $order->generateOrderNumber();
                $order->status = 'pending';
                $order->ordered_at = now();
                $order->total_amount = 0;
                $order->save();

                $total = 0;
                foreach ($supplierItems as $item) {
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

            return response()->json([
                'success' => true,
                'message' => 'Orders created successfully.'
            ]);
        }

        // Handle regular order creation
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $order = new InventoryOrder();
        $order->supplier_id = $validated['supplier_id'];
        $order->order_number = $order->generateOrderNumber();
        $order->total_amount = 0; // Will be calculated
        $order->status = 'pending';
        $order->ordered_at = now();
        $order->notes = $validated['notes'] ?? null;
        $order->save();

        $total = 0;
        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $total += $itemTotal;
            
            $order->items()->create([
                'inventory_item_id' => $item['inventory_item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $itemTotal
            ]);
        }

        $order->update(['total_amount' => $total]);

        return redirect()
            ->route('admin.supply.orders.index')
            ->with('success', 'Inventory order created successfully.');
    }

    public function show(InventoryOrder $order)
    {
        $order->load(['supplier', 'items.inventoryItem']);
        return view('desktop.admin.supply.orders.show', compact('order'));
    }

    public function edit(InventoryOrder $order)
    {
        $order->load(['supplier', 'items.inventoryItem']);
        $suppliers = Supplier::orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();
        
        return view('desktop.admin.supply.orders.edit', compact('order', 'suppliers', 'inventoryItems'));
    }

    public function update(Request $request, InventoryOrder $order)
    {
        if ($request->has('status')) {
            $validated = $request->validate([
                'status' => 'required|in:confirmed,received,cancelled',
                'items' => 'required_if:status,received|array',
                'items.*.id' => 'required|exists:inventory_order_items,id',
                'items.*.actual_received_quantity' => 'required|numeric|min:0',
                'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
                'notes' => 'nullable|string'
            ]);

            if ($validated['status'] === 'received') {
                try {
                    DB::beginTransaction();

                    // Process each item in the array
                    foreach ($validated['items'] as $itemData) {
                        $orderItem = $order->items()->findOrFail($itemData['id']);
                        $orderItem->actual_received_quantity = $itemData['actual_received_quantity'];
                        $orderItem->save();

                        // Update inventory item quantity
                        $inventoryItem = $orderItem->inventoryItem;
                        $inventoryItem->quantity += $itemData['actual_received_quantity'];
                        $inventoryItem->save();
                    }

                    // Update order status and add notes
                    $order->status = 'received';
                    $order->received_at = now();
                    $order->notes = $validated['notes'] ?? 'Order received.';
                    $order->save();

                    // Send email notification to supplier
                    if ($order->supplier && $order->supplier->email) {
                        Mail::to($order->supplier->email)->send(new SupplierOrderMail($order, 'received', [
                            'notes' => $validated['notes'] ?? null,
                            'received_items' => $order->items()->whereIn('id', collect($validated['items'])->pluck('id'))->get()
                        ]));
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Order has been marked as received and supplier has been notified.'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process receive: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                $order->update([
                    'status' => $validated['status'],
                    'received_at' => $validated['status'] === 'received' ? now() : null
                ]);
            }
        } else {
            // Only validate supplier_id when updating order details
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array',
                'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            $order->update([
                'supplier_id' => $validated['supplier_id'],
                'notes' => $validated['notes'] ?? null
            ]);

            // Delete existing items
            $order->items()->delete();

            // Create new items
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['unit_price'];
                $order->items()->create([
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price']
                ]);
            }

            $order->update(['total_amount' => $total]);
        }

        return redirect()
            ->route('admin.supply.orders.index')
            ->with('success', 'Inventory order updated successfully.');
    }

    public function destroy(InventoryOrder $order)
    {
        $order->delete();
        return redirect()
            ->route('admin.supply.orders.index')
            ->with('success', 'Inventory order deleted successfully.');
    }

    public function sendToSupplier(InventoryOrder $order)
    {
        if (!$order->supplier || !$order->supplier->email) {
            return redirect()->back()->with('error', 'Supplier does not have an email address.');
        }
        Mail::to($order->supplier->email)->send(new SupplierOrderMail($order, $order->supplier, $order->items->toArray()));
        return redirect()->back()->with('success', 'Order sent to supplier successfully.');
    }

    public function getOrderItems(InventoryOrder $order)
    {
        try {
            $items = $order->items()
                ->with(['inventoryItem' => function($query) {
                    $query->select('id', 'name', 'unit');
                }])
                ->get(['id', 'inventory_item_id', 'quantity', 'unit_price', 'total_price']);

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function partialReceive(Request $request, InventoryOrder $order)
    {
        try {
            DB::beginTransaction();

            // Validate the request
            $request->validate([
                'items' => 'required|array',
                'items.*.actual_received_quantity' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            // Update each order item with actual received quantity
            foreach ($request->items as $itemId => $data) {
                $orderItem = $order->items()->findOrFail($itemId);
                $orderItem->actual_received_quantity = $data['actual_received_quantity'];
                $orderItem->save();

                // Update inventory item quantity
                $inventoryItem = $orderItem->inventoryItem;
                $inventoryItem->quantity += $data['actual_received_quantity'];
                $inventoryItem->save();
            }

            // Update order status and add notes
            $order->status = 'partially_received';
            $order->notes = $request->notes ?? 'Order partially received.';
            $order->save();

            // Log the partial receive
            Activity::log('Order partially received');

            // TODO: Send email notification to supplier
            // You can implement this using Laravel's notification system

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order has been marked as partially received.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process partial receive: ' . $e->getMessage()
            ], 500);
        }
    }
} 