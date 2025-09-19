@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F4E9E1] py-4">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Cart</span>
                </div>
                <div class="w-12 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Delivery Info</span>
                </div>
                <div class="w-12 h-0.5 bg-[#6E0D25]"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-[#6E0D25] text-white rounded-full flex items-center justify-center text-sm font-semibold">3</div>
                    <span class="ml-2 text-sm font-medium text-[#6E0D25]">Payment</span>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-xl font-bold text-gray-900">Complete Your Order</h1>
            <p class="text-sm text-gray-600 mt-1">Step 3: Choose your payment method</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#6E0D25]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Payment Method
                        </h2>
                        <p class="text-xs text-gray-600 mt-1">Choose how you'd like to pay for your order</p>
                    </div>
                    
                    <form id="payment-form" class="p-6 space-y-6">
                        <!-- Payment Method Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Select Payment Method</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="wallet" checked
                                           class="hidden" onchange="togglePaymentDetails()">
                                    <span class="text-2xl mb-2">💰</span>
                                    <span class="text-xs text-gray-700 text-center">Wallet</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="cash"
                                           class="hidden" onchange="togglePaymentDetails()">
                                    <span class="text-2xl mb-2">💵</span>
                                    <span class="text-xs text-gray-700 text-center">Cash</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="fonepay"
                                           class="hidden" onchange="togglePaymentDetails()">
                                    <span class="text-2xl mb-2">📱</span>
                                    <span class="text-xs text-gray-700 text-center">FonePay</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="esewa"
                                           class="hidden" onchange="togglePaymentDetails()">
                                    <span class="text-2xl mb-2">💳</span>
                                    <span class="text-xs text-gray-700 text-center">eSewa</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="khalti"
                                           class="hidden" onchange="togglePaymentDetails()">
                                    <span class="text-2xl mb-2">💜</span>
                                    <span class="text-xs text-gray-700 text-center">Khalti</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="card"
                                           class="hidden" onchange="togglePaymentDetails()">
                                    <span class="text-2xl mb-2">💳</span>
                                    <span class="text-xs text-gray-700 text-center">Card</span>
                                </label>
                            </div>
                        </div>

                        <!-- Wallet Payment Details -->
                        <div id="wallet-payment-details" class="space-y-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <h3 class="text-sm font-semibold text-blue-800">Your Wallet</h3>
                            </div>
                            
                            <!-- User Wallet Info -->
                            <div class="bg-white border border-blue-300 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Wallet Number</p>
                                        <p class="text-lg font-semibold text-blue-600" id="user-wallet-number">Loading...</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-700">Available Balance</p>
                                        <p class="text-lg font-bold text-green-600" id="user-wallet-balance">Rs.0.00</p>
                                    </div>
                                </div>
                                
                                <!-- Balance Status -->
                                <div class="mt-3 p-2 rounded-lg" id="wallet-balance-status-container">
                                    <p class="text-sm" id="wallet-balance-status">Checking balance...</p>
                                </div>
                            </div>

                            <!-- Refresh Balance Button -->
                            <button type="button" id="refresh-balance-btn"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-300 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh Balance
                            </button>

                            <!-- Hidden wallet number input for form submission -->
                            <input type="hidden" id="wallet_number" name="wallet_number" value="{{ auth()->user()->phone ?? '' }}">
                        </div>

                        <!-- Other Payment Method Details (Hidden by default) -->
                        <div id="other-payment-details" class="hidden space-y-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="text-sm font-semibold text-gray-800" id="other-payment-title">Payment Details</h3>
                            </div>
                            <p class="text-sm text-gray-600" id="other-payment-description">
                                You will be redirected to complete your payment after placing the order.
                            </p>
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

                        <!-- Back to Checkout -->
                        <a href="{{ route('checkout') }}" 
                           class="block w-full text-center bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-300">
                            Back to Checkout
                        </a>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#6E0D25]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Order Summary
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div id="payment-items" class="space-y-2 mb-4">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-3 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-900" id="payment-subtotal">Rs.0.00</span>
                            </div>
                            
                            <!-- Applied Offer Section -->
                            <div id="payment-offer-section" style="display: none;">
                                <div class="flex justify-between items-center text-xs bg-green-50 border border-green-200 rounded px-2 py-1 mb-1">
                                    <div>
                                        <span class="font-semibold text-green-700">Offer:</span>
                                        <span class="font-mono text-green-800" id="payment-offer-code"></span>
                                        <span class="text-xs text-green-600 ml-1" id="payment-offer-discount"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-green-700">Discount</span>
                                    <span class="font-medium text-green-700" id="payment-discount-amount">-Rs.0.00</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium text-gray-900" id="payment-delivery">{{ formatPrice(getDeliveryFee()) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Tax ({{ getTaxRate() }}%)</span>
                                <span class="font-medium text-gray-900" id="payment-tax">Rs.0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex justify-between text-sm font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-[#6E0D25]" id="payment-total">Rs.0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
@include('components.cart-modal')

@endsection

<!-- Payment Confirmation Components -->
@include('payment.confirmations.fonepay')
@include('payment.confirmations.esewa')
@include('payment.confirmations.khalti')
@include('payment.confirmations.card')

@push('scripts')
<script src="{{ asset('js/cart.js') }}"></script>
<script>
console.log('Payment page script starting...');

// Payment page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payment page loaded');
    
    // Try to display cart immediately
    updatePaymentPage();
    
    // Also set up a fallback in case CartManager loads later
    const checkCartManager = setInterval(() => {
        if (typeof window.cartManager !== 'undefined') {
            console.log('CartManager found, updating payment page');
            clearInterval(checkCartManager);
            updatePaymentPage(); // Refresh display with CartManager
        }
    }, 100);
    
    // Stop checking after 5 seconds to avoid infinite loop
    setTimeout(() => {
        clearInterval(checkCartManager);
        console.log('CartManager check timeout - using localStorage fallback');
    }, 5000);
    
    // Handle form submission
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        e.preventDefault();
        placeOrder();
    });
    
    // Listen for localStorage changes (e.g., offer applied from another page)
    window.addEventListener('storage', function(event) {
        if (event.key === 'applied_offer' || event.key === 'momo_cart') {
            console.log('Storage change detected, updating payment page');
            updatePaymentPage();
        }
    });
    
    // Listen for custom offerApplied event
    window.addEventListener('offerApplied', function(event) {
        console.log('Offer applied event detected, updating payment page');
        updatePaymentPage();
    });

    // Initialize payment details on page load
    togglePaymentDetails();
    
    // Add event listeners
    document.getElementById('refresh-balance-btn').addEventListener('click', refreshWalletBalance);
});

