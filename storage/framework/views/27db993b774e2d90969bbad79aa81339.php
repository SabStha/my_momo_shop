<?php $__env->startSection('content'); ?>
<div class="bg-gradient-to-br from-red-100 to-yellow-50 pt-12 pb-20 px-4 sm:px-6 lg:px-8 min-h-[calc(100vh-8rem)]">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 items-center gap-10">
        
        <div class="text-center lg:text-left space-y-6">
            <h1 class="text-3xl sm:text-4xl lg:text-6xl font-extrabold text-[#6E0D25] mb-4 leading-tight">
                Nepal's Favorite MOMO!
            </h1>
            <p class="text-base sm:text-lg lg:text-2xl text-[#6e3d1b] mb-6">
                Freshly handmade with love at <strong>AmaKo MOMO</strong>.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="<?php echo e(route('menu')); ?>"
                   class="inline-block bg-[#6E0D25] hover:bg-[#891234] text-white px-8 py-4 rounded-xl font-semibold text-sm sm:text-base md:text-lg transition transform hover:scale-105">
                    View Our Menu
                </a>
                <a href="<?php echo e(route('bulk')); ?>"
                   class="inline-block bg-white hover:bg-gray-50 text-[#6E0D25] border-2 border-[#6E0D25] px-8 py-4 rounded-xl font-semibold text-sm sm:text-base md:text-lg transition transform hover:scale-105">
                    Bulk Orders
                </a>
            </div>

            
            <div class="text-red-500 font-bold mt-4 text-lg md:text-xl">
                TAILWIND IS WORKING
            </div>
        </div>

        
        <div class="flex justify-center lg:justify-end">
            <div class="relative">
                <img src="<?php echo e(asset('storage/products/momo1.jpg')); ?>"
                     alt="Delicious momo"
                     class="rounded-3xl shadow-lg w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-xl xl:max-w-2xl object-cover transform hover:scale-105 transition duration-300" />
                <div class="absolute -bottom-4 -right-4 bg-yellow-400 text-[#6E0D25] px-6 py-3 rounded-xl font-bold text-lg shadow-lg">
                    Best Seller!
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/home.blade.php ENDPATH**/ ?>