
<nav class="fixed top-0 left-0 right-0 z-50 bg-white border-b shadow flex justify-between items-center px-4 py-2">
    <!-- Shop Name -->
    <a href="<?php echo e(route('home')); ?>" class="text-lg font-bold text-[#6E0D25]">
        Ama Ko Shop
    </a>

    <!-- Notification & Cart Icons -->
    <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14v-3a6 6 0 00-4-5.7V4a2 2 0 10-4 0v1.3A6 6 0 006 11v3a2 2 0 01-.6 1.4L4 17h5"/>
        </svg>

        <!-- Cart Icon (bold, rounded) -->
        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 3h1.5l1.5 13.5h12.75l1.5-9H5.25" />
            <circle cx="9" cy="20" r="1.25"/>
            <circle cx="17" cy="20" r="1.25"/>
        </svg>

    </div>
</nav>
<?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/partials/topnav.blade.php ENDPATH**/ ?>