@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Welcome to Momo Shop</h1>
    <div class="featured-products">
        <h2>Featured Momos</h2>
        <div class="products-grid">
            @foreach($featuredProducts as $product)
                <div class="product-card">
                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}">
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                    <p class="price">${{ number_format($product->price, 2) }}</p>
                    <a href="{{ route('products.show', $product) }}" class="btn">View Details</a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection 