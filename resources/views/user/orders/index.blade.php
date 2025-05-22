@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>My Orders</h2>
    <div class="table-responsive mt-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td><span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($order->status) }}</span></td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td><a href="{{ route('dashboard.orders.show', $order) }}" class="btn btn-sm btn-info">View</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">You have no orders yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection 