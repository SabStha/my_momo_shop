@extends('layouts.app')

@section('content')
<style>
    .top-nav {
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 1rem 0;
    }
    .top-nav .nav-item {
        color: #333;
        font-weight: 500;
        text-decoration: none;
        padding: 0.5rem 1rem;
    }
    .top-nav .nav-item:hover {
        color: #c1440e;
    }
    .top-nav .nav-item.active {
        color: #c1440e;
        border-bottom: 2px solid #c1440e;
    }
</style>
<div class="container" style="max-width: 700px; margin: 2rem auto;">
    <h1 style="font-weight: 700; margin-bottom: 1.5rem;">Shopping Cart</h1>
    
    @if(count($cart) > 0)
        <div class="cart-items" style="display: flex; flex-direction: column; gap: 1.2rem;">
            @foreach($cart as $item)
                <div class="cart-item" style="display: flex; align-items: center; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 1rem; gap: 1rem;">
                    <img src="{{ asset('storage/' . ($item['image'] ?? 'default-image.jpg')) }}" alt="{{ $item['name'] ?? 'Product' }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                    <div class="item-details" style="flex: 1;">
                        <h3 style="margin: 0 0 0.3rem 0; font-size: 1.1rem; font-weight: 600;">{{ $item['name'] ?? 'Unnamed Product' }}</h3>
                        <p class="price" style="margin: 0 0 0.5rem 0; color: #c1440e; font-weight: 500;">${{ number_format($item['price'] ?? 0, 2) }}</p>
                        <div class="quantity" style="display: flex; align-items: center; gap: 0.5rem;">
                            <form action="{{ route('cart.update', ['id' => $item['product_id'] ?? '']) }}" method="POST" style="display: flex; align-items: center; gap: 0.3rem;">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product_id'] ?? '' }}">
                                <button type="submit" name="quantity" value="{{ max(1, ($item['quantity'] ?? 1) - 1) }}" style="background: #eee; border: none; border-radius: 50%; width: 28px; height: 28px; font-size: 1.1rem;">-</button>
                                <input type="number" name="quantity" value="{{ $item['quantity'] ?? 1 }}" min="1" style="width: 40px; text-align: center; border: 1px solid #e8d9cc; border-radius: 6px; padding: 2px 0;">
                                <button type="submit" name="quantity" value="{{ ($item['quantity'] ?? 1) + 1 }}" style="background: #eee; border: none; border-radius: 50%; width: 28px; height: 28px; font-size: 1.1rem;">+</button>
                            </form>
                        </div>
                    </div>
                    <form action="{{ route('cart.remove', ['id' => $item['product_id'] ?? '']) }}" method="POST" style="margin-left: 0.5rem;">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item['product_id'] ?? '' }}">
                        <button type="submit" class="remove-btn" style="background: #fff0f0; color: #c1440e; border: none; border-radius: 50%; width: 32px; height: 32px; font-size: 1.2rem; cursor: pointer; transition: background 0.2s;" title="Remove item">&times;</button>
                    </form>
                </div>
            @endforeach
        </div>
        
        <div class="cart-summary" style="margin-top: 2rem; background: #fffaf3; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 1.2rem; text-align: right;">
            <h3 style="margin: 0 0 1rem 0; font-size: 1.3rem; font-weight: 700; color: #6e3d1b;">Total: ${{ number_format($total ?? 0, 2) }}</h3>
            <form action="{{ route('checkout') }}" method="GET" style="display: inline-block;">
                <button type="submit" class="btn" style="background: #c1440e; color: #fff; border: none; border-radius: 8px; padding: 0.7rem 2.2rem; font-size: 1.1rem; font-weight: 600; box-shadow: 0 2px 8px rgba(193,68,14,0.08); transition: background 0.2s;">Proceed to Checkout</button>
            </form>
        </div>
    @else
        <div style="text-align: center; margin: 3rem 0; color: #aaa;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">🛒</div>
            <p style="font-size: 1.2rem; margin-bottom: 1.5rem;">Your cart is empty.<br>Start adding delicious momos!</p>
            <a href="{{ route('home') }}" class="btn" style="background: #c1440e; color: #fff; border-radius: 8px; padding: 0.7rem 2rem; font-weight: 600; text-decoration: none;">Continue Shopping</a>
        </div>
    @endif
</div>
<div class="bottom-nav">
    <a href="{{ route('home') }}" class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('offers') }}" class="nav-item {{ request()->routeIs('offers') ? 'active' : '' }}">
        <i class="fas fa-gift"></i>
        <span>Offers</span>
    </a>
    <a href="{{ route('menu') }}" class="nav-item {{ request()->routeIs('menu') ? 'active' : '' }}">
        <i class="fas fa-utensils"></i>
        <span>Menu</span>
    </a>
    <a href="{{ route('cart') }}" class="nav-item {{ request()->routeIs('cart') ? 'active' : '' }}">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
    </a>
</div>
@endsection 