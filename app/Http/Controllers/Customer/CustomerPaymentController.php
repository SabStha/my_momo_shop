<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerPaymentController extends Controller
{
    public function __construct()
    {
        // Only use web middleware
        $this->middleware('web');
    }

    public function showPaymentViewer(Request $request)
    {
        try {
            $branchId = $request->query('branch');
            $orderId = $request->query('order');

            Log::info('Payment viewer accessed', [
                'branch_id' => $branchId,
                'order_id' => $orderId
            ]);

            if (!$branchId) {
                Log::warning('Payment viewer accessed without branch ID');
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            // If no order ID is provided, just show the empty viewer
            if (!$orderId) {
                return view('customer.payment-viewer', [
                    'branchId' => $branchId,
                    'orderId' => null
                ]);
            }

            // Try to fetch the order
            $order = Order::where('id', $orderId)
                ->where('branch_id', $branchId)
                ->where('payment_status', '!=', 'paid')
                ->first();

            if (!$order) {
                Log::warning('No pending order found', [
                    'order_id' => $orderId,
                    'branch_id' => $branchId
                ]);
                return view('customer.payment-viewer', [
                    'branchId' => $branchId,
                    'orderId' => null
                ]);
            }

            return view('customer.payment-viewer', [
                'branchId' => $branchId,
                'orderId' => $orderId
            ]);

        } catch (\Exception $e) {
            Log::error('Error in payment viewer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to load payment viewer'], 500);
        }
    }

    public function getActiveOrder(Request $request)
    {
        try {
            $branchId = $request->query('branch');
            $orderId = $request->query('order');

            if (!$branchId) {
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            // If no order ID is provided, return empty state
            if (!$orderId) {
                return response()->json([
                    'order' => null,
                    'message' => 'No order selected'
                ]);
            }

            $order = Order::where('id', $orderId)
                ->where('branch_id', $branchId)
                ->with(['items' => function($query) {
                    $query->with('product');
                }])
                ->first();

            if (!$order) {
                return response()->json([
                    'order' => null,
                    'message' => 'No active order found'
                ]);
            }

            // Format the order data
            $formattedOrder = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'subtotal' => $order->subtotal,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'amount_received' => $order->amount_received,
                'change_amount' => $order->change,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name
                        ] : null
                    ];
                })
            ];

            return response()->json([
                'order' => $formattedOrder
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching active order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId ?? null,
                'branch_id' => $branchId ?? null
            ]);
            return response()->json(['error' => 'Failed to fetch order details'], 500);
        }
    }
} 