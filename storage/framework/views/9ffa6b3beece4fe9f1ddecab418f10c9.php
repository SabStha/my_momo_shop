<?php $__env->startSection('content'); ?>
<!-- Toast Notification System -->
<div id="toast" class="fixed top-4 right-4 z-50 transform transition-all duration-300 translate-x-full">
    <div id="toast-content" class="bg-white border border-gray-200 rounded-lg shadow-lg px-4 py-3 max-w-sm">
        <div class="flex items-center">
            <div id="toast-icon" class="flex-shrink-0 mr-3">
                <!-- Icon will be set by JavaScript -->
            </div>
            <div>
                <p id="toast-message" class="text-sm font-medium text-gray-900"></p>
            </div>
        </div>
    </div>
</div>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Cart Items -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                        <p class="text-gray-600 mt-1" id="cart-header-text">Loading...</p>
                    </div>
                    
                    <div id="cart-items-container">
                        <!-- Cart items will be loaded here -->
                    </div>
                </div>
            </div>
            <!-- Cart Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    <div class="p-6">
                        <div id="cart-summary-container">
                            <!-- Cart summary will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Use the global CartManager instead of local functions
function updateQuantity(productId, newQuantity) {
    if (cartManager) {
        cartManager.updateQuantity(productId, parseInt(newQuantity));
        displayCart();
    } else {
        // Fallback to localStorage if CartManager is not available
        const cart = JSON.parse(localStorage.getItem('momo_cart') || '[]');
        const item = cart.find(item => item.id === productId);
        if (item) {
            if (newQuantity <= 0) {
                cart.splice(cart.indexOf(item), 1);
            } else {
                item.quantity = newQuantity;
            }
            localStorage.setItem('momo_cart', JSON.stringify(cart));
            displayCart();
        }
    }
}

function removeFromCart(productId) {
    if (cartManager) {
        cartManager.removeFromCart(productId);
        displayCart();
    } else {
        // Fallback to localStorage if CartManager is not available
        const cart = JSON.parse(localStorage.getItem('momo_cart') || '[]');
        const newCart = cart.filter(item => item.id !== productId);
        localStorage.setItem('momo_cart', JSON.stringify(newCart));
        displayCart();
    }
}

