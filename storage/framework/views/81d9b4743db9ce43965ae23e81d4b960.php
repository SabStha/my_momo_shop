

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h2 class="mb-4 text-center">Menu</h2>
    <div class="text-center mb-4">
        <button class="btn btn-outline-dark filter-btn me-2 active" data-filter="all">All</button>
        <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button class="btn btn-outline-dark filter-btn me-2" data-filter="<?php echo e($tag); ?>"><?php echo e(ucwords(str_replace('_', ' ', $tag))); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="row g-4" id="menuGrid">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 menu-item" data-tag="<?php echo e(strtolower($product->tag)); ?>">
            <div class="card h-100 shadow-sm">
                <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="card-img-top" alt="<?php echo e($product->name); ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo e($product->name); ?></h5>
                    <div class="mb-2 fw-bold">$<?php echo e(number_format($product->price, 2)); ?></div>
                    <div class="mt-auto d-flex gap-2">
                        <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-sm btn-danger flex-fill">View Details</a>
                        <form action="<?php echo e(route('checkout.buyNow', $product)); ?>" method="POST" class="flex-fill m-0 p-0">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-sm btn-danger flex-fill">Buy Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const menuItems = document.querySelectorAll('.menu-item');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelector('.filter-btn.active')?.classList.remove('active');
            btn.classList.add('active');
            const filter = btn.getAttribute('data-filter');
            menuItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-tag') === filter) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/menu.blade.php ENDPATH**/ ?>