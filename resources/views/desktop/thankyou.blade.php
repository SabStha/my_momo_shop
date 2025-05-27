@extends('desktop.layouts.app')

@section('content')
<div class="container">
    <div class="thank-you">
        <h1>Thank You for Your Order!</h1>
        <p>Your order has been successfully placed.</p>
        <p>Order Number: #{{ $order->id }}</p>
        <p>We'll send you an email confirmation shortly.</p>
        <a href="{{ route('home') }}" class="btn">Continue Shopping</a>
    </div>
</div>
@endsection 