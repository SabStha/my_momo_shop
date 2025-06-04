@extends('layouts.app')
@section('content')

<div class="container-fluid px-1">
    <div class="hero position-relative" style="min-height: 400px; background-color: var(--background-color); overflow: hidden;">
        @if($featuredProducts->count())
            <div id="featuredCarousel" class="carousel carousel-fade slide h-100" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner h-100">
                    @foreach($featuredProducts as $product)
                        @php
                            $img = $product->image ? asset('storage/' . $product->image) : asset('storage/products/background.png');
                        @endphp
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}" style="position: relative; height: 420px; overflow: hidden;">
                            <img src="{{ $img }}" alt="{{ $product->name }}"
                                class="position-absolute top-0 start-0 w-100 h-100"
                                style="object-fit: cover; z-index: 1; background-color: var(--background-color); opacity:0.9 ">

                            <div class="carousel-caption d-flex flex-column justify-content-end text-start p-4"
                                style="z-index: 2; top: 0; left: 0; right: 0; bottom: 0; height: 100%;">
                                
                                <h1 class="fs-4 fs-md-2 fw-bold mb-1">{{ $product->name }}</h1>
                                <p class="fs-6 mb-2">{{ $product->description }}</p>

                                <div class="row gx-2">
                                    <div class="col-6">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-primary w-100">Buy Now</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('menu') }}" class="btn btn-outline-light w-100">View Menu</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($featuredProducts->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                @endif
            </div>
        @else
        <img src="{{ asset('storage/products/background.png') }}"
             alt="Momo Bowl"
                 class="w-100 h-100"
                 style="object-fit: cover; z-index: 1; height: 400px; background-color: var(--background-color);">
        <div class="position-absolute bottom-0 start-0 p-3 p-sm-4" style="z-index: 2; color: white; background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0)); width: 100%;">
            <h1 class="fs-4 fs-md-2 fw-bold">Fresh, Authentic Momos<br>Delivered to Your Door</h1>
            <p class="fs-6">Enjoy our delicious dumplings at home</p>
            <div class="row gx-2">
                <div class="col-6">
                        <a href="{{ route('menu') }}" class="btn btn-primary w-100">Order Now</a>
                </div>
                <div class="col-6">
                        <a href="{{ route('menu') }}" class="btn btn-outline-light w-100">View Menu</a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($menuHighlights->count())
    <div class="varieties-section">
        <h2 class="mb-3">MENU HIGHLIGHTS</h2>
        <div class="row justify-content-center g-3">
            @foreach($menuHighlights as $product)
            <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <x-momo-card :product="$product" />
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
    const buyNowForms = document.querySelectorAll('.buy-now-form');
    
    buyNowForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const productId = this.dataset.productId;
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `/products/${productId}`;
                } else {
                    alert(data.message || 'Failed to process order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>
@endsection

