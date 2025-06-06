<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PosPaymentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|in:cash,esewa,khalti',
            'amount' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $data['method'],
            'amount' => $data['amount'],
            'paid' => true,
            'paid_at' => now(),
        ]);

        return response()->json(['message' => 'Payment recorded', 'payment' => $payment]);
    }
} 