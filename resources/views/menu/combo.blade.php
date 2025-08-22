<div class="bg-[#F9F6F3] min-h-screen overflow-x-hidden">
    <div class="w-full px-4 py-4 space-y-8 overflow-x-hidden">
        
        <!-- HEADER -->
        <div class="text-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-[#6E0D25] mb-2">🍽️ Combo Menu</h1>
            <p class="text-sm sm:text-base text-gray-600">Perfect meal combinations - great value for money</p>
        </div>

        @if($combos && $combos->count() > 0)
        <!-- COMBOS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($combos as $product)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                <div class="relative combo-image">
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
        @else
        <!-- NO COMBOS MESSAGE -->
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🍽️</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Combos Available</h3>
            <p class="text-gray-500">Check back later for great meal combinations!</p>
        </div>
        @endif
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

    // Combo card entrance animations
    const comboCards = document.querySelectorAll('.combo-card');
    comboCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.2}s`;
        card.classList.add('card-animate');
    });
});
</script>

<style>
/* Combo Card Animations */
.combo-card {
    transition: all 0.3s ease;
}

.combo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.combo-card:active {
    transform: scale(0.98);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Combo Image Enhancements */
.combo-image {
    transition: all 0.5s ease;
    overflow: hidden;
    position: relative;
}

.combo-image:hover {
    transform: scale(1.03);
    filter: brightness(1.1) contrast(1.05);
}

.combo-image img {
    transition: all 0.5s ease;
    transform-origin: center;
}

.combo-image:hover img {
    transform: scale(1.08);
    filter: brightness(1.15) contrast(1.1) saturate(1.1);
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

/* Mobile Optimizations */
@media (max-width: 768px) {
    .combo-card {
        transition: all 0.2s ease;
    }
    
    .combo-card:active {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .combo-image:active {
        transform: scale(1.02);
        filter: brightness(1.05);
    }
}
</style>
