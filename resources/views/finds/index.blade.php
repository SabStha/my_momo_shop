@extends('layouts.app')

@section('content')
<style>
/* Mobile-specific improvements */
@media (max-width: 640px) {
    .mobile-container {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    .mobile-text {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .mobile-flex {
        flex-direction: column;
    }
    
    .mobile-grid {
        grid-template-columns: 1fr;
    }
    
    .mobile-nav {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .mobile-nav::-webkit-scrollbar {
        display: none;
    }
}

/* Professional badge styling */
.professional-badge {
    background: linear-gradient(135deg, #6E0D25, #8B0D2F);
    color: white;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.premium-badge {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Improved card spacing and alignment */
.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* Better button alignment */
.add-button {
    min-width: 60px;
    text-align: center;
}

/* Touch Feedback Animations */
.touch-ripple {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.touch-ripple:active {
    transform: scale(0.95);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Touch ripple effect */
.touch-ripple::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.touch-ripple:active::after {
    width: 100px;
    height: 100px;
}

/* Scroll-Triggered Animations */
.scroll-fade-in {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.scroll-fade-in.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Staggered card animations */
.card-stagger {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.5s ease;
}

.card-stagger:nth-child(1) { transition-delay: 0.1s; }
.card-stagger:nth-child(2) { transition-delay: 0.2s; }
.card-stagger:nth-child(3) { transition-delay: 0.3s; }
.card-stagger:nth-child(4) { transition-delay: 0.4s; }

/* Slide up from bottom */
.slide-up {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.6s ease;
}

.slide-up.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Scale in animation */
.scale-in {
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.5s ease;
}

.scale-in.visible {
    opacity: 1;
    transform: scale(1);
}

/* Loading & State Animations */
.pulse-loading {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Skeleton loading */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}

@keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Mobile-Specific Interactions */
.swipe-left {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.swipe-right {
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

/* Pull to refresh animation */
.pull-refresh {
    transform: translateY(0);
    transition: transform 0.3s ease;
}

.pull-refresh.pulling {
    transform: translateY(60px);
}

/* Tab Switching Animations */
.tab-content {
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s ease;
}

.tab-content.active {
    opacity: 1;
    transform: translateX(0);
}

/* Tab indicator animation */
.tab-indicator {
    transition: transform 0.3s ease, width 0.3s ease;
}

/* Enhanced Product Card Animations */
.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.product-card:active {
    transform: scale(0.98);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Professional Image Animations */
.product-image {
    transition: all 0.3s ease;
    overflow: hidden;
}

.product-image:hover {
    transform: scale(1.02);
    filter: brightness(1.05);
}

.product-image img {
    transition: all 0.3s ease;
    transform-origin: center;
}

.product-image:hover img {
    transform: scale(1.05);
    filter: brightness(1.08);
}

/* Clean image effects */
.image-clean {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.image-clean:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Professional image styling */
.image-professional {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.image-professional:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.image-professional img {
    transition: all 0.3s ease;
}

.image-professional:hover img {
    transform: scale(1.05);
    filter: brightness(1.08);
}

/* Mobile-optimized card entrance */
.card-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.card-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Clean, professional badge styling */
.badge-clean {
    background: linear-gradient(135deg, #6E0D25, #8B0D2F);
    color: white;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.badge-clean:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Enhanced product card styling */
.product-card {
    position: relative;
    overflow: hidden;
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.6s ease;
    z-index: 1;
}

.product-card:hover::before {
    left: 100%;
}

/* Professional urgency badge */
.urgency-badge {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: white;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.urgency-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Emotional hook styling */
.emotional-hook {
    background: #FFF6DA;
    border-left: 4px solid #f59e0b;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: 11px;
    line-height: 1.3;
    color: #92400e;
    font-style: italic;
    min-height: 32px;
    display: flex;
    align-items: center;
}

/* Social proof styling */
.social-proof {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    color: #6b7280;
    margin-top: 4px;
}

.social-proof .heart {
    color: #ef4444;
    animation: heart-beat 1.5s ease-in-out infinite;
}

@keyframes heart-beat {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Wishlist button styling */
.wishlist-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
}

.wishlist-btn:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}

.wishlist-btn.saved {
    color: #ef4444;
}

.wishlist-btn:not(.saved) {
    color: #6b7280;
}

/* Progress indicator */
.progress-indicator {
    background: linear-gradient(90deg, #ef4444, #f97316);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: bold;
    margin-bottom: 8px;
    text-align: center;
}

/* Earn it tooltip */
.earn-tooltip {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 11px;
    color: #374151;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 20;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.earn-tooltip.show {
    opacity: 1;
    visibility: visible;
}
</style>

<div x-data="findsData()" class="bg-[#F4E9E1] min-h-screen mobile-container">

    <!-- HEADER WITH CHARITY MESSAGE -->
    <div class="relative z-20 pt-4 sm:pt-6 pb-1 sm:pb-2 px-2">
        <div class="w-full sm:w-max mx-auto bg-white rounded-xl shadow px-4 sm:px-6 py-3 text-center mobile-text">
            <h1 class="text-base sm:text-lg md:text-xl font-bold text-[#6E0D25] mb-2">{{ $config['finds_title'] }}</h1>
            <p class="text-xs sm:text-sm text-[#6E0D25] font-medium">{{ $config['finds_subtitle'] }}</p>
        </div>
    </div>

    <!-- CATEGORY SELECTION SECTION -->
    <div class="bg-white/95 backdrop-blur-sm border-b border-[#6E0D25]/20 py-1 sm:py-2">
        <div class="container mx-auto px-1 sm:px-2">
            <div class="text-center mb-1 sm:mb-2">
                <h3 class="text-xs sm:text-sm font-semibold text-[#6E0D25]">Select Category: <span class="text-[#6E0D25] font-bold" x-text="activeTab.toUpperCase()"></span></h3>
            </div>
            <div class="grid grid-cols-6 gap-2 sm:gap-3 md:gap-4">
                @foreach($categories as $category)
                <button @click="activeTab = '{{ $category['key'] }}'" 
                        :class="{ 'bg-gradient-to-br from-[#6E0D25] to-[#8B0D2F] text-white shadow-lg border border-[#6E0D25] transform scale-105': activeTab === '{{ $category['key'] }}', 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-300 hover:border-[#6E0D25]/30 shadow-sm hover:shadow-md': activeTab !== '{{ $category['key'] }}' }" 
                        class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 md:py-4 rounded-xl font-medium text-xs sm:text-sm transition-all duration-300 min-h-[40px] sm:min-h-[44px] md:min-h-[48px] hover:scale-102 active:scale-98 flex flex-col items-center justify-center gap-1">
                    <div class="text-sm sm:text-base">{!! $category['icon'] !!}</div>
                    <span class="text-[10px] sm:text-xs font-semibold">{{ $category['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CATEGORY SELECTION FEEDBACK -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-1 mx-2 mb-1 rounded">
        <p class="text-xs sm:text-sm text-blue-800">
            <strong>Selected Category:</strong> <span x-text="activeTab.toUpperCase()"></span>
            <span x-show="loading" class="text-blue-600">(Loading...)</span>
            <span x-show="!loading" class="text-blue-600">(Showing filtered results)</span>
        </p>
    </div>

    <!-- LOADING INDICATOR -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#6E0D25] mx-auto"></div>
            <p class="text-sm text-gray-600 mt-2">Loading merchandise...</p>
        </div>
    </div>



    <!-- TAB CONTENT AREA -->
    <div class="px-0 pb-2 space-y-2 sm:space-y-4">
        <!-- BUYABLE ITEMS SECTION -->
        <div x-show="activeTab === 'buyable'" x-transition>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="item in [...merchandise.tshirts, ...merchandise.accessories, ...merchandise.toys, ...merchandise.limited].filter(item => item.purchasable)" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                        <!-- Product Image with Overlay -->
                        <div class="relative h-64 sm:h-72 overflow-hidden combo-image image-professional">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                            
                            <!-- Product Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3">
                                <h3 class="font-bold text-white text-sm mb-1" x-text="item.name"></h3>
                                <div class="text-white/90 text-xs" x-text="item.description"></div>
                            </div>
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" @click="toggleWishlist(item.id)" :class="{ 'saved': isWishlisted(item.id) }">
                                ❤️
                </button>
                            
                            <template x-if="item.badge">
                                <div class="absolute top-3 right-12 px-2 py-1 rounded-full text-xs font-bold text-white badge-clean">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                            <!-- Professional urgency badges -->
                            <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white urgency-badge">
                                {{ $config['urgency_badge_text'] }}
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Price -->
                            <div class="text-lg font-bold text-[#6E0D25] mb-3" x-text="item.formatted_price"></div>
                            
                            <!-- Add to Cart Button -->
                            <button data-add-to-cart
                                    :data-product-id="item.id"
                                    :data-product-name="item.name"
                                    :data-product-price="item.price"
                                    :data-product-image="item.image_url"
                                    class="w-full bg-[#6E0D25] text-white py-3 rounded-lg text-sm hover:bg-[#B91C1C] transition-colors touch-ripple font-medium shadow-md">
                                {{ $config['add_to_cart_text'] }}
                </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- UNLOCKABLE ITEMS SECTION -->
        <div x-show="activeTab === 'unlockable'" x-transition>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="item in [...merchandise.tshirts, ...merchandise.accessories, ...merchandise.toys, ...merchandise.limited].filter(item => !item.purchasable)" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                        <!-- Product Image with Overlay -->
                        <div class="relative h-64 sm:h-72 overflow-hidden combo-image image-professional">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                            
                            <!-- Product Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3">
                                <h3 class="font-bold text-white text-sm mb-1" x-text="item.name"></h3>
                                <div class="text-white/90 text-xs" x-text="item.description"></div>
                            </div>
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" @click="toggleWishlist(item.id)" :class="{ 'saved': isWishlisted(item.id) }">
                                ❤️
                            </button>
                            
                            <template x-if="item.badge">
                                <div class="absolute top-3 right-12 px-2 py-1 rounded-full text-xs font-bold text-white badge-clean">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                            <!-- Professional earn badges -->
                            <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white premium-badge cursor-pointer earn-badge" @click="showEarnTooltip($event, item.id)">
                                {{ $config['earn_badge_text'] }}
                                <div class="earn-tooltip" :id="'tooltip-' + item.id">
                                    {{ $config['earn_tooltip_message'] }}
            </div>
        </div>
    </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Price -->
                            <div class="text-lg font-bold text-[#6E0D25] mb-3" x-text="item.formatted_price"></div>
                            
                            <!-- Unlockable Badge -->
                            <div class="w-full text-center text-gray-500 text-xs bg-gray-100 py-3 rounded-lg">
                                {{ $config['unlockable_text'] }}
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- T-SHIRTS SECTION -->
        <div x-show="activeTab === 'tshirts'" x-transition>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="item in merchandise.tshirts" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                        <!-- Product Image with Overlay -->
                        <div class="relative h-64 sm:h-72 overflow-hidden combo-image image-professional">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                            
                            <!-- Product Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3">
                                <h3 class="font-bold text-white text-sm mb-1" x-text="item.name"></h3>
                                <div class="text-white/90 text-xs" x-text="item.description"></div>
                            </div>
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" @click="toggleWishlist(item.id)" :class="{ 'saved': isWishlisted(item.id) }">
                                ❤️
                            </button>
                            
                            <template x-if="item.badge">
                                <div class="absolute top-3 right-12 px-2 py-1 rounded-full text-xs font-bold text-white badge-clean" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                            <!-- Urgency/FOMO badges -->
                            <template x-if="item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white urgency-badge">
                                    {{ $config['urgency_badge_text'] }}
                                </div>
                            </template>
                            <template x-if="!item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white premium-badge">
                                    {{ $config['earn_badge_text'] }}
                                </div>
                            </template>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Price -->
                            <div class="text-lg font-bold text-[#6E0D25] mb-3" x-text="item.formatted_price"></div>
                            
                            <!-- Add to Cart Button or Unlockable Badge -->
                                <template x-if="item.purchasable">
                                    <button data-add-to-cart
                                            :data-product-id="item.id"
                                            :data-product-name="item.name"
                                            :data-product-price="item.price"
                                            :data-product-image="item.image_url"
                                        class="w-full bg-[#6E0D25] text-white py-3 rounded-lg text-sm hover:bg-[#B91C1C] transition-colors touch-ripple font-medium shadow-md">
                                    {{ $config['add_to_cart_text'] }}
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                <div class="w-full text-center text-gray-500 text-xs bg-gray-100 py-3 rounded-lg">
                                    {{ $config['unlockable_text'] }}
                                </div>
                                </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ACCESSORIES SECTION -->
        <div x-show="activeTab === 'accessories'" x-transition>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="item in merchandise.accessories" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                        <!-- Product Image with Overlay -->
                        <div class="relative h-64 sm:h-72 overflow-hidden combo-image image-professional">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                            
                            <!-- Product Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3">
                                <h3 class="font-bold text-white text-sm mb-1" x-text="item.name"></h3>
                                <div class="text-white/90 text-xs" x-text="item.description"></div>
                            </div>
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" @click="toggleWishlist(item.id)" :class="{ 'saved': isWishlisted(item.id) }">
                                ❤️
                            </button>
                            
                            <template x-if="item.badge">
                                <div class="absolute top-3 right-12 px-2 py-1 rounded-full text-xs font-bold text-white badge-clean" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                            <!-- Urgency/FOMO badges -->
                            <template x-if="item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white urgency-badge">
                                    {{ $config['urgency_badge_text'] }}
                                </div>
                            </template>
                            <template x-if="!item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white premium-badge">
                                    {{ $config['earn_badge_text'] }}
                                </div>
                            </template>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Price -->
                            <div class="text-lg font-bold text-[#6E0D25] mb-3" x-text="item.formatted_price"></div>
                            
                            <!-- Add to Cart Button or Unlockable Badge -->
                                <template x-if="item.purchasable">
                                    <button data-add-to-cart
                                            :data-product-id="item.id"
                                            :data-product-name="item.name"
                                            :data-product-price="item.price"
                                            :data-product-image="item.image_url"
                                        class="w-full bg-[#6E0D25] text-white py-3 rounded-lg text-sm hover:bg-[#B91C1C] transition-colors touch-ripple font-medium shadow-md">
                                    {{ $config['add_to_cart_text'] }}
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                <div class="w-full text-center text-gray-500 text-xs bg-gray-100 py-3 rounded-lg">
                                    {{ $config['unlockable_text'] }}
                                </div>
                                </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- TOYS SECTION -->
        <div x-show="activeTab === 'toys'" x-transition>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="item in merchandise.toys" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                        <!-- Product Image with Overlay -->
                        <div class="relative h-64 sm:h-72 overflow-hidden combo-image image-professional">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                            
                            <!-- Product Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3">
                                <h3 class="font-bold text-white text-sm mb-1" x-text="item.name"></h3>
                                <div class="text-white/90 text-xs" x-text="item.description"></div>
                            </div>
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" @click="toggleWishlist(item.id)" :class="{ 'saved': isWishlisted(item.id) }">
                                ❤️
                            </button>
                            
                            <template x-if="item.badge">
                                <div class="absolute top-3 right-12 px-2 py-1 rounded-full text-xs font-bold text-white badge-clean" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                            <!-- Urgency/FOMO badges -->
                            <template x-if="item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white urgency-badge">
                                    {{ $config['urgency_badge_text'] }}
                                </div>
                            </template>
                            <template x-if="!item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white premium-badge">
                                    {{ $config['earn_badge_text'] }}
                                </div>
                            </template>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Price -->
                            <div class="text-lg font-bold text-[#6E0D25] mb-3" x-text="item.formatted_price"></div>
                            
                            <!-- Add to Cart Button or Unlockable Badge -->
                                <template x-if="item.purchasable">
                                    <button data-add-to-cart
                                            :data-product-id="item.id"
                                            :data-product-name="item.name"
                                            :data-product-price="item.price"
                                            :data-product-image="item.image_url"
                                        class="w-full bg-[#6E0D25] text-white py-3 rounded-lg text-sm hover:bg-[#B91C1C] transition-colors touch-ripple font-medium shadow-md">
                                    {{ $config['add_to_cart_text'] }}
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                <div class="w-full text-center text-gray-500 text-xs bg-gray-100 py-3 rounded-lg">
                                    {{ $config['unlockable_text'] }}
                                </div>
                                </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- LIMITED EDITION SECTION -->
        <div x-show="activeTab === 'limited'" x-transition>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="item in merchandise.limited" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 combo-card touch-ripple card-animate">
                        <!-- Product Image with Overlay -->
                        <div class="relative h-64 sm:h-72 overflow-hidden combo-image image-professional">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                            
                            <!-- Product Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3">
                                <h3 class="font-bold text-white text-sm mb-1" x-text="item.name"></h3>
                                <div class="text-white/90 text-xs" x-text="item.description"></div>
                            </div>
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" @click="toggleWishlist(item.id)" :class="{ 'saved': isWishlisted(item.id) }">
                                ❤️
                            </button>
                            
                            <template x-if="item.badge">
                                <div class="absolute top-3 right-12 px-2 py-1 rounded-full text-xs font-bold text-white badge-clean" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                            <!-- Urgency/FOMO badges -->
                            <template x-if="item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white urgency-badge">
                                    {{ $config['urgency_badge_text'] }}
                                </div>
                            </template>
                            <template x-if="!item.purchasable && item.category !== 'limited'">
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white premium-badge">
                                    {{ $config['earn_badge_text'] }}
                                </div>
                            </template>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Price -->
                            <div class="text-lg font-bold text-[#6E0D25] mb-3" x-text="item.formatted_price"></div>
                            
                            <!-- Add to Cart Button or Unlockable Badge -->
                                <template x-if="item.purchasable">
                                    <button data-add-to-cart
                                            :data-product-id="item.id"
                                            :data-product-name="item.name"
                                            :data-product-price="item.price"
                                            :data-product-image="item.image_url"
                                        class="w-full bg-[#6E0D25] text-white py-3 rounded-lg text-sm hover:bg-[#B91C1C] transition-colors touch-ripple font-medium shadow-md">
                                    {{ $config['add_to_cart_text'] }}
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                <div class="w-full text-center text-gray-500 text-xs bg-gray-100 py-3 rounded-lg">
                                    {{ $config['unlockable_text'] }}
                                </div>
                                </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- BULK PACKAGES SECTION -->
        <div x-show="activeTab === 'bulk'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bulkPackages as $package)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                    <div class="relative">
                        <div class="w-full h-48 bg-gradient-to-br from-[#6E0D25] to-[#8B1A3A] flex items-center justify-center">
                            <div class="text-6xl">{{ $package->emoji }}</div>
                        </div>
                        @if($package->badge)
                            <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold text-white" style="background-color: {{ $package->badge_color }}">
                                {{ $package->badge }}
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ $package->description }}</p>
                        <div class="space-y-2 mb-3">
                            @foreach($package->items as $item)
                            <div class="flex justify-between text-xs">
                                <span class="truncate flex-1 mr-2">{{ $item['name'] }}</span>
                                <span class="font-semibold flex-shrink-0 {{ $item['price'] < 0 ? 'text-green-600' : '' }}">Rs. {{ $item['price'] }}</span>
                            </div>
                            @endforeach
                            <hr class="my-2">
                            <div class="flex justify-between font-bold text-base">
                                <span>Total</span>
                                <span class="text-[#6E0D25]">Rs. {{ $package->total_price }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <button data-add-to-cart
                                    data-product-id="bulk-{{ $package->id }}"
                                    data-product-name="{{ $package->name }}"
                                    data-product-price="{{ $package->total_price }}"
                                    class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#5A0A1F] transition-colors duration-200">
                                Order Now
                            </button>
                            <button class="border border-[#6E0D25] text-[#6E0D25] px-4 py-2 rounded-lg hover:bg-[#6E0D25] hover:text-white transition-colors duration-200">
                                Customize
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Include Cart Modal -->
@include('components.cart-modal')

<!-- AOS (optional animations) -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

<script>
function findsData() {
    return {
        merchandise: @json($merchandise),
        config: @json($config),
        loading: false,
        activeTab: 'buyable',
        wishlist: [],
        
        toggleWishlist(itemId) {
            const index = this.wishlist.indexOf(itemId);
            if (index > -1) {
                this.wishlist.splice(index, 1);
                this.showToast('Removed from wishlist');
            } else {
                this.wishlist.push(itemId);
                this.showToast('Added to wishlist');
            }
        },
        
        isWishlisted(itemId) {
            return this.wishlist.includes(itemId);
        },
        
        showEarnTooltip(event, itemId) {
            // Hide all tooltips first
            document.querySelectorAll('.earn-tooltip').forEach(tooltip => {
                tooltip.classList.remove('show');
            });
            
            // Show this tooltip
            const tooltip = document.getElementById('tooltip-' + itemId);
            if (tooltip) {
                tooltip.classList.add('show');
                
                // Hide after 3 seconds
                setTimeout(() => {
                    tooltip.classList.remove('show');
                }, 3000);
            }
        },
        
        showToast(message) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 2000);
        }
    }
}

// Scroll-triggered animations
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
    const animatedElements = document.querySelectorAll('.card-animate, .scroll-fade-in, .slide-up, .scale-in');
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

    // Tab switching animations
    const tabButtons = document.querySelectorAll('[x-on\\:click*="activeTab"]');
    const tabContents = document.querySelectorAll('[x-show*="activeTab"]');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Add active class to clicked button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Animate tab content
            tabContents.forEach(content => {
                content.classList.remove('active');
                setTimeout(() => {
                    content.classList.add('active');
                }, 100);
            });
        });
    });

    // Loading states
    const loadingStates = document.querySelectorAll('.loading');
    loadingStates.forEach(element => {
        element.classList.add('pulse-loading');
    });

    // Pull to refresh simulation
    let startY = 0;
    let currentY = 0;
    const pullThreshold = 60;
    
    document.addEventListener('touchstart', function(e) {
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchmove', function(e) {
        currentY = e.touches[0].clientY;
        const pullDistance = currentY - startY;
        
        if (pullDistance > 0 && window.scrollY === 0) {
            const pullElement = document.querySelector('.pull-refresh');
            if (pullElement && pullDistance > pullThreshold) {
                pullElement.classList.add('pulling');
            }
        }
    });
    
    document.addEventListener('touchend', function() {
        const pullElement = document.querySelector('.pull-refresh');
        if (pullElement) {
            pullElement.classList.remove('pulling');
        }
    });

    // Enhanced image animations for mobile
    const productImages = document.querySelectorAll('.product-image');
    productImages.forEach(image => {
        // Add shimmer effect on touch
        image.addEventListener('touchstart', function() {
            this.classList.add('image-shimmer');
        });
        
        image.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('image-shimmer');
            }, 2000);
        });
        
        // Add floating animation for featured products
        const badge = this.querySelector('.badge-clean');
        if (badge && (badge.textContent.includes('Featured') || badge.textContent.includes('New'))) {
            this.classList.add('image-float');
        }
    });

    // Image hover effects for desktop
    if (window.innerWidth > 768) {
        productImages.forEach(image => {
            image.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.filter = 'brightness(1.1) contrast(1.05)';
            });
            
            image.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.filter = '';
            });
        });
    }

    // Add urgency effects to buyable items
    const urgencyBadges = document.querySelectorAll('.urgency-badge');
    urgencyBadges.forEach(badge => {
        // Add pulsing effect
        badge.style.animation = 'urgency-pulse 1.5s ease-in-out infinite';
        
        // Add click to show countdown
        badge.addEventListener('click', function() {
            const countdown = document.createElement('div');
            countdown.className = 'absolute top-0 left-0 w-full h-full bg-red-600 text-white text-xs flex items-center justify-center rounded-full';
            countdown.textContent = '🔥 Limited!';
            this.appendChild(countdown);
            
            setTimeout(() => {
                countdown.remove();
            }, 2000);
        });
    });

    // Add emotional hooks to unlockable items
    const unlockableItems = document.querySelectorAll('.product-card');
    unlockableItems.forEach(card => {
        const earnBadge = card.querySelector('.premium-badge');
        if (earnBadge) {
            earnBadge.addEventListener('click', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute top-8 left-2 bg-white border border-gray-200 rounded-lg p-2 text-xs shadow-lg z-10';
                tooltip.innerHTML = '🎁 Complete any combo meal to unlock this exclusive gift!';
                this.appendChild(tooltip);
                
                setTimeout(() => {
                    tooltip.remove();
                }, 3000);
            });
        }
    });
});
</script>
@endsection 