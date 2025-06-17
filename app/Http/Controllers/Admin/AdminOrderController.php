<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = Branch::findOrFail($branchId);

        $orders = Order::where('branch_id', $branchId)
            ->with(['items', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.orders.index', compact('orders', 'branch'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'branch']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.orders.index', ['branch' => $order->branch_id])
            ->with('success', 'Order status updated successfully.');
    }

    public function destroy(Order $order)
    {
        try {
            $branchId = $order->branch_id;
            $order->delete();
            
            return redirect()
                ->route('admin.orders.index', ['branch' => $branchId])
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error deleting order. Please try again.');
        }
    }
} 