@extends('layouts.admin')

@section('title', 'Simple Reports & Analytics')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Reports & Analytics</h2>
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
                <div class="display-6">{{ number_format($totalOrders) }}</div>
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