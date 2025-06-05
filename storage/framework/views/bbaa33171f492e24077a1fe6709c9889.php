<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AMAKO MOMO</title>

    <link rel="stylesheet" href="<?php echo e(mix('css/app.css')); ?>">
    <script src="<?php echo e(mix('js/app.js')); ?>" defer></script>

    <!-- Font Awesome (if needed) -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
        crossorigin="anonymous"
    />

    <style>
        :root {
            --top-nav-height: 70px;
            --bottom-nav-height: 65px;
            --brand-color: #6E0D25;
            --highlight-color: #FFFFB3;
        }
    </style>
</head>
<body class="bg-[#fffaf3] text-[#6e3d1b] font-sans">

    <!-- Top Navigation -->
    <?php if (! (isset($hideTopNav))): ?>
    <nav class="fixed top-0 left-0 w-full h-[var(--top-nav-height)] bg-[#6E0D25] text-white z-50 shadow">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center space-x-2 text-white text-xl font-bold">
                <img src="<?php echo e(url('storage/logo/momo_icon.png')); ?>" alt="Logo" class="h-10 w-10 object-contain">
                <span>AmaKo MOMO</span>
            </a>

            <div class="flex items-center gap-4">
                <a href="<?php echo e(route('notifications')); ?>" class="relative">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1">3</span>
                </a>
                <a href="<?php echo e(route('cart')); ?>" class="relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="absolute -top-1 -right-2 bg-yellow-400 text-black text-xs rounded-full px-1">2</span>
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Page Content -->
    <main class="pt-[var(--top-nav-height)] pb-[var(--bottom-nav-height)] min-h-screen">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Bottom Navigation -->
    <?php if (! (isset($hideBottomNav))): ?>
    <div class="fixed bottom-0 left-0 w-full h-[var(--bottom-nav-height)] bg-[#6E0D25] text-white flex justify-around items-center z-50 shadow-inner">
        <a href="<?php echo e(route('menu')); ?>" class="flex flex-col items-center text-xs <?php echo e(request()->is('menu') ? 'text-yellow-200 font-semibold' : ''); ?>">
            <i class="fas fa-utensils text-lg"></i>
            <span>Menu</span>
        </a>
        <a href="<?php echo e(route('bulk')); ?>" class="flex flex-col items-center text-xs <?php echo e(request()->is('bulk') ? 'text-yellow-200 font-semibold' : ''); ?>">
            <i class="fas fa-box-open text-lg"></i>
            <span>Bulk</span>
        </a>
        <a href="<?php echo e(route('finds')); ?>" class="flex flex-col items-center text-xs <?php echo e(request()->is('finds') ? 'text-yellow-200 font-semibold' : ''); ?>">
            <i class="fas fa-dumpster text-lg"></i>
            <span>AmaKo Finds</span>
        </a>
        <a href="<?php echo e(route('search')); ?>" class="flex flex-col items-center text-xs <?php echo e(request()->is('search') ? 'text-yellow-200 font-semibold' : ''); ?>">
            <i class="fas fa-search text-lg"></i>
            <span>Search</span>
        </a>
        <a href="<?php echo e(route('account')); ?>" class="flex flex-col items-center text-xs <?php echo e(request()->is('account') ? 'text-yellow-200 font-semibold' : ''); ?>">
            <i class="fas fa-user text-lg"></i>
            <span>Account</span>
        </a>
    </div>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/layouts/app.blade.php ENDPATH**/ ?>