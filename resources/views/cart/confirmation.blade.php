@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h1 class="mb-4">Thank You for Your Order!</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('coupon_message'))
        <div class="alert alert-info">{{ session('coupon_message') }}</div>
    @endif
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <h4 class="card-title">Order #{{ $order->id }}</h4>
            <p class="card-text">Total: <strong>${{ number_format($order->total_amount, 2) }}</strong></p>
            @if($order->discount_amount > 0)
                <p class="card-text text-success">Discount Applied: -${{ number_format($order->discount_amount, 2) }}</p>
                <p class="card-text fw-bold">Grand Total: <span class="text-warning">${{ number_format($order->grand_total, 2) }}</span></p>
            @endif
            <p class="card-text">We have received your order and will process it soon.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Home</a>
        </div>
    </div>
</div>
@endsection 