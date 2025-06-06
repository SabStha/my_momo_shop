@props(['product'])

<div class="momo-card">
    <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid" alt="{{ $product->name }}">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start mt-2 gap-2">
        <div class="w-100">
            <h5>{{ $product->name }}</h5>
            <p>From <strong>Rs. {{ number_format($product->price, 2) }}</strong></p>
            @if($product->is_featured)
                <span class="badge bg-warning text-dark">Featured</span>
            @endif
        </div>
        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-success w-100 w-sm-auto">Buy Now</a>
    </div>
</div> 