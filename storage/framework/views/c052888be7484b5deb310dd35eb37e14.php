<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Vite Assets -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 min-h-screen bg-gray-800 text-white">
                <div class="p-4 text-center">
                    <h4 class="text-xl font-bold">Momo Shop</h4>
                    <p class="text-gray-400">Admin Panel</p>
                </div>
                <nav class="mt-4">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" 
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </a>
                    <a href="<?php echo e(route('admin.products.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.products.*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-box mr-3"></i> Products
                    </a>
                    <a href="<?php echo e(route('admin.orders.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.orders.*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-shopping-cart mr-3"></i> Shop Orders
                    </a>
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cashier')): ?>
                    <a href="<?php echo e(route('admin.payment-manager')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.payment-manager*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-credit-card mr-3"></i> Payment Management
                    </a>
                    <a href="/pos"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                        <i class="fas fa-cash-register mr-3"></i> POS
                    </a>
                    <a href="<?php echo e(route('admin.pos-access-logs')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.pos-access-logs') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-history mr-3"></i> POS Access Logs
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('home')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                        <i class="fas fa-store mr-3"></i> View Shop
                    </a>
                    <a href="<?php echo e(route('admin.employees.schedules.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->is('admin/employees/schedules*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-calendar-alt mr-3"></i> Employee Schedule
                    </a>
                    <a href="<?php echo e(route('admin.inventory.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->is('admin/inventory*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-warehouse mr-3"></i> Inventory
                    </a>
                    <a href="<?php echo e(route('admin.roles.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.roles.*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-user-shield mr-3"></i> Role & Permission Management
                    </a>
                    <a href="<?php echo e(route('admin.wallet.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.wallet.*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-wallet mr-3"></i> Wallet Management
                    </a>
                    <a href="<?php echo e(route('admin.creator-dashboard.index')); ?>"
                       class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 <?php echo e(request()->routeIs('admin.creator-dashboard.*') ? 'bg-gray-700' : ''); ?>">
                        <i class="fas fa-user-edit mr-3"></i> Manage Creators
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-auto">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 text-left">
                            <i class="fas fa-sign-out-alt mr-3"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Top Navbar -->
                <nav class="bg-white shadow">
                    <div class="px-4 py-3">
                        <div class="flex justify-between items-center">
                            <h1 class="text-xl font-semibold"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
                            <div class="flex items-center">
                                <span class="text-gray-600">Welcome, <?php echo e(Auth::user()->name); ?></span>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <main class="p-6">
                    <?php echo $__env->yieldContent('content'); ?>
                </main>
            </div>
        </div>
    </div>
</body>
</html> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/layouts/admin.blade.php ENDPATH**/ ?>