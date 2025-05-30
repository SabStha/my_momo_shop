<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::with('user')
                ->latest()
                ->paginate(10);
                
            return view('desktop.admin.orders.index', compact('orders'));
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading orders. Please try again.');
        }
    }

    public function show(Order $order)
    {
        try {
            $order->load(['user', 'items.product']);
            return view('desktop.admin.orders.show', compact('order'));
        } catch (\Exception $e) {
            Log::error('Error fetching order details: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading order details. Please try again.');
        }
    }

    public function edit(Order $order)
    {
        try {
            $order->load(['user', 'items.product']);
            return view('desktop.admin.orders.edit', compact('order'));
        } catch (\Exception $e) {
            Log::error('Error loading order edit form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading order edit form. Please try again.');
        }
    }

    public function update(Request $request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled',
                'payment_status' => 'required|in:pending,paid,failed'
            ]);

            $order->update([
                'status' => $request->status,
                'payment_status' => $request->payment_status
            ]);

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating order. Please try again.')
                ->withInput();
        }
    }

    public function destroy(Order $order)
    {
        try {
            $order->delete();
            return redirect()->route('admin.orders.index')
                ->with('success', 'Order deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting order. Please try again.');
        }
    }
} 