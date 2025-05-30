<div class="p-5 text-center">
    <h1>FEATURED</h1>
    <div class="row justify-content-center">
        <?php $__currentLoopData = $featuredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo e(asset('storage/' . $item->image)); ?>" class="card-img-top" alt="<?php echo e($item->name); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo e($item->name); ?></h5>
                        <p class="card-text"><?php echo e($item->tagline); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\Users\sabst\momo_shop\resources\views/menu/_featured.blade.php ENDPATH**/ ?>