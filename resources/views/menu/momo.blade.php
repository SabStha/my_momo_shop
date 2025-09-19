<div class="px-4 sm:px-6 py-4 max-w-screen-sm mx-auto">
    <style>
        /* Momo Section Auto-Hover Effects */
        .momo-combo-section {
            animation: subtleFloat 4s ease-in-out infinite;
        }
        
        .momo-combo-section:nth-child(odd) {
            animation-delay: 0s;
        }
        
        .momo-combo-section:nth-child(even) {
            animation-delay: 2s;
        }
        
        .momo-image {
            animation: imageGlow 3s ease-in-out infinite;
        }
        
        .momo-image:nth-child(odd) {
            animation-delay: 0.5s;
        }
        
        .momo-image:nth-child(even) {
            animation-delay: 1.5s;
        }
        
        @keyframes subtleFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-2px);
            }
        }
        
        @keyframes imageGlow {
            0%, 100% {
                filter: brightness(1) drop-shadow(0 0 0 rgba(0,0,0,0));
            }
            50% {
                filter: brightness(1.05) drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            }
        }
        
        /* Enhanced hover effects */
        .momo-combo-section:hover {
            transform: translateY(-1px);
            transition: transform 0.3s ease;
        }
        
        .momo-image:hover {
            transform: scale(1.05);
            filter: brightness(1.1) drop-shadow(0 6px 12px rgba(0,0,0,0.15));
            transition: all 0.3s ease;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .momo-combo-section {
                animation-duration: 5s;
            }
            
            .momo-image {
                animation-duration: 4s;
            }
        }
        
        /* Reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            .momo-combo-section,
            .momo-image {
                animation: none;
            }
        }
    </style>

    <!-- GROUP COMBO (Text | Image) -->
    <div class="grid grid-cols-2 items-start gap-6 mb-12 momo-combo-section">
        <div data-aos="fade-right" class="p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-lg sm:text-xl font-semibold text-[#B4342D]">GROUP COMBO</h2>
                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">🌟 Most Popular</span>
            </div>
            <p class="text-sm text-gray-700 leading-snug mb-4">
                <strong>Perfect for friends hangout or team meals.</strong><br>
                Includes assorted momos, fries, sausage, and drinks for sharing.
            </p>
            <p class="text-xs text-gray-500 italic mb-4">
                <em>Ingredients:</em> Mixed momos (chicken, buff, veg), fries, sausage, assorted dips, drinks.
            </p>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-xl font-bold text-[#B4342D]">Rs. 899</div>
                <button data-add-to-cart
                        data-product-id="group-combo"
                        data-product-name="GROUP COMBO"
                        data-product-price="899"
                        data-product-image="{{ asset('storage/products/combos/group-combo.jpg') }}"
                        class="bg-[#7B1E3A] text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm hover:bg-[#6E0D25] transition-colors duration-300">
                    Add to Cart
                </button>
            </div>
        </div>
        <div data-aos="fade-left" class="flex justify-center items-start pt-4 sm:pt-6">
            <img src="{{ asset('storage/products/combos/group-combo.jpg') }}"
                 class="w-24 h-24 sm:w-36 sm:h-36 object-cover rounded-xl shadow-lg hover:scale-105 transition duration-500 momo-image"
                 alt="Group Combo">
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200 mb-12"></div>

    <!-- FAMILY SET (Image | Text) -->
    <div class="grid grid-cols-2 items-start gap-6 mb-12 momo-combo-section">
        <div data-aos="fade-right" class="flex justify-center items-start pt-4 sm:pt-6">
            <img src="{{ asset('storage/products/combos/family-set.jpg') }}"
                 class="w-24 h-24 sm:w-36 sm:h-36 object-cover rounded-xl shadow-lg hover:scale-105 transition duration-500 momo-image"
                 alt="Family Set">
        </div>
        <div data-aos="fade-left" class="p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-lg sm:text-xl font-semibold text-[#B4342D]">FAMILY SET</h2>
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">👨‍👩‍👧‍👦 Family Size</span>
            </div>
            <p class="text-sm text-gray-700 leading-snug mb-4">
                <strong>Hearty meal for the whole family.</strong><br>
                Creamy, savory pasta with pancetta, eggs, and Parmesan cheese.
            </p>
            <p class="text-xs text-gray-500 italic mb-4">
                <em>Ingredients:</em> Spaghetti, pancetta, eggs, Parmesan cheese, black pepper, salt.
            </p>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-xl font-bold text-[#B4342D]">Rs. 699</div>
                <button data-add-to-cart
                        data-product-id="family-set"
                        data-product-name="FAMILY SET"
                        data-product-price="699"
                        data-product-image="{{ asset('storage/products/combos/family-set.jpg') }}"
                        class="bg-[#7B1E3A] text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm hover:bg-[#6E0D25] transition-colors duration-300">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200 mb-12"></div>

    <!-- PARTY SET (Text | Image) -->
    <div class="grid grid-cols-2 items-start gap-6 mb-12 momo-combo-section">
        <div data-aos="fade-right" class="p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-lg sm:text-xl font-semibold text-[#B4342D]">PARTY SET</h2>
                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">🎉 Party Size</span>
            </div>
            <p class="text-sm text-gray-700 leading-snug mb-4">
                <strong>Bring the party vibes with this mega set.</strong><br>
                A big platter of momos, crispy sides, sausages, drinks, and dessert.
            </p>
            <p class="text-xs text-gray-500 italic mb-4">
                <em>Ingredients:</em> Momos, karaage, sausage, fries, lemon tea, brownie sundae.
            </p>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-xl font-bold text-[#B4342D]">Rs. 1299</div>
                <button data-add-to-cart
                        data-product-id="party-set"
                        data-product-name="PARTY SET"
                        data-product-price="1299"
                        data-product-image="{{ asset('storage/products/combos/party-set.jpg') }}"
                        class="bg-[#7B1E3A] text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm hover:bg-[#6E0D25] transition-colors duration-300">
                    Add to Cart
                </button>
            </div>
        </div>
        <div data-aos="fade-left" class="flex justify-center items-start pt-4 sm:pt-6">
            <img src="{{ asset('storage/products/combos/party-set.jpg') }}"
                 class="w-24 h-24 sm:w-36 sm:h-36 object-cover rounded-xl shadow-lg hover:scale-105 transition duration-500 momo-image"
                 alt="Party Set">
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200 mb-12"></div>

    <!-- STUDENT SET (Image | Text) -->
    <div class="grid grid-cols-2 items-start gap-6 mb-12 momo-combo-section">
        <div data-aos="fade-right" class="flex justify-center items-start pt-4 sm:pt-6">
            <img src="{{ asset('storage/products/combos/student-set.jpg') }}"
                 class="w-24 h-24 sm:w-36 sm:h-36 object-cover rounded-xl shadow-lg hover:scale-105 transition duration-500 momo-image"
                 alt="Student Set">
        </div>
        <div data-aos="fade-left" class="p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-lg sm:text-xl font-semibold text-[#B4342D]">STUDENT SET</h2>
                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">💰 Budget Friendly</span>
            </div>
            <p class="text-sm text-gray-700 leading-snug mb-4">
                <strong>Budget-friendly and filling for students on the go.</strong><br>
                Includes steamed momos, fries or sausage, and a soft drink.
            </p>
            <p class="text-xs text-gray-500 italic mb-4">
                <em>Ingredients:</em> Chicken/veg momos, fries or sausage, coke/sprite.
            </p>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-xl font-bold text-[#B4342D]">Rs. 399</div>
                <button data-add-to-cart
                        data-product-id="student-set"
                        data-product-name="STUDENT SET"
                        data-product-price="399"
                        data-product-image="{{ asset('storage/products/combos/student-set.jpg') }}"
                        class="bg-[#7B1E3A] text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm hover:bg-[#6E0D25] transition-colors duration-300">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200 mb-12"></div>

    <!-- OFFICE WORKER SET (Text | Image) -->
    <div class="grid grid-cols-2 items-start gap-6 mb-12 momo-combo-section">
        <div data-aos="fade-right" class="p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-lg sm:text-xl font-semibold text-[#B4342D]">OFFICE WORKER SET</h2>
                <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">💼 Quick Lunch</span>
            </div>
            <p class="text-sm text-gray-700 leading-snug mb-4">
                <strong>Quick, satisfying, and energizing lunch for busy days.</strong><br>
                Fried momos, sausage or karaage, and hot milk tea.
            </p>
            <p class="text-xs text-gray-500 italic mb-4">
                <em>Ingredients:</em> Fried momos, karaage or sausage, milk tea or black tea.
            </p>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-xl font-bold text-[#B4342D]">Rs. 549</div>
                <button data-add-to-cart
                        data-product-id="office-worker-set"
                        data-product-name="OFFICE WORKER SET"
                        data-product-price="549"
                        data-product-image="{{ asset('storage/products/combos/office-worker-set.jpg') }}"
                        class="bg-[#7B1E3A] text-white rounded-md px-4 py-2 text-sm font-medium shadow-sm hover:bg-[#6E0D25] transition-colors duration-300">
                    Add to Cart
                </button>
            </div>
        </div>
        <div data-aos="fade-left" class="flex justify-center items-start pt-4 sm:pt-6">
            <img src="{{ asset('storage/products/combos/office-worker-set.jpg') }}"
                 class="w-24 h-24 sm:w-36 sm:h-36 object-cover rounded-xl shadow-lg hover:scale-105 transition duration-500 momo-image"
                 alt="Office Worker Set">
        </div>
    </div>

</div>

<!-- Include Cart Modal -->
@include('components.cart-modal')
