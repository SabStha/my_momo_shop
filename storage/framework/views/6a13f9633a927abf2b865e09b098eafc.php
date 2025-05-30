<?php $__env->startSection('content'); ?>
<div class="login-wrapper" style="min-height: 100vh; background: url('<?php echo e(asset('storage/products/background.png')); ?>') center/cover no-repeat; display: flex; justify-content: center; align-items: center; flex-direction: column;">

    
    <div class="text-center mb-4">
        <img src="<?php echo e(asset('storage/logo/momo_icon.png')); ?>" alt="AmaKo MOMO" style="height: 60px;">
        <h2 class="mt-2" style="font-weight: bold; color: #fff; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">AmaKo MOMO</h2>
    </div>

    
    <div class="login-card" style="background-color: #fffaf3; border-radius: 20px; padding: 2rem 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-width: 400px; width: 100%;">
        
        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label for="email" class="form-label" style="color: #6e3d1b;">Email address</label>
                <input type="email" name="email" id="email" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label" style="color: #6e3d1b;">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn w-100" style="background-color: #6e3d1b; color: #fff; font-weight: bold;">
                Log in
            </button>
        </form>

        <div class="text-center mt-4">
            <span style="color: #6e3d1b;">Don’t have an account?</span>
            <a href="<?php echo e(route('register')); ?>" style="color: #a04d1a; font-weight: bold;">Sign up</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/auth/login.blade.php ENDPATH**/ ?>