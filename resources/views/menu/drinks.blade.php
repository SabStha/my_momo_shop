<div x-data="{ 
    activeDrinkTab: 'hot',
    isLoaded: false,
    animateTab: false,
    hoveredTab: null
}" 
x-init="
    isLoaded = true;
    setTimeout(() => animateTab = true, 100);
" 
:class="{
    'bg-gradient-to-br from-orange-50 to-red-100': activeDrinkTab === 'hot',
    'bg-gradient-to-br from-blue-50 to-cyan-100': activeDrinkTab === 'cold',
    'bg-gradient-to-br from-purple-50 to-pink-100': activeDrinkTab === 'boba'
}"
class="min-h-screen overflow-x-hidden transition-all duration-700 ease-in-out">
    <div class="w-full px-4 py-2 space-y-5 overflow-x-hidden">
        
        <!-- HEADER -->
        <div class="text-center mb-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#6E0D25] mb-2">🥤 Drinks Menu</h1>
            <p class="text-sm sm:text-base text-gray-600">Cool, refreshing sips for every mood</p>
        </div>

        <!-- DRINKS SUB-CATEGORY TABS -->
        <div class="relative z-10 pt-2 pb-1 overflow-x-hidden" 
             x-show="isLoaded" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="w-full bg-white rounded-xl shadow-lg px-2 py-2 hover:shadow-xl transition-all duration-300">
                <div class="flex w-full font-bold text-xs sm:text-sm text-[#000]">
                    <button @click="activeDrinkTab = 'hot'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                            @mouseenter="hoveredTab = 'hot'"
                            @mouseleave="hoveredTab = null"
                            @touchstart="hoveredTab = 'hot'"
                            @touchend="setTimeout(() => hoveredTab = null, 200)"
                            :class="{ 'text-red-600 scale-110 shadow-lg': activeDrinkTab === 'hot' }" 
                            class="tab-button relative flex-1 px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95">
                        <span class="relative z-10">☕ HOT</span>
                        <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                             :class="{ 'scale-x-100': hoveredTab === 'hot' || activeDrinkTab === 'hot' }"></div>
                    </button>
                    <button @click="activeDrinkTab = 'cold'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                            @mouseenter="hoveredTab = 'cold'"
                            @mouseleave="hoveredTab = null"
                            @touchstart="hoveredTab = 'cold'"
                            @touchend="setTimeout(() => hoveredTab = null, 200)"
                            :class="{ 'text-red-600 scale-110 shadow-lg': activeDrinkTab === 'cold' }" 
                            class="tab-button relative flex-1 px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95">
                        <span class="relative z-10">🧊 COLD</span>
                        <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                             :class="{ 'scale-x-100': hoveredTab === 'cold' || activeDrinkTab === 'cold' }"></div>
                    </button>
                    <button @click="activeDrinkTab = 'boba'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                            @mouseenter="hoveredTab = 'boba'"
                            @mouseleave="hoveredTab = null"
                            @touchstart="hoveredTab = 'boba'"
                            @touchend="setTimeout(() => hoveredTab = null, 200)"
                            :class="{ 'text-red-600 scale-110 shadow-lg': activeDrinkTab === 'boba' }" 
                            class="tab-button relative flex-1 px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95">
                        <span class="relative z-10">🧋 BOBA</span>
                        <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                             :class="{ 'scale-x-100': hoveredTab === 'boba' || activeDrinkTab === 'boba' }"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB CONTENT AREA -->
        <div class="px-0 py-0 space-y-0 overflow-x-hidden">
            <!-- HOT DRINKS TAB -->
            <div x-show="activeDrinkTab === 'hot' && animateTab" 
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
                 x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
                @if($hotDrinks && $hotDrinks->count() > 0)
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($hotDrinks as $product)
                        <div class="relative bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate group">
                            <!-- Blurred Background Effect -->
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-25 transition-opacity duration-500 blur-bg">
                                <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover" />
                            </div>
                            
                            <!-- Main Content -->
                            <div class="relative z-10 flex flex-col h-full">
                                <!-- Image Section - Takes 80% of card -->
                                <div class="relative food-image flex-shrink-0" style="height: 80%;">
                                    <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                    
                                    <!-- Product Info Overlay - Inside Image -->
                                    <div class="absolute bottom-3 left-3">
                                        <div class="bg-black/60 backdrop-blur-sm rounded-lg px-3 py-3 inline-block">
                                            <h3 class="font-bold text-lg text-white text-left mb-2">{{ $product->name }}</h3>
                                            <p class="text-sm text-white leading-relaxed mb-2 line-clamp-2">{{ $product->description }}</p>
                                            
                                            <!-- Reviews Section -->
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center">
                                                    <span class="text-yellow-400 text-sm">⭐⭐⭐⭐⭐</span>
                                                    <span class="text-xs text-gray-300 ml-1">(4.8)</span>
                                                </div>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-300">127 reviews</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Content Section - Takes 20% of card (Only Price and Buttons) -->
                                <div class="p-4 content-area flex-shrink-0" style="height: 20%;">
                                    <!-- Price and Actions -->
                                    <div class="flex justify-between items-center h-full">
                                    <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                                    <div class="flex gap-2">
                                        <!-- Ingredients Button -->
                                        <button @click="
                                            selectedProduct = {
                                                name: '{{ $product->name }}',
                                                ingredients: '{{ $product->ingredients ?? 'Fresh ingredients prepared daily' }}',
                                                allergens: '{{ $product->allergens ?? 'No allergens' }}',
                                                calories: '{{ $product->calories ?? 'N/A' }}',
                                                preparation_time: '{{ $product->preparation_time ?? '3-5 minutes' }}',
                                                spice_level: '{{ $product->spice_level ?? 'None' }}',
                                                serving_size: '{{ $product->serving_size ?? '1 cup' }}',
                                                is_vegetarian: {{ $product->is_vegetarian ? 'true' : 'false' }},
                                                is_vegan: {{ $product->is_vegan ? 'true' : 'false' }},
                                                is_gluten_free: {{ $product->is_gluten_free ? 'true' : 'false' }},
                                                image: '{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}'
                                            };
                                            showIngredientsModal = true;
                                        "
                                                class="bg-blue-500 border border-blue-600 text-white text-xs font-medium px-3 py-2 rounded-lg flex items-center gap-1.5 hover:bg-blue-600 hover:border-blue-700 hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-sm hover:shadow-md">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Info</span>
                                        </button>
                                        
                                        <!-- Add to Cart Button -->
                                        <button data-add-to-cart
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-product-price="{{ $product->price }}"
                                                data-product-image="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}"
                                                class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                            <span class="text-base">＋</span>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <!-- NO HOT DRINKS MESSAGE -->
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">☕</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Hot Drinks Available</h3>
                    <p class="text-gray-500">Check back later for delicious hot beverages!</p>
                </div>
                @endif
            </div>

            <!-- COLD DRINKS TAB -->
            <div x-show="activeDrinkTab === 'cold' && animateTab" 
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
                 x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
                @if($coldDrinks && $coldDrinks->count() > 0)
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($coldDrinks as $product)
                            <div class="relative bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate group">
                                <!-- Blurred Background Effect -->
                                <div class="absolute inset-0 opacity-0 group-hover:opacity-25 transition-opacity duration-500 blur-bg">
                                    <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover" />
                                </div>
                                
                                <!-- Main Content -->
                                <div class="relative z-10 flex flex-col h-full">
                                    <!-- Image Section - Takes 80% of card -->
                                    <div class="relative food-image flex-shrink-0" style="height: 80%;">
                                        <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                        
                                        <!-- Product Info Overlay - Inside Image -->
                                        <div class="absolute bottom-3 left-3">
                                            <div class="bg-black/60 backdrop-blur-sm rounded-lg px-3 py-3 inline-block">
                                                <h3 class="font-bold text-lg text-white text-left mb-2">{{ $product->name }}</h3>
                                                <p class="text-sm text-white leading-relaxed mb-2 line-clamp-2">{{ $product->description }}</p>
                                                
                                                <!-- Reviews Section -->
                                                <div class="flex items-center gap-2">
                                                    <div class="flex items-center">
                                                        <span class="text-yellow-400 text-sm">⭐⭐⭐⭐⭐</span>
                                                        <span class="text-xs text-gray-300 ml-1">(4.7)</span>
                                                    </div>
                                                    <span class="text-xs text-gray-400">•</span>
                                                    <span class="text-xs text-gray-300">89 reviews</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Content Section - Takes 20% of card (Only Price and Buttons) -->
                                    <div class="p-4 content-area flex-shrink-0" style="height: 20%;">
                                        <!-- Price and Actions -->
                                        <div class="flex justify-between items-center h-full">
                                        <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                                        <div class="flex gap-2">
                                            <!-- Ingredients Button -->
                                            <button @click="showIngredientsModal = true; selectedProduct = {
                                                name: '{{ $product->name }}',
                                                ingredients: '{{ $product->ingredients ?? 'Water, natural flavors, sugar, preservatives' }}',
                                                allergens: '{{ $product->allergens ?? 'Contains: Dairy, Soy' }}',
                                                calories: '{{ $product->calories ?? '120-150' }}',
                                                image: '{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}'
                                            }"
                                                    class="bg-blue-500 border border-blue-600 text-white text-xs font-medium px-3 py-2 rounded-lg flex items-center gap-1.5 hover:bg-blue-600 hover:border-blue-700 hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-sm hover:shadow-md">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Info</span>
                                            </button>
                                            
                                            <!-- Add to Cart Button -->
                                            <button data-add-to-cart
                                                    data-product-id="{{ $product->id }}"
                                                    data-product-name="{{ $product->name }}"
                                                    data-product-price="{{ $product->price }}"
                                                    data-product-image="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}"
                                                    class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                                <span class="text-base">＋</span>
                                                <span>Add</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            @endforeach
                    </div>
                </div>
                @else
                <!-- NO COLD DRINKS MESSAGE -->
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🧊</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Cold Drinks Available</h3>
                    <p class="text-gray-500">Check back later for refreshing cold beverages!</p>
                </div>
                @endif
            </div>

            <!-- BOBA DRINKS TAB -->
            <div x-show="activeDrinkTab === 'boba' && animateTab" 
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
                 x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
                 x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
                @if($bobaDrinks && $bobaDrinks->count() > 0)
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($bobaDrinks as $product)
                        <div class="relative bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate group">
                            <!-- Blurred Background Effect -->
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-25 transition-opacity duration-500 blur-bg">
                                <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover" />
                            </div>
                            
                            <!-- Main Content -->
                            <div class="relative z-10 flex flex-col h-full">
                                <!-- Image Section - Takes 80% of card -->
                                <div class="relative food-image flex-shrink-0" style="height: 80%;">
                                    <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                    
                                    <!-- Product Info Overlay - Inside Image -->
                                    <div class="absolute bottom-3 left-3">
                                        <div class="bg-black/60 backdrop-blur-sm rounded-lg px-3 py-3 inline-block">
                                            <h3 class="font-bold text-lg text-white text-left mb-2">{{ $product->name }}</h3>
                                            <p class="text-sm text-white leading-relaxed mb-2 line-clamp-2">{{ $product->description }}</p>
                                            
                                            <!-- Reviews Section -->
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center">
                                                    <span class="text-yellow-400 text-sm">⭐⭐⭐⭐⭐</span>
                                                    <span class="text-xs text-gray-300 ml-1">(4.9)</span>
                                                </div>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-300">156 reviews</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Content Section - Takes 20% of card (Only Price and Buttons) -->
                                <div class="p-4 content-area flex-shrink-0" style="height: 20%;">
                                    <!-- Price and Actions -->
                                    <div class="flex justify-between items-center h-full">
                                    <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                                    <div class="flex gap-2">
                                        <!-- Ingredients Button -->
                                        <button @click="
                                            selectedProduct = {
                                                name: '{{ $product->name }}',
                                                ingredients: '{{ $product->ingredients ?? 'Milk tea, tapioca pearls, sweetener, and ice' }}',
                                                allergens: '{{ $product->allergens ?? 'Contains: Dairy, Tapioca' }}',
                                                calories: '{{ $product->calories ?? '250-350' }}',
                                                preparation_time: '{{ $product->preparation_time ?? '5-7 minutes' }}',
                                                spice_level: '{{ $product->spice_level ?? 'None' }}',
                                                serving_size: '{{ $product->serving_size ?? '16 oz' }}',
                                                is_vegetarian: {{ $product->is_vegetarian ? 'true' : 'false' }},
                                                is_vegan: {{ $product->is_vegan ? 'true' : 'false' }},
                                                is_gluten_free: {{ $product->is_gluten_free ? 'true' : 'false' }},
                                                image: '{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}'
                                            };
                                            showIngredientsModal = true;
                                        "
                                                class="bg-blue-500 border border-blue-600 text-white text-xs font-medium px-3 py-2 rounded-lg flex items-center gap-1.5 hover:bg-blue-600 hover:border-blue-700 hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-sm hover:shadow-md">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Info</span>
                                        </button>
                                        
                                        <!-- Add to Cart Button -->
                                        <button data-add-to-cart
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-product-price="{{ $product->price }}"
                                                data-product-image="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}"
                                                class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                            <span class="text-base">＋</span>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <!-- NO BOBA DRINKS MESSAGE -->
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🧋</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Boba Drinks Available</h3>
                    <p class="text-gray-500">Check back later for delicious boba beverages!</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>