function displayCart() {
    let cart = [];
    let itemCount = 0;
    
    if (cartManager) {
        cart = cartManager.getCartItems();
        itemCount = cartManager.getCartItemCount();
    } else {
        // Fallback to localStorage if CartManager is not available
        cart = JSON.parse(localStorage.getItem('momo_cart') || '[]');
        itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
    
    const container = document.getElementById('cart-items-container');
    const headerText = document.getElementById('cart-header-text');
    const summaryContainer = document.getElementById('cart-summary-container');
    
    // Update header
    headerText.textContent = cart.length === 0 ? 'Your cart is empty' : `${itemCount} items in your cart`;
    
    if (cart.length === 0) {
        // Empty cart
        container.innerHTML = `
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
                <a href="/" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Start Shopping
                </a>
            </div>
        `;
        summaryContainer.innerHTML = '<div class="text-center text-gray-500"><p>No items in cart</p></div>';
        return;
    }
    
    // Display cart items
    let itemsHtml = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        itemsHtml += `
            <div class="p-6 border-b border-gray-200 last:border-b-0">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        ${item.image ? 
                            `<img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg">` :
                            `<div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 truncate">${item.name}</h3>
                        <p class="text-sm text-gray-500">Rs.${item.price.toFixed(2)} each</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQuantity('${item.id}', ${item.quantity - 1})" 
                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="w-12 text-center text-sm font-medium text-gray-900">${item.quantity}</span>
                        <button onclick="updateQuantity('${item.id}', ${item.quantity + 1})" 
                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-[#6E0D25]">Rs.${itemTotal.toFixed(2)}</p>
                        <button onclick="removeFromCart('${item.id}')" 
                                class="text-xs text-red-600 hover:text-red-800 transition-colors mt-1">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = itemsHtml;
    
    // Update summary
    const deliveryFee = subtotal >= 25 ? 0 : 5;
    const tax = subtotal * 0.13;
    
    // Offer logic - Improved with better error handling
    let offer = null;
    let discountAmount = 0;
    
    try {
        // Try to get offer from cartManager first, then fallback to localStorage
        if (window.cartManager && typeof window.cartManager.getAppliedOffer === 'function') {
            offer = window.cartManager.getAppliedOffer();
            console.log('Cart offer from cartManager:', offer);
        } else {
            // Fallback to localStorage if cartManager is not available
            const storedOffer = localStorage.getItem('applied_offer');
            console.log('Raw stored offer from localStorage:', storedOffer);
            if (storedOffer) {
                offer = JSON.parse(storedOffer);
                console.log('Parsed offer from localStorage:', offer);
            } else {
                console.log('No stored offer found in localStorage');
            }
        }
        
        if (offer && offer.discount) {
            // Convert discount to number if it's a string
            const discountValue = parseFloat(offer.discount);
            discountAmount = subtotal * (discountValue / 100);
            console.log('Discount value:', discountValue, 'Discount amount calculated:', discountAmount);
        } else {
            console.log('No valid offer found or no discount. Offer object:', offer);
        }
    } catch (error) {
        console.error('Error processing offer:', error);
        offer = null;
        discountAmount = 0;
    }
    
    const total = subtotal + deliveryFee + tax - discountAmount;

    let summaryItemsHtml = '';
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        summaryItemsHtml += `
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">${item.name} × ${item.quantity}</span>
                <span class="font-medium text-gray-900">Rs.${itemTotal.toFixed(2)}</span>
            </div>
        `;
    });
    
    // Offer summary HTML - Improved with better validation
    let offerHtml = '';
    if (offer && offer.code && (offer.discount || offer.discount === 0)) {
        const discountValue = parseFloat(offer.discount);
        offerHtml = `
            <div class="flex justify-between items-center text-sm bg-green-50 border border-green-200 rounded-lg px-3 py-2 mb-2">
                <div>
                    <span class="font-semibold text-green-700">Offer Applied:</span>
                    <span class="font-mono text-green-800">${offer.code}</span>
                    <span class="text-xs text-green-600 ml-1">(${discountValue}% OFF)</span>
                </div>
                <button onclick="removeCartOffer(); displayCart();" class="text-xs text-red-500 hover:text-red-700 ml-2">Remove</button>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-green-700">Discount</span>
                <span class="font-medium text-green-700">-Rs.${discountAmount.toFixed(2)}</span>
            </div>
        `;
    }
    
    summaryContainer.innerHTML = `
        <div class="space-y-3 mb-6">
            ${summaryItemsHtml}
        </div>
        <div class="space-y-3 border-t border-gray-200 pt-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium text-gray-900">Rs.${subtotal.toFixed(2)}</span>
            </div>
            ${offerHtml}
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Delivery</span>
                <span class="font-medium text-gray-900">
                    ${deliveryFee === 0 ? 'Free' : `Rs.${deliveryFee.toFixed(2)}`}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax (13%)</span>
                <span class="font-medium text-gray-900">Rs.${tax.toFixed(2)}</span>
            </div>
            <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-2">
                <span class="text-[#6E0D25]">Total</span>
                <span class="text-[#6E0D25]">Rs.${total.toFixed(2)}</span>
            </div>
        </div>
        <div class="mt-6 space-y-3">
            <button onclick="proceedToCheckout()" 
                    class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg hover:bg-[#8B0D2F] transition-colors font-medium">
                Proceed to Checkout
            </button>
            <button onclick="clearCart()" 
                    class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                Clear Cart
            </button>
        </div>
    `;
}

function proceedToCheckout() {
    console.log('=== PROCEED TO CHECKOUT DEBUG START ===');
    
    let cart = [];
    let isEmpty = true;
    
    if (cartManager) {
        cart = cartManager.getCartItems();
        isEmpty = cartManager.isEmpty();
        console.log('1. Using cartManager - Cart:', cart, 'IsEmpty:', isEmpty);
    } else {
        // Fallback to localStorage if CartManager is not available
        cart = JSON.parse(localStorage.getItem('momo_cart') || '[]');
        isEmpty = cart.length === 0;
        console.log('2. Using localStorage fallback - Cart:', cart, 'IsEmpty:', isEmpty);
    }
    
    if (isEmpty) {
        console.log('3. Cart is empty, showing alert');
        alert('Your cart is empty!');
        return;
    }
    
    // Check if user has any offers they can apply
    checkAndSuggestOffers(cart).then(() => {
        // Store cart in localStorage for checkout page to read
        localStorage.setItem('checkout_cart', JSON.stringify(cart));
        console.log('4. Stored cart in checkout_cart:', cart);
        console.log('5. localStorage checkout_cart after setting:', localStorage.getItem('checkout_cart'));
        
        // Navigate to checkout page
        console.log('6. Navigating to checkout page');
        window.location.href = '/checkout';
    });
    
    console.log('=== PROCEED TO CHECKOUT DEBUG END ===');
}

// Function to check and suggest relevant offers
async function checkAndSuggestOffers(cart) {
    try {
        // Calculate cart total
        const cartTotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        
        // Check if user already has an offer applied
        const appliedOffer = localStorage.getItem('applied_offer');
        
        if (appliedOffer) {
            console.log('User already has an offer applied, proceeding to checkout');
            return;
        }
        
        // Fetch available offers
        const response = await fetch('/offers/available');
        const data = await response.json();
        
        if (data.success && data.offers.length > 0) {
            // Filter offers that are relevant to this cart
            const relevantOffers = data.offers.filter(offer => {
                const minPurchase = parseFloat(offer.min_purchase || 0);
                return cartTotal >= minPurchase;
            });
            
            if (relevantOffers.length > 0) {
                // Show offer suggestions modal
                showOfferSuggestionsModal(relevantOffers, cartTotal);
                return new Promise((resolve) => {
                    // This will be resolved when user makes a choice in the modal
                    window.checkoutProceed = resolve;
                });
            }
        }
        
        console.log('No relevant offers found, proceeding to checkout');
        return Promise.resolve();
        
    } catch (error) {
        console.error('Error checking offers:', error);
        return Promise.resolve();
    }
}

// Function to show offer suggestions modal
function showOfferSuggestionsModal(offers, cartTotal) {
    const modal = document.createElement('div');
    modal.id = 'offer-suggestions-modal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    let offersHtml = '';
    offers.forEach(offer => {
        const discountAmount = cartTotal * (parseFloat(offer.discount) / 100);
        offersHtml += `
            <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4 mb-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-green-800">${offer.title}</h4>
                        <p class="text-sm text-gray-600 mb-2">${offer.description}</p>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">${offer.discount}% OFF</span>
                            <span class="text-green-600">Save Rs.${discountAmount.toFixed(2)}</span>
                        </div>
                    </div>
                    <button onclick="applySuggestedOffer('${offer.code}', this)" 
                            class="bg-green-600 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-green-700 transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        `;
    });
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">💡 Save Money!</h3>
                    <button onclick="closeOfferSuggestionsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 mb-4">You have ${offers.length} offer${offers.length > 1 ? 's' : ''} available for your order of Rs.${cartTotal.toFixed(2)}:</p>
                ${offersHtml}
                <div class="flex gap-3 mt-6">
                    <button onclick="closeOfferSuggestionsModal()" 
                            class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                        Continue Without Offer
                    </button>
                    <button onclick="viewAllOffers()" 
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        View All Offers
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Function to apply suggested offer
function applySuggestedOffer(offerCode, button) {
    // Find the offer details
    const offerElement = button.closest('.bg-gradient-to-r');
    const offerTitle = offerElement.querySelector('h4').textContent;
    const discountText = offerElement.querySelector('.bg-green-100').textContent;
    const discount = discountText.replace('% OFF', '');
    
    // Apply the offer
    const offerData = {
        code: offerCode,
        discount: parseFloat(discount),
        title: offerTitle,
        applied_at: new Date().toISOString()
    };
    
    // Store in localStorage
    localStorage.setItem('applied_offer', JSON.stringify(offerData));
    
    // Also try to use cartManager if available
    if (window.cartManager && typeof window.cartManager.applyOffer === 'function') {
        window.cartManager.applyOffer(offerData);
    }
    
    // Update button to show applied state
    button.innerHTML = `
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <span>Applied!</span>
        </div>
    `;
    button.className = 'bg-green-700 text-white px-4 py-2 rounded text-sm font-semibold cursor-not-allowed';
    button.disabled = true;
    
    // Show beautiful success notification
    if (typeof showSuccessNotification === 'function') {
        showSuccessNotification(
            `🎉 ${offerTitle} applied successfully! You'll save ${discount}% on your order.`,
            'Continue to Checkout',
            '/checkout'
        );
    } else if (typeof showToast === 'function') {
        showToast(`Offer ${offerCode} applied successfully!`, 'success');
    }
    
    // Close modal and proceed to checkout after a delay
    setTimeout(() => {
        closeOfferSuggestionsModal();
        if (window.checkoutProceed) {
            window.checkoutProceed();
        }
    }, 2000);
}

// Function to close offer suggestions modal
function closeOfferSuggestionsModal() {
    const modal = document.getElementById('offer-suggestions-modal');
    if (modal) {
        modal.remove();
    }
    if (window.checkoutProceed) {
        window.checkoutProceed();
    }
}

// Function to view all offers
function viewAllOffers() {
    // Close the modal
    closeOfferSuggestionsModal();
    
    // Show the offers popup (if available) or redirect to offers page
    if (typeof window.showOffersPopup === 'function') {
        window.showOffersPopup();
    } else {
        // Try to trigger the offers popup from the topnav
        const offersButton = document.querySelector('[x-data*="open"] button');
        if (offersButton) {
            offersButton.click();
        } else {
            // Fallback alert
            alert('Offers are available in the top navigation bell icon!');
        }
    }
}

// Function to show offers popup (expose globally)
function showOffersPopup() {
    // Try to trigger the offers popup from the topnav
    const offersButton = document.querySelector('[x-data*="open"] button');
    if (offersButton) {
        offersButton.click();
    } else {
        alert('Offers are available in the top navigation bell icon!');
    }
}

// Expose functions globally
window.showOffersPopup = showOffersPopup;
window.applySuggestedOffer = applySuggestedOffer;
window.closeOfferSuggestionsModal = closeOfferSuggestionsModal;
window.viewAllOffers = viewAllOffers;

// Load cart when page loads and CartManager is available
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart page DOM loaded');
    
    // Try to display cart immediately
    displayCart();
    
    // Load available offers and claimed offers
    loadOffers();
    
    // Also set up a fallback in case CartManager loads later
    const checkCartManager = setInterval(() => {
        if (typeof cartManager !== 'undefined') {
            clearInterval(checkCartManager);
            console.log('CartManager found, refreshing display');
            displayCart(); // Refresh display with CartManager
        }
    }, 100);
    
    // Stop checking after 5 seconds to avoid infinite loop
    setTimeout(() => {
        clearInterval(checkCartManager);
        console.log('CartManager check timeout - using fallback');
    }, 5000);
});

