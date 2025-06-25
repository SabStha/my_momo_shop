<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ama Ko Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/theme.css')); ?>">
</head>
<body class="min-h-screen bg-[url('/images/back.png')] bg-cover bg-center bg-fixed text-gray-800">

    
    <?php echo $__env->make('partials.topnav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <main class="pt-8 pb-8 px-1">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <?php echo $__env->make('partials.bottomnav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="<?php echo e(asset('js/home.js')); ?>"></script>
    <script src="<?php echo e(asset('js/special-offers.js')); ?>"></script>
    <script src="<?php echo e(asset('js/cart.js')); ?>"></script>

</body>
</html>
<?php /**PATH C:\Users\sabst\momo_shop\resources\views/layouts/app.blade.php ENDPATH**/ ?>