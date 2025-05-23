@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Order #{{ $order->id }}</h2>
    <div class="mb-3">
        <strong>Table:</strong> {{ $order->table->name ?? '-' }}<br>
        <strong>Type:</strong> {{ ucfirst($order->type) }}<br>
        <strong>Status:</strong> {{ ucfirst($order->status) }}<br>
        <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}<br>
        <strong>Created At:</strong> {{ $order->created_at->format('Y-m-d H:i') }}<br>
        @if($order->user)
            <strong>Guest:</strong> {{ $order->user->name }} ({{ $order->user->email }})<br>
        @endif
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mb-3">
        <strong>Subtotal:</strong> Rs. {{ number_format($order->total, 2) }}<br>
        <strong>Tax (13%):</strong> Rs. {{ number_format($order->total * 0.13, 2) }}<br>
        <strong>Total:</strong> Rs. {{ number_format($order->total * 1.13, 2) }}
    </div>
    <div class="mb-3">
        <strong>Created:</strong> {{ $order->created_at->format('Y-m-d H:i') }}<br>
        <strong>Updated:</strong> {{ $order->updated_at->format('Y-m-d H:i') }}
    </div>
    @if($order->payment_status !== 'paid')
    <form method="POST" action="{{ route('orders.pay', $order) }}" class="row g-3">
        @csrf
        <div class="col-md-3">
            <label>Amount Received</label>
            <input type="number" step="0.01" name="amount_received" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Payment Method</label>
            <select name="payment_method" class="form-select" required>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="qr">QR</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-success">Mark as Paid</button>
        </div>
    </form>
    @else
        <div class="alert alert-success">Order is paid. Change: Rs. {{ number_format($order->change, 2) }}</div>
    @endif
    <div class="mt-3">
        <a href="{{ route('orders.kitchen-receipt', $order) }}" target="_blank" class="btn btn-secondary">Print Kitchen Receipt</a>
        <a href="{{ route('orders.receipt', $order) }}" target="_blank" class="btn btn-dark">Print Customer Receipt</a>
    </div>
</div>
@endsection 