// Load offers from the database
function loadOffers() {
    console.log('Loading offers...');
    
    // Load available offers
    fetch('/offers/available')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Available offers loaded:', data.offers);
                displayAvailableOffers(data.offers);
            } else {
                console.log('No available offers or error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading available offers:', error);
        });
    
    // Load claimed offers
    fetch('/offers/my-claims')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Claimed offers loaded:', data.claims);
                displayClaimedOffers(data.claims);
            } else {
                console.log('No claimed offers or error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading claimed offers:', error);
        });
}

// Display available offers to claim
function displayAvailableOffers(offers) {
    const offersContainer = document.getElementById('available-offers-container');
    if (!offersContainer || offers.length === 0) return;
    
    let offersHTML = '<h3 class="text-lg font-semibold text-gray-900 mb-3">Available Offers</h3>';
    offersHTML += '<div class="space-y-2">';
    
    offers.forEach(offer => {
        offersHTML += `
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-semibold text-green-800">${offer.title}</h4>
                        <p class="text-sm text-green-700 mb-2">${offer.description}</p>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">${offer.discount}% OFF</span>
                            <span class="text-green-600">Code: ${offer.code}</span>
                        </div>
                    </div>
                    <button onclick="claimOffer('${offer.code}', this)" 
                            class="bg-green-600 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-green-700 transition-colors">
                        Claim
                    </button>
                </div>
            </div>
        `;
    });
    
    offersHTML += '</div>';
    offersContainer.innerHTML = offersHTML;
}

