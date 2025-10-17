<!-- Featured Products Section -->
<section id="featured-products" class="pt-2 pb-4 sm:pt-3 sm:pb-6 px-0 sm:px-4 bg-white" 
         x-data="{ 
             showIngredientsModal: false,
             selectedProduct: null
         }">
    <style>
        /* Hide elements with x-cloak until Alpine.js initializes */
        [x-cloak] {
            display: none !important;
        }
        
        /* Eye-catching zoom animation for product images */
        @keyframes imageZoom {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        /* Apply zoom animation to product images with staggered delays */
        .featured-product-img {
            animation: imageZoom 4s ease-in-out infinite;
        }
        
    </style>
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <!-- Section Header -->
            <div class="text-center mb-4 sm:mb-6">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mb-2 hover:scale-105 transition-transform duration-300 cursor-pointer bg-[#152039] border border-[#1a2749] px-4 py-2 rounded-full shadow-lg inline-flex items-center gap-2">
                    <span class="bg-white text-[#152039] w-6 h-6 rounded-full flex items-center justify-center text-sm">🌟</span>
                    Featured Products
                </h2>
                <p class="text-xs sm:text-sm text-gray-800 hover:text-amk-brown-1 hover:scale-105 transition-all duration-300 cursor-default">Discover our handpicked premium products.</p>
            </div>
            
            <!-- Featured Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @forelse($featuredProducts ?? [] as $index => $product)
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 overflow-hidden flex flex-col h-full group cursor-pointer">
                    <!-- Product Image Section -->
                    <div class="relative flex-1 overflow-hidden">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover transition-all duration-300 group-hover:scale-105 featured-product-img"
                             style="animation-delay: {{ $index * 0.5 }}s;">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent transition-opacity duration-500 group-hover:from-black/30"></div>
                        
                        @if($product->is_featured)
                        <div class="absolute top-2 right-2 bg-[#152039] text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg">
                            ⭐ Featured
                        </div>
                        @endif
                        
                        
                        <!-- Product Info Overlay - Inside Image -->
                        <div class="absolute bottom-2 left-2 right-2 sm:bottom-3 sm:left-3 sm:right-auto">
                            <div class="bg-black/50 backdrop-blur-sm rounded-md sm:rounded-lg px-2 py-2 sm:px-3 sm:py-3 inline-block transform transition-all duration-500 group-hover:scale-105 group-hover:shadow-lg max-w-full">
                                <h3 class="text-xs sm:text-base lg:text-lg font-semibold text-white mb-1 sm:mb-2 transform transition-all duration-300 group-hover:text-yellow-200 line-clamp-1 sm:line-clamp-none">{{ $product->name }}</h3>
                                <p class="text-white text-[10px] sm:text-xs lg:text-sm mb-1 sm:mb-2 line-clamp-1 sm:line-clamp-2 transform transition-all duration-300 group-hover:text-gray-100">{{ $product->description }}</p>
                                
                                <!-- Reviews Section - Hidden on mobile to save space -->
                                <div class="hidden sm:flex items-center gap-2">
                                    <div class="flex items-center">
                                        <span class="text-yellow-400 text-sm transform transition-all duration-300 group-hover:text-yellow-300 group-hover:scale-110">⭐⭐⭐⭐⭐</span>
                                        <span class="text-xs text-gray-300 ml-1">(4.8)</span>
                                    </div>
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-300">127 reviews</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Actions Section -->
                    <div class="p-2 sm:p-3 lg:p-4 flex-shrink-0">
                        <!-- Price and Actions -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0">
                            <div class="text-sm sm:text-lg lg:text-xl font-bold text-amk-brown-1">
                                Rs.{{ number_format($product->price, 2) }}
                            </div>
                            <div class="flex gap-1 sm:gap-2 w-full sm:w-auto">
                                <!-- Info Button - Now visible on all screen sizes -->
                                <button @click="showIngredientsModal = true; selectedProduct = {
                                    name: '{{ $product->name }}',
                                    ingredients: '{{ $product->ingredients ?? 'Fresh ingredients prepared daily' }}',
                                    allergens: '{{ $product->allergens ?? 'No allergens' }}',
                                    calories: '{{ $product->calories ?? 'N/A' }}',
                                    preparation_time: '{{ $product->preparation_time ?? '10-15 minutes' }}',
                                    spice_level: '{{ $product->spice_level ?? 'Medium' }}',
                                    serving_size: '{{ $product->serving_size ?? '1 serving' }}',
                                    is_vegetarian: {{ $product->is_vegetarian ? 'true' : 'false' }},
                                    is_vegan: {{ $product->is_vegan ? 'true' : 'false' }},
                                    is_gluten_free: {{ $product->is_gluten_free ? 'true' : 'false' }},
                                    image: '{{ asset('storage/' . $product->image) }}'
                                };"
                                        class="flex bg-blue-500 border border-blue-600 text-white text-xs font-medium px-2 lg:px-3 py-1.5 lg:py-2 rounded-lg items-center gap-1.5 hover:bg-blue-600 hover:border-blue-700 hover:scale-105 active:scale-95 transition-all duration-200 transform shadow-sm hover:shadow-md">
                                    <svg class="w-3 h-3 lg:w-4 lg:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Info</span>
                                </button>
                                
                                <!-- Add to Cart Button -->
                                <button data-add-to-cart
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->price }}"
                                        data-product-image="{{ asset('storage/' . $product->image) }}"
                                        class="add-to-cart-btn bg-red-500 text-white px-2 sm:px-3 lg:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-red-600 hover:shadow-lg hover:scale-105 transition-all duration-300 flex items-center gap-1 sm:gap-2 min-h-[36px] sm:min-h-[40px] flex-1 sm:flex-none justify-center animate-pulse hover:animate-none relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 sm:w-4 sm:h-4 relative z-10 transform transition-transform duration-300 hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 8h14l1 12a2 2 0 01-2 2H6a2 2 0 01-2-2l1-12z"/>
                                    </svg>
                                    <span class="text-[10px] sm:text-xs lg:text-sm relative z-10 font-semibold">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <!-- Fallback Content -->
                <div class="col-span-full text-center py-8 sm:py-12">
                    <div class="text-4xl sm:text-6xl mb-4">🥟</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">No Featured Products Yet</h3>
                    <p class="text-sm sm:text-base text-gray-500">Check back soon for our featured momo selections!</p>
                </div>
                @endforelse
            </div>

            <!-- View All Button -->
            <div class="text-center mt-6 sm:mt-8">
                <a href="{{ route('menu') }}" 
                   class="inline-flex items-center gap-2 bg-[#FFF8E6] text-[#5A2E22] border border-[#E9DFCA] px-4 sm:px-6 py-2 sm:py-3 rounded-full font-semibold hover:bg-[#E36414] hover:text-white transition-all duration-300 text-sm sm:text-base min-h-[44px]">
                    View All Products
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Ingredients Modal for Featured Products -->
    <div x-show="showIngredientsModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop">
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-all duration-300" 
             @click="showIngredientsModal = false"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-2 sm:mx-4 max-h-[85vh] overflow-y-auto modal-content"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 transform scale-95 translate-y-4">
            
            <!-- Close Button -->
            <button @click="showIngredientsModal = false" 
                    class="absolute top-4 right-4 z-10 bg-white/80 hover:bg-white text-gray-600 hover:text-gray-800 rounded-full p-2 shadow-lg close-button">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Product Image -->
            <div class="relative h-32 sm:h-36 overflow-hidden rounded-t-2xl">
                <img :src="selectedProduct?.image" 
                     :alt="selectedProduct?.name"
                     class="w-full h-full object-cover modal-image">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-3 left-3 text-white">
                    <h3 class="text-lg sm:text-xl font-bold" x-text="selectedProduct?.name"></h3>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                <!-- Ingredients Section -->
                <div class="info-section bg-gradient-to-r from-green-50 to-emerald-50 p-3 rounded-xl border-l-4 border-green-500">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">🥘</span>
                        <h4 class="text-base font-semibold text-green-800">Ingredients</h4>
                    </div>
                    <p class="text-green-700 text-sm" x-text="selectedProduct?.ingredients"></p>
                </div>
                
                <!-- Allergens Section -->
                <div class="info-section bg-gradient-to-r from-amk-blush/20 to-amk-sand/20 p-3 rounded-xl border-l-4 border-amk-brown-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">⚠️</span>
                        <h4 class="text-base font-semibold text-amk-brown-1">Allergen Information</h4>
                    </div>
                    <p class="text-amk-olive text-sm" x-text="selectedProduct?.allergens"></p>
                </div>
                
                <!-- Nutritional Information -->
                <div class="info-section bg-gradient-to-r from-orange-50 to-yellow-50 p-3 rounded-xl border-l-4 border-orange-500">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">🔥</span>
                        <h4 class="text-base font-semibold text-orange-800">Nutritional Information</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-orange-700 text-sm">
                        <div>
                            <span class="font-medium">Calories:</span>
                            <span x-text="selectedProduct?.calories"></span>
                        </div>
                        <div>
                            <span class="font-medium">Serving Size:</span>
                            <span x-text="selectedProduct?.serving_size"></span>
                        </div>
                        <div>
                            <span class="font-medium">Prep Time:</span>
                            <span x-text="selectedProduct?.preparation_time"></span>
                        </div>
                        <div>
                            <span class="font-medium">Spice Level:</span>
                            <span x-text="selectedProduct?.spice_level"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Dietary Information -->
                <div class="info-section bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-xl border-l-4 border-blue-500">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">🌱</span>
                        <h4 class="text-base font-semibold text-blue-800">Dietary Information</h4>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        <span x-show="selectedProduct?.is_vegetarian" class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Vegetarian</span>
                        <span x-show="selectedProduct?.is_vegan" class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Vegan</span>
                        <span x-show="selectedProduct?.is_gluten_free" class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Gluten-Free</span>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gradient-to-r from-amk-brown-1 to-amk-brown-2 p-3 sm:p-4 rounded-b-2xl">
                <button @click="showIngredientsModal = false" 
                        class="w-full bg-white text-amk-brown-1 py-2 sm:py-3 px-4 sm:px-6 rounded-xl font-semibold hover:bg-gray-50 transition-colors duration-200 flex items-center justify-center gap-2 text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Close
                </button>
            </div>
        </div>
    </div>
</section> 