function updatePaymentPage() {
    console.log('updatePaymentPage called');
    
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
    
    const itemsContainer = document.getElementById('payment-items');
    
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
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
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
    const taxRate = window.taxDeliverySettings.tax_rate || 13;
    const tax = subtotal * (taxRate / 100);
    
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
            document.getElementById('payment-offer-section').style.display = 'block';
            document.getElementById('payment-offer-code').textContent = offer.code;
            document.getElementById('payment-offer-discount').textContent = `(${discountValue}% OFF)`;
            document.getElementById('payment-discount-amount').textContent = `-Rs.${discountAmount.toFixed(2)}`;
        } else {
            // Hide offer section
            document.getElementById('payment-offer-section').style.display = 'none';
        }
    } catch (error) {
        console.error('Error processing offer:', error);
        document.getElementById('payment-offer-section').style.display = 'none';
    }
    
    const total = subtotal + deliveryFee + tax - discountAmount;
    
    console.log('Totals calculated:', { subtotal, deliveryFee, tax, discountAmount, total });
    
    // Update totals
    document.getElementById('payment-subtotal').textContent = `Rs.${subtotal.toFixed(2)}`;
    document.getElementById('payment-tax').textContent = `Rs.${tax.toFixed(2)}`;
    document.getElementById('payment-total').textContent = `Rs.${total.toFixed(2)}`;
    document.getElementById('payment-delivery').textContent = deliveryFee === 0 ? 'Free' : `Rs.${deliveryFee.toFixed(2)}`;
}

