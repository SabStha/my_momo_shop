<!-- Special Combos Section -->
<section class="py-4 sm:py-8 px-3 sm:px-4">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <!-- Section Header -->
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-[#6E0D25] mb-2">🎉 Special Combos</h2>
                <p class="text-sm sm:text-base text-gray-600">Perfect sets for every occasion - save more when you order together!</p>
            </div>

            <!-- Combos Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Group Combo -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative h-40 sm:h-48 overflow-hidden">
                        <img src="{{ asset('storage/products/combos/group-combo.jpg') }}" 
                             alt="Group Combo"
                             class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            🔥 Popular
                        </div>
                    </div>
                    <div class="p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Group Combo</h3>
                        <p class="text-gray-600 text-xs sm:text-sm mb-3 line-clamp-2">Perfect for friends hangout or team meals. Includes assorted momos, fries, sausage, and drinks.</p>
                        <div class="flex justify-between items-center">
                            <div class="text-lg sm:text-xl font-bold text-[#6E0D25]">$24.99</div>
                            <button onclick="addComboToCart('group')" 
                                    class="bg-[#6E0D25] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors duration-300 text-xs sm:text-sm min-h-[40px] min-w-[80px]">
                                Order Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Family Set -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative h-40 sm:h-48 overflow-hidden">
                        <img src="{{ asset('storage/products/combos/family-set.jpg') }}" 
                             alt="Family Set"
                             class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            👨‍👩‍👧‍👦 Family
                        </div>
                    </div>
                    <div class="p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Family Set</h3>
                        <p class="text-gray-600 text-xs sm:text-sm mb-3 line-clamp-2">Hearty meal for the whole family. Creamy, savory pasta with pancetta, eggs, and Parmesan cheese.</p>
                        <div class="flex justify-between items-center">
                            <div class="text-lg sm:text-xl font-bold text-[#6E0D25]">$29.99</div>
                            <button onclick="addComboToCart('family')" 
                                    class="bg-[#6E0D25] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors duration-300 text-xs sm:text-sm min-h-[40px] min-w-[80px]">
                                Order Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Party Set -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative h-40 sm:h-48 overflow-hidden">
                        <img src="{{ asset('storage/products/combos/party-set.jpg') }}" 
                             alt="Party Set"
                             class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 bg-purple-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            🎉 Party
                        </div>
                    </div>
                    <div class="p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Party Set</h3>
                        <p class="text-gray-600 text-xs sm:text-sm mb-3 line-clamp-2">Bring the party vibes with this mega set. A big platter of momos, crispy sides, sausages, drinks, and dessert.</p>
                        <div class="flex justify-between items-center">
                            <div class="text-lg sm:text-xl font-bold text-[#6E0D25]">$34.99</div>
                            <button onclick="addComboToCart('party')" 
                                    class="bg-[#6E0D25] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors duration-300 text-xs sm:text-sm min-h-[40px] min-w-[80px]">
                                Order Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Student Set -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative h-40 sm:h-48 overflow-hidden">
                        <img src="{{ asset('storage/products/combos/student-set.jpg') }}" 
                             alt="Student Set"
                             class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            🎓 Student
                        </div>
                    </div>
                    <div class="p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Student Set</h3>
                        <p class="text-gray-600 text-xs sm:text-sm mb-3 line-clamp-2">Budget-friendly and filling for students on the go. Includes steamed momos, fries or sausage, and a soft drink.</p>
                        <div class="flex justify-between items-center">
                            <div class="text-lg sm:text-xl font-bold text-[#6E0D25]">$12.99</div>
                            <button onclick="addComboToCart('student')" 
                                    class="bg-[#6E0D25] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors duration-300 text-xs sm:text-sm min-h-[40px] min-w-[80px]">
                                Order Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Office Worker Set -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative h-40 sm:h-48 overflow-hidden">
                        <img src="{{ asset('storage/products/combos/office-worker-set.jpg') }}" 
                             alt="Office Worker Set"
                             class="w-full h-full object-cover">
                        <div class="absolute top-2 left-2 bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            💼 Office
                        </div>
                    </div>
                    <div class="p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Office Worker Set</h3>
                        <p class="text-gray-600 text-xs sm:text-sm mb-3 line-clamp-2">Quick, satisfying, and energizing lunch for busy days. Fried momos, sausage or karaage, and hot milk tea.</p>
                        <div class="flex justify-between items-center">
                            <div class="text-lg sm:text-xl font-bold text-[#6E0D25]">$15.99</div>
                            <button onclick="addComboToCart('office')" 
                                    class="bg-[#6E0D25] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors duration-300 text-xs sm:text-sm min-h-[40px] min-w-[80px]">
                                Order Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Order CTA -->
            <div class="text-center mt-6 sm:mt-8 p-4 sm:p-6 bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] rounded-xl text-white">
                <h3 class="text-xl sm:text-2xl font-bold mb-2">Need More for Events?</h3>
                <p class="text-white/90 mb-4 text-sm sm:text-base">Get special pricing for bulk orders and catering events</p>
                <a href="{{ route('bulk') }}" 
                   class="bg-white text-[#6E0D25] px-4 sm:px-6 py-2 sm:py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors duration-300 inline-flex items-center gap-2 text-sm sm:text-base min-h-[44px]">
                    🎯 Bulk Order
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section> 