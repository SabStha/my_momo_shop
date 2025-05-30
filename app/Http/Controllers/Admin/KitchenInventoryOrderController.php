<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockItem;
use App\Models\KitchenInventoryOrder;
use App\Models\KitchenInventoryOrderItem;
use Illuminate\Support\Facades\DB;

class KitchenInventoryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = KitchenInventoryOrder::with('items.stockItem');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                  ->orWhere('supplier_contact', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->get('status') !== '') {
            $query->where('status', $request->get('status'));
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
            
        return view('admin.kitchen-inventory.orders', compact('orders'));
    }

    public function create()
    {
        // Get only kitchen-related stock items
        $items = StockItem::where('category', 'kitchen')->get();
        return view('admin.kitchen-inventory.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_contact' => 'required|string|max:255',
            'expected_delivery' => 'required|date',
            'items' => 'required|array',
            'items.*.stock_item_id' => 'required|exists:stock_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $order = KitchenInventoryOrder::create([
                'supplier_name' => $validated['supplier_name'],
                'supplier_contact' => $validated['supplier_contact'],
                'expected_delivery' => $validated['expected_delivery'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'total_amount' => 0
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $stockItem = StockItem::find($item['stock_item_id']);
                
                // Verify that the item is a kitchen item
                if ($stockItem->category !== 'kitchen') {
                    throw new \Exception('Invalid item category. Only kitchen items are allowed.');
                }

                $subtotal = $item['quantity'] * $item['unit_price'];
                $total += $subtotal;

                KitchenInventoryOrderItem::create([
                    'kitchen_inventory_order_id' => $order->id,
                    'stock_item_id' => $item['stock_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal
                ]);
            }

            $order->update(['total_amount' => $total]);

            DB::commit();

            return redirect()
                ->route('admin.kitchen-inventory.orders')
                ->with('success', 'Kitchen order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error creating kitchen order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = KitchenInventoryOrder::with('items.stockItem')
            ->findOrFail($id);
        return view('admin.kitchen-inventory.show', compact('order'));
    }

    public function confirm($id)
    {
        try {
            DB::beginTransaction();

            $order = KitchenInventoryOrder::with('items.stockItem')
                ->findOrFail($id);

            if ($order->status !== 'pending') {
                throw new \Exception('Order is not in pending status');
            }

            // Update stock quantities
            foreach ($order->items as $item) {
                $stockItem = $item->stockItem;
                if ($stockItem->category !== 'kitchen') {
                    throw new \Exception('Invalid item category. Only kitchen items are allowed.');
                }
                
                $stockItem->update([
                    'quantity' => $stockItem->quantity + $item->quantity
                ]);
            }

            $order->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kitchen order confirmed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error confirming kitchen order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $order = KitchenInventoryOrder::findOrFail($id);
            
            if ($order->status !== 'pending') {
                throw new \Exception('Only pending orders can be cancelled');
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kitchen order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling kitchen order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        $orders = KitchenInventoryOrder::with('items.stockItem')
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kitchen_inventory_orders.csv"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Order ID',
                'Supplier',
                'Contact',
                'Status',
                'Expected Delivery',
                'Total Amount',
                'Created At',
                'Completed At'
            ]);

            // Add data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->supplier_name,
                    $order->supplier_contact,
                    $order->status,
                    $order->expected_delivery,
                    $order->total_amount,
                    $order->created_at,
                    $order->completed_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print($id)
    {
        $order = KitchenInventoryOrder::with('items.stockItem')
            ->findOrFail($id);
        return view('admin.kitchen-inventory.print', compact('order'));
    }
} 