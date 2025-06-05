<div class="bottom-nav">
    <a href="<?php echo e(route('home')); ?>" class="nav-item <?php echo e(request()->is('/') ? 'active' : ''); ?>">
        <i class="fas fa-home"></i>
        <div>Home</div>
    </a>
    <a href="<?php echo e(route('offers')); ?>" class="nav-item <?php echo e(request()->is('offers') ? 'active' : ''); ?>">
        <i class="fas fa-gift"></i>
        <div>Offers</div>
    </a>
    <a href="<?php echo e(route('menu')); ?>" class="nav-item <?php echo e(request()->is('menu') ? 'active' : ''); ?>">
        <i class="fas fa-utensils"></i>
        <div>Menu</div>
    </a>
    <a href="<?php echo e(route('cart')); ?>" class="nav-item <?php echo e(request()->is('cart') ? 'active' : ''); ?>">
        <i class="fas fa-shopping-cart"></i>
        <div>Cart</div>
    </a>
</div> <?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/partials/bottomnav.blade.php ENDPATH**/ ?>