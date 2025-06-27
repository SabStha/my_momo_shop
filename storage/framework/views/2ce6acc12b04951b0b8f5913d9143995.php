<!-- Hero Section -->
<section class="relative flex items-start sm:items-center justify-center pt-0 mt-0 mb-0 bg-gradient-to-b from-[#FFF8F0] via-[#FCEDC0] to-white">
    <div class="w-full max-w-7xl mx-auto px-0">
        <!-- Main Hero Content -->

            

                <!-- Hero Carousel -->
                <div class="mb-1 sm:mb-2 lg:mb-3">
                    <!-- Carousel Container -->
                    <div class="relative overflow-hidden rounded-xl lg:rounded-2xl bg-gradient-to-b from-[#FFF8F0] via-[#FCEDC0] to-white">
                        <div id="hero-carousel" class="flex transition-transform duration-500 ease-in-out">
                            <?php $__empty_1 = true; $__currentLoopData = $menuHighlights ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="carousel-slide w-full flex-shrink-0">
                                <div class="relative h-80 sm:h-[28rem] md:h-[32rem] lg:h-[40rem] xl:h-[45rem]">
                                    <!-- Product Image -->
                                    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" 
                                         alt="<?php echo e($product->name); ?>"
                                         class="w-full h-full object-cover">
                                    
                                    <!-- Overlay: Top (Tagline first, then Badge) -->
                                    <!-- OUTER WRAPPER -->
                                    <!-- OUTER WRAPPER -->
                                    <div class="absolute top-0 left-0 right-0 z-10 px-4 pt-4 lg:px-8 lg:pt-8 space-y-0 pointer-events-none">

                                    <!-- First row: Brand name LEFT, Highlight badge RIGHT -->
                                    <div class="flex justify-between items-center pointer-events-auto">
                                        <!-- Brand Name Tagline -->
                                        <div class="bg-black/60 backdrop-blur-md rounded-md px-3 py-2 lg:px-6 lg:py-3">
                                        <h1 class="text-xs sm:text-sm lg:text-base xl:text-lg font-bold text-yellow-400">
                                            Ama Ko Momo — <span class="text-yellow-400">Nepal's Momo</span>
                                        </h1>

                                        </div>

                                        <!-- Highlight badge -->
                                        <?php if($product->is_menu_highlight): ?>
                                        <div class="bg-yellow-400 text-yellow-900 px-3 py-1 lg:px-4 lg:py-2 rounded-full text-xs lg:text-sm font-bold ml-4 whitespace-nowrap shadow-md">
                                            ⭐ Highlighted
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tagline Description (always left-aligned) -->
                                    <div class="bg-black/50 backdrop-blur-md rounded-md px-3 py-1 lg:px-4 lg:py-2 w-fit pointer-events-auto">
                                        <p class="text-xs sm:text-sm lg:text-base xl:text-lg text-white/90 leading-tight">
                                            A taste of love, a bond of home.
                                        </p>
                                    </div>

                                    </div>




                                    <!-- Product Overlay (Bottom) -->
                                    <div class="absolute bottom-0 left-0 right-0 z-10 p-4 sm:p-6 lg:p-8 xl:p-10 text-white bg-gradient-to-t from-[#FFF8F0]/80 via-white/60 to-transparent">
                                        <!-- Product Name & Description -->
                                        <div class="bg-black/40 backdrop-blur-sm rounded-md px-3 py-2 lg:px-6 lg:py-4 inline-block w-fit">

                                            <h3 class="text-lg lg:text-2xl xl:text-3xl font-extrabold text-white leading-tight">
                                                <?php echo e($product->name); ?>

                                            </h3>

                                            <p class="text-sm sm:text-base lg:text-lg xl:text-xl text-white/80 line-clamp-2 lg:line-clamp-3">
                                                <?php echo e($product->description); ?>

                                            </p>
                                        </div>

                                        <!-- Price + Add to Cart -->
                                        <div class="flex items-center justify-between gap-3 mb-3 lg:mb-4">
                                            <span class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-yellow-400">
                                                Rs.<?php echo e(number_format($product->price, 0)); ?>

                                            </span>
                                            <button onclick="addToCart('<?php echo e($product->id); ?>', '<?php echo e($product->name); ?>', <?php echo e($product->price); ?>, '<?php echo e(asset('storage/' . $product->image)); ?>')" 
                                                data-add-to-cart
                                                data-product-id="<?php echo e($product->id); ?>"
                                                data-product-name="<?php echo e($product->name); ?>"
                                                data-product-price="<?php echo e($product->price); ?>"
                                                data-product-image="<?php echo e(asset('storage/' . $product->image)); ?>"
                                                class="bg-[#6E0D25] text-white px-3 sm:px-4 lg:px-6 py-2 lg:py-3 rounded-lg hover:bg-[#8B0D2F] transition-all duration-300 flex items-center gap-2 min-h-[40px] lg:min-h-[50px] min-w-[80px] lg:min-w-[120px] justify-center shadow-sm hover:shadow-md hover:scale-[0.98] active:scale-[0.95]">
                                                
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 lg:w-5 lg:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 8h14l1 12a2 2 0 01-2 2H6a2 2 0 01-2-2l1-12z"/>
                                                </svg>

                                                <span class="text-xs sm:text-sm lg:text-base">Add to Cart</span>
                                            </button>

                                        </div>

                                        <!-- View Menu -->
                                        <a href="<?php echo e(route('menu')); ?>" 
                                        class="bg-white/90 text-[#6E0D25] text-xs lg:text-sm px-3 py-2 lg:px-6 lg:py-3 rounded-lg border border-[#6E0D25] font-medium hover:bg-gray-100 transition-all duration-300 flex items-center justify-center w-full shadow-sm hover:shadow-md hover:scale-[0.98] active:scale-[0.95]">
                                            View Menu
                                            <svg class="w-3 h-3 lg:w-4 lg:h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <!-- Fallback Content -->
                            <div class="carousel-slide w-full flex-shrink-0">
                                <div class="relative h-64 sm:h-80 md:h-96 lg:h-[40rem] xl:h-[45rem] bg-gradient-to-br from-[#6E0D25] to-[#8B0D2F] flex items-center justify-center">
                                    <div class="text-center text-white">
                                        <div class="text-6xl sm:text-8xl lg:text-9xl xl:text-[10rem] mb-4">🥟</div>
                                        <h3 class="text-xl sm:text-2xl lg:text-4xl xl:text-5xl font-bold mb-2">Welcome to Ama Ko Shop</h3>
                                        <p class="text-sm sm:text-base lg:text-xl xl:text-2xl opacity-90 mb-4">Discover our delicious momo varieties</p>
                                        <a href="<?php echo e(route('menu')); ?>" 
                                           class="bg-white/90 text-[#6E0D25] text-xs lg:text-base px-3 py-1 lg:px-6 lg:py-3 rounded border border-[#6E0D25] font-medium hover:bg-gray-100 transition-all duration-300 inline-flex items-center gap-1 shadow-sm hover:shadow-md hover:scale-[0.98] active:scale-[0.95]">
                                            View Menu
                                            <svg class="w-3 h-3 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Carousel Navigation Dots -->
                        <?php if(($menuHighlights ?? [])->count() > 1): ?>
                        <div class="absolute bottom-20 sm:bottom-24 lg:bottom-28 left-1/2 transform -translate-x-1/2 flex space-x-3 lg:space-x-4 z-20">
                            <?php $__currentLoopData = $menuHighlights ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button onclick="goToSlide(<?php echo e($index); ?>)" 
                                    class="carousel-dot w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 rounded-full bg-white/60 hover:bg-white transition-all duration-300 shadow-lg border-2 border-white/30 <?php echo e($index === 0 ? 'bg-white shadow-xl scale-110' : ''); ?>"
                                    data-slide="<?php echo e($index); ?>">
                            </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Carousel Navigation Arrows -->
                        <?php if(($menuHighlights ?? [])->count() > 1): ?>
                        <button onclick="previousSlide()" 
                                class="absolute left-2 sm:left-4 lg:left-8 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white p-2 lg:p-3 rounded-full transition-colors duration-300 min-w-[44px] min-h-[44px] lg:min-w-[56px] lg:min-h-[56px] flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        
                        <button onclick="nextSlide()" 
                                class="absolute right-2 sm:right-4 lg:right-8 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white p-2 lg:p-3 rounded-full transition-colors duration-300 min-w-[44px] min-h-[44px] lg:min-w-[56px] lg:min-h-[56px] flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-2 sm:gap-4 lg:gap-6 text-center">
                    <div class="bg-[#6E0D25]/20 backdrop-blur-md rounded-lg p-2 sm:p-3 lg:p-4 shadow-lg border border-[#6E0D25]/30">
                        <div class="text-lg sm:text-2xl lg:text-3xl xl:text-4xl font-black text-[#6E0D25] drop-shadow-sm" data-stat="happy_customers"><?php echo e($statistics['happy_customers'] ?? '500+'); ?></div>
                        <div class="text-xs sm:text-sm lg:text-base font-semibold text-[#6E0D25]/80">Happy Customers</div>
                    </div>
                    <div class="bg-[#6E0D25]/20 backdrop-blur-md rounded-lg p-2 sm:p-3 lg:p-4 shadow-lg border border-[#6E0D25]/30">
                        <div class="text-lg sm:text-2xl lg:text-3xl xl:text-4xl font-black text-[#6E0D25] drop-shadow-sm" data-stat="momo_varieties"><?php echo e($statistics['momo_varieties'] ?? '15+'); ?></div>
                        <div class="text-xs sm:text-sm lg:text-base font-semibold text-[#6E0D25]/80">Momo Varieties</div>
                    </div>
                    <div class="bg-[#6E0D25]/20 backdrop-blur-md rounded-lg p-2 sm:p-3 lg:p-4 shadow-lg border border-[#6E0D25]/30">
                        <div class="text-lg sm:text-2xl lg:text-3xl xl:text-4xl font-black text-[#6E0D25] drop-shadow-sm" data-stat="customer_rating"><?php echo e($statistics['customer_rating'] ?? '5.0'); ?>⭐</div>
                        <div class="text-xs sm:text-sm lg:text-base font-semibold text-[#6E0D25]/80">Customer Rating</div>
                    </div>
                </div>
            
        

        <!-- Floating Action Button for Quick Order -->
        <div class="fixed bottom-24 sm:bottom-20 lg:bottom-8 right-4 lg:right-8 z-40">
            <button onclick="openQuickOrder()" 
                    class="bg-[#6E0D25] text-white p-4 lg:p-5 rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 min-w-[56px] min-h-[56px] lg:min-w-[64px] lg:min-h-[64px] flex items-center justify-center shadow-sm hover:shadow-md active:scale-[0.95]">
                <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </button>
        </div>
    </div>
</section> <?php /**PATH /var/www/my_momo_shop/resources/views/home/sections/hero.blade.php ENDPATH**/ ?>