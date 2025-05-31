<?php $__env->startSection('content'); ?>
    <div class="w-full px-4 py-6">
        <div id="pos-app" class="max-w-screen-xl mx-auto"></div>
    </div>
<?php $__env->stopSection(); ?>

<!-- Scripts -->
<script src="<?php echo e(mix('js/app.js')); ?>" defer></script>

<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/pos.blade.php ENDPATH**/ ?>