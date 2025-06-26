<!-- Add to Cart Success Toast -->
<div id="cart-toast" class="fixed top-16 sm:top-20 right-2 sm:right-4 z-50 hidden">
    <div class="bg-green-500 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-lg shadow-lg flex items-center gap-2 sm:gap-3 animate-slide-in max-w-[calc(100vw-1rem)] sm:max-w-sm">
        <div class="text-xl sm:text-2xl flex-shrink-0">✅</div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm sm:text-base">Added to Cart!</div>
            <div class="text-xs sm:text-sm opacity-90 truncate" id="cart-toast-message">Item has been added to your cart</div>
        </div>
        <button onclick="hideCartToast()" class="ml-2 sm:ml-4 text-white hover:text-gray-200 p-1 min-w-[32px] min-h-[32px] flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<!-- Error Toast -->
<div id="error-toast" class="fixed top-16 sm:top-20 right-2 sm:right-4 z-50 hidden">
    <div class="bg-red-500 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-lg shadow-lg flex items-center gap-2 sm:gap-3 animate-slide-in max-w-[calc(100vw-1rem)] sm:max-w-sm">
        <div class="text-xl sm:text-2xl flex-shrink-0">❌</div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm sm:text-base">Error!</div>
            <div class="text-xs sm:text-sm opacity-90 truncate" id="error-toast-message">Something went wrong</div>
        </div>
        <button onclick="hideErrorToast()" class="ml-2 sm:ml-4 text-white hover:text-gray-200 p-1 min-w-[32px] min-h-[32px] flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loading-spinner" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 flex items-center gap-3 max-w-sm w-full">
        <div class="animate-spin rounded-full h-5 w-5 sm:h-6 sm:w-6 border-b-2 border-[#6E0D25] flex-shrink-0"></div>
        <span class="text-gray-700 text-sm sm:text-base">Processing...</span>
    </div>
</div> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/home/components/cart-toast.blade.php ENDPATH**/ ?>