function placeOrder() {
    console.log('placeOrder called');
    const form = document.getElementById('payment-form');
    const submitBtn = document.getElementById('place-order-btn');
    
    // Get selected payment method
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!selectedMethod) {
        alert('Please select a payment method');
        return;
    }
    
    // Get payment amount
    const amount = parseFloat(document.getElementById('payment-total').textContent.replace('Rs.', ''));
    
    // Use enhanced payment processing with confirmation modals
    processPaymentWithConfirmation(selectedMethod.value, amount);
    return; // Exit early as confirmation modals will handle the rest
}

// Separate function for actual order processing
function processOrder() {
    console.log('processOrder called');
    const form = document.getElementById('payment-form');
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
    
    // Get checkout data from sessionStorage (from checkout page) or localStorage fallback
    let checkoutData = {};
    const sessionCheckoutData = sessionStorage.getItem('checkoutFormData');
    if (sessionCheckoutData) {
        checkoutData = JSON.parse(sessionCheckoutData);
        console.log('Found checkout data in sessionStorage:', checkoutData);
    } else {
        checkoutData = JSON.parse(localStorage.getItem('checkout_data') || '{}');
        console.log('Using checkout data from localStorage:', checkoutData);
    }
    
    // Get form data
    const formData = new FormData(form);
    // Map cart items to correct structure
    const items = cartItems.map(item => {
        // Handle bulk packages and regular products differently
        let productId = item.id || item.product_id;
        
        // For bulk packages, we need to handle them specially
        if (productId && productId.toString().startsWith('bulk-')) {
            // For bulk packages, we'll use a special handling in the backend
            return {
                product_id: productId.toString(), // Ensure it's a string
                quantity: item.quantity,
                type: 'bulk'
            };
        } else {
            // For regular products, convert to string (backend expects string)
            return {
                product_id: productId.toString(), // Convert to string for validation
                quantity: item.quantity,
                type: 'product'
            };
        }
    });
    const orderData = {
        name: checkoutData.name || formData.get('name') || '',
        email: checkoutData.email || formData.get('email') || '',
        phone: checkoutData.phone || formData.get('phone') || '',
        city: checkoutData.city || formData.get('city') || '',
        ward_number: checkoutData.ward_number || formData.get('ward_number') || '',
        area_locality: checkoutData.area_locality || formData.get('area_locality') || '',
        building_name: checkoutData.building_name || formData.get('building_name') || '',
        detailed_directions: checkoutData.detailed_directions || formData.get('detailed_directions') || '',
        branch_id: checkoutData.branch_id || formData.get('branch_id') || null,
        payment_method: formData.get('payment_method'),
        items: items,
        total: parseFloat(document.getElementById('payment-total').textContent.replace('Rs.', '')),
        applied_offer: localStorage.getItem('applied_offer')
    };

    // Add GPS location data if available (from sessionStorage or localStorage)
    let savedLocation = sessionStorage.getItem('checkoutGpsLocation');
    if (!savedLocation) {
        savedLocation = localStorage.getItem('checkout_gps_location');
    }
    if (savedLocation) {
        try {
            const locationData = JSON.parse(savedLocation);
            orderData.gps_location = {
                latitude: locationData.lat,
                longitude: locationData.lng,
                coordinates: `${locationData.lat.toFixed(6)}, ${locationData.lng.toFixed(6)}`
            };
        } catch (error) {
            console.error('Error parsing saved location:', error);
        }
    }
    
    console.log('Order data (fixed):', orderData);
    console.log('Customer data from checkout:', checkoutData);
    console.log('Cart items before mapping:', cartItems);
    console.log('Mapped items for order:', items);
    
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
    console.log('Submitting order to backend...');
    fetch('/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(orderData),
        signal: AbortSignal.timeout(30000) // 30 second timeout
    })
    .then(async response => {
        console.log('Response received!');
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response headers:', response.headers);
        
        const data = await response.json();
        console.log('Response data:', data);
        if ((response.ok || response.status === 201) && data.success) {
            console.log('Order successful! Clearing data and showing success modal...');
            
            try {
                // Clear cart and checkout data
                localStorage.removeItem('momo_cart');
                localStorage.removeItem('applied_offer');
                localStorage.removeItem('checkout_data');
                localStorage.removeItem('checkout_gps_location');
                sessionStorage.removeItem('checkoutFormData');
                sessionStorage.removeItem('checkoutCart');
                sessionStorage.removeItem('checkoutOffer');
                sessionStorage.removeItem('checkoutGpsLocation');
                
                if (typeof window.cartManager !== 'undefined' && window.cartManager) {
                    window.cartManager.clearCart();
                }
                
                console.log('Data cleared, showing success modal...');
                // Show beautiful success modal with order details
                showSuccessModal(data.order);
            } catch (error) {
                console.error('Error during success handling:', error);
                // Even if there's an error, still show success modal
                showSuccessModal(data.order);
            }
        } else {
            // Show validation errors if present
            if (data.errors) {
                let errorMsg = 'Please fix the following errors:\n';
                Object.keys(data.errors).forEach(key => {
                    errorMsg += `- ${data.errors[key].join(', ')}\n`;
                });
                alert(errorMsg);
            } else {
                throw new Error(data.message || 'Failed to place order');
            }
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack,
            name: error.name
        });
        
        // Log the order data that was sent
        console.error('Order data that failed:', orderData);
        console.error('Cart items that failed:', cartItems);
        console.error('Mapped items that failed:', items);
        
        // Handle different types of errors
        let errorMessage = 'Failed to place order. Please try again.';
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            errorMessage = 'Network error. Please check your connection and try again.';
        } else if (error.name === 'AbortError') {
            errorMessage = 'Request was cancelled. Please try again.';
        } else if (error.message) {
            errorMessage = `Order failed: ${error.message}`;
        }
        
        alert(errorMessage);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Toggle payment method details
