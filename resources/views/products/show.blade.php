@extends('layouts.app')

@section('content')
<div class="container">
    <div class="product-detail">
        <div class="product-image">
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" width="400" height="400">
        </div>
        <div class="product-info">
            <h1>{{ $product->name }}</h1>
            <p class="description">{{ $product->description }}</p>
            <p class="price">${{ number_format($product->price, 2) }}</p>
            <form action="{{ route('cart.add', $product) }}" method="POST">
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="cart/add"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const qty = document.getElementById('quantity').value;
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quantity: qty
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                } else {
                    alert(data.message || 'Could not add to cart.');
                }
            });
        });
    }
});
</script>
@endsection 