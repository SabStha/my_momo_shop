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
use App\Models\InventoryCategory;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;

class InventoryOrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = $branchId ? Branch::findOrFail($branchId) : null;

        $query = InventoryItem::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        $items = $query->with(['category', 'supplier'])
            ->orderBy('name')
            ->paginate(15);

        // Get categories and suppliers for filter dropdowns
        $categories = InventoryCategory::orderBy('name')->get();
        $suppliers = InventorySupplier::orderBy('name')->get();

        return view('admin.inventory.orders.index', compact('items', 'branch', 'categories', 'suppliers'));
    }

    public function ordersList(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = $branchId ? Branch::findOrFail($branchId) : null;

        $query = InventoryOrder::query();
        
        if ($branchId) {
            // For branch orders, filter by requesting_branch_id to show orders requested by this branch
            // For main branch, show all orders where it's the main branch
            if ($branch && $branch->is_main) {
                $query->where('branch_id', $branchId);
            } else {
                $query->where('requesting_branch_id', $branchId);
            }
        }

        // Apply filters
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('supplier', function($supplierQuery) use ($request) {
                      $supplierQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $orders = $query->with(['supplier', 'branch', 'requestingBranch'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get suppliers for filter dropdown
        $suppliers = InventorySupplier::orderBy('name')->get();

        // Get branch orders for buttons (orders requested by other branches)
        $branchOrders = collect();
        if ($branch && $branch->is_main) {
            // For main branch, get orders requested by other branches
            $branchOrders = InventoryOrder::where('requesting_branch_id', '!=', null)
                ->where('requesting_branch_id', '!=', $branch->id)
                ->where('branch_id', $branch->id)
                ->with(['requestingBranch', 'supplier'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('requesting_branch_id');
        } elseif ($branch && !$branch->is_main) {
            // For regular branches, get their own orders
            $branchOrders = InventoryOrder::where('requesting_branch_id', $branch->id)
                ->with(['requestingBranch', 'supplier'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('requesting_branch_id');
        }

        return view('admin.inventory.orders', compact('orders', 'branch', 'suppliers', 'branchOrders'));
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

        // Check if this is a regular branch (not main branch)
        $isRegularBranch = $branch && !$branch->is_main;
        $mainBranch = Branch::where('is_main', true)->first();

        return view('admin.inventory.orders.create', compact('items', 'suppliers', 'branch', 'isRegularBranch', 'mainBranch'));
    }

    public function store(Request $request)
    {
        try {
            // Log the incoming request data
            \Log::info('Inventory Order Store Request:', [
                'data' => $request->all(),
                'expects_json' => $request->expectsJson()
            ]);

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

            \Log::info('Validation passed:', $validated);

            DB::beginTransaction();

            // Get the requesting branch
            $requestingBranch = Branch::findOrFail($validated['branch_id']);
            $mainBranch = Branch::where('is_main', true)->first();

            \Log::info('Branches found:', [
                'requesting_branch' => $requestingBranch->toArray(),
                'main_branch' => $mainBranch ? $mainBranch->toArray() : null
            ]);

            // Determine the actual branch for the order
            $orderBranchId = $validated['branch_id'];
            $isCentralizedOrder = false;

            // If this is a regular branch creating an order, route it to main branch
            if ($requestingBranch && !$requestingBranch->is_main) {
                if (!$mainBranch) {
                    throw new \Exception('Main branch not found. Please contact administrator.');
                }
                $orderBranchId = $mainBranch->id;
                $isCentralizedOrder = true;
                // Add note about centralized ordering
                $centralizedNote = "Order requested by {$requestingBranch->name}. ";
                $validated['notes'] = $centralizedNote . ($validated['notes'] ?? '');
                // Always set supplier to main branch for branch orders
                $validated['supplier_id'] = $mainBranch->id;
            }

            \Log::info('Order branch determined:', [
                'order_branch_id' => $orderBranchId,
                'is_centralized' => $isCentralizedOrder
            ]);

            // Create the order
            $orderData = [
                'order_number' => 'INV-' . strtoupper(Str::random(8)),
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $orderBranchId,
                'order_date' => now(),
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'status' => 'pending',
                'notes' => $validated['notes'],
                'requesting_branch_id' => $requestingBranch->id, // Track which branch requested this order
            ];

            \Log::info('Creating order with data:', $orderData);

            $order = InventoryOrder::create($orderData);

            \Log::info('Order created:', $order->toArray());

            // Create order items
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                \Log::info('Creating order item:', $item);
                
                $totalPrice = $item['quantity'] * $item['unit_price'];
                
                $orderItem = InventoryOrderItem::create([
                    'inventory_order_id' => $order->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'original_quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);

                \Log::info('Order item created:', $orderItem->toArray());

                $totalAmount += $totalPrice;
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            \Log::info('Order total updated:', ['total_amount' => $totalAmount]);

            // Send notification to main branch if this is a branch order
            if ($isCentralizedOrder) {
                try {
                    $mainBranchEmail = 'evanhuc404@gmail.com'; // Use the main email address
                    \Mail::to($mainBranchEmail)->send(new \App\Mail\NewBranchOrderNotification($order, $requestingBranch));
                    \Log::info('Main branch notified of new branch order:', [
                        'order_id' => $order->id,
                        'main_branch_email' => $mainBranchEmail
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send main branch notification email:', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Set appropriate success message
            $successMessage = 'Order created successfully.';
            if ($isCentralizedOrder) {
                $successMessage = "Order created successfully and routed to {$mainBranch->name} for supplier processing.";
            }

            \Log::info('Order creation successful:', [
                'order_id' => $order->id,
                'message' => $successMessage
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'order' => $order->load('items.item'),
                    'order_id' => $order->id,
                ]);
            }

            // Return redirect for form submissions
            return redirect()
                ->route('admin.inventory.orders.supplier-view', ['branch' => $validated['branch_id']])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error creating inventory order:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating order: ' . $e->getMessage()
                ], 500);
            }

            // Return redirect for form submissions
            return back()
                ->withInput()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function show(Request $request, InventoryOrder $order)
    {
        $order->load(['supplier', 'items.item', 'branch', 'requestingBranch']);
        
        // Get the branch context from the request (from where the user came from)
        $branchContext = $request->query('branch');
        $viewingBranch = null;
        $isBranchView = false;
        
        // If no branch context, check if this is a branch order and redirect appropriately
        if (!$branchContext) {
            // Check if this order has a requesting branch (branch order)
            if ($order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id) {
                // This is a branch order, redirect to the requesting branch context
                return redirect()->route('admin.inventory.orders.show', [
                    'order' => $order->id,
                    'branch' => $order->requesting_branch_id
                ]);
            }
        }
        
        // If branch context is provided, validate it matches the order
        if ($branchContext) {
            $viewingBranch = Branch::find($branchContext);
            
            // For main branch viewing branch orders
            if ($viewingBranch && $viewingBranch->is_main && $order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id) {
                // Main branch viewing a branch order - this is correct
                $isBranchView = false; // Main branch view
            }
            
            // For regular branches viewing their own orders
            elseif ($viewingBranch && !$viewingBranch->is_main && $order->requesting_branch_id == $viewingBranch->id) {
                // Regular branch viewing their own order - this is correct
                $isBranchView = true; // Branch view
            }
            
            // For main branch viewing their own orders
            elseif ($viewingBranch && $viewingBranch->is_main && $order->branch_id == $viewingBranch->id && !$order->requesting_branch_id) {
                // Main branch viewing their own order - this is correct
                $isBranchView = false; // Main branch view
            }
            
            // If we get here, the branch context doesn't match the order
            // Redirect to the correct branch context
            else {
                if ($order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id) {
                    // This is a branch order, redirect to requesting branch
                    return redirect()->route('admin.inventory.orders.show', [
                        'order' => $order->id,
                        'branch' => $order->requesting_branch_id
                    ]);
                } else {
                    // This is a main branch order, redirect to main branch
                    return redirect()->route('admin.inventory.orders.show', [
                        'order' => $order->id,
                        'branch' => $order->branch_id
                    ]);
                }
            }
        }
        
        // Default case - show the order (assume main branch view)
        if (!$viewingBranch) {
            $viewingBranch = $order->branch;
            $isBranchView = false;
        }
        
        return view('admin.inventory.orders.show', compact('order', 'viewingBranch', 'isBranchView'));
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
                ->route('admin.inventory.orders.index', ['branch' => $order->branch_id])
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
                    'original_quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
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

    public function detailedConfirm(Request $request, InventoryOrder $order)
    {
        // Validate the request
        $validated = $request->validate([
            'received_quantities' => 'required|array',
            'received_quantities.*' => 'required|integer|min:0',
            'receipt_notes' => 'nullable|string|max:1000',
            'receipt_date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            // Check if order is in supplier_confirmed status
            if ($order->status !== 'supplier_confirmed') {
                throw new \Exception('Order must be confirmed by supplier before admin can confirm receipt.');
            }

            $receivedItems = [];
            $totalReceived = 0;
            $hasPartialReceipt = false;

            // Process each received item
            foreach ($validated['received_quantities'] as $itemId => $receivedQty) {
                $orderItem = $order->items()->find($itemId);
                
                if (!$orderItem) {
                    continue;
                }

                // Validate received quantity doesn't exceed ordered quantity
                if ($receivedQty > $orderItem->quantity) {
                    throw new \Exception("Received quantity for {$orderItem->item->name} cannot exceed ordered quantity ({$orderItem->quantity})");
                }

                // Check if this item has partial receipt
                if ($receivedQty < $orderItem->quantity) {
                    $hasPartialReceipt = true;
                }

                // Update order item with received quantity
                $orderItem->update([
                    'received_quantity' => $receivedQty
                ]);

                $receivedItems[] = [
                    'item' => $orderItem->item->name,
                    'sku' => $orderItem->item->sku,
                    'ordered' => $orderItem->quantity,
                    'received' => $receivedQty
                ];

                $totalReceived += $receivedQty;
            }

            // Update order status and add receipt details
            $order->update([
                'status' => 'received',
                'received_at' => $validated['receipt_date'],
                'receipt_notes' => $validated['receipt_notes'] ?? null
            ]);

            // Update inventory for the branch
            foreach ($validated['received_quantities'] as $itemId => $receivedQty) {
                if ($receivedQty > 0) {
                    $orderItem = $order->items()->find($itemId);
                    
                    // Find or create branch inventory record
                    $branchInventory = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('inventory_item_id', $orderItem->inventory_item_id)
                        ->first();

                    if ($branchInventory) {
                        // Update existing record
                        $branchInventory->update([
                            'current_stock' => $branchInventory->current_stock + $receivedQty
                        ]);
                    } else {
                        // Create new record
                        BranchInventory::create([
                            'branch_id' => $order->branch_id,
                            'inventory_item_id' => $orderItem->inventory_item_id,
                            'current_stock' => $receivedQty,
                            'min_stock' => 0,
                            'max_stock' => 1000
                        ]);
                    }

                    // Log inventory transaction
                    InventoryTransaction::create([
                        'inventory_item_id' => $orderItem->inventory_item_id,
                        'type' => 'purchase',
                        'quantity' => $receivedQty,
                        'unit_price' => $orderItem->unit_price,
                        'total_amount' => $receivedQty * $orderItem->unit_price,
                        'notes' => 'Received from supplier',
                        'user_id' => auth()->id()
                    ]);
                }
            }

            // Send email notification to supplier
            if ($order->supplier && $order->supplier->email) {
                try {
                    \Mail::to($order->supplier->email)
                        ->send(new \App\Mail\SupplierReceiptConfirmation(
                            $order, 
                            $validated
                        ));
                    
                    \Log::info('Supplier receipt confirmation email sent:', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'supplier_email' => $order->supplier->email,
                        'has_partial_receipt' => $hasPartialReceipt,
                        'total_received' => $totalReceived
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send supplier receipt confirmation email:', [
                        'order_id' => $order->id,
                        'supplier_email' => $order->supplier->email,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the entire operation if email fails
                }
            }

            // Log the receipt confirmation
            \Log::info('Inventory order receipt confirmed:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'received_items' => $receivedItems,
                'total_received' => $totalReceived,
                'has_partial_receipt' => $hasPartialReceipt,
                'receipt_notes' => $validated['receipt_notes'],
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Receipt confirmed successfully. Inventory updated and supplier notified.',
                'order' => $order->load('items.item', 'supplier', 'branch')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error confirming inventory order receipt:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error confirming receipt: ' . $e->getMessage()
            ], 500);
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
                    $order->order_number,
                    $order->supplier->name,
                    $order->order_date->format('Y-m-d'),
                    $order->expected_delivery_date->format('Y-m-d'),
                    $order->status,
                    $order->items->sum('quantity'),
                    $order->total_amount
                ]);
            }
            
            fclose($file);
        }, 'inventory_orders_' . date('Y-m-d') . '.csv');
    }

    /**
     * Update order status (for main branch supply chain management)
     */
    public function updateStatus(Request $request, InventoryOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,sent,supplier_confirmed,processed,received,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            // Update status and timestamps
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'sent' && $oldStatus !== 'sent') {
                $updateData['sent_at'] = now();
            }

            if ($newStatus === 'supplier_confirmed' && $oldStatus !== 'supplier_confirmed') {
                $updateData['supplier_confirmed_at'] = now();
            }

            if ($newStatus === 'received' && $oldStatus !== 'received') {
                $updateData['received_at'] = now();
                
                // Handle branch order receipt
                if ($order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id) {
                    // This is a branch order - update requesting branch inventory
                    foreach ($order->items as $orderItem) {
                        $inventoryItem = $orderItem->item;
                        
                        // Add to requesting branch inventory
                        $branchInventory = \App\Models\BranchInventory::firstOrCreate([
                            'branch_id' => $order->requesting_branch_id,
                            'inventory_item_id' => $inventoryItem->id
                        ], [
                            'current_stock' => 0,
                            'minimum_stock' => 0,
                            'reorder_point' => 0,
                            'is_main' => false
                        ]);

                        $branchInventory->increment('current_stock', $orderItem->quantity);

                        // Log inventory update
                        \Log::info('Branch order receipt - inventory updated:', [
                            'order_id' => $order->id,
                            'item_id' => $inventoryItem->id,
                            'item_name' => $inventoryItem->name,
                            'quantity_added' => $orderItem->quantity,
                            'branch_id' => $order->requesting_branch_id
                        ]);
                    }
                }
            }

            $order->update($updateData);

            // Send email notification to supplier when order is sent
            if ($newStatus === 'sent' && $oldStatus !== 'sent' && $order->supplier && $order->supplier->email) {
                try {
                    \Mail::to($order->supplier->email)
                        ->send(new \App\Mail\SupplierOrderNotification($order, $order->supplier));
                    
                    \Log::info('Supplier order notification email sent:', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'supplier_email' => $order->supplier->email
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send supplier order notification email:', [
                        'order_id' => $order->id,
                        'supplier_email' => $order->supplier->email,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the entire operation if email fails
                }
            }

            // Log the status change
            \Log::info('Inventory order status updated:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => $order->load('items.item', 'supplier', 'branch')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating inventory order status:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Distribute received items to requesting branches
     */
    public function distribute(Request $request, InventoryOrder $order)
    {
        $validated = $request->validate([
            'received_quantities' => 'required|array',
            'received_quantities.*' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Verify order is in received status
            if ($order->status !== 'received') {
                throw new \Exception('Order must be in received status to distribute items');
            }

            // Verify order is from main branch
            if (!$order->branch->is_main) {
                throw new \Exception('Only main branch can distribute items');
            }

            $distributedItems = [];
            $totalDistributed = 0;

            foreach ($validated['received_quantities'] as $itemId => $receivedQty) {
                $orderItem = $order->items()->find($itemId);
                
                if (!$orderItem) {
                    continue;
                }

                // Validate received quantity doesn't exceed ordered quantity
                if ($receivedQty > $orderItem->quantity) {
                    throw new \Exception("Received quantity for {$orderItem->item->name} cannot exceed ordered quantity");
                }

                // Update branch inventory for each item
                $branchInventory = BranchInventory::where('branch_id', $order->branch_id)
                    ->where('inventory_item_id', $orderItem->inventory_item_id)
                    ->first();

                if ($branchInventory) {
                    // Update existing record
                    $branchInventory->update([
                        'current_stock' => $branchInventory->current_stock + $receivedQty,
                        'inventory_item_id' => $orderItem->inventory_item_id,
                    ]);
                } else {
                    // Create new record
                    BranchInventory::create([
                        'branch_id' => $order->branch_id,
                        'inventory_item_id' => $orderItem->inventory_item_id,
                        'current_stock' => $receivedQty,
                        'minimum_stock' => 0,
                        'reorder_point' => 0,
                        'is_main' => false,
                    ]);
                }

                // Create inventory transaction log
                InventoryTransaction::create([
                    'inventory_item_id' => $orderItem->inventory_item_id,
                    'type' => 'purchase',
                    'quantity' => $receivedQty,
                    'unit_price' => $orderItem->unit_price ?? 0,
                    'total_amount' => ($orderItem->unit_price ?? 0) * $receivedQty,
                    'notes' => "Distributed from order #{$order->id}",
                    'user_id' => auth()->id(),
                ]);

                $distributedItems[] = [
                    'item_name' => $orderItem->item->name,
                    'quantity' => $receivedQty
                ];

                $totalDistributed += $receivedQty;
            }

            // Log the distribution
            \Log::info('Inventory items distributed:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'distributed_items' => $distributedItems,
                'total_distributed' => $totalDistributed,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully distributed {$totalDistributed} items to branches",
                'distributed_items' => $distributedItems
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error distributing inventory items:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error distributing items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders grouped by supplier for main branch view
     */
    public function supplierView(Request $request)
    {
        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        
        if (!$branch || !$branch->is_main) {
            return redirect()->route('admin.inventory.orders.index')
                ->with('error', 'Supplier view is only available for main branch');
        }

        // Get all orders for main branch, grouped by supplier
        $suppliers = InventorySupplier::with(['orders' => function($query) use ($branch) {
            $query->where('branch_id', $branch->id)
                  ->with(['items.item', 'branch', 'requestingBranch'])
                  ->orderBy('created_at', 'desc');
        }])->get();

        // Group orders by status for each supplier
        foreach ($suppliers as $supplier) {
            $supplier->orders_by_status = $supplier->orders->groupBy('status');
        }

        return view('admin.inventory.orders.supplier-view', compact('suppliers', 'branch'));
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

    public function generateForecast(Request $request)
    {
        $branchId = $request->input('branch_id');
        $aiService = new \App\Services\AIForecastService();
        $result = $aiService->generateForecast($branchId);
        return response()->json($result);
    }

    /**
     * Update status of multiple orders at once
     */
    public function bulkStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|integer|exists:inventory_orders,id',
            'status' => 'required|in:pending,sent,received,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $failedOrders = [];

            foreach ($validated['order_ids'] as $orderId) {
                try {
                    $order = InventoryOrder::findOrFail($orderId);
                    $oldStatus = $order->status;
                    $newStatus = $validated['status'];

                    // Update status and timestamps
                    $updateData = ['status' => $newStatus];

                    if ($newStatus === 'sent' && $oldStatus !== 'sent') {
                        $updateData['sent_at'] = now();
                    }

                    if ($newStatus === 'received' && $oldStatus !== 'received') {
                        $updateData['received_at'] = now();
                    }

                    $order->update($updateData);
                    $updatedCount++;

                    // Send email notification to supplier when order is sent
                    if ($newStatus === 'sent' && $oldStatus !== 'sent' && $order->supplier && $order->supplier->email) {
                        try {
                            \Mail::to($order->supplier->email)
                                ->send(new \App\Mail\SupplierOrderNotification($order, $order->supplier));
                            
                            \Log::info('Supplier order notification email sent (bulk):', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'supplier_email' => $order->supplier->email
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send supplier order notification email (bulk):', [
                                'order_id' => $order->id,
                                'supplier_email' => $order->supplier->email,
                                'error' => $e->getMessage()
                            ]);
                            // Don't fail the entire operation if email fails
                        }
                    }

                    // Log the status change
                    \Log::info('Inventory order status updated (bulk):', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'user_id' => auth()->id()
                    ]);

                } catch (\Exception $e) {
                    $failedOrders[] = [
                        'order_id' => $orderId,
                        'error' => $e->getMessage()
                    ];
                    
                    \Log::error('Error updating inventory order status (bulk):', [
                        'order_id' => $orderId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $message = "Successfully updated {$updatedCount} order(s) to '{$validated['status']}' status.";
            if (!empty($failedOrders)) {
                $message .= " Failed to update " . count($failedOrders) . " order(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'failed_orders' => $failedOrders
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error in bulk status update:', [
                'error' => $e->getMessage(),
                'order_ids' => $validated['order_ids'] ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating order statuses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process and fulfill a branch order
     */
    public function processBranchOrder(Request $request, InventoryOrder $order)
    {
        try {
            // Validate this is a branch order
            if (!$order->requesting_branch_id || $order->requesting_branch_id == $order->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is not a branch order'
                ], 400);
            }

            // Validate order is in pending status
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order must be in pending status to process'
                ], 400);
            }

            DB::beginTransaction();

            // Update order status to processed (not received)
            $order->update([
                'status' => 'processed',
                'receipt_notes' => 'Processed by main branch - ready for branch pickup'
            ]);

            // Update inventory for main branch
            foreach ($order->items as $orderItem) {
                $inventoryItem = $orderItem->item;
                
                // Add to main branch inventory
                $branchInventory = \App\Models\BranchInventory::firstOrCreate([
                    'branch_id' => $order->branch_id, // Main branch
                    'inventory_item_id' => $inventoryItem->id
                ], [
                    'current_stock' => 0,
                    'minimum_stock' => 0,
                    'reorder_point' => 0,
                    'is_main' => false
                ]);

                $branchInventory->increment('current_stock', $orderItem->quantity);

                // Log inventory update
                \Log::info('Branch order inventory updated:', [
                    'order_id' => $order->id,
                    'item_id' => $inventoryItem->id,
                    'item_name' => $inventoryItem->name,
                    'quantity_added' => $orderItem->quantity,
                    'branch_id' => $order->branch_id
                ]);
            }

            // Send notification to requesting branch
            $requestingBranch = \App\Models\Branch::find($order->requesting_branch_id);
            if ($requestingBranch && $requestingBranch->email) {
                try {
                    \Mail::to($requestingBranch->email)
                        ->send(new \App\Mail\BranchOrderFulfilledNotification($order, $requestingBranch));
                    
                    \Log::info('Branch order fulfilled notification sent:', [
                        'order_id' => $order->id,
                        'requesting_branch_email' => $requestingBranch->email
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send branch order fulfilled notification:', [
                        'order_id' => $order->id,
                        'requesting_branch_email' => $requestingBranch->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            \Log::info('Branch order processed successfully:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'requesting_branch_id' => $order->requesting_branch_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branch order processed successfully. Inventory updated and requesting branch notified.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error processing branch order:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing branch order: ' . $e->getMessage()
            ], 500);
        }
    }
} 