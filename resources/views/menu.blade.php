@extends('layouts.app')

@section('content')
@if($featured->count())
    <div id="featuredCarousel" class="carousel carousel-fade slide h-100" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner h-100">
            @foreach($featured as $product)
                @php
                    $img = $product->image ? asset('storage/' . $product->image) : asset('storage/products/background.png');
                @endphp
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}" style="position: relative; height: 420px; overflow: hidden;">
                    <img src="{{ $img }}" alt="{{ $product->name }}"
                        class="position-absolute top-0 start-0 w-100 h-100"
                        style="object-fit: cover; z-index: 1; background-color: var(--background-color); opacity:0.9;">
                    <div class="carousel-caption d-flex flex-column justify-content-end text-start p-4"
                        style="z-index: 2; top: 0; left: 0; right: 0; bottom: 0; height: 100%;">
                        <h1 class="fs-4 fs-md-2 fw-bold mb-1">{{ $product->name }}</h1>
                        <p class="fs-6 mb-2">{{ $product->description }}</p>
                        <div class="row gx-2">
                            <div class="col-6">
                                <form action="{{ route('checkout.buyNow', $product) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary w-100">Buy Now</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <a href="#menuSection" class="btn btn-outline-light w-100">View Menu</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($featured->count() > 1)
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
@endif

<!-- Menu Section -->
<div class="container py-4" id="menuSection" x-data="menuTabs()">
    <!-- Tab Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-outline-dark btn-sm" @click="prevTab">&#8592;</button>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <template x-for="(tab, index) in tabs" :key="index">
                <button 
                    @click="currentTab = index" 
                    class="btn btn-sm"
                    :class="currentTab === index ? 'btn-primary' : 'btn-outline-primary'"
                    x-text="tab.label">
                </button>
            </template>
        </div>
        <button class="btn btn-outline-dark btn-sm" @click="nextTab">&#8594;</button>
    </div>

    <!-- Product Grid -->
    <div class="row g-3 justify-content-center">
        <template x-for="item in filteredItems" :key="item.id">
            <div class="col-md-3 col-sm-6">
                <div class="card h-100">
                    <img :src="'/storage/' + item.image" class="card-img-top" :alt="item.name">
                    <div class="card-body">
                        <h5 class="card-title" x-text="item.name"></h5>
                        <p class="card-text">Rs. <span x-text="item.price"></span></p>
                        <button class="btn btn-sm btn-primary w-100">Buy Now</button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<!-- Alpine.js Component -->
<script>
function menuTabs() {
    return {
        currentTab: 0,
        tabs: [
            { label: '🔥 Featured', key: 'featured' },
            { label: '🥟 Combo', key: 'combos' },
            { label: '🍜 Momo', key: 'momoes' },
            { label: '🥤 Drinks', key: 'drinks' },
            { label: '🍽 All', key: 'all' },
        ],
        data: {
            featured: @json($featured),
            combos: @json($combos),
            momoes: @json($momoes ?? []),
            drinks: @json($drinks),
        },
        get filteredItems() {
            const key = this.tabs[this.currentTab].key;
            if (key === 'all') {
                return [
                    ...this.data.featured,
                    ...this.data.combos,
                    ...this.data.momoes,
                    ...this.data.drinks,
                ];
            }
            return this.data[key] ?? [];
        },
        prevTab() {
            if (this.currentTab > 0) this.currentTab--;
        },
        nextTab() {
            if (this.currentTab < this.tabs.length - 1) this.currentTab++;
        }
    }
}
</script>
@endsection
