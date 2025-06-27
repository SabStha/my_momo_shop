<?php $__env->startSection('content'); ?>
<main class="pb-4 min-h-screen">
    
    <!-- Hero Section -->
    <?php echo $__env->make('home.sections.hero', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <!-- Featured Products -->
    <?php echo $__env->make('home.sections.featured-products', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <!-- Why Choose Us & Success Story -->
    <?php echo $__env->make('home.sections.why-choose-us', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <!-- Customer Reviews -->
    <?php echo $__env->make('home.sections.customer-reviews', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <!-- Shop Info -->
    <?php echo $__env->make('home.sections.shop-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</main>

<!-- Quick Order Modal -->
<?php echo $__env->make('home.components.quick-order-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Add to Cart Success Toast -->
<?php echo $__env->make('home.components.cart-toast', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('meta'); ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="theme-color" content="#6E0D25">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/my_momo_shop/resources/views/home.blade.php ENDPATH**/ ?>