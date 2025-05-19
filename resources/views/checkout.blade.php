@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Checkout</h1>
    
    <div class="checkout-container">
        <div class="order-summary">
            <h2>Order Summary</h2>
            @foreach($cartItems as $item)
                <div class="checkout-item">
                    <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                    <span>${{ number_format($item->product->price * $item->quantity, 2) }}</span>
                </div>
            @endforeach
            <div class="total">
                <strong>Total:</strong>
                <span>${{ number_format($total, 2) }}</span>
            </div>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST" class="checkout-form">
            @csrf
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" required>
            </div>
            
            <div class="form-group">
                <label for="address">Delivery Address</label>
                <textarea name="address" id="address" required></textarea>
            </div>

            <button type="submit" class="btn">Place Order</button>
        </form>
    </div>
</div>
@endsection 