// Display claimed offers that can be applied
function displayClaimedOffers(claims) {
    const claimedOffersContainer = document.getElementById('claimed-offers-container');
    if (!claimedOffersContainer) return;
    
    const activeClaims = claims.filter(claim => claim.status === 'active');
    
    if (activeClaims.length === 0) {
        claimedOffersContainer.style.display = 'none';
        return;
    }
    
    claimedOffersContainer.style.display = 'block';
    let claimsHTML = '<h3 class="text-lg font-semibold text-gray-900 mb-3">Your Claimed Offers</h3>';
    claimsHTML += '<div class="space-y-2">';
    
    activeClaims.forEach(claim => {
        claimsHTML += `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-semibold text-blue-800">${claim.offer.title}</h4>
                        <p class="text-sm text-blue-700 mb-2">${claim.offer.description}</p>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">${claim.offer.discount}% OFF</span>
                            <span class="text-blue-600">Code: ${claim.offer.code}</span>
                        </div>
                    </div>
                    <button onclick="applyClaimedOffer(${claim.id}, this)" 
                            class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm font-semibold hover:bg-blue-700 transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        `;
    });
    
    claimsHTML += '</div>';
    claimedOffersContainer.innerHTML = claimsHTML;
}

// Debug function to check applied offer
function debugAppliedOffer() {
    console.log('=== DEBUG APPLIED OFFER ===');
    console.log('cartManager available:', typeof cartManager !== 'undefined');
    if (typeof cartManager !== 'undefined') {
        console.log('cartManager.getAppliedOffer:', typeof cartManager.getAppliedOffer);
        console.log('Offer from cartManager:', cartManager.getAppliedOffer());
    }
    console.log('localStorage applied_offer:', localStorage.getItem('applied_offer'));
    const storedOffer = localStorage.getItem('applied_offer');
    if (storedOffer) {
        console.log('Parsed offer from localStorage:', JSON.parse(storedOffer));
    }
    
    // Check all localStorage keys related to offers
    console.log('All localStorage keys:');
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.includes('offer')) {
            console.log(`${key}:`, localStorage.getItem(key));
        }
    }
    console.log('=== END DEBUG ===');
}