function togglePaymentDetails() {
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const walletDetails = document.getElementById('wallet-payment-details');
    const otherDetails = document.getElementById('other-payment-details');
    
    if (selectedMethod === 'wallet') {
        walletDetails.classList.remove('hidden');
        otherDetails.classList.add('hidden');
        // Automatically load user's wallet balance after a short delay to ensure elements are visible
        setTimeout(() => {
            loadUserWalletBalance();
        }, 100);
    } else {
        walletDetails.classList.add('hidden');
        otherDetails.classList.remove('hidden');
        
        // Update other payment method details
        const title = document.getElementById('other-payment-title');
        const description = document.getElementById('other-payment-description');
        
        switch(selectedMethod) {
            case 'cash':
                title.textContent = 'Cash Payment';
                description.textContent = 'Pay with cash when your order is delivered.';
                break;
            case 'fonepay':
                title.textContent = 'FonePay Payment';
                description.textContent = 'You will be redirected to FonePay to complete your payment.';
                break;
            case 'esewa':
                title.textContent = 'eSewa Payment';
                description.textContent = 'You will be redirected to eSewa to complete your payment.';
                break;
            case 'khalti':
                title.textContent = 'Khalti Payment';
                description.textContent = 'You will be redirected to Khalti to complete your payment.';
                break;
            case 'card':
                title.textContent = 'Card Payment';
                description.textContent = 'You will be redirected to complete your card payment.';
                break;
        }
    }
}

// Enhanced payment processing with confirmation modals
function processPaymentWithConfirmation(paymentMethod, amount) {
    switch(paymentMethod) {
        case 'fonepay':
            showFonePayConfirmation(amount);
            break;
        case 'esewa':
            showEsewaConfirmation(amount);
            break;
        case 'khalti':
            showKhaltiConfirmation(amount);
            break;
        case 'card':
            showCardConfirmation(amount);
            break;
        case 'cash':
            // For cash payments, proceed directly
            processOrder();
            break;
        case 'wallet':
            // For wallet payments, proceed directly
            processOrder();
            break;
        default:
            processOrder();
    }
}

