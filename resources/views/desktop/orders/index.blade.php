@extends('layouts.app')
@section('content')
<div class="container">
    <h2 class="mb-4">Order Management</h2>
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status')=='pending')>Pending</option>
                <option value="preparing" @selected(request('status')=='preparing')>Preparing</option>
                <option value="completed" @selected(request('status')=='completed')>Completed</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="payment_status" class="form-select">
                <option value="">All Payment</option>
                <option value="paid" @selected(request('payment_status')=='paid')>Paid</option>
                <option value="unpaid" @selected(request('payment_status')=='unpaid')>Unpaid</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Order ID</th>
                    <th>Table</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Created By</th>
                    <th>Paid By</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->table->name ?? '-' }}</td>
                    <td>{{ ucfirst($order->type) }}</td>
                    <td>Rs. {{ number_format($order->total * 1.13, 2) }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                    <td>
                        <span class="badge {{ $order->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td>{{ $order->createdBy?->name ?? '-' }}</td>
                    <td>{{ $order->paidBy?->name ?? '-' }}</td>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-primary">View</a>
                        <a href="{{ route('orders.kitchen-receipt', $order) }}" target="_blank" class="btn btn-sm btn-secondary">Print Kitchen</a>
                        <a href="{{ route('orders.receipt', $order) }}" target="_blank" class="btn btn-sm btn-dark">Print Customer</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
</div>
@endsection 