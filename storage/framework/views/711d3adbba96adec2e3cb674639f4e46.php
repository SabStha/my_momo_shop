<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-2">Complete your order</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    
                    <div class="p-6">
                        <div id="checkout-items" class="space-y-4 mb-6">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-900" id="checkout-subtotal">Rs.0.00</span>
                            </div>
                            
                            <!-- Applied Offer Section -->
                            <div id="checkout-offer-section" style="display: none;">
                                <div class="flex justify-between items-center text-sm bg-green-50 border border-green-200 rounded-lg px-3 py-2 mb-2">
                                    <div>
                                        <span class="font-semibold text-green-700">Offer Applied:</span>
                                        <span class="font-mono text-green-800" id="checkout-offer-code"></span>
                                        <span class="text-xs text-green-600 ml-1" id="checkout-offer-discount"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-green-700">Discount</span>
                                    <span class="font-medium text-green-700" id="checkout-discount-amount">-Rs.0.00</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium text-gray-900" id="checkout-delivery">Rs.5.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (13%)</span>
                                <span class="font-medium text-gray-900" id="checkout-tax">Rs.0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-[#6E0D25]" id="checkout-total">Rs.0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Delivery Information</h2>
                    </div>
                    
                    <form id="checkout-form" class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors">
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                            <textarea id="address" name="address" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="cash" checked
                                           class="w-4 h-4 text-[#6E0D25] border-gray-300 focus:ring-[#6E0D25]">
                                    <span class="ml-3 text-sm text-gray-700">Cash on Delivery</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="card"
                                           class="w-4 h-4 text-[#6E0D25] border-gray-300 focus:ring-[#6E0D25]">
                                    <span class="ml-3 text-sm text-gray-700">Credit/Debit Card</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="online"
                                           class="w-4 h-4 text-[#6E0D25] border-gray-300 focus:ring-[#6E0D25]">
                                    <span class="ml-3 text-sm text-gray-700">Online Payment</span>
                                </label>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div>
                            <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">Special Instructions (Optional)</label>
                            <textarea id="instructions" name="instructions" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                      placeholder="Any special delivery instructions..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                id="place-order-btn"
                                class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Place Order
                        </button>

                        <!-- Back to Cart -->
                        <a href="<?php echo e(route('cart')); ?>" 
                           class="block w-full text-center bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-300">
                            Back to Cart
                        </a>
                        
                        <!-- Debug Button (temporary) -->
                        <button type="button" onclick="testCheckoutCart()" 
                                class="block w-full text-center bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg font-semibold hover:bg-yellow-200 transition-colors duration-300 text-sm">
                            Debug: Test Cart Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
<?php echo $__env->make('components.cart-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/cart.js')); ?>"></script>
<script>
console.log('Checkout script starting...');

// Manual test function for debugging
window.testCheckoutCart = function() {
    console.log('=== MANUAL CART TEST ===');
    console.log('localStorage momo_cart:', localStorage.getItem('momo_cart'));
    console.log('localStorage applied_offer:', localStorage.getItem('applied_offer'));
    console.log('window.cartManager:', window.cartManager);
    if (window.cartManager) {
        console.log('cartManager.getCartItems():', window.cartManager.getCartItems());
        console.log('cartManager.getCartItemCount():', window.cartManager.getCartItemCount());
    }
    console.log('=== END TEST ===');
};

// Checkout page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout page loaded');
    
    // Try to display cart immediately
    updateCheckoutPage();
    
    // Also set up a fallback in case CartManager loads later
    const checkCartManager = setInterval(() => {
        if (typeof window.cartManager !== 'undefined') {
            console.log('CartManager found, updating checkout');
            clearInterval(checkCartManager);
            updateCheckoutPage(); // Refresh display with CartManager
        }
    }, 100);
    
    // Stop checking after 5 seconds to avoid infinite loop
    setTimeout(() => {
        clearInterval(checkCartManager);
        console.log('CartManager check timeout - using localStorage fallback');
    }, 5000);
    
    // Handle form submission
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();
        placeOrder();
    });
    
    // Listen for localStorage changes (e.g., offer applied from another page)
    window.addEventListener('storage', function(event) {
        if (event.key === 'applied_offer' || event.key === 'momo_cart') {
            console.log('Storage change detected, updating checkout');
            updateCheckoutPage();
        }
    });
    
    // Listen for custom offerApplied event
    window.addEventListener('offerApplied', function(event) {
        console.log('Offer applied event detected, updating checkout');
        updateCheckoutPage();
    });
});

