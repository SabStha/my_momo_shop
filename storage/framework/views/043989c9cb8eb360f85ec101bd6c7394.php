<!-- Customer Reviews Section -->
<section class="py-4 sm:py-6 px-0 sm:px-2 bg-gradient-to-b from-[#FFF8F0] via-[#FCEDC0] to-white">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl p-1 sm:p-2 md:p-4 shadow-xl">
            <!-- Section Header -->
            <div class="text-center mb-0.5 sm:mb-1">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#6E0D25] mb-0">💬 Customer Reviews</h2>
                <p class="text-[10px] sm:text-xs text-gray-600">See what our happy customers are saying about us</p>
            </div>

            <!-- Overall Rating -->
            <div class="text-center mb-0">
                <div class="text-xl sm:text-2xl mb-0">⭐</div>
                <div class="text-base sm:text-lg font-bold text-[#6E0D25] mb-0" data-stat="customer_rating"><?php echo e($statistics['customer_rating'] ?? '4.9'); ?>/5</div>
                <div class="text-[10px] sm:text-xs text-gray-600 mb-0">Based on <?php echo e($statistics['happy_customers'] ?? '500+'); ?> reviews</div>
                <div class="flex justify-center gap-0.5 sm:gap-1 mb-0">
                    <span class="text-yellow-400 text-xs sm:text-base">★★★★★</span>
                </div>
            </div>

            <!-- Reviews Slider (Mobile) / Grid (Desktop) -->
            <div x-data="{
                current: 0,
                reviews: [
                    { avatar: 'SM', color: 'from-red-400 to-red-600', name: 'Sarah Mitchell', stars: 5, comment: 'Absolutely love the steamed chicken momos! The dipping sauce is perfect and the delivery was super fast. Will definitely order again!', order: 'Steamed Chicken Momos' },
                    { avatar: 'JD', color: 'from-blue-400 to-blue-600', name: 'John Davis', stars: 5, comment: 'The family combo was perfect for our dinner. Great value for money and the kids loved the variety. Highly recommended!', order: 'Family Combo' },
                    { avatar: 'ML', color: 'from-green-400 to-green-600', name: 'Maria Lopez', stars: 5, comment: 'Best momos in town! The vegetarian options are amazing and the customer service is outstanding. Thank you!', order: 'Veg Momos' },
                    { avatar: 'RK', color: 'from-purple-400 to-purple-600', name: 'Robert Kim', stars: 5, comment: 'Ordered for our office party and everyone loved it! The bulk order discount was great and delivery was on time.', order: 'Bulk Order' },
                    { avatar: 'AP', color: 'from-orange-400 to-orange-600', name: 'Anna Patel', stars: 5, comment: 'The spicy chicken momos are my favorite! Perfect amount of spice and the texture is just right. Love the loyalty rewards too!', order: 'Spicy Chicken Momos' },
                    { avatar: 'TW', color: 'from-pink-400 to-pink-600', name: 'Tom Wilson', stars: 5, comment: 'Amazing service! The app is easy to use, delivery is fast, and the food is always hot and fresh. My go-to for momos!', order: 'Mixed Momos' },
                ],
                interval: null,
                prev: 0,
                direction: 'right',
                startAutoSlide() {
                    this.interval = setInterval(() => {
                        this.prev = this.current;
                        this.direction = 'right';
                        this.current = (this.current + 1) % this.reviews.length;
                    }, 4000);
                },
                stopAutoSlide() {
                    clearInterval(this.interval);
                },
                goTo(i) {
                    this.direction = i > this.current ? 'right' : 'left';
                    this.prev = this.current;
                    this.current = i;
                },
                next() {
                    this.direction = 'right';
                    this.prev = this.current;
                    this.current = (this.current + 1) % this.reviews.length;
                },
                prevSlide() {
                    this.direction = 'left';
                    this.prev = this.current;
                    this.current = (this.current - 1 + this.reviews.length) % this.reviews.length;
                }
            }"
            x-init="startAutoSlide()"
            @mouseenter="stopAutoSlide()" @mouseleave="startAutoSlide()"
            class="">
                <!-- Mobile Slider -->
                <div class="block sm:hidden relative min-h-[140px] mb-4">
                    <template x-for="(review, i) in reviews" :key="i">
                        <div
                            x-show="current === i"
                            :class="'absolute top-0 left-0 w-full transition-all duration-500'"
                            x-transition:enter="transform ease-out duration-500"
                            x-transition:enter-start="opacity-0 translate-x-20"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transform ease-in duration-500"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-20"
                            style="will-change: transform;"
                        >
                            <div :class="'flex ' + (i % 2 === 0 ? 'justify-start' : 'justify-end')">
                                <div class="bg-[#FFF7F0] border border-gray-100 rounded-lg p-3 shadow-sm w-full flex flex-col gap-2">
                                    <div class="flex items-center mb-2">
                                        <div :class="'w-6 h-6 bg-gradient-to-br ' + review.color + ' rounded-full flex items-center justify-center text-white font-bold text-xs mr-2'">
                                            <span x-text="review.avatar"></span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-xs text-gray-800" x-text="review.name"></div>
                                            <div class="text-yellow-400 text-[10px] leading-none">★★★★★</div>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 text-xs mb-2 leading-relaxed min-h-[40px]" x-text="review.comment"></p>
                                    <div class="text-[10px] text-gray-400" x-text="'Ordered: ' + review.order"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- Navigation -->
                    <div class="flex justify-center gap-1 mt-3">
                        <button @click="prevSlide()" class="w-5 h-5 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 border border-gray-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <template x-for="(review, i) in reviews" :key="i">
                            <button @click="goTo(i)" :class="'w-1.5 h-1.5 rounded-full mx-0.5 ' + (current === i ? 'bg-[#6E0D25]' : 'bg-gray-300')"></button>
                        </template>
                        <button @click="next()" class="w-5 h-5 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 border border-gray-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
                <!-- Desktop Grid -->
                <div class="hidden sm:grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                    <!-- Repeat the original review cards for desktop -->
                    <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                        <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">SM</div>
                            <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">Sarah Mitchell</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"Absolutely love the steamed chicken momos! The dipping sauce is perfect and the delivery was super fast. Will definitely order again!"</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: Steamed Chicken Momos</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                        <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">JD</div>
                            <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">John Davis</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"The family combo was perfect for our dinner. Great value for money and the kids loved the variety. Highly recommended!"</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: Family Combo</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                        <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">ML</div>
                            <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">Maria Lopez</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"Best momos in town! The vegetarian options are amazing and the customer service is outstanding. Thank you!"</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: Veg Momos</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                        <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">RK</div>
                            <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">Robert Kim</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"Ordered for our office party and everyone loved it! The bulk order discount was great and delivery was on time."</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: Bulk Order</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                        <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">AP</div>
                            <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">Anna Patel</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"The spicy chicken momos are my favorite! Perfect amount of spice and the texture is just right. Love the loyalty rewards too!"</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: Spicy Chicken Momos</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                        <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-pink-400 to-pink-600 rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">TW</div>
                            <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">Tom Wilson</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"Amazing service! The app is easy to use, delivery is fast, and the food is always hot and fresh. My go-to for momos!"</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: Mixed Momos</div>
                    </div>
                </div>
            </div>

            <!-- Review Stats -->
            <div class="mt-0 mb-1 sm:mt-0 sm:mb-2 overflow-x-auto">
                <div class="grid grid-cols-3 gap-1 sm:gap-2 text-center">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-1.5 sm:p-3">
                        <div class="text-xs sm:text-lg font-bold text-blue-600">25min</div>
                        <div class="text-[9px] sm:text-xs text-gray-600">Avg Delivery Time</div>
                    </div>
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-1.5 sm:p-3">
                        <div class="text-xs sm:text-lg font-bold text-purple-600" data-stat="happy_customers"><?php echo e($statistics['happy_customers'] ?? '500+'); ?></div>
                        <div class="text-[9px] sm:text-xs text-gray-600">Happy Reviews</div>
                    </div>
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg p-1.5 sm:p-3">
                        <div class="text-xs sm:text-lg font-bold text-orange-600" data-stat="customer_rating"><?php echo e($statistics['customer_rating'] ?? '4.9'); ?></div>
                        <div class="text-[9px] sm:text-xs text-gray-600">Average Rating</div>
                    </div>
                </div>
            </div>

            <!-- Write Review CTA -->
            <div class="text-center mt-1 sm:mt-2">
                <a href="#" onclick="writeReview()" 
                   class="inline-flex items-center gap-1 bg-[#6E0D25] text-white px-2 sm:px-3 py-1 sm:py-1.5 rounded-full font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 text-[10px] sm:text-xs">
                    ✍️ Write a Review
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section> <?php /**PATH /var/www/my_momo_shop/resources/views/home/sections/customer-reviews.blade.php ENDPATH**/ ?>