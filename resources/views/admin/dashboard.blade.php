@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Admin Dashboard</h1>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p>{{ $totalOrders }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Products</h3>
            <p>{{ $totalProducts }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <p>${{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

    <div class="recent-orders">
        <h2>Recent Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->customer->name }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 