<!-- Quick Categories Section -->
<section class="py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl p-6 md:p-8 shadow-xl">
            <!-- Section Header -->
            <div class="text-center mb-8">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#6E0D25] mb-2">🍽️ Quick Menu</h2>
                <p class="text-gray-600">Explore our delicious menu categories</p>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Momos Category -->
                <a href="{{ route('menu') }}#momo" 
                   class="group bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border-2 border-transparent hover:border-red-200">
                    <div class="text-4xl mb-3">🥟</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Momos</h3>
                    <p class="text-sm text-gray-600">Steamed & Fried</p>
                    <div class="mt-2 text-xs text-red-600 font-medium group-hover:underline">
                        View All →
                    </div>
                </a>

                <!-- Combos Category -->
                <a href="{{ route('menu') }}#combo" 
                   class="group bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border-2 border-transparent hover:border-blue-200">
                    <div class="text-4xl mb-3">🎉</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Combos</h3>
                    <p class="text-sm text-gray-600">Perfect Sets</p>
                    <div class="mt-2 text-xs text-blue-600 font-medium group-hover:underline">
                        View All →
                    </div>
                </a>

                <!-- Drinks Category -->
                <a href="{{ route('menu') }}#drinks" 
                   class="group bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border-2 border-transparent hover:border-green-200">
                    <div class="text-4xl mb-3">🥤</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Drinks</h3>
                    <p class="text-sm text-gray-600">Hot & Cold</p>
                    <div class="mt-2 text-xs text-green-600 font-medium group-hover:underline">
                        View All →
                    </div>
                </a>

                <!-- Desserts Category -->
                <a href="{{ route('menu') }}#desserts" 
                   class="group bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border-2 border-transparent hover:border-purple-200">
                    <div class="text-4xl mb-3">🍰</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Desserts</h3>
                    <p class="text-sm text-gray-600">Sweet Treats</p>
                    <div class="mt-2 text-xs text-purple-600 font-medium group-hover:underline">
                        View All →
                    </div>
                </a>
            </div>

            <!-- Special Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                <!-- Ama's Finds -->
                <a href="{{ route('finds') }}" 
                   class="group bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-4 text-center hover:shadow-lg transition-all duration-300 border-2 border-transparent hover:border-yellow-200">
                    <div class="text-2xl mb-2">🔍</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Ama's Finds</h3>
                    <p class="text-sm text-gray-600">Special Discoveries</p>
                </a>

                <!-- Bulk Orders -->
                <a href="{{ route('bulk') }}" 
                   class="group bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-4 text-center hover:shadow-lg transition-all duration-300 border-2 border-transparent hover:border-indigo-200">
                    <div class="text-2xl mb-2">📦</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Bulk Orders</h3>
                    <p class="text-sm text-gray-600">Events & Catering</p>
                </a>

                <!-- Loyalty Program -->
                <div class="group bg-gradient-to-r from-pink-50 to-red-50 rounded-xl p-4 text-center hover:shadow-lg transition-all duration-300 border-2 border-transparent hover:border-pink-200">
                    <div class="text-2xl mb-2">👑</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">Loyalty Rewards</h3>
                    <p class="text-sm text-gray-600">Earn Points</p>
                </div>
            </div>

            <!-- Quick Search -->
            <div class="mt-8 p-4 bg-gray-50 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           placeholder="Search for your favorite momos..." 
                           class="flex-1 bg-transparent border-none outline-none text-gray-700 placeholder-gray-400"
                           onkeyup="searchProducts(this.value)">
                </div>
            </div>
        </div>
    </div>
</section> 