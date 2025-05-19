@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Shopping Cart</h1>
    
    @if(count($cartItems) > 0)
        <div class="cart-items">
            @foreach($cartItems as $item)
                <div class="cart-item">
                    <img src="{{ asset($item->product->image) }}" alt="{{ $item->product->name }}">
                    <div class="item-details">
                        <h3>{{ $item->product->name }}</h3>
                        <p class="price">${{ number_format($item->product->price, 2) }}</p>
                        <div class="quantity">
                            <form action="{{ route('cart.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1">
                                <button type="submit">Update</button>
                            </form>
                        </div>
                    </div>
                    <form action="{{ route('cart.remove') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>
                </div>
            @endforeach
        </div>
        
        <div class="cart-summary">
            <h3>Total: ${{ number_format($total, 2) }}</h3>
            <a href="{{ route('checkout.index') }}" class="btn">Proceed to Checkout</a>
        </div>
    @else
        <p>Your cart is empty.</p>
        <a href="{{ route('products.index') }}" class="btn">Continue Shopping</a>
    @endif
</div>
@endsection 