<div class="bg-[#F9F6F3] min-h-screen overflow-x-hidden">
    <div class="w-full px-4 py-4 space-y-8 overflow-x-hidden">
        
        <!-- HEADER -->
        <div class="text-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#6E0D25] mb-2">🍽️ Food Menu</h1>
            <p class="text-sm sm:text-base text-gray-600">Delicious food items - from traditional momos to modern favorites</p>
        </div>

        @if($buffItems && $buffItems->count() > 0)
        <!-- BUFF ITEMS SECTION -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="text-2xl">🍖</div>
                <h2 class="text-xl font-bold text-[#6E0D25]">Buff Items</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($buffItems as $product)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate">
                    <div class="relative food-image">
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 sm:h-56 object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <div class="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <h3 class="font-bold text-lg text-white text-left">{{ $product->name }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
                        <div class="flex justify-between items-center">
                            <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                            <button data-add-to-cart
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ asset('storage/' . $product->image) }}"
                                    class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                <span class="text-base">＋</span>
                                <span>Add</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($chickenItems && $chickenItems->count() > 0)
        <!-- CHICKEN ITEMS SECTION -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="text-2xl">🍗</div>
                <h2 class="text-xl font-bold text-[#6E0D25]">Chicken Items</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($chickenItems as $product)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate">
                    <div class="relative food-image">
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 sm:h-56 object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <div class="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <h3 class="font-bold text-lg text-white text-left">{{ $product->name }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
                        <div class="flex justify-between items-center">
                            <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                            <button data-add-to-cart
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ asset('storage/' . $product->image) }}"
                                    class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                <span class="text-base">＋</span>
                                <span>Add</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($mainItems && $mainItems->count() > 0)
        <!-- MAINS SECTION -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="text-2xl">🍱</div>
                <h2 class="text-xl font-bold text-[#6E0D25]">Mains</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($mainItems as $product)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate">
                    <div class="relative food-image">
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 sm:h-56 object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <div class="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <h3 class="font-bold text-lg text-white text-left">{{ $product->name }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
                        <div class="flex justify-between items-center">
                            <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                            <button data-add-to-cart
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ asset('storage/' . $product->image) }}"
                                    class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                <span class="text-base">＋</span>
                                <span>Add</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($sideSnacks && $sideSnacks->count() > 0)
        <!-- SIDE SNACKS SECTION -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="text-2xl">🍟</div>
                <h2 class="text-xl font-bold text-[#6E0D25]">Side Snacks</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($sideSnacks as $product)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate">
                    <div class="relative food-image">
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 sm:h-56 object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <div class="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <h3 class="font-bold text-lg text-white text-left">{{ $product->name }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
                        <div class="flex justify-between items-center">
                            <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                            <button data-add-to-cart
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ asset('storage/' . $product->image) }}"
                                    class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                <span class="text-base">＋</span>
                                <span>Add</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($foods && $foods->count() > 0 && !$buffItems && !$chickenItems && !$mainItems && !$sideSnacks)
        <!-- GENERAL FOODS SECTION -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="text-2xl">🍽️</div>
                <h2 class="text-xl font-bold text-[#6E0D25]">Food Items</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($foods as $product)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 food-card touch-ripple card-animate">
                    <div class="relative food-image">
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 sm:h-56 object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                        <div class="absolute bottom-3 left-3">
                            <div class="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <h3 class="font-bold text-lg text-white text-left">{{ $product->name }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
                        <div class="flex justify-between items-center">
                            <div class="font-bold text-2xl text-[#8B1A3A]">{{ formatPrice($product->price, 0) }}</div>
                            <button data-add-to-cart
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ asset('storage/' . $product->image) }}"
                                    class="bg-[#A43E2D] text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#8B1A3A] hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-md touch-ripple">
                                <span class="text-base">＋</span>
                                <span>Add</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Food Card Animations */
.food-card {
    transition: all 0.3s ease;
}

.food-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.food-card:active {
    transform: scale(0.98);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Food Image Enhancements */
.food-image {
    transition: all 0.5s ease;
    overflow: hidden;
    position: relative;
}

.food-image:hover {
    transform: scale(1.03);
    filter: brightness(1.1) contrast(1.05);
}

.food-image img {
    transition: all 0.5s ease;
    transform-origin: center;
}

.food-image:hover img {
    transform: scale(1.08);
    filter: brightness(1.15) contrast(1.1) saturate(1.1);
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

/* Mobile Optimizations */
@media (max-width: 768px) {
    .food-card {
        transition: all 0.2s ease;
    }
    
    .food-card:active {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .food-image:active {
        transform: scale(1.02);
        filter: brightness(1.05);
    }
}
</style>

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

    // Food card entrance animations
    const foodCards = document.querySelectorAll('.food-card');
    foodCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('card-animate');
    });
});
</script> 