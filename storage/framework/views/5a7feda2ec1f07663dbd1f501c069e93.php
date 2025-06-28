<!-- Quick Order Modal -->
<div id="quick-order-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-2 sm:p-4">
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-4 sm:p-6 border-b sticky top-0 bg-white z-10">
                <h3 class="text-lg sm:text-xl font-bold text-[#6E0D25]">Quick Order</h3>
                <button onclick="closeQuickOrder()" class="text-gray-500 hover:text-gray-700 p-2 min-w-[44px] min-h-[44px] flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-4 sm:p-6">
                <!-- Popular Items -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Popular Items</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 text-sm sm:text-base">Steamed Chicken Momos</div>
                                <div class="text-xs sm:text-sm text-gray-600">6 pieces</div>
                            </div>
                            <div class="flex items-center gap-2 ml-3">
                                <span class="font-bold text-[#6E0D25] text-sm sm:text-base">$8.99</span>
                                <button onclick="addToQuickOrder('steamed-chicken', 8.99)" 
                                        class="bg-[#6E0D25] text-white px-3 py-2 rounded-lg text-xs sm:text-sm hover:bg-[#8B0D2F] transition-colors min-h-[36px] min-w-[50px]">
                                    Add
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 text-sm sm:text-base">Spicy Chicken Momos</div>
                                <div class="text-xs sm:text-sm text-gray-600">6 pieces</div>
                            </div>
                            <div class="flex items-center gap-2 ml-3">
                                <span class="font-bold text-[#6E0D25] text-sm sm:text-base">$9.99</span>
                                <button onclick="addToQuickOrder('spicy-chicken', 9.99)" 
                                        class="bg-[#6E0D25] text-white px-3 py-2 rounded-lg text-xs sm:text-sm hover:bg-[#8B0D2F] transition-colors min-h-[36px] min-w-[50px]">
                                    Add
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 text-sm sm:text-base">Veg Momos</div>
                                <div class="text-xs sm:text-sm text-gray-600">6 pieces</div>
                            </div>
                            <div class="flex items-center gap-2 ml-3">
                                <span class="font-bold text-[#6E0D25] text-sm sm:text-base">$7.99</span>
                                <button onclick="addToQuickOrder('veg-momos', 7.99)" 
                                        class="bg-[#6E0D25] text-white px-3 py-2 rounded-lg text-xs sm:text-sm hover:bg-[#8B0D2F] transition-colors min-h-[36px] min-w-[50px]">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Combos -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Quick Combos</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 text-sm sm:text-base">Student Set</div>
                                <div class="text-xs sm:text-sm text-gray-600">Momos + Fries + Drink</div>
                            </div>
                            <div class="flex items-center gap-2 ml-3">
                                <span class="font-bold text-[#6E0D25] text-sm sm:text-base">$12.99</span>
                                <button onclick="addToQuickOrder('student-set', 12.99)" 
                                        class="bg-[#6E0D25] text-white px-3 py-2 rounded-lg text-xs sm:text-sm hover:bg-[#8B0D2F] transition-colors min-h-[36px] min-w-[50px]">
                                    Add
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 text-sm sm:text-base">Office Worker Set</div>
                                <div class="text-xs sm:text-sm text-gray-600">Fried Momos + Sausage + Tea</div>
                            </div>
                            <div class="flex items-center gap-2 ml-3">
                                <span class="font-bold text-[#6E0D25] text-sm sm:text-base">$15.99</span>
                                <button onclick="addToQuickOrder('office-set', 15.99)" 
                                        class="bg-[#6E0D25] text-white px-3 py-2 rounded-lg text-xs sm:text-sm hover:bg-[#8B0D2F] transition-colors min-h-[36px] min-w-[50px]">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Order Summary -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Your Order</h4>
                    <div id="quick-order-items" class="space-y-2 mb-3">
                        <!-- Items will be added here dynamically -->
                    </div>
                    <div class="border-t pt-3">
                        <div class="flex justify-between font-bold text-base sm:text-lg">
                            <span>Total:</span>
                            <span id="quick-order-total" class="text-[#6E0D25]">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="viewFullMenu()" 
                            class="flex-1 bg-white text-[#6E0D25] border-2 border-[#6E0D25] py-3 rounded-lg font-semibold hover:bg-[#6E0D25] hover:text-white transition-colors text-sm sm:text-base min-h-[48px]">
                        View Full Menu
                    </button>
                    <button onclick="proceedToCheckout()" 
                            class="flex-1 bg-[#6E0D25] text-white py-3 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors text-sm sm:text-base min-h-[48px]">
                        Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/home/components/quick-order-modal.blade.php ENDPATH**/ ?>