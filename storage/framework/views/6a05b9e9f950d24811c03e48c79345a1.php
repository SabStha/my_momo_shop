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
</div> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/partials/bottomnav.blade.php ENDPATH**/ ?>