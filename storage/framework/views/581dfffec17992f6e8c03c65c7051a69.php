<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#000000">
    <link rel="manifest" href="<?php echo e(url('/manifest.json')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(url('/images/icons/icon-192x192.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/vue@3.4.15/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('css/theme.css')); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background-color: #fffaf3; color: #6e3d1b;">
    <div class="position-relative">
        <?php if(!isset($hideTopNav)): ?>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container d-flex justify-content-between align-items-center">
                
                <a class="navbar-brand fw-bold d-flex align-items-center" href="#" style="font-size: 1.8rem; color: #fff;">
                    <img src="<?php echo e(url('storage/logo/momo_icon.png')); ?>" alt="Momo Icon" style="height: 50px; margin-right: 2px;">
                    AmaKo MOMO
                </a>

                
                <div class="d-flex justify-content-end align-items-center gap-3">
                    
                    <a href="<?php echo e(route('notifications')); ?>" class="text-white position-relative">
                        <i class="fas fa-bell fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>

                    
                    <a href="<?php echo e(route('cart')); ?>" class="text-white position-relative">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                            2
                        </span>
                    </a>
                </div>
            </div>
        </nav>
        <?php endif; ?>


        <main class="py-0">
            
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <div class="bottom-nav">
            <a href="<?php echo e(route('home')); ?>" class="nav-item <?php echo e(request()->is('/') ? 'active' : ''); ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="<?php echo e(route('offers')); ?>" class="nav-item <?php echo e(request()->is('offers') ? 'active' : ''); ?>">
                <i class="fas fa-gift"></i>
                <span>Offers</span>
            </a>
            <a href="<?php echo e(route('menu')); ?>" class="nav-item <?php echo e(request()->is('menu') ? 'active' : ''); ?>">
                <i class="fas fa-utensils"></i>
                <span>Menu</span>
            </a>
            <a href="<?php echo e(route('cart')); ?>" class="nav-item <?php echo e(request()->is('cart') ? 'active' : ''); ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
            </a>
            <a href="<?php echo e(route('account')); ?>" class="nav-item <?php echo e(request()->is('account') ? 'active' : ''); ?>">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
        </div>
    </div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/layouts/app.blade.php ENDPATH**/ ?>