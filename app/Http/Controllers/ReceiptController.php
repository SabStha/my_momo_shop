<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function print($id, Request $request)
    {
        $order = Order::with(['items.product'])->findOrFail($id);
        $type = $request->query('type', 'counter');

        if ($type === 'kitchen') {
            return view('receipts.kitchen', compact('order'));
        }

        return view('receipts.counter', compact('order'));
    }
} 