<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $branch = session('selected_branch');
        $orders = Order::where('branch_id', $branch->id)
            ->with(['user', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $branch = session('selected_branch');
        return view('admin.orders.create', compact('branch'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $branch = session('selected_branch');
        $validated['branch_id'] = $branch->id;
        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        $order = Order::create($validated);

        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'] ?? 0,
            ]);
        }

        $order->calculateTotals();

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'branch']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'delivery_status' => 'nullable|in:pending,processing,delivered,cancelled',
            'delivery_address' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $order->update($validated);
        $order->calculateTotals();

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update($validated);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    public function updateDelivery(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_status' => 'required|in:pending,processing,delivered,cancelled',
            'delivery_time' => 'nullable|date',
            'delivery_notes' => 'nullable|string'
        ]);

        $order->update($validated);

        return redirect()->back()
            ->with('success', 'Delivery status updated successfully.');
    }
} 