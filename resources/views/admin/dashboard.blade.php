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
    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-users"></i> Manage Employees
            </a>
            <a href="{{ route('admin.clock.index') }}" class="btn btn-success btn-lg ms-2">
                <i class="fas fa-clock"></i> Employee Clock In/Out
            </a>
        </div>
    </div>
</div>
{{-- Reports & Analytics Section --}}
<div class="mt-4">
    <h3 class="section-title">Reports & Analytics</h3>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-body mb-2">
                <b>Total Sales</b>
                <div class="display-6">Rs. {{ number_format($totalSales) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body mb-2">
                <b>Total Orders</b>
                <div class="display-6">{{ number_format($totalOrdersReport) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body mb-2">
                <b>Total Profit</b>
                <div class="display-6">Rs. {{ number_format($totalProfit) }}</div>
            </div>
        </div>
    </div>
    <h4 class="mt-4">Employee Working Hours</h4>
    <table class="table table-striped mb-4">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Total Hours</th>
                <th>Overtime</th>
                <th>Total Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employeeHours as $emp)
            <tr>
                <td>{{ $emp['name'] }}</td>
                <td>{{ $emp['totalHours'] }}</td>
                <td>{{ $emp['overtime'] }}</td>
                <td>Rs. {{ number_format($emp['totalPay']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="container-fluid dashboard-section">
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
{{-- Move Profit Analysis table to the bottom --}}
<div class="container-fluid dashboard-section mt-4">
    <h4 class="mt-4">Profit Analysis</h4>
    <table class="table table-striped mb-4">
        <thead>
            <tr>
                <th>Date</th>
                <th>Revenue</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Profit Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profitAnalysis as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>Rs. {{ number_format($row['revenue']) }}</td>
                <td>Rs. {{ number_format($row['cost']) }}</td>
                <td>Rs. {{ number_format($row['profit']) }}</td>
                <td>{{ $row['margin'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
@endpush
