<style>
/* Mobile-specific improvements for custom builder */
@media (max-width: 640px) {
    .mobile-form-container {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    .mobile-form-text {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .mobile-form-flex {
        flex-direction: column;
    }
    
    .mobile-form-grid {
        grid-template-columns: 1fr;
    }
    
    .mobile-input {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>

<div x-data="customBulkBuilder()" class="space-y-6 sm:space-y-8 mobile-form-container">

    <!-- STEP 1: TYPE OF ORDER -->
    <div class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">🥟 Step 1: Type of Order</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mobile-form-grid">
            <label class="flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 min-h-[60px] sm:min-h-[80px]">
                <input type="radio" name="orderType" value="cooked" x-model="orderType" class="mr-3 mt-1 w-4 h-4">
                <div class="flex-1 min-w-0 mobile-form-text">
                    <div class="font-semibold text-sm sm:text-base">🔥 Cooked</div>
                    <div class="text-xs sm:text-sm text-gray-600">Hot and ready to eat</div>
                    <div x-show="orderType === 'cooked'" class="mt-2">
                        <input type="datetime-local" x-model="deliveryDateTime" class="w-full p-2 border rounded text-xs sm:text-sm mobile-input">
                    </div>
                </div>
            </label>
            <label class="flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 min-h-[60px] sm:min-h-[80px]">
                <input type="radio" name="orderType" value="frozen" x-model="orderType" class="mr-3 mt-1 w-4 h-4">
                <div class="flex-1 min-w-0 mobile-form-text">
                    <div class="font-semibold text-sm sm:text-base">❄️ Frozen</div>
                    <div class="text-xs sm:text-sm text-gray-600">Ready for your freezer</div>
                    <div x-show="orderType === 'frozen'" class="mt-2">
                        <select x-model="pickupOption" class="w-full p-2 border rounded text-xs sm:text-sm mobile-input">
                            <option value="pickup">Pickup from store</option>
                            <option value="delivery">Home delivery</option>
                        </select>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <!-- STEP 2: PACKAGING & USE -->
    <div class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">🧊 Step 2: Packaging & Use</h3>
        <select x-model="packagingType" class="w-full p-3 border rounded-lg text-sm sm:text-base min-h-[44px] mobile-input">
            <option value="">Select packaging type...</option>
            <option value="office">Office Lunch</option>
            <option value="party">Party/Event</option>
            <option value="home">Home Stock (Frozen)</option>
            <option value="gift">Gifting</option>
            <option value="restaurant">Restaurant resale (B2B)</option>
            <option value="other">Other</option>
        </select>
        <div x-show="packagingType === 'other'" class="mt-2">
            <input type="text" x-model="customPackaging" placeholder="Describe your packaging needs..." class="w-full p-3 border rounded-lg text-sm sm:text-base min-h-[44px] mobile-input">
        </div>
    </div>

    <!-- STEP 3: SELECT QUANTITY -->
    <div class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">📋 Step 3: Select Quantity</h3>
        <div class="space-y-3 sm:space-y-4">
            <template x-for="(item, index) in momoItems" :key="index">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 sm:p-4 border rounded-lg mobile-form-flex">
                    <div class="flex-1 min-w-0 mobile-form-text">
                        <label class="font-semibold text-sm sm:text-base block" x-text="item.name"></label>
                        <div class="text-xs sm:text-sm text-gray-600" x-text="'Rs. ' + item.price + ' per piece'"></div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-2">
                        <button @click="decreaseQuantity(index)" class="w-10 h-10 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 text-sm min-h-[44px] sm:min-h-[32px]">-</button>
                        <input type="number" x-model.number="item.quantity" min="0" class="w-20 sm:w-16 text-center border rounded p-2 sm:p-1 text-sm min-h-[44px] sm:min-h-[32px] mobile-input">
                        <button @click="increaseQuantity(index)" class="w-10 h-10 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 text-sm min-h-[44px] sm:min-h-[32px]">+</button>
                    </div>
                    <div class="text-right font-semibold text-sm sm:text-base flex-shrink-0" x-text="'Rs. ' + (item.price * item.quantity)"></div>
                </div>
            </template>
        </div>
    </div>

    <!-- STEP 4: ADD SIDE DISHES -->
    <div class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">🍛 Step 4: Add Side Dishes</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mobile-form-grid">
            <template x-for="(side, index) in sideDishes" :key="index">
                <div class="flex items-center justify-between p-3 border rounded-lg min-h-[60px]">
                    <div class="flex-1 min-w-0 mobile-form-text">
                        <label class="font-semibold text-sm sm:text-base block" x-text="side.name"></label>
                        <div class="text-xs sm:text-sm text-gray-600" x-text="'Rs. ' + side.price"></div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="decreaseSideQuantity(index)" class="w-8 h-8 sm:w-6 sm:h-6 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 text-xs sm:text-sm min-h-[44px] sm:min-h-[24px]">-</button>
                        <span x-text="side.quantity" class="w-10 sm:w-8 text-center text-sm min-h-[44px] sm:min-h-[24px] flex items-center justify-center"></span>
                        <button @click="increaseSideQuantity(index)" class="w-8 h-8 sm:w-6 sm:h-6 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 text-xs sm:text-sm min-h-[44px] sm:min-h-[24px]">+</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- STEP 5: SAUCE SELECTION -->
    <div x-show="orderType === 'cooked'" class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">🧴 Step 5: Sauce Selection (Required for cooked orders)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mobile-form-grid">
            <template x-for="(sauce, index) in sauces" :key="index">
                <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 min-h-[60px]">
                    <input type="radio" name="sauceType" :value="sauce.type" x-model="selectedSauce" class="mr-2 mt-1 w-4 h-4">
                    <div class="flex-1 min-w-0 mobile-form-text">
                        <div class="font-semibold text-sm sm:text-base" x-text="sauce.name"></div>
                        <div class="text-xs sm:text-sm text-gray-600" x-text="sauce.description"></div>
                    </div>
                </label>
            </template>
        </div>
        <div class="mt-3 sm:mt-4">
            <label class="block text-xs sm:text-sm font-medium mb-2">Sauce Quantity (1 pot per 10 momos)</label>
            <input type="number" x-model.number="sauceQuantity" min="1" class="w-28 sm:w-32 p-2 border rounded text-sm min-h-[44px] mobile-input">
            <div class="text-xs sm:text-sm text-gray-600 mt-1">Auto-calculated: <span x-text="Math.ceil(totalMomoQuantity / 10)"></span> pots</div>
        </div>
    </div>

    <!-- STEP 6: DELIVERY DETAILS -->
    <div x-show="orderType === 'cooked' || (orderType === 'frozen' && pickupOption === 'delivery')" class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">🕒 Step 6: Delivery Details</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mobile-form-grid">
            <div>
                <label class="block text-xs sm:text-sm font-medium mb-2">Delivery Area</label>
                <select x-model="deliveryArea" class="w-full p-3 border rounded-lg text-sm sm:text-base min-h-[44px] mobile-input">
                    <option value="">Select your area...</option>
                    <option value="kathmandu">Kathmandu</option>
                    <option value="lalitpur">Lalitpur</option>
                    <option value="bhaktapur">Bhaktapur</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium mb-2">Delivery Address</label>
                <textarea x-model="deliveryAddress" placeholder="Enter your full address..." class="w-full p-3 border rounded-lg h-20 text-sm sm:text-base min-h-[44px] mobile-input"></textarea>
            </div>
        </div>
        <div x-show="deliveryArea === 'other'" class="mt-3 sm:mt-4 p-3 sm:p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-yellow-800 text-xs sm:text-sm">📍 We'll contact you to confirm delivery availability for your area.</p>
        </div>
    </div>

    <!-- STEP 7: STORAGE PREFERENCE (FROZEN ONLY) -->
    <div x-show="orderType === 'frozen'" class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">❄️ Step 7: Storage Preference</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mobile-form-grid">
            <label class="flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 min-h-[60px] sm:min-h-[80px]">
                <input type="radio" name="storageType" value="pack10" x-model="storageType" class="mr-3 mt-1 w-4 h-4">
                <div class="flex-1 min-w-0 mobile-form-text">
                    <div class="font-semibold text-sm sm:text-base">Packed in 10s</div>
                    <div class="text-xs sm:text-sm text-gray-600">Standard packaging</div>
                </div>
            </label>
            <label class="flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 min-h-[60px] sm:min-h-[80px]">
                <input type="radio" name="storageType" value="pack20" x-model="storageType" class="mr-3 mt-1 w-4 h-4">
                <div class="flex-1 min-w-0 mobile-form-text">
                    <div class="font-semibold text-sm sm:text-base">Packed in 20s</div>
                    <div class="text-xs sm:text-sm text-gray-600">Larger portions</div>
                </div>
            </label>
            <label class="flex items-start p-3 sm:p-4 border rounded-lg cursor-pointer hover:bg-gray-50 min-h-[60px] sm:min-h-[80px]">
                <input type="radio" name="storageType" value="vacuum" x-model="storageType" class="mr-3 mt-1 w-4 h-4">
                <div class="flex-1 min-w-0 mobile-form-text">
                    <div class="font-semibold text-sm sm:text-base">Vacuum packed</div>
                    <div class="text-xs sm:text-sm text-gray-600">Extra Rs. 200</div>
                </div>
            </label>
        </div>
    </div>

    <!-- SPECIAL NOTES -->
    <div class="border-b pb-4 sm:pb-6">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">💬 Special Notes</h3>
        <textarea x-model="specialNotes" placeholder="Allergy, spice level, packaging, timing notes, etc..." class="w-full p-3 border rounded-lg h-24 text-sm sm:text-base min-h-[44px] mobile-input"></textarea>
    </div>

    <!-- PRICE ESTIMATE & CTA -->
    <div class="bg-gray-50 p-4 sm:p-6 rounded-lg">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">💳 Price Estimate</h3>
        <div class="space-y-2 mb-4 sm:mb-6">
            <div class="flex justify-between text-sm sm:text-base">
                <span>Momo Subtotal:</span>
                <span x-text="'Rs. ' + momoSubtotal"></span>
            </div>
            <div class="flex justify-between text-sm sm:text-base">
                <span>Side Dishes:</span>
                <span x-text="'Rs. ' + sideDishesSubtotal"></span>
            </div>
            <div x-show="orderType === 'cooked'" class="flex justify-between text-sm sm:text-base">
                <span>Sauces:</span>
                <span x-text="'Rs. ' + sauceSubtotal"></span>
            </div>
            <div x-show="storageType === 'vacuum'" class="flex justify-between text-sm sm:text-base">
                <span>Vacuum Packing:</span>
                <span>Rs. 200</span>
            </div>
            <div class="flex justify-between text-sm sm:text-base">
                <span>Delivery/Pickup:</span>
                <span x-text="deliveryCost"></span>
            </div>
            <hr class="my-2">
            <div class="flex justify-between font-bold text-base sm:text-lg">
                <span>Total:</span>
                <span class="text-[#6E0D25]" x-text="'Rs. ' + totalPrice"></span>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mobile-form-flex">
            <button @click="submitOrder()" class="flex-1 bg-[#6E0D25] text-white py-4 sm:py-3 rounded-lg hover:bg-[#8B1A3A] transition font-semibold text-sm sm:text-base min-h-[44px]">
                🛒 Add to Cart & Continue Shopping
            </button>
            <button @click="checkoutNow()" class="flex-1 bg-green-600 text-white py-4 sm:py-3 rounded-lg hover:bg-green-700 transition font-semibold text-sm sm:text-base min-h-[44px]">
                💳 Checkout Now
            </button>
        </div>
    </div>

    <!-- TRUST SECTION -->
    <div class="bg-blue-50 p-4 sm:p-6 rounded-lg">
        <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-3 sm:mb-4">🤝 Trust & Quality Guarantee</h3>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-center mobile-form-grid">
            <div>
                <div class="text-2xl sm:text-3xl mb-2">🛡️</div>
                <div class="font-semibold text-sm sm:text-base">Quality Assured</div>
                <div class="text-xs sm:text-sm text-gray-600">Fresh ingredients only</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl mb-2">⏰</div>
                <div class="font-semibold text-sm sm:text-base">On-Time Delivery</div>
                <div class="text-xs sm:text-sm text-gray-600">Or it's free</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl mb-2">🐾</div>
                <div class="font-semibold text-sm sm:text-base">Supporting Dogs</div>
                <div class="text-xs sm:text-sm text-gray-600">Profits to shelters</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl mb-2">📞</div>
                <div class="font-semibold text-sm sm:text-base">24/7 Support</div>
                <div class="text-xs sm:text-sm text-gray-600">Always here to help</div>
            </div>
        </div>
    </div>
</div>

<script>
function customBulkBuilder() {
    return {
        orderType: 'cooked',
        deliveryDateTime: '',
        pickupOption: 'pickup',
        packagingType: '',
        customPackaging: '',
        deliveryArea: '',
        deliveryAddress: '',
        storageType: 'pack10',
        specialNotes: '',
        selectedSauce: 'mild',
        sauceQuantity: 1,
        
        momoItems: [
            { name: 'Chicken Momo', price: 25, quantity: 0 },
            { name: 'Veg Momo', price: 20, quantity: 0 },
            { name: 'Buff Momo', price: 22, quantity: 0 },
            { name: 'Pork Momo', price: 28, quantity: 0 },
            { name: 'Cheese Momo', price: 30, quantity: 0 }
        ],
        
        sideDishes: [
            { name: 'Chutney (Small)', price: 50, quantity: 0 },
            { name: 'Chutney (Large)', price: 100, quantity: 0 },
            { name: 'Soup (Small)', price: 80, quantity: 0 },
            { name: 'Soup (Large)', price: 150, quantity: 0 }
        ],
        
        sauces: [
            { type: 'mild', name: 'Mild Sauce', description: 'Perfect for everyone' },
            { type: 'medium', name: 'Medium Sauce', description: 'A bit of kick' },
            { type: 'hot', name: 'Hot Sauce', description: 'For spice lovers' }
        ],
        
        get totalMomoQuantity() {
            return this.momoItems.reduce((sum, item) => sum + item.quantity, 0);
        },
        
        get momoSubtotal() {
            return this.momoItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        
        get sideDishesSubtotal() {
            return this.sideDishes.reduce((sum, side) => sum + (side.price * side.quantity), 0);
        },
        
        get sauceSubtotal() {
            return this.sauceQuantity * 50; // Rs. 50 per sauce pot
        },
        
        get deliveryCost() {
            if (this.orderType === 'frozen' && this.pickupOption === 'pickup') {
                return 'Rs. 0';
            }
            return 'Rs. 100';
        },
        
        get totalPrice() {
            let total = this.momoSubtotal + this.sideDishesSubtotal + this.sauceSubtotal;
            if (this.storageType === 'vacuum') total += 200;
            if (this.deliveryCost !== 'Rs. 0') total += 100;
            return total;
        },
        
        increaseQuantity(index) {
            this.momoItems[index].quantity++;
        },
        
        decreaseQuantity(index) {
            if (this.momoItems[index].quantity > 0) {
                this.momoItems[index].quantity--;
            }
        },
        
        increaseSideQuantity(index) {
            this.sideDishes[index].quantity++;
        },
        
        decreaseSideQuantity(index) {
            if (this.sideDishes[index].quantity > 0) {
                this.sideDishes[index].quantity--;
            }
        },
        
        submitOrder() {
            // Add to cart logic
            console.log('Adding to cart:', this.getOrderData());
        },
        
        checkoutNow() {
            // Direct checkout logic
            console.log('Checking out:', this.getOrderData());
        },
        
        getOrderData() {
            return {
                orderType: this.orderType,
                deliveryDateTime: this.deliveryDateTime,
                pickupOption: this.pickupOption,
                packagingType: this.packagingType,
                customPackaging: this.customPackaging,
                deliveryArea: this.deliveryArea,
                deliveryAddress: this.deliveryAddress,
                storageType: this.storageType,
                specialNotes: this.specialNotes,
                selectedSauce: this.selectedSauce,
                sauceQuantity: this.sauceQuantity,
                momoItems: this.momoItems.filter(item => item.quantity > 0),
                sideDishes: this.sideDishes.filter(side => side.quantity > 0),
                totalPrice: this.totalPrice
            };
        }
    }
}
</script> 