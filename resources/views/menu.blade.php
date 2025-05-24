@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center">Menu</h2>
    <div class="text-center mb-4">
        <button class="btn btn-outline-dark filter-btn me-2 active" data-filter="all">All</button>
        @foreach($tags as $tag)
            <button class="btn btn-outline-dark filter-btn me-2" data-filter="{{ $tag }}">{{ ucwords(str_replace('_', ' ', $tag)) }}</button>
        @endforeach
    </div>
    <div class="row g-4" id="menuGrid">
        @foreach($products as $product)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 menu-item" data-tag="{{ strtolower($product->tag) }}">
            <div class="card h-100 shadow-sm">
                <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <div class="mb-2 fw-bold">${{ number_format($product->price, 2) }}</div>
                    <div class="mt-auto d-flex gap-2">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-danger flex-fill">View Details</a>
                        <form action="{{ route('checkout.buyNow', $product) }}" method="POST" class="flex-fill m-0 p-0">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-sm btn-danger flex-fill">Buy Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const menuItems = document.querySelectorAll('.menu-item');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelector('.filter-btn.active')?.classList.remove('active');
            btn.classList.add('active');
            const filter = btn.getAttribute('data-filter');
            menuItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-tag') === filter) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endsection 