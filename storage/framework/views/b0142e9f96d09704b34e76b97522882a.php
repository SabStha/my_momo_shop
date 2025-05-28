

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title"><?php echo e($creator->user->name); ?></h1>
                    <p class="card-text">Code: <?php echo e($creator->code); ?></p>
                    <p class="card-text"><?php echo e($creator->bio); ?></p>
                    <?php if($creator->avatar): ?>
                        <img src="<?php echo e(asset('storage/' . $creator->avatar)); ?>" alt="<?php echo e($creator->user->name); ?>" class="img-fluid mb-3">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creators/show.blade.php ENDPATH**/ ?>