@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="text-center mb-4">Special Offers & Promotions</h1>

    @if($products->isEmpty())
        <div class="text-center py-5">
            <p class="text-muted">No special offers available at the moment.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($products as $product)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $product->image_url ?? asset('storage/default.jpg') }}" 
                             class="card-img-top" 
                             alt="{{ $product->name }}"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ $product->description }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                    <span class="ms-2 text-danger fw-bold">
                                        ${{ number_format($product->discount_price, 2) }}
                                    </span>
                                </div>
                                <form action="{{ route('cart.add', $product) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection 