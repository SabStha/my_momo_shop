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
use Spatie\Activitylog\Facades\Activity;

class SupplyOrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        
        $query = InventoryOrder::with(['supplier', 'items.item']);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        $ordersBySupplier = $orders->groupBy('supplier_id');
        
        return view('admin.supply.orders.list', compact('ordersBySupplier', 'branchId'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $inventoryItems = InventoryItem::where('branch_id', session('branch_id'))
            ->orderBy('name')
            ->get();
        
        return view('admin.supply.orders.create', compact('suppliers', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        if ($request->has('item_ids')) {
            // Handle order creation from selected inventory items
            $itemIds = $request->input('item_ids');
            $branchId = $request->input('branch_id');
            
            // Log the incoming request data
            \Log::info('Attempting to create order with data:', [
                'item_ids' => $itemIds,
                'branch_id' => $branchId,
                'session_branch_id' => session('branch_id')
            ]);
            
            // First check if items exist and their current state
            $allItems = InventoryItem::whereIn('id', $itemIds)->get();
            \Log::info('All items found:', [
                'count' => $allItems->count(),
                'items' => $allItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'is_locked' => $item->is_locked,
                        'branch_id' => $item->branch_id
                    ];
                })->toArray()
            ]);
            
            // Now get only the locked items
            $items = InventoryItem::whereIn('id', $itemIds)
                ->where('branch_id', $branchId)
                ->where('is_locked', true)
                ->get();

            // Log the found locked items
            \Log::info('Found locked items:', [
                'count' => $items->count(),
                'items' => $items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'is_locked' => $item->is_locked,
                        'branch_id' => $item->branch_id
                    ];
                })->toArray()
            ]);

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No locked items found to order.'
                ], 400);
            }

            try {
                DB::beginTransaction();

                // Group items by supplier
                $supplierGroups = $items->groupBy('supplier_id');

                foreach ($supplierGroups as $supplierId => $supplierItems) {
                    $order = new InventoryOrder();
                    $order->supplier_id = $supplierId;
                    $order->branch_id = $branchId;
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

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Orders created successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error creating supply orders: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create orders: ' . $e->getMessage()
                ], 500);
            }
        }

        // Handle regular order creation
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branch_inventories,id',
            'items' => 'required|array',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $order = new InventoryOrder();
            $order->supplier_id = $validated['supplier_id'];
            $order->branch_id = $validated['branch_id'];
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

            DB::commit();

            return redirect()
                ->route('admin.supply.orders.index')
                ->with('success', 'Inventory order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating inventory order: ' . $e->getMessage());
            return back()->with('error', 'Error creating order. Please try again.');
        }
    }

    public function show(InventoryOrder $order)
    {
        $order->load(['supplier', 'items.item']);
        return view('admin.supply.orders.show', compact('order'));
    }

    public function edit(InventoryOrder $order)
    {
        $order->load(['supplier', 'items.item']);
        $suppliers = Supplier::orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();
        
        return view('admin.supply.orders.edit', compact('order', 'suppliers', 'inventoryItems'));
    }

    public function update(Request $request, InventoryOrder $order)
    {
        if ($request->has('status')) {
            $validated = $request->validate([
                'status' => 'required|in:sent,received',
                'items' => 'required_if:status,received|array',
                'items.*.id' => 'required|exists:inventory_order_items,id',
                'items.*.actual_received_quantity' => 'required|numeric|min:0',
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
                        $inventoryItem = $orderItem->item;
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
                            'items' => $order->items()->with('item')->get()
                        ]));
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Order has been marked as received and supplier has been notified.'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error receiving order: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process receive: ' . $e->getMessage()
                    ], 500);
                }
            }
        }

        // Handle regular order updates
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
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be sent to suppliers.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);
            
            // Send email to supplier if they have an email address
            if ($order->supplier && $order->supplier->email) {
                try {
                    Mail::to($order->supplier->email)->send(new SupplierOrderMail($order, 'sent', [
                        'notes' => $order->notes,
                        'items' => $order->items()->with('item')->get()
                    ]));
                } catch (\Exception $e) {
                    \Log::error('Failed to send email to supplier: ' . $e->getMessage());
                    // Don't throw the error, just log it
                }
            }

            // Log the activity
            Activity::log('Order sent to supplier: ' . $order->order_number);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order has been sent to the supplier.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error sending order to supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderItems(InventoryOrder $order)
    {
        try {
            $items = $order->items()
                ->with(['item' => function($query) {
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
                $inventoryItem = $orderItem->item;
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