// Expose debug function globally
window.debugAppliedOffer = debugAppliedOffer;

// Test function to manually apply an offer
function testApplyOffer() {
    const testOffer = {
        code: 'TEST20',
        discount: 20,
        title: 'Test 20% Off'
    };
    localStorage.setItem('applied_offer', JSON.stringify(testOffer));
    console.log('Test offer applied:', testOffer);
    displayCart(); // Refresh the cart display
}

// Expose test function globally
window.testApplyOffer = testApplyOffer;

// Function to apply any offer manually
function applyOfferManually(code, discount, title) {
    const offer = {
        code: code,
        discount: discount,
        title: title || `${discount}% Off`
    };
    
    try {
        if (window.cartManager && typeof window.cartManager.applyOffer === 'function') {
            window.cartManager.applyOffer(offer);
            console.log('Offer applied via cartManager:', offer);
        } else {
            localStorage.setItem('applied_offer', JSON.stringify(offer));
            console.log('Offer applied to localStorage:', offer);
        }
        
        displayCart(); // Refresh the cart display
        
        // Show beautiful success notification
        if (typeof showSuccessNotification === 'function') {
            showSuccessNotification(
                `🎉 ${offer.title} applied successfully! You'll save ${offer.discount}% on your order.`,
                'View Cart',
                '/cart'
            );
        } else if (typeof showToast === 'function') {
            showToast(`Offer ${code} applied successfully!`, 'success');
        } else {
            alert(`Offer ${code} applied successfully!`);
        }
        
    } catch (error) {
        console.error('Error applying offer:', error);
        if (typeof showToast === 'function') {
            showToast('Error applying offer', 'error');
        } else {
            alert('Error applying offer');
        }
    }
}

