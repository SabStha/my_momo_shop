<!-- Cart Modal -->
<div id="cart-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-2 sm:p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-2 sm:mx-4 max-h-[90vh] transform transition-all duration-300 scale-95 opacity-0 flex flex-col" id="cart-modal-content">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Added to Cart!</h3>
                        <p class="text-sm text-gray-600">Item successfully added</p>
                    </div>
                </div>
                <button onclick="closeCartModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-1">
                <!-- Added Item Details -->
                <div id="added-item-details" class="mb-6">
                    <!-- This will be populated by JavaScript -->
                </div>

                <!-- Package Contents (for bulk packages) -->
                <div id="package-contents" class="mb-6 hidden">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-[#6E0D25]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h4 class="text-sm font-semibold text-gray-900">What's included in this pack:</h4>
                    </div>
                    <div id="package-items-list" class="space-y-2">
                        <!-- Package items will be populated here -->
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Cart Total:</span>
                        <span class="text-lg font-bold text-[#6E0D25]" id="cart-total-amount">$0.00</span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <span id="cart-item-count">0 items</span>
                        <span id="cart-shipping-info">Free delivery</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button onclick="viewCart()" 
                            class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                        </svg>
                        View Cart (<span id="cart-modal-item-count">0</span>)
                    </button>
                    
                    <button onclick="checkout()" 
                            class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-300 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Checkout Now
                    </button>
                    
                    <button onclick="closeCartModal()" 
                            class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-300">
                        Continue Shopping
                    </button>
                </div>

                <!-- Quick Add Suggestions -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">You might also like:</h4>
                    <div id="quick-add-suggestions" class="grid grid-cols-2 gap-2">
                        <!-- This will be populated with suggested items -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Toast Notification - REMOVED --> 