function updateCheckoutPage() {
    console.log('updateCheckoutPage called');
    
    // Get cart data
    let cart = [];
    let itemCount = 0;
    
    if (window.cartManager && typeof window.cartManager.getCartItems === 'function') {
        console.log('Using cartManager to get cart data');
        cart = window.cartManager.getCartItems();
        itemCount = window.cartManager.getCartItemCount();
    } else {
        console.log('Using localStorage fallback to get cart data');
        // Fallback to localStorage if CartManager is not available
        const storedCart = localStorage.getItem('momo_cart');
        console.log('Stored cart from localStorage:', storedCart);
        cart = JSON.parse(storedCart || '[]');
        itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
    
    console.log('Cart data:', cart);
    console.log('Item count:', itemCount);
    
    const itemsContainer = document.getElementById('checkout-items');
    
    if (cart.length === 0) {
        console.log('Cart is empty, showing empty state');
        // Show empty state
        itemsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Add some items to your cart to see them here.</p>
                <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
                    Start Shopping
                </a>
            </div>
        `;
        return;
    }
    
    console.log('Displaying cart items');
    // Display items
    let itemsHtml = '';
    let subtotal = 0;
    
    cart.forEach((item) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        itemsHtml += `
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-lg">` :
                        `<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>`
                    }
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 truncate">${item.name}</h3>
                    <p class="text-sm text-gray-500">Quantity: ${item.quantity}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-[#6E0D25]">Rs.${itemTotal.toFixed(2)}</p>
                </div>
            </div>
        `;
    });
    
    itemsContainer.innerHTML = itemsHtml;
    
    // Calculate totals with offer support
    const deliveryFee = subtotal >= 25 ? 0 : 5;
    const tax = subtotal * 0.13;
    
    // Handle applied offer
    let offer = null;
    let discountAmount = 0;
    
    try {
        // Try to get offer from cartManager first, then fallback to localStorage
        if (window.cartManager && typeof window.cartManager.getAppliedOffer === 'function') {
            offer = window.cartManager.getAppliedOffer();
            console.log('Offer from cartManager:', offer);
        } else {
            // Fallback to localStorage if cartManager is not available
            const storedOffer = localStorage.getItem('applied_offer');
            console.log('Stored offer from localStorage:', storedOffer);
            if (storedOffer) {
                offer = JSON.parse(storedOffer);
            }
        }
        
        if (offer && offer.discount) {
            // Convert discount to number if it's a string
            const discountValue = parseFloat(offer.discount);
            discountAmount = subtotal * (discountValue / 100);
            
            // Show offer section
            document.getElementById('checkout-offer-section').style.display = 'block';
            document.getElementById('checkout-offer-code').textContent = offer.code;
            document.getElementById('checkout-offer-discount').textContent = `(${discountValue}% OFF)`;
            document.getElementById('checkout-discount-amount').textContent = `-Rs.${discountAmount.toFixed(2)}`;
        } else {
            // Hide offer section
            document.getElementById('checkout-offer-section').style.display = 'none';
        }
    } catch (error) {
        console.error('Error processing offer:', error);
        document.getElementById('checkout-offer-section').style.display = 'none';
    }
    
    const total = subtotal + deliveryFee + tax - discountAmount;
    
    console.log('Totals calculated:', { subtotal, deliveryFee, tax, discountAmount, total });
    
    // Update totals
    document.getElementById('checkout-subtotal').textContent = `Rs.${subtotal.toFixed(2)}`;
    document.getElementById('checkout-tax').textContent = `Rs.${tax.toFixed(2)}`;
    document.getElementById('checkout-total').textContent = `Rs.${total.toFixed(2)}`;
    document.getElementById('checkout-delivery').textContent = deliveryFee === 0 ? 'Free' : `Rs.${deliveryFee.toFixed(2)}`;
}

function placeOrder() {
    console.log('placeOrder called');
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    
    // Get cart items
    let cartItems = [];
    if (typeof window.cartManager !== 'undefined') {
        cartItems = window.cartManager.getCartItems();
        console.log('Cart items from cartManager:', cartItems);
    } else {
        cartItems = JSON.parse(localStorage.getItem('momo_cart') || '[]');
        console.log('Cart items from localStorage:', cartItems);
    }
    
    if (cartItems.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Get form data
    const formData = new FormData(form);
    const orderData = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        payment_method: formData.get('payment_method'),
        instructions: formData.get('instructions'),
        items: cartItems,
        total: parseFloat(document.getElementById('checkout-total').textContent.replace('Rs.', '')),
        applied_offer: localStorage.getItem('applied_offer') // Include applied offer
    };
    
    console.log('Order data:', orderData);
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span>Processing...</span>
        </div>
    `;
    submitBtn.disabled = true;
    
    // Submit order to backend
    fetch('/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            localStorage.removeItem('momo_cart');
            localStorage.removeItem('applied_offer');
            
            if (typeof window.cartManager !== 'undefined') {
                window.cartManager.clearCart();
            }
            
            // Show success message
            alert('Order placed successfully! We\'ll contact you soon.');
            
            // Redirect to home
            window.location.href = '<?php echo e(route("home")); ?>';
        } else {
            throw new Error(data.message || 'Failed to place order');
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        alert('Failed to place order. Please try again.');
        
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/checkout.blade.php ENDPATH**/ ?>