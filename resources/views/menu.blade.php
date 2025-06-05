@extends('layouts.app')

@section('content')
@if($featured->count())
<div class="text-3xl text-red-500 font-bold">TAILWIND IS WORKING</div>

<div id="featuredCarousel" class="relative overflow-hidden h-[420px] mb-10">
    <div class="carousel-inner relative w-full h-full">
        @foreach($featured as $product)
            @php
                $img = $product->image ? asset('storage/' . $product->image) : asset('storage/products/background.png');
            @endphp
            <div class="carousel-item absolute inset-0 transition-opacity duration-700 ease-in-out {{ $loop->first ? 'opacity-100 z-20' : 'opacity-0 z-10' }}">
                <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/20 flex flex-col justify-end p-6 text-white">
                    <h1 class="text-2xl md:text-4xl font-bold mb-2">{{ $product->name }}</h1>
                    <p class="mb-4 text-gray-200">{{ $product->description }}</p>
                    <div class="flex space-x-4">
                        <form action="{{ route('checkout.process', $product) }}" method="POST">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="bg-teal-400 hover:bg-teal-300 text-black font-semibold px-4 py-2 rounded transition">Buy Now</button>
                        </form>
                        <a href="#menuSection" class="border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-black transition">View Menu</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="container mx-auto px-4 pb-20" id="menuSection" x-data="menuTabs()">
    <!-- Tab Navigation -->
    <div class="flex items-center justify-between mb-6">
        <button class="text-2xl px-2" @click="prevTab">&#8592;</button>
        <div class="flex flex-wrap justify-center gap-2">
            <template x-for="(tab, index) in tabs" :key="index">
                <button 
                    @click="currentTab = index"
                    class="px-4 py-2 rounded-full border font-medium"
                    :class="currentTab === index ? 'bg-teal-500 text-white' : 'bg-white border-gray-300 text-gray-700'"
                    x-text="tab.label">
                </button>
            </template>
        </div>
        <button class="text-2xl px-2" @click="nextTab">&#8594;</button>
    </div>

    <!-- Product Grid -->
    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <template x-for="item in filteredItems" :key="item.id">
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                <img :src="'/storage/' + item.image" class="w-full h-40 object-cover" :alt="item.name">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1" x-text="item.name"></h3>
                    <p class="text-sm text-gray-600 mb-2">Rs. <span x-text="item.price"></span></p>
                    <button class="bg-teal-600 text-white w-full py-2 rounded hover:bg-teal-700 transition">Buy Now</button>
                </div>
            </div>
        </template>
    </div>
</div>

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
