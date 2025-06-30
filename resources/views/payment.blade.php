@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
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
                                <span class="font-medium text-gray-900" id="payment-delivery">Rs.5.00</span>
                            </div>
                            
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Tax (13%)</span>
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
    document.getElementById('payment-form').addEventListener('submit', handleFormSubmission);
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
    
    // Get checkout data from localStorage
    const checkoutData = JSON.parse(localStorage.getItem('checkout_data') || '{}');
    
    // Get form data
    const formData = new FormData(form);
    const orderData = {
        name: checkoutData.name || '',
        email: checkoutData.email || '',
        phone: checkoutData.phone || '',
        city: checkoutData.city || '',
        ward_number: checkoutData.ward_number || '',
        area_locality: checkoutData.area_locality || '',
        building_name: checkoutData.building_name || '',
        detailed_directions: checkoutData.detailed_directions || '',
        payment_method: formData.get('payment_method'),
        items: cartItems,
        total: parseFloat(document.getElementById('payment-total').textContent.replace('Rs.', '')),
        applied_offer: localStorage.getItem('applied_offer') // Include applied offer
    };

    // Add GPS location data if available
    const savedLocation = localStorage.getItem('checkout_gps_location');
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
            // Clear cart and checkout data
            localStorage.removeItem('momo_cart');
            localStorage.removeItem('applied_offer');
            localStorage.removeItem('checkout_data');
            localStorage.removeItem('checkout_gps_location');
            
            if (typeof window.cartManager !== 'undefined') {
                window.cartManager.clearCart();
            }
            
            // Show success message
            alert('Order placed successfully! We\'ll contact you soon.');
            
            // Redirect to home
            window.location.href = '{{ route("home") }}';
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

// Toggle payment method details
function togglePaymentDetails() {
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const walletDetails = document.getElementById('wallet-payment-details');
    const otherDetails = document.getElementById('other-payment-details');
    
    if (selectedMethod === 'wallet') {
        walletDetails.classList.remove('hidden');
        otherDetails.classList.add('hidden');
        // Automatically load user's wallet balance
        loadUserWalletBalance();
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

// Load user's wallet balance automatically
function loadUserWalletBalance() {
    const balanceElement = document.getElementById('user-wallet-balance');
    const statusElement = document.getElementById('wallet-balance-status');
    const statusContainer = document.getElementById('wallet-balance-status-container');
    const walletNumberElement = document.getElementById('user-wallet-number');
    const walletNumberInput = document.getElementById('wallet_number');
    
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
            const orderTotal = parseFloat(document.getElementById('payment-total').textContent.replace('Rs.', '').trim());
            
            // Update wallet balance
            balanceElement.textContent = `Rs.${balance.toFixed(2)}`;
            
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
                statusElement.textContent = '✅ Sufficient balance for this order';
                statusContainer.className = 'mt-3 p-2 rounded-lg bg-green-50 border border-green-200';
            } else {
                statusElement.textContent = '❌ Insufficient balance for this order';
                statusContainer.className = 'mt-3 p-2 rounded-lg bg-red-50 border border-red-200';
            }
        } else {
            throw new Error(data.message || 'Failed to fetch balance');
        }
    })
    .catch(error => {
        console.error('Error fetching wallet balance:', error);
        balanceElement.textContent = 'Rs.0.00';
        
        // Show user-friendly error message
        if (error.message.includes('log in')) {
            statusElement.textContent = '❌ ' + error.message;
            statusContainer.className = 'mt-3 p-2 rounded-lg bg-yellow-50 border border-yellow-200';
        } else {
            statusElement.textContent = '❌ Error loading balance. Please try again.';
            statusContainer.className = 'mt-3 p-2 rounded-lg bg-red-50 border border-red-200';
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
        const balanceStatus = document.getElementById('wallet-balance-status').textContent;
        if (balanceStatus.includes('Insufficient')) {
            alert('Insufficient wallet balance for this order. Please choose another payment method.');
            return;
        }
        
        if (balanceStatus.includes('Loading')) {
            alert('Please wait while we check your wallet balance.');
            return;
        }
    }
    
    // If all validations pass, submit the form
    this.submit();
}

// Tax and delivery settings from server
window.taxDeliverySettings = @json(\App\Services\TaxDeliveryService::getAllSettings());
</script>
@endpush 