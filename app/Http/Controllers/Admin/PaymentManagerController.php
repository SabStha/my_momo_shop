<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentManagerController extends Controller
{
    public function index()
    {
        $branch = session('selected_branch');
        if (!$branch) {
            return redirect()->route('admin.branches.select');
        }

        $payments = Payment::where('branch_id', $branch->id)
            ->with(['order', 'user'])
            ->latest()
            ->paginate(10);

        // Get POS orders that are pending payment
        $posOrders = Order::where('branch_id', $branch->id)
            ->where('order_type', 'pos')
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with(['items', 'user'])
            ->latest()
            ->get();

        // Get online orders that are pending payment
        $onlineOrders = Order::where('branch_id', $branch->id)
            ->where('order_type', 'online')
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with(['items', 'user'])
            ->latest()
            ->get();

        // Get recent order history
        $orderHistory = Order::where('branch_id', $branch->id)
            ->where('payment_status', 'paid')
            ->with(['items', 'user', 'payments'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.payment-manager.index', compact('payments', 'posOrders', 'onlineOrders', 'orderHistory'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,wallet',
            'notes' => 'nullable|string|max:255'
        ]);

        $branch = session('selected_branch');
        if (!$branch) {
            return redirect()->route('admin.branches.select');
        }

        $order = Order::findOrFail($request->order_id);
        
        if ($order->branch_id !== $branch->id) {
            return back()->with('error', 'This order does not belong to the current branch.');
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'branch_id' => $branch->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'notes' => $request->notes
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed'
            ]);

            DB::commit();
            return back()->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $branch = session('selected_branch');
        if (!$branch) {
            return redirect()->route('admin.branches.select');
        }

        $payments = Payment::where('branch_id', $branch->id)
            ->with(['order', 'user'])
            ->latest()
            ->paginate(20);

        $totalAmount = $payments->sum('amount');
        $totalPayments = $payments->count();

        return view('admin.payment-manager.history', compact('payments', 'totalAmount', 'totalPayments'));
    }
} 