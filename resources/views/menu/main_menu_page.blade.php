@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#b8d8ba] to-[#d9dbbc]">
    {{-- Menu Tabs --}}
    <div class="sticky top-0 z-10 bg-[#fcddbc] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex overflow-x-auto scrollbar-hide">
                <button class="menu-tab active px-6 py-3 text-[#69585f] font-medium whitespace-nowrap border-b-2 border-[#ef959d] hover:bg-[#b8d8ba] transition-colors duration-200" data-tab="featured">
                    🔥 Featured
                </button>
                <button class="menu-tab px-6 py-3 text-[#69585f] font-medium whitespace-nowrap border-b-2 border-transparent hover:border-[#ef959d] hover:bg-[#b8d8ba] transition-colors duration-200" data-tab="combo">
                    🥡 Combo
                </button>
                <button class="menu-tab px-6 py-3 text-[#69585f] font-medium whitespace-nowrap border-b-2 border-transparent hover:border-[#ef959d] hover:bg-[#b8d8ba] transition-colors duration-200" data-tab="momo">
                    🥟 Momo
                </button>
                <button class="menu-tab px-6 py-3 text-[#69585f] font-medium whitespace-nowrap border-b-2 border-transparent hover:border-[#ef959d] hover:bg-[#b8d8ba] transition-colors duration-200" data-tab="drinks">
                    🥤 Drinks
                </button>
                <button class="menu-tab px-6 py-3 text-[#69585f] font-medium whitespace-nowrap border-b-2 border-transparent hover:border-[#ef959d] hover:bg-[#b8d8ba] transition-colors duration-200" data-tab="all">
                    🍽 All
                </button>
            </div>
        </div>
    </div>

    {{-- Menu Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Featured Section --}}
        <div class="tab-content active" id="featured-content">
            <h2 class="text-3xl font-bold text-[#6E0D25] mb-6">🔥 Featured Items</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featured as $product)
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="{{ asset('storage/' . $product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-48 object-cover" />
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $product->name }}</h3>
                        <p class="text-gray-600 mt-2">{{ $product->description }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($product->price, 2) }}</span>
                            <a href="{{ route('products.show', $product) }}" 
                               class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                                Order Now
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Combo Section --}}
        <div class="tab-content hidden" id="combo-content">
            <h2 class="text-3xl font-bold text-[#6E0D25] mb-6">🥡 Combo Sets</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($combos as $combo)
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="{{ asset('storage/' . $combo->image) }}" 
                         alt="{{ $combo->name }}" 
                         class="w-full h-48 object-cover" />
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $combo->name }}</h3>
                        <p class="text-gray-600 mt-2">{{ $combo->description }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($combo->price, 2) }}</span>
                            <a href="{{ route('products.show', $combo) }}" 
                               class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                                Order Now
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Momo Section --}}
        <div class="tab-content hidden" id="momo-content">
            <h2 class="text-3xl font-bold text-[#6E0D25] mb-6">🥟 Momos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($momoes as $momo)
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="{{ asset('storage/' . $momo->image) }}" 
                         alt="{{ $momo->name }}" 
                         class="w-full h-48 object-cover" />
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $momo->name }}</h3>
                        <p class="text-gray-600 mt-2">{{ $momo->description }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($momo->price, 2) }}</span>
                            <a href="{{ route('products.show', $momo) }}" 
                               class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                                Order Now
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Drinks Section --}}
        <div class="tab-content hidden" id="drinks-content">
            <h2 class="text-3xl font-bold text-[#6E0D25] mb-6">🥤 Drinks</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($drinks as $drink)
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="{{ asset('storage/' . $drink->image) }}" 
                         alt="{{ $drink->name }}" 
                         class="w-full h-48 object-cover" />
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $drink->name }}</h3>
                        <p class="text-gray-600 mt-2">{{ $drink->description }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($drink->price, 2) }}</span>
                            <a href="{{ route('products.show', $drink) }}" 
                               class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                                Order Now
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- All Items Section --}}
        <div class="tab-content hidden" id="all-content">
            <h2 class="text-3xl font-bold text-[#6E0D25] mb-6">🍽 All Menu Items</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featured->concat($momoes)->concat($drinks) as $item)
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="{{ asset('storage/' . $item->image) }}" 
                         alt="{{ $item->name }}" 
                         class="w-full h-48 object-cover" />
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $item->name }}</h3>
                        <p class="text-gray-600 mt-2">{{ $item->description }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($item->price, 2) }}</span>
                            <a href="{{ route('products.show', $item) }}" 
                               class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                                Order Now
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.menu-tab');
        const contents = document.querySelectorAll('.tab-content');

        function activateTab(tabName) {
            // Update tab buttons
            tabs.forEach(tab => {
                if (tab.dataset.tab === tabName) {
                    tab.classList.add('active', 'border-[#ef959d]');
                    tab.classList.remove('border-transparent');
                } else {
                    tab.classList.remove('active', 'border-[#ef959d]');
                    tab.classList.add('border-transparent');
                }
            });

            // Update content sections
            contents.forEach(content => {
                if (content.id === `${tabName}-content`) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        }

        // Add click event listeners to tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                activateTab(tab.dataset.tab);
            });
        });

        // Swipe functionality
        let startX = 0;
        const swipeZone = document.querySelector('.min-h-screen');

        swipeZone.addEventListener('touchstart', e => {
            startX = e.touches[0].clientX;
        });

        swipeZone.addEventListener('touchend', e => {
            const endX = e.changedTouches[0].clientX;
            const deltaX = endX - startX;
            const currentTab = document.querySelector('.menu-tab.active').dataset.tab;
            const tabIndex = Array.from(tabs).findIndex(tab => tab.dataset.tab === currentTab);

            if (Math.abs(deltaX) > 50) {
                if (deltaX < 0 && tabIndex < tabs.length - 1) {
                    activateTab(tabs[tabIndex + 1].dataset.tab);
                } else if (deltaX > 0 && tabIndex > 0) {
                    activateTab(tabs[tabIndex - 1].dataset.tab);
                }
            }
        });
    });
</script>
@endpush
@endsection
