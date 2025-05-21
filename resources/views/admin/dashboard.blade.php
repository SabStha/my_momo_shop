@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- <style>
    body {
        background-color: #5c2c11; /* dark orange-brown */
    }

    .dashboard-section {
        background-color: #f9c784; /* warm light orange */
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #f4a259; /* lighter brown/orange */
        color: #3b1f0d;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stat-card h3 {
        font-weight: bold;
        margin-bottom: 10px;
    }

    .stat-card .value {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #3b1f0d;
        margin-bottom: 15px;
    }

    .custom-table th {
        background-color: #e76f51;
        color: #fff;
    }

    .custom-table td, .custom-table th {
        color: #3b1f0d;
    }

    .custom-table {
        background-color: #fff3e0;
        border-radius: 8px;
        overflow: hidden;
    }
</style> -->

<div class="container-fluid dashboard-section">
    <div class="row text-center">
        <div class="col-md-4">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value">{{ $totalOrders }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="value">{{ $totalProducts }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="value">{{ $pendingOrders }}</div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-6">
            <h4 class="section-title">Recent Orders</h4>
            <table class="table custom-table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentOrders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->user->name ?? 'Guest' }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <h4 class="section-title">Top Selling Products</h4>
            <table class="table custom-table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sold_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