<!-- Include Cart Modal -->

@include('components.cart-modal')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe all animated elements
    const animatedElements = document.querySelectorAll('.card-animate');
    animatedElements.forEach(el => observer.observe(el));

    // Touch feedback for mobile
    const touchElements = document.querySelectorAll('.touch-ripple');
    touchElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        element.addEventListener('touchend', function() {
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Enhanced badge animations
    const badges = document.querySelectorAll('.zigzag-highlight, .premium-zigzag, .popular-zigzag, .value-zigzag, .lunch-zigzag');
    badges.forEach(badge => {
        badge.classList.add('badge-mobile-pulse');
        
        if (badge.textContent.includes('Chef') || badge.textContent.includes('Premium')) {
            badge.classList.add('badge-bounce');
        }
    });

    // Drink card entrance animations with staggered auto-hover delays
    const drinkCards = document.querySelectorAll('.drink-card');
    drinkCards.forEach((card, index) => {
        // Entrance animation delay
        card.style.animationDelay = `${index * 0.2}s`;
        card.classList.add('card-animate');
        
        // Staggered auto-hover animation delays for more dynamic effect
        const autoHoverDelay = index * 0.5; // 0.5s delay between each card's animation
        card.style.setProperty('--auto-hover-delay', `${autoHoverDelay}s`);
    });
});
</script>

