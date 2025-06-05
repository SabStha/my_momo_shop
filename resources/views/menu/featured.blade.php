@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="text-center mb-4">🔥 Featured Items</h1>

    @if($featuredProducts->count())
        <div class="row g-4">
            @foreach($featuredProducts as $product)
                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm h-100 featured-card">
                        <img src="{{ asset('storage/' . $product->image) }}"
                             onerror="this.src='/storage/products/default.png'"
                             class="card-img-top"
                             alt="{{ $product->name }}"
                             style="height: 200px; object-fit: cover; border-radius: 8px 8px 0 0;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">{{ $product->name }}</h5>
                            <p class="text-muted small">{{ $product->description }}</p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rs. {{ number_format($product->price) }}</span>
                                <button class="btn btn-sm btn-success">
                                    <i class="bi bi-cart-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-warning text-center mt-5">
            No featured items found.
        </div>
    @endif
</div>
@endsection
