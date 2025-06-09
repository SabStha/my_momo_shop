<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosPaymentController extends Controller
{
    public function store(Request $request)
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer',
            'reference_number' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::where('id', $request->order_id)
                ->where('branch_id', $branchId)
                ->firstOrFail();

            if ($order->status === 'completed') {
                return response()->json(['error' => 'Order is already completed'], 400);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => 'completed',
            ]);

            // Update order status if payment amount equals or exceeds total
            $totalPaid = $order->payments()->sum('amount');
            if ($totalPaid >= $order->total) {
                $order->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'order' => $order->load('payments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process payment: ' . $e->getMessage()], 500);
        }
    }
} 