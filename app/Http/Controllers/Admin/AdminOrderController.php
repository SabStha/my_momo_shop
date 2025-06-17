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

        // POS Orders: order_type = 'dine_in' or 'takeaway'
        $posOrdersPaid = Order::where('branch_id', $branchId)
            ->whereIn('order_type', ['dine_in', 'takeaway'])
            ->where('payment_status', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        $posOrdersUnpaid = Order::where('branch_id', $branchId)
            ->whereIn('order_type', ['dine_in', 'takeaway'])
            ->where('payment_status', '!=', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Online Orders: order_type = 'online'
        $onlineOrdersPaid = Order::where('branch_id', $branchId)
            ->where('order_type', 'online')
            ->where('payment_status', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        $onlineOrdersUnpaid = Order::where('branch_id', $branchId)
            ->where('order_type', 'online')
            ->where('payment_status', '!=', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Order History: all orders, paginated
        $orderHistory = Order::where('branch_id', $branchId)
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Debug information
        \Log::info('Orders fetched:', [
            'pos_paid_count' => $posOrdersPaid->count(),
            'pos_unpaid_count' => $posOrdersUnpaid->count(),
            'online_paid_count' => $onlineOrdersPaid->count(),
            'online_unpaid_count' => $onlineOrdersUnpaid->count(),
            'history_count' => $orderHistory->count()
        ]);

        return view('admin.orders.index', compact(
            'branch',
            'posOrdersPaid', 'posOrdersUnpaid',
            'onlineOrdersPaid', 'onlineOrdersUnpaid',
            'orderHistory'
        ));
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