// Load user's wallet balance automatically
function loadUserWalletBalance() {
    const balanceElement = document.getElementById('user-wallet-balance');
    const statusElement = document.getElementById('wallet-balance-status');
    const statusContainer = document.getElementById('wallet-balance-status-container');
    const walletNumberElement = document.getElementById('user-wallet-number');
    const walletNumberInput = document.getElementById('wallet_number');
    
    // Check if elements exist before proceeding
    if (!balanceElement || !statusElement || !statusContainer) {
        console.error('Wallet balance elements not found on page', {
            balanceElement: !!balanceElement,
            statusElement: !!statusElement,
            statusContainer: !!statusContainer
        });
        return;
    }
    
    // Additional check to ensure elements are visible and accessible
    if (balanceElement.offsetParent === null || statusElement.offsetParent === null || statusContainer.offsetParent === null) {
        console.error('Wallet balance elements are not visible on page');
        return;
    }
    
    // Check if wallet payment method is currently selected
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
    if (!selectedMethod || selectedMethod.value !== 'wallet') {
        console.log('Wallet payment method not selected, skipping balance load');
        return;
    }
    
    // Debug: Check if user is authenticated
    console.log('Loading wallet balance...');
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
    console.log('User authenticated check:', '{{ auth()->check() }}');
    console.log('User ID:', '{{ auth()->id() }}');
    
    // Show loading state
    balanceElement.textContent = 'Rs.0.00';
    statusElement.textContent = 'Loading your balance...';
    statusContainer.className = 'mt-3 p-2 rounded-lg bg-blue-50 border border-blue-200';
    
    // Check if user is authenticated first
    if (!document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) {
        console.error('No CSRF token found - user may not be authenticated');
        balanceElement.textContent = 'Rs.0.00';
        statusElement.textContent = '❌ Please log in to view your wallet balance';
        statusContainer.className = 'mt-3 p-2 rounded-lg bg-yellow-50 border border-yellow-200';
        return;
    }
    
    // Fetch real wallet balance from API
    fetch('/api/user/wallet/balance', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin' // Include cookies for session authentication
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Please log in to view your wallet balance');
            } else if (response.status === 403) {
                throw new Error('Access denied. Please contact support.');
            } else {
                throw new Error(`Server error (${response.status}). Please try again.`);
            }
        }
        return response.json();
    })
    .then(data => {
        console.log('Wallet balance response:', data);
        
        if (data.success) {
            const balance = parseFloat(data.balance);
            const paymentTotalElement = document.getElementById('payment-total');
            const orderTotal = paymentTotalElement ? parseFloat(paymentTotalElement.textContent.replace('Rs.', '').trim()) : 0;
            
            // Update wallet balance
            if (balanceElement) {
                balanceElement.textContent = `Rs.${balance.toFixed(2)}`;
            }
            
            // Update wallet number if available
            if (data.wallet_number && walletNumberElement) {
                walletNumberElement.textContent = data.wallet_number;
                console.log('Updated wallet number:', data.wallet_number);
            }
            
            // Update hidden input for form submission
            if (data.wallet_number && walletNumberInput) {
                walletNumberInput.value = data.wallet_number;
            }
            
            if (balance >= orderTotal) {
                if (statusElement) {
                    statusElement.textContent = '✅ Sufficient balance for this order';
                }
                if (statusContainer) {
                    statusContainer.className = 'mt-3 p-2 rounded-lg bg-green-50 border border-green-200';
                }
            } else {
                if (statusElement) {
                    statusElement.textContent = '❌ Insufficient balance for this order';
                }
                if (statusContainer) {
                    statusContainer.className = 'mt-3 p-2 rounded-lg bg-red-50 border border-red-200';
                }
            }
        } else {
            throw new Error(data.message || 'Failed to fetch balance');
        }
    })
    .catch(error => {
        console.error('Error fetching wallet balance:', error);
        if (balanceElement) {
            balanceElement.textContent = 'Rs.0.00';
        }
        
        // Show user-friendly error message
        if (statusElement) {
            if (error.message.includes('log in')) {
                statusElement.textContent = '❌ ' + error.message;
            } else {
                statusElement.textContent = '❌ Error loading balance. Please try again.';
            }
        }
        
        if (statusContainer) {
            if (error.message.includes('log in')) {
                statusContainer.className = 'mt-3 p-2 rounded-lg bg-yellow-50 border border-yellow-200';
            } else {
                statusContainer.className = 'mt-3 p-2 rounded-lg bg-red-50 border border-red-200';
            }
        }
    });
}

