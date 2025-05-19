@extends('layouts.app')

@section('content')
<div class="container">
    <div class="product-detail">
        <div class="product-image">
            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}">
        </div>
        <div class="product-info">
            <h1>{{ $product->name }}</h1>
            <p class="description">{{ $product->description }}</p>
            <p class="price">${{ number_format($product->price, 2) }}</p>
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="quantity">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1">
                </div>
                <button type="submit" class="btn">Add to Cart</button>
            </form>
        </div>
    </div>
</div>
@endsection 