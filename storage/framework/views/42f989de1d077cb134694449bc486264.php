<div class="p-5 text-center">
    <h1>DRINKS</h1>
    <div class="row justify-content-center">
        <?php $__currentLoopData = $drinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $drink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="<?php echo e(asset('storage/' . $drink->image)); ?>" class="card-img-top" alt="<?php echo e($drink->name); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo e($drink->name); ?></h5>
                        <p class="card-text">Rs. <?php echo e($drink->price); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\Users\sabst\momo_shop\resources\views/menu/_drinks.blade.php ENDPATH**/ ?>