<style>
/* Drink Card Animations */
.drink-card {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    animation: autoHover 3s ease-in-out infinite;
    animation-delay: var(--auto-hover-delay, 0s);
}

.drink-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    animation-play-state: paused;
}

.drink-card:active {
    transform: scale(0.98);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    animation-play-state: paused;
}

/* Auto Hover Animation */
@keyframes autoHover {
    0%, 100% {
        transform: translateY(0) scale(1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    25% {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    50% {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }
    75% {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
}

/* Enhanced Blur Background Effect */
.drink-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    opacity: 0;
    transition: opacity 0.5s ease;
    z-index: 1;
    pointer-events: none;
}

.drink-card:hover::before {
    opacity: 1;
}

/* Drink Image Enhancements */
.drink-image {
    transition: all 0.5s ease;
    overflow: hidden;
    position: relative;
    animation: imagePulse 4s ease-in-out infinite;
    animation-delay: var(--auto-hover-delay, 0s);
}

.drink-image:hover {
    transform: scale(1.03);
    filter: brightness(1.1) contrast(1.05);
    animation-play-state: paused;
}

.drink-image img {
    transition: all 0.5s ease;
    transform-origin: center;
    animation: imageZoom 5s ease-in-out infinite;
    animation-delay: calc(var(--auto-hover-delay, 0s) + 0.3s);
}

.drink-image:hover img {
    transform: scale(1.08);
    filter: brightness(1.15) contrast(1.1) saturate(1.1);
    animation-play-state: paused;
}

/* Image Pulse Animation */
@keyframes imagePulse {
    0%, 100% {
        filter: brightness(1) contrast(1);
    }
    50% {
        filter: brightness(1.05) contrast(1.02);
    }
}

/* Image Zoom Animation */
@keyframes imageZoom {
    0%, 100% {
        transform: scale(1);
    }
    25% {
        transform: scale(1.02);
    }
    50% {
        transform: scale(1.03);
    }
    75% {
        transform: scale(1.02);
    }
}

/* Badge Animations */
.zigzag-highlight {
    animation: zigzag-highlight 2s ease-in-out infinite;
    background: linear-gradient(45deg, #fbbf24, #f59e0b, #d97706);
    background-size: 200% 200%;
}

.premium-zigzag {
    animation: premium-zigzag 2s ease-in-out infinite;
    background: linear-gradient(45deg, #a855f7, #9333ea, #7c3aed);
    background-size: 200% 200%;
}

.popular-zigzag {
    animation: popular-zigzag 2s ease-in-out infinite;
    background: linear-gradient(45deg, #4ade80, #22c55e, #16a34a);
    background-size: 200% 200%;
}

.value-zigzag {
    animation: value-zigzag 2s ease-in-out infinite;
    background: linear-gradient(45deg, #60a5fa, #3b82f6, #2563eb);
    background-size: 200% 200%;
}

.lunch-zigzag {
    animation: lunch-zigzag 2s ease-in-out infinite;
    background: linear-gradient(45deg, #fb923c, #f97316, #ea580c);
    background-size: 200% 200%;
}

@keyframes zigzag-highlight {
    0%, 100% {
        background: linear-gradient(45deg, #fbbf24, #f59e0b, #d97706);
        transform: scale(1);
    }
    25% {
        background: linear-gradient(45deg, #f59e0b, #d97706, #fbbf24);
        transform: scale(1.05);
    }
    50% {
        background: linear-gradient(45deg, #d97706, #fbbf24, #f59e0b);
        transform: scale(1.1);
    }
    75% {
        background: linear-gradient(45deg, #fbbf24, #f59e0b, #d97706);
        transform: scale(1.05);
    }
}

@keyframes premium-zigzag {
    0%, 100% {
        background: linear-gradient(45deg, #a855f7, #9333ea, #7c3aed);
        transform: scale(1);
    }
    25% {
        background: linear-gradient(45deg, #9333ea, #7c3aed, #a855f7);
        transform: scale(1.05);
    }
    50% {
        background: linear-gradient(45deg, #7c3aed, #a855f7, #9333ea);
        transform: scale(1.1);
    }
    75% {
        background: linear-gradient(45deg, #a855f7, #9333ea, #7c3aed);
        transform: scale(1.05);
    }
}

@keyframes popular-zigzag {
    0%, 100% {
        background: linear-gradient(45deg, #4ade80, #22c55e, #16a34a);
        transform: scale(1);
    }
    25% {
        background: linear-gradient(45deg, #22c55e, #16a34a, #4ade80);
        transform: scale(1.05);
    }
    50% {
        background: linear-gradient(45deg, #16a34a, #4ade80, #22c55e);
        transform: scale(1.1);
    }
    75% {
        background: linear-gradient(45deg, #4ade80, #22c55e, #16a34a);
        transform: scale(1.05);
    }
}

@keyframes value-zigzag {
    0%, 100% {
        background: linear-gradient(45deg, #60a5fa, #3b82f6, #2563eb);
        transform: scale(1);
    }
    25% {
        background: linear-gradient(45deg, #3b82f6, #2563eb, #60a5fa);
        transform: scale(1.05);
    }
    50% {
        background: linear-gradient(45deg, #2563eb, #60a5fa, #3b82f6);
        transform: scale(1.1);
    }
    75% {
        background: linear-gradient(45deg, #60a5fa, #3b82f6, #2563eb);
        transform: scale(1.05);
    }
}

@keyframes lunch-zigzag {
    0%, 100% {
        background: linear-gradient(45deg, #fb923c, #f97316, #ea580c);
        transform: scale(1);
    }
    25% {
        background: linear-gradient(45deg, #f97316, #ea580c, #fb923c);
        transform: scale(1.05);
    }
    50% {
        background: linear-gradient(45deg, #ea580c, #fb923c, #f97316);
        transform: scale(1.1);
    }
    75% {
        background: linear-gradient(45deg, #fb923c, #f97316, #ea580c);
        transform: scale(1.05);
    }
}

/* Touch Feedback */
.touch-ripple {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.touch-ripple:active {
    transform: scale(0.95);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Card Entrance Animation */
.card-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.card-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Blur Background Enhancements */
.drink-card .blur-bg {
    filter: blur(20px) brightness(1.2) saturate(1.3);
    transform: scale(1.1);
    transition: all 0.5s ease;
}

.drink-card:hover .blur-bg {
    filter: blur(15px) brightness(1.3) saturate(1.4);
    transform: scale(1.15);
}

/* Content Area Enhancements */
.drink-card .content-area {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.drink-card:hover .content-area {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Mobile Optimizations */
@media (max-width: 768px) {
    .drink-card {
        transition: all 0.2s ease;
        /* Slightly slower animation on mobile for better performance */
        animation-duration: 4s;
    }
    
    .drink-card:active {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .drink-image:active {
        transform: scale(1.02);
        filter: brightness(1.05);
    }
    
    /* Reduce blur intensity on mobile for better performance */
    .drink-card .blur-bg {
        filter: blur(15px) brightness(1.1) saturate(1.2);
    }
    
    .drink-card:hover .blur-bg {
        filter: blur(12px) brightness(1.2) saturate(1.3);
    }
    
    /* Reduce animation intensity on mobile */
    .drink-image {
        animation-duration: 5s;
    }
    
    .drink-image img {
        animation-duration: 6s;
    }
}

/* Dramatic background transition effects */
.drink-card {
    transition: all 0.3s ease-in-out;
}

/* Enhanced card effects based on background */
.bg-gradient-to-br.from-orange-50.to-red-100 .drink-card {
    box-shadow: 0 4px 15px rgba(249, 115, 22, 0.1);
    border: 1px solid rgba(249, 115, 22, 0.1);
}

.bg-gradient-to-br.from-blue-50.to-cyan-100 .drink-card {
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.1);
}

/* Tab button enhanced effects */
.tab-button {
    position: relative;
    overflow: hidden;
}

.tab-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s;
}

.tab-button:hover::before {
    left: 100%;
}

/* Reduced motion preference */
@media (prefers-reduced-motion: reduce) {
    .drink-card,
    .drink-image,
    .drink-image img {
        animation: none;
    }
    
    .drink-card:hover {
        transform: translateY(-2px) scale(1.01);
    }
    
    .tab-button::before {
        display: none;
    }
}
</style>