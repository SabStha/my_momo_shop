@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            <img src="{{ asset('storage/' . $product->image) }}" 
                 class="img-fluid rounded" 
                 alt="{{ $product->name }}">
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <h1 class="mb-3">{{ $product->name }}</h1>
            
            <div class="mb-3">
                @if($product->discount_price)
                    <span class="text-decoration-line-through text-muted">
                        Rs. {{ number_format($product->price, 2) }}
                    </span>
                    <span class="ms-2 text-danger fw-bold fs-4">
                        Rs. {{ number_format($product->discount_price, 2) }}
                    </span>
                @else
                    <span class="fw-bold fs-4">
                        Rs. {{ number_format($product->price, 2) }}
                    </span>
                @endif
            </div>

            <p class="mb-4">{{ $product->description }}</p>

            <div class="mb-4">
                <h5>Details</h5>
                <ul class="list-unstyled">
                    <li><strong>Category:</strong> {{ $product->category }}</li>
                    @if($product->tag)
                        <li><strong>Tag:</strong> {{ $product->tag }}</li>
                    @endif
                    <li><strong>Availability:</strong> 
                        @if($product->stock > 0)
                            <span class="text-success">In Stock ({{ $product->stock }} available)</span>
                        @else
                            <span class="text-danger">Out of Stock</span>
                        @endif
                    </li>
                </ul>
            </div>

            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="quantity" class="col-form-label">Quantity:</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   class="form-control" 
                                   value="1" 
                                   min="1" 
                                   max="{{ $product->stock }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="mt-5">
            <h3 class="mb-4">You May Also Like</h3>
            <div class="row g-4">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col-md-3">
                        <x-momo-card :product="$relatedProduct" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif
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