// Expose applyOfferManually globally
window.applyOfferManually = applyOfferManually;

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    const toastIcon = document.getElementById('toast-icon');
    const toastContent = document.getElementById('toast-content');
    
    // Set message
    toastMessage.textContent = message;
    
    // Set icon and colors based on type
    if (type === 'success') {
        toastIcon.innerHTML = `
            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        `;
        toastContent.className = 'bg-green-50 border-green-200 rounded-lg shadow-lg px-4 py-3 max-w-sm';
    } else {
        toastIcon.innerHTML = `
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
        `;
        toastContent.className = 'bg-red-50 border-red-200 rounded-lg shadow-lg px-4 py-3 max-w-sm';
    }
    
    // Show toast
    toast.classList.remove('translate-x-full');
    toast.classList.add('translate-x-0');
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        toast.classList.remove('translate-x-0');
    }, 3000);
}

// Expose showToast globally
window.showToast = showToast;

// Add proper removeCartOffer function
function removeCartOffer() {
    console.log('Removing cart offer...');
    
    try {
        if (window.cartManager && typeof window.cartManager.removeOffer === 'function') {
            window.cartManager.removeOffer();
            console.log('Offer removed via cartManager');
        } else {
            // Fallback to localStorage
            localStorage.removeItem('applied_offer');
            console.log('Offer removed from localStorage');
        }
        
        // Show success message
        if (typeof showToast === 'function') {
            showToast('Offer removed successfully', 'success');
        } else {
            alert('Offer removed successfully');
        }
        
        // Refresh cart display
        displayCart();
        
    } catch (error) {
        console.error('Error removing offer:', error);
        if (typeof showToast === 'function') {
            showToast('Error removing offer', 'error');
        } else {
            alert('Error removing offer');
        }
    }
}

// Expose removeCartOffer globally
window.removeCartOffer = removeCartOffer;

// Listen for localStorage changes (e.g., offer applied in another tab or module)
window.addEventListener('storage', function(event) {
    if (event.key === 'applied_offer') {
        console.log('localStorage changed, refreshing cart');
        displayCart();
    }
});

// Listen for custom offerApplied event
window.addEventListener('offerApplied', function(event) {
    console.log('Offer applied event received:', event.detail);
    displayCart();
});

// Function to check localStorage state
function checkLocalStorage() {
    console.log('=== LOCALSTORAGE CHECK ===');
    console.log('applied_offer:', localStorage.getItem('applied_offer'));
    console.log('momo_cart:', localStorage.getItem('momo_cart'));
    console.log('All keys:', Object.keys(localStorage));
    console.log('=== END CHECK ===');
}
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/cart/index.blade.php ENDPATH**/ ?>