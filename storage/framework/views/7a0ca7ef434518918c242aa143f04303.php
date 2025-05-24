<?php $__env->startSection('content'); ?>
<div class="w-full px-4 py-6">
    <div id="pos-app" class="max-w-screen-xl mx-auto"></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo app('Illuminate\Foundation\Vite')('resources/js/app.js'); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/pos.blade.php ENDPATH**/ ?>