
<nav class="fixed top-0 left-0 right-0 z-50 bg-[#6E0D25]/80 backdrop-blur-md text-white flex justify-between items-center px-4 py-2 h-12 min-h-[48px]">
    <!-- Shop Logo -->
    <a href="<?php echo e(route('home')); ?>" class="flex items-center h-full">
    <img 
    src="<?php echo e(asset('storage/logo/momokologo.png')); ?>" 
    alt="Ama Ko Momo Logo" 
    class="h-[100px] w-auto object-contain drop-shadow-lg"
    />
</a>

    <!-- Notification & Cart Icons -->
    <div class="flex items-center gap-4 relative">
        <!-- Enhanced Notification Bell with Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="focus:outline-none relative group">
                <!-- Heroicons Bell Outline with enhanced styling -->
                <svg class="w-6 h-6 text-white hover:text-[#FFD700] transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 01-3.46 0" />
                </svg>
                <!-- Animated notification dot -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            </button>
            <div x-show="open" @click.away="open = false" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                 class="absolute right-0 mt-3 w-80 max-w-xs bg-white/95 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl z-50 overflow-hidden" 
                 style="display: none;">
                
                <!-- Enhanced Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/10 bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] rounded-t-2xl">
                    <div class="flex items-center gap-2">
                        <span class="text-xl animate-bounce">🎁</span>
                        <div>
                            <span class="font-bold text-white text-base">Special Offers</span>
                            <div class="text-white/80 text-xs"><?php echo e(isset($activeOffers) ? $activeOffers->count() : 4); ?> active deals</div>
                        </div>
                    </div>
                    <button @click="open = false" class="text-white/70 hover:text-white transition-colors duration-200 hover:scale-110">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Enhanced Offers Container -->
                <div class="p-4 bg-white rounded-b-2xl max-h-[70vh] overflow-y-auto">
                    <div class="space-y-3">
                        <?php if(isset($activeOffers) && $activeOffers->count() > 0): ?>
                            <?php $__currentLoopData = $activeOffers->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-[#6E0D25]/30" 
                                     style="animation-delay: <?php echo e($index * 0.1); ?>s;">
                                    
                                    <!-- Enhanced Animated Background -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#6E0D25]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                    
                                    <!-- Enhanced Badge -->
                                    <div class="absolute top-2 right-2 bg-[#6E0D25] text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                        <?php echo e(strtoupper(substr($offer->title, 0, 3))); ?>

                                    </div>
                                    
                                    <!-- Enhanced Icon -->
                                    <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: <?php echo e($index * 0.2); ?>s;">
                                        <?php switch(strtolower($offer->title)):
                                            case ('first order discount'): ?>
                                                🎉
                                                <?php break; ?>
                                            <?php case ('combo deal'): ?>
                                                🥟
                                                <?php break; ?>
                                            <?php case ('weekend special'): ?>
                                                🌅
                                                <?php break; ?>
                                            <?php case ('loyalty rewards'): ?>
                                                👑
                                                <?php break; ?>
                                            <?php case ('bulk discount'): ?>
                                                📦
                                                <?php break; ?>
                                            <?php case ('flash sale'): ?>
                                                ⚡
                                                <?php break; ?>
                                            <?php default: ?>
                                                🎁
                                        <?php endswitch; ?>
                                    </div>
                                    
                                    <!-- Enhanced Content -->
                                    <h4 class="font-bold text-sm mb-1 group-hover:text-[#6E0D25] transition-colors"><?php echo e($offer->title); ?></h4>
                                    <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                        <?php echo e(Str::limit($offer->description, 50)); ?>

                                        <?php if($offer->code): ?>
                                            <br><span class="font-mono bg-[#6E0D25]/10 text-[#6E0D25] px-1.5 py-0.5 rounded text-xs hover:bg-[#6E0D25]/20 transition-colors cursor-pointer" onclick="copyToClipboard('<?php echo e($offer->code); ?>')" title="Click to copy"><?php echo e($offer->code); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <!-- Enhanced Progress Bar for Limited Offers -->
                                    <?php if($offer->valid_until): ?>
                                        <div class="mb-2">
                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                <span>⏰ Ends <?php echo e($offer->valid_until->diffForHumans()); ?></span>
                                                <span class="text-[#6E0D25] font-semibold"><?php echo e($offer->discount); ?>% OFF</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                                <?php
                                                    $totalDuration = $offer->valid_until->diffInSeconds($offer->valid_from);
                                                    $remainingDuration = $offer->valid_until->diffInSeconds(now());
                                                    $progressPercentage = max(0, min(100, (($totalDuration - $remainingDuration) / $totalDuration) * 100));
                                                ?>
                                                <div class="bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] h-1 rounded-full transition-all duration-1000 relative overflow-hidden" style="width: <?php echo e($progressPercentage); ?>%">
                                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Enhanced Button -->
                                    <button onclick="claimOffer('<?php echo e($offer->code); ?>', this)" 
                                            class="w-full bg-[#6E0D25] text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-[#8B0D2F] transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                        <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Claim Offer</span>
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                    </button>
                                    
                                    <!-- Success Indicator -->
                                    <div class="absolute top-1 left-1 w-1.5 h-1.5 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <!-- Enhanced Fallback Hardcoded Offers -->
                            <!-- First Order Discount -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-[#6E0D25]/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#6E0D25]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-[#6E0D25] text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm animate-bounce">
                                    NEW
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce">🎉</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-[#6E0D25] transition-colors">First Order 20% Off</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    New customers get 20% off. Use code: <span class="font-mono bg-[#6E0D25]/10 text-[#6E0D25] px-1.5 py-0.5 rounded text-xs hover:bg-[#6E0D25]/20 transition-colors cursor-pointer" onclick="copyToClipboard('WELCOME20')" title="Click to copy">WELCOME20</span>
                                </p>
                                
                                <div class="mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-500">Claimed: 847</span>
                                        <span class="text-[#6E0D25] font-semibold">20% OFF</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                        <div class="bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] h-1 rounded-full transition-all duration-1000 relative overflow-hidden" style="width: 85%">
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button onclick="claimOffer('WELCOME20', this)" 
                                        class="w-full bg-[#6E0D25] text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-[#8B0D2F] transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Claim Offer</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                                
                                <div class="absolute top-1 left-1 w-1.5 h-1.5 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>

                            <!-- Combo Deal -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-[#6E0D25]/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#6E0D25]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-[#6E0D25] text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                    🔥 POPULAR
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: 0.2s;">🥟</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-[#6E0D25] transition-colors">Buy 2 Get 1 Free</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    Order any 2 momo dishes and get 1 free!
                                </p>
                                
                                <div class="mb-2">
                                    <div class="inline-flex items-center gap-1 bg-[#6E0D25]/10 px-2 py-0.5 rounded-full text-xs group-hover:bg-[#6E0D25]/20 transition-colors">
                                        <span>💰</span>
                                        <span class="text-[#6E0D25]">Save up to $8.99</span>
                                    </div>
                                </div>
                                
                                <button onclick="addComboToCart('bogo', this)" 
                                        class="w-full bg-[#6E0D25] text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-[#8B0D2F] transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Order Now</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                                
                                <div class="absolute top-1 left-1 w-1.5 h-1.5 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>

                            <!-- Flash Sale -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-[#6E0D25]/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#6E0D25]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm animate-pulse">
                                    ⚡ FLASH
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: 0.4s;">⚡</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-[#6E0D25] transition-colors">Flash Sale - 30% Off</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    Limited time! 30% off all steamed momos.
                                </p>
                                
                                <div class="mb-2">
                                    <div class="text-xs text-gray-500 mb-1">⏰ Time Remaining</div>
                                    <div class="flex gap-1" id="flash-sale-timer">
                                        <div class="bg-[#6E0D25]/10 text-[#6E0D25] rounded px-1.5 py-0.5 text-xs font-mono animate-pulse">02</div>
                                        <div class="bg-[#6E0D25]/10 text-[#6E0D25] rounded px-1.5 py-0.5 text-xs font-mono animate-pulse" style="animation-delay: 0.5s;">00</div>
                                        <div class="bg-[#6E0D25]/10 text-[#6E0D25] rounded px-1.5 py-0.5 text-xs font-mono animate-pulse" style="animation-delay: 1s;">00</div>
                                    </div>
                                </div>
                                
                                <button onclick="addFlashSale(this)" 
                                        class="w-full bg-[#6E0D25] text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-[#8B0D2F] transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Shop Now</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                                
                                <div class="absolute top-1 left-1 w-1.5 h-1.5 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>

                            <!-- Loyalty Program -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-[#6E0D25]/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#6E0D25]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-[#6E0D25] text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                    👑 LOYALTY
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: 0.6s;">👑</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-[#6E0D25] transition-colors">Earn Points & Save</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    Join our loyalty program. 100 points = $5 off!
                                </p>
                                
                                <div class="mb-2">
                                    <div class="flex items-center gap-1 text-xs">
                                        <div class="w-4 h-4 bg-[#6E0D25]/10 rounded-full flex items-center justify-center animate-pulse">🎯</div>
                                        <span class="text-gray-500">Join 2,847 members</span>
                                    </div>
                                </div>
                                
                                <button onclick="joinLoyalty(this)" 
                                        class="w-full bg-[#6E0D25] text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-[#8B0D2F] transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Join Now</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                                
                                <div class="absolute top-1 left-1 w-1.5 h-1.5 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        <?php endif; ?>

                        <!-- Enhanced View All Offers Link -->
                        <div class="text-center pt-3 border-t border-gray-200">
                            <a href="<?php echo e(route('home')); ?>" 
                               class="inline-flex items-center gap-1 text-[#6E0D25] text-xs font-semibold hover:text-[#8B0D2F] transition-colors duration-300 group/link">
                                <span>View All Offers</span>
                                <span class="group-hover/link:translate-x-0.5 transition-transform duration-300">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Icon (clickable, Heroicons outline) -->
        <a href="<?php echo e(route('cart')); ?>" class="focus:outline-none relative group">
            <svg class="w-6 h-6 text-white hover:text-[#FFD700] transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4" />
              <circle cx="7" cy="21" r="1.5" />
              <circle cx="17" cy="21" r="1.5" />
            </svg>
            <!-- Cart notification dot -->
            <div class="cart-count absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse" style="display: none;">0</div>
        </a>

    </div>
</nav>

<!-- Cart Modal -->
<?php echo $__env->make('components.cart-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH C:\Users\sabst\momo_shop\resources\views/partials/topnav.blade.php ENDPATH**/ ?>