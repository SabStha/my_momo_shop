<!-- Featured Products Section -->
<section id="featured-products" class="py-4 sm:py-6 px-0 sm:px-4 bg-gradient-to-b from-[#FFF8F0] via-[#FCEDC0] to-white">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <!-- Section Header -->
            <div class="text-center mb-4 sm:mb-6">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mb-2 hover:scale-105 transition-transform duration-300 cursor-pointer bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] px-4 py-2 rounded-full shadow-lg inline-block">🌟 Featured Products</h2>
                <p class="text-xs sm:text-sm text-gray-800 hover:text-[#6E0D25] hover:scale-105 transition-all duration-300 cursor-default">Discover our handpicked selection of premium products — handpicked for you.</p>
            </div>
            
            <!-- Featured Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <?php $__empty_1 = true; $__currentLoopData = $featuredProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 overflow-hidden">
                    <!-- Product Image -->
                    <div class="relative h-40 sm:h-48 overflow-hidden">
                        <img src="<?php echo e(asset('storage/' . $product->image)); ?>" 
                             alt="<?php echo e($product->name); ?>"
                             class="w-full h-full object-cover">
                        <?php if($product->is_featured): ?>
                        <div class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-bold">
                            ⭐ Featured
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info -->
                    <div class="p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2"><?php echo e($product->name); ?></h3>
                        <p class="text-gray-600 text-xs sm:text-sm mb-3 line-clamp-2"><?php echo e($product->description); ?></p>
                        
                        <!-- Price and Actions -->
                        <div class="flex justify-between items-center">
                            <div class="text-lg sm:text-xl font-bold text-[#6E0D25]">
                                $<?php echo e(number_format($product->price, 2)); ?>

                            </div>
                            <button onclick="addToCart('<?php echo e($product->id); ?>', '<?php echo e($product->name); ?>', <?php echo e($product->price); ?>, '<?php echo e(asset('storage/' . $product->image)); ?>')" 
                                    data-add-to-cart
                                    data-product-id="<?php echo e($product->id); ?>"
                                    data-product-name="<?php echo e($product->name); ?>"
                                    data-product-price="<?php echo e($product->price); ?>"
                                    data-product-image="<?php echo e(asset('storage/' . $product->image)); ?>"
                                    class="bg-[#6E0D25] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#B91C1C] hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-1 sm:gap-2 min-h-[40px] min-w-[60px] justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 8h14l1 12a2 2 0 01-2 2H6a2 2 0 01-2-2l1-12z"/>
                                </svg>
                                <span class="text-xs sm:text-sm">Add</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <!-- Fallback Content -->
                <div class="col-span-full text-center py-8 sm:py-12">
                    <div class="text-4xl sm:text-6xl mb-4">🥟</div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">No Featured Products Yet</h3>
                    <p class="text-sm sm:text-base text-gray-500">Check back soon for our featured momo selections!</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- View All Button -->
            <div class="text-center mt-6 sm:mt-8">
                <a href="<?php echo e(route('menu')); ?>" 
                   class="inline-flex items-center gap-2 bg-white text-[#6E0D25] border-2 border-[#6E0D25] px-4 sm:px-6 py-2 sm:py-3 rounded-full font-semibold hover:bg-[#6E0D25] hover:text-white transition-all duration-300 text-sm sm:text-base min-h-[44px]">
                    View All Products
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/home/sections/featured-products.blade.php ENDPATH**/ ?>