<!-- Featured Products Section -->
<div class="space-y-6">
    <!-- Featured Header -->
    <div class="text-center py-4">
        <h2 class="text-2xl font-bold text-[#6E0D25] mb-2">🌟 Featured Products</h2>
        <p class="text-gray-600 text-sm">Our handpicked favorites - the best of the best!</p>
    </div>

    <!-- Featured Products Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @php
            // Get featured products from database or use fallback
            $featuredProducts = \App\Models\Product::where('is_featured', true)
                ->where('is_active', true)
                ->take(8)
                ->get();
        @endphp

        @forelse($featuredProducts as $product)
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 overflow-hidden">
            <!-- Product Image -->
            <div class="relative h-32 overflow-hidden">
                <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}" 
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover">
                <div class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-bold">
                    ⭐ Featured
                </div>
            </div>

            <!-- Product Info -->
            <div class="p-3">
                <h3 class="font-semibold text-gray-800 text-sm mb-1">{{ $product->name }}</h3>
                <p class="text-gray-600 text-xs mb-2 line-clamp-2">{{ $product->description }}</p>
                
                <!-- Price and Actions -->
                <div class="flex justify-between items-center">
                    <div class="text-lg font-bold text-[#6E0D25]">
                        Rs.{{ number_format($product->price, 2) }}
                    </div>
                    <button data-add-to-cart
                            data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}"
                            data-product-price="{{ $product->price }}"
                            data-product-image="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.svg') }}"
                            class="bg-[#6E0D25] text-white px-3 py-1 rounded text-xs hover:bg-[#B91C1C] transition-colors">
                        Add
                    </button>
                </div>
            </div>
        </div>
        @empty
        <!-- Fallback Featured Products -->
        <div class="col-span-full text-center py-8">
            <div class="text-4xl mb-4">🥟</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Featured Products Coming Soon!</h3>
            <p class="text-gray-500">We're preparing some amazing featured items for you.</p>
        </div>
        @endforelse
    </div>

    <!-- Featured Categories -->
    <div class="mt-8">
        <h3 class="text-lg font-bold text-[#6E0D25] mb-4 text-center">Featured Categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                <div class="text-3xl mb-2">🔥</div>
                <h4 class="font-semibold text-red-800">Spicy Specials</h4>
                <p class="text-xs text-red-600">Hot & Spicy</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                <div class="text-3xl mb-2">🌱</div>
                <h4 class="font-semibold text-green-800">Vegetarian</h4>
                <p class="text-xs text-green-600">Fresh & Healthy</p>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                <div class="text-3xl mb-2">🥩</div>
                <h4 class="font-semibold text-blue-800">Meat Lovers</h4>
                <p class="text-xs text-blue-600">Premium Quality</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                <div class="text-3xl mb-2">🍯</div>
                <h4 class="font-semibold text-purple-800">Sweet Treats</h4>
                <p class="text-xs text-purple-600">Desserts & More</p>
            </div>
        </div>
    </div>

    <!-- Special Offers -->
    <div class="mt-8 bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] rounded-lg p-6 text-white">
        <h3 class="text-xl font-bold mb-2 text-center">🎉 Special Featured Offers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white/10 rounded-lg p-4">
                <h4 class="font-semibold mb-2">Featured Combo Deal</h4>
                <p class="text-sm opacity-90">Get 20% off when you order any featured combo!</p>
                <button class="mt-2 bg-white text-[#6E0D25] px-4 py-2 rounded text-sm font-semibold hover:bg-gray-100 transition-colors">
                    View Combos
                </button>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <h4 class="font-semibold mb-2">New Customer Special</h4>
                <p class="text-sm opacity-90">First-time customers get 15% off featured items!</p>
                <button class="mt-2 bg-white text-[#6E0D25] px-4 py-2 rounded text-sm font-semibold hover:bg-gray-100 transition-colors">
                    Claim Offer
                </button>
            </div>
        </div>
    </div>
</div>
