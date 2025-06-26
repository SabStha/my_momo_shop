<!-- resources/views/partials/menu-item.blade.php -->
<div class="bg-white p-4 rounded-xl shadow-md flex gap-4">
<img src="<?php echo e(asset('storage/' . $item->image)); ?>" alt="<?php echo e($item->name); ?>" class="w-full h-40 object-cover rounded-xl shadow-md" />

    <div>
        <h3 class="text-lg font-bold text-[#2E2E2E]"><?php echo e($item->name); ?></h3>
        <p class="text-sm text-gray-600"><?php echo e($item->description); ?></p>
        <div class="text-[#6E0D25] font-semibold mt-1">₱<?php echo e(number_format($item->price, 2)); ?></div>
    </div>
</div>
<?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/partials/menu-card.blade.php ENDPATH**/ ?>