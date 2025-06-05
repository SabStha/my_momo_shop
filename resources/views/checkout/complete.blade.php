@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Order Confirmed</h2>
                </div>

                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Thank you for your order!</h4>
                        <p>Your order has been successfully placed.</p>
                    </div>

                    <div class="mb-4">
                        <h4>Order Details</h4>
                        <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                        <p><strong>Total Amount:</strong> Rs. {{ number_format($order->total_amount, 2) }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('home') }}" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 