// Refresh wallet balance
function refreshWalletBalance() {
    loadUserWalletBalance();
}

// Handle form submission
function handleFormSubmission(e) {
    e.preventDefault();
    
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (selectedMethod === 'wallet') {
        // Check if balance is sufficient
        const balanceStatusElement = document.getElementById('wallet-balance-status');
        if (balanceStatusElement) {
            const balanceStatus = balanceStatusElement.textContent;
            if (balanceStatus.includes('Insufficient')) {
                alert('Insufficient wallet balance for this order. Please choose another payment method.');
                return;
            }
            
            if (balanceStatus.includes('Loading')) {
                alert('Please wait while we check your wallet balance.');
                return;
            }
        }
    }
    
    // If all validations pass, call placeOrder instead of submitting the form
    placeOrder();
}

// Tax and delivery settings from server
window.taxDeliverySettings = @json(\App\Services\TaxDeliveryService::getAllSettings());

// Success Modal Functions
function showSuccessModal(orderData) {
    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    const orderDetails = document.getElementById('orderDetails');
    const orderInfo = document.getElementById('orderInfo');
    
    // Show order details if available
    if (orderData && orderData.id) {
        orderDetails.classList.remove('hidden');
        orderInfo.innerHTML = `
            <div class="space-y-1">
                <div class="flex justify-between">
                    <span>Order #:</span>
                    <span class="font-medium">${orderData.order_number || orderData.id}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total:</span>
                    <span class="font-medium">Rs. ${parseFloat(orderData.grand_total || orderData.total).toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                    <span>Status:</span>
                    <span class="font-medium text-green-600">Pending</span>
                </div>
            </div>
        `;
    } else {
        orderDetails.classList.add('hidden');
    }
    
    // Show modal with animation
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    
    // Hide modal with animation
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function viewOrderHistory() {
    closeSuccessModal();
    // Redirect to my-account page with order history tab
    window.location.href = '{{ route("account") }}#order-history';
}

function continueShopping() {
    closeSuccessModal();
    window.location.href = '{{ route("home") }}';
}

// Add keyboard support for success modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSuccessModal();
    }
});

// Add click outside to close functionality for success modal
document.addEventListener('click', function(event) {
    const modal = document.getElementById('successModal');
    if (event.target === modal) {
        closeSuccessModal();
    }
});
</script>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
        <div class="p-6 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <!-- Success Title -->
            <h3 class="text-lg font-medium text-gray-900 mb-2">Order Placed Successfully!</h3>
            
            <!-- Success Message -->
            <p class="text-sm text-gray-500 mb-6">Your order has been placed successfully. We'll contact you soon with delivery updates.</p>
            
            <!-- Order Details -->
            <div id="orderDetails" class="bg-gray-50 rounded-lg p-4 mb-6 hidden">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Order Details:</h4>
                <div id="orderInfo" class="text-sm text-gray-600"></div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button type="button" onclick="viewOrderHistory()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    View Order History
                </button>
                <button type="button" onclick="continueShopping()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Continue Shopping
                </button>
            </div>
        </div>
    </div>
</div>

@endpush 