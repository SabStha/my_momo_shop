// Cart Management System
// Prevent duplicate class declaration
if (typeof window.CartManager === 'undefined') {
    window.CartManager = class CartManager {
        constructor() {
            this.cart = this.loadCart();
            this.updateCartDisplay();
            this.initializeEventListeners();
        }

        // Load cart from localStorage
        loadCart() {
            const savedCart = localStorage.getItem('momo_cart');
            return savedCart ? JSON.parse(savedCart) : [];
        }

        // Save cart to localStorage
        saveCart() {
            localStorage.setItem('momo_cart', JSON.stringify(this.cart));
            this.updateCartDisplay();
        }

        // Add item to cart
        addToCart(productId, productName, price, image = null, quantity = 1) {
            const existingItem = this.cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                this.cart.push({
                    id: productId,
                    name: productName,
                    price: parseFloat(price),
                    image: image,
                    quantity: quantity
                });
            }
            
            this.saveCart();
            this.showCartModal(productId, productName, price, image, quantity);
            this.showCartToast(`${productName} added to cart!`);
            
            // Add haptic feedback on mobile
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        }

        // Remove item from cart
        removeFromCart(productId) {
            this.cart = this.cart.filter(item => item.id !== productId);
            this.saveCart();
            this.showCartToast('Item removed from cart');
        }

        // Update item quantity
        updateQuantity(productId, quantity) {
            const item = this.cart.find(item => item.id === productId);
            if (item) {
                if (quantity <= 0) {
                    this.removeFromCart(productId);
                } else {
                    item.quantity = quantity;
                    this.saveCart();
                }
            }
        }

        // Get cart total (with offer discount if applied)
        getCartTotal() {
            let subtotal = this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            const offer = this.getAppliedOffer();
            let discountAmount = 0;
            if (offer && offer.discount) {
                discountAmount = subtotal * (offer.discount / 100);
            }
            return subtotal - discountAmount;
        }

        // Get applied offer from localStorage
        getAppliedOffer() {
            const offer = localStorage.getItem('applied_offer');
            return offer ? JSON.parse(offer) : null;
        }

        // Apply an offer and save to localStorage
        applyOffer(offer) {
            localStorage.setItem('applied_offer', JSON.stringify(offer));
            this.updateCartDisplay();
        }

        // Remove applied offer
        removeOffer() {
            localStorage.removeItem('applied_offer');
            this.updateCartDisplay();
        }

        // Get cart item count
        getCartItemCount() {
            return this.cart.reduce((count, item) => count + item.quantity, 0);
        }

        // Show cart modal
        showCartModal(productId, productName, price, image, quantity) {
            const modal = document.getElementById('cart-modal');
            const modalContent = document.getElementById('cart-modal-content');
            const addedItemDetails = document.getElementById('added-item-details');
            const cartTotalAmount = document.getElementById('cart-total-amount');
            const cartItemCount = document.getElementById('cart-item-count');
            const cartModalItemCount = document.getElementById('cart-modal-item-count');

            // Check if cart modal elements exist before trying to use them
            if (!modal || !modalContent || !addedItemDetails || !cartTotalAmount || !cartItemCount || !cartModalItemCount) {
                console.log('Cart modal elements not found on this page, skipping modal display');
                return;
            }

            // Populate added item details
            addedItemDetails.innerHTML = `
                <div class="flex items-center gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                    ${image ? `<img src="${image}" alt="${productName}" class="w-16 h-16 object-cover rounded-lg">` : 
                    `<div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>`}
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${productName}</h4>
                        <p class="text-sm text-gray-600">Quantity: ${quantity}</p>
                        <p class="text-lg font-bold text-[#6E0D25]">${window.currencySymbol}${(price * quantity).toFixed(2)}</p>
                    </div>
                </div>
            `;

            // Show package contents if it's a bulk package
            this.showPackageContents(productId, productName);

            // Update cart summary
            cartTotalAmount.textContent = `${window.currencySymbol}${this.getCartTotal().toFixed(2)}`;
            cartItemCount.textContent = `${this.getCartItemCount()} items`;
            cartModalItemCount.textContent = this.getCartItemCount();

            // Show modal with animation
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);

            // Load quick add suggestions
            this.loadQuickAddSuggestions();
        }

        // Show package contents for bulk packages
        showPackageContents(productId, productName) {
            const packageContents = document.getElementById('package-contents');
            const packageItemsList = document.getElementById('package-items-list');
            
            if (!packageContents || !packageItemsList) {
                console.log('Package contents elements not found');
                return;
            }

            console.log('showPackageContents called with:', productId, productName);

            // Check if it's a bulk package (any package that's not a regular product)
            if (productId.startsWith('bulk-') || productName.includes('Pack') || productName.includes('Feast') || productName.includes('Saver')) {
                // Try to get package data from the page
                const bulkPackageData = this.getBulkPackageData(productId, productName);
                
                console.log('Found package data:', bulkPackageData);
                
                if (bulkPackageData && bulkPackageData.items && Array.isArray(bulkPackageData.items)) {
                    console.log('Displaying package contents:', bulkPackageData.items);
                    let itemsHtml = '';
                    bulkPackageData.items.forEach(item => {
                        const quantity = item.quantity ? `${item.quantity} pcs` : '1 pc';
                        itemsHtml += `
                            <div class="flex justify-between items-center py-3 px-4 bg-white rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                                <span class="text-sm text-gray-700 font-medium">${item.name}</span>
                                <span class="text-sm font-semibold text-[#6E0D25]">${quantity}</span>
                            </div>
                        `;
                    });
                    
                    packageItemsList.innerHTML = itemsHtml;
                    packageContents.classList.remove('hidden');
                } else {
                    console.log('No package items found or invalid data structure');
                    console.log('Package data:', bulkPackageData);
                    packageContents.classList.add('hidden');
                }
            } else {
                console.log('Not a bulk package, hiding contents');
                packageContents.classList.add('hidden');
            }
        }

        // Get bulk package data from page context
        getBulkPackageData(productId, productName) {
            // Try to find package data from the bulk packages on the page
            try {
                console.log('Looking for package:', productId, productName);
                console.log('Available packages:', window.bulkPackages);
                console.log('Available packages list:', window.bulkPackagesList);
                
                // First try to find by exact name match in the packages list
                if (window.bulkPackagesList && Array.isArray(window.bulkPackagesList)) {
                    const packageData = window.bulkPackagesList.find(pkg => pkg.name === productName);
                    if (packageData) {
                        console.log('Found package by exact name:', packageData);
                        return packageData;
                    }
                    
                    // Try partial name match
                    const partialMatch = window.bulkPackagesList.find(pkg => 
                        productName.includes(pkg.name) || pkg.name.includes(productName)
                    );
                    if (partialMatch) {
                        console.log('Found package by partial name match:', partialMatch);
                        return partialMatch;
                    }
                }
                
                // Try to find by package ID (for bulk packages with ID like "bulk-1")
                if (productId.startsWith('bulk-')) {
                    const packageId = parseInt(productId.replace('bulk-', ''));
                    if (window.bulkPackagesList && Array.isArray(window.bulkPackagesList)) {
                        const packageData = window.bulkPackagesList.find(pkg => pkg.id === packageId);
                        if (packageData) {
                            console.log('Found package by ID:', packageData);
                            return packageData;
                        }
                    }
                }
                
                // Try to find in the organized packages structure
                if (window.bulkPackages) {
                    // Check both cooked and frozen packages
                    for (const type of ['cooked', 'frozen']) {
                        if (window.bulkPackages[type] && typeof window.bulkPackages[type] === 'object') {
                            for (const packageKey in window.bulkPackages[type]) {
                                const packageData = window.bulkPackages[type][packageKey];
                                if (packageData && packageData.name === productName) {
                                    console.log('Found package in organized structure:', packageData);
                                    return packageData;
                                }
                            }
                        }
                    }
                }
                
                // Fallback: check if we have package data in localStorage
                const storedPackages = localStorage.getItem('bulkPackages');
                if (storedPackages) {
                    try {
                        const packages = JSON.parse(storedPackages);
                        const packageKey = productId.replace('bulk-', '');
                        if (packages[packageKey]) {
                            console.log('Found package in localStorage:', packages[packageKey]);
                            return packages[packageKey];
                        }
                    } catch (e) {
                        console.log('Error parsing localStorage packages:', e);
                    }
                }
                
                // Last resort: try to fetch from server
                console.log('Attempting to fetch from server...');
                this.fetchPackageDataFromServer(productId, productName);
                
                console.log('Package not found in any location');
                return null;
            } catch (error) {
                console.log('Could not load package data:', error);
                return null;
            }
        }

        // Fetch package data from server as fallback
        async fetchPackageDataFromServer(productId, productName) {
            try {
                const packageKey = productId.replace('bulk-', '');
                const response = await fetch(`/api/bulk-packages/${packageKey}`);
                
                if (response.ok) {
                    const packageData = await response.json();
                    console.log('Fetched package data from server:', packageData);
                    
                    // Store in localStorage for future use
                    const storedPackages = JSON.parse(localStorage.getItem('bulkPackages') || '{}');
                    storedPackages[packageKey] = packageData;
                    localStorage.setItem('bulkPackages', JSON.stringify(storedPackages));
                    
                    // Update the modal with the fetched data
                    this.showPackageContents(productId, productName);
                    
                    return packageData;
                }
            } catch (error) {
                console.log('Could not fetch package data from server:', error);
            }
            return null;
        }

        // Close cart modal
        closeCartModal() {
            const modal = document.getElementById('cart-modal');
            const modalContent = document.getElementById('cart-modal-content');
            
            // Check if cart modal elements exist before trying to use them
            if (!modal || !modalContent) {
                console.log('Cart modal elements not found on this page, skipping modal close');
                return;
            }
            
            modalContent.classList.add('scale-95', 'opacity-0');
            modalContent.classList.remove('scale-100', 'opacity-100');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Show cart toast
        showCartToast(message) {
            const toast = document.getElementById('cart-toast');
            const toastMessage = document.getElementById('cart-toast-message');
            
            // Check if toast elements exist before trying to use them
            if (!toast || !toastMessage) {
                console.log('Cart toast elements not found on this page, skipping toast display');
                return;
            }
            
            toastMessage.textContent = message;
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
            
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                toast.classList.remove('translate-x-0');
            }, 3000);
        }

        // Update cart display in navigation
        updateCartDisplay() {
            const cartCountElements = document.querySelectorAll('.cart-count');
            const cartTotalElements = document.querySelectorAll('.cart-total');
            
            // Check if cart display elements exist before trying to update them
            if (cartCountElements.length === 0 && cartTotalElements.length === 0) {
                console.log('Cart display elements not found on this page, skipping display update');
                return;
            }
            
            const offer = this.getAppliedOffer();
            const itemCount = this.getCartItemCount();
            const subtotal = this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            let discountAmount = 0;
            if (offer && offer.discount) {
                discountAmount = subtotal * (offer.discount / 100);
            }
            const total = subtotal - discountAmount;

            cartCountElements.forEach(element => {
                if (element) {
                    element.textContent = itemCount;
                    element.style.display = itemCount > 0 ? 'block' : 'none';
                }
            });
            cartTotalElements.forEach(element => {
                if (element) {
                    element.textContent = `${window.currencySymbol}${total.toFixed(2)}`;
                }
            });
        }

        // Load quick add suggestions
        loadQuickAddSuggestions() {
            const suggestionsContainer = document.getElementById('quick-add-suggestions');
            
            // Check if suggestions container exists before trying to use it
            if (!suggestionsContainer) {
                console.log('Quick add suggestions container not found on this page, skipping suggestions load');
                return;
            }
            
            // Sample suggestions - in a real app, this would come from an API
            const suggestions = [
                { id: 'suggest1', name: 'Steamed Momo', price: 12.99, image: null },
                { id: 'suggest2', name: 'Fried Momo', price: 14.99, image: null },
                { id: 'suggest3', name: 'Jhol Momo', price: 16.99, image: null },
                { id: 'suggest4', name: 'Cheese Momo', price: 13.99, image: null }
            ];
            
            suggestionsContainer.innerHTML = suggestions.map(item => `
                <button onclick="cartManager.addToCart('${item.id}', '${item.name}', ${item.price})" 
                        class="p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors text-left">
                    <div class="text-xs font-semibold text-gray-900">${item.name}</div>
                    <div class="text-xs text-[#6E0D25] font-bold">${window.currencySymbol}${item.price}</div>
                </button>
            `).join('');
        }

        // Initialize event listeners
        initializeEventListeners() {
            const modal = document.getElementById('cart-modal');
            
            // Check if cart modal exists before adding event listeners
            if (!modal) {
                console.log('Cart modal not found on this page, skipping event listener initialization');
                return;
            }
            
            // Close modal on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target.id === 'cart-modal') {
                    this.closeCartModal();
                }
            });

            // Close modal on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeCartModal();
                }
            });
        }

        // Clear cart
        clearCart() {
            this.cart = [];
            this.saveCart();
            this.showCartToast('Cart cleared');
        }

        // Get cart items
        getCartItems() {
            return this.cart;
        }

        // Check if cart is empty
        isEmpty() {
            return this.cart.length === 0;
        }
    };
}

// Global cart manager instance
let cartManager;

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (!cartManager && typeof window.CartManager !== 'undefined') {
        try {
            cartManager = new window.CartManager();
            window.cartManager = cartManager;
            console.log('Global cartManager initialized');
        } catch (error) {
            console.error('Failed to initialize CartManager:', error);
        }
    } else if (cartManager) {
        console.log('CartManager already initialized, skipping');
    } else {
        console.error('CartManager class not available');
    }
});

// Global functions for onclick handlers
if (typeof addToCart === 'undefined') {
    function addToCart(productId, productName, price, image = null) {
        if (cartManager) {
            cartManager.addToCart(productId, productName, price, image);
        }
    }
}

if (typeof closeCartModal === 'undefined') {
    function closeCartModal() {
        if (cartManager) {
            cartManager.closeCartModal();
        }
    }
}

if (typeof viewCart === 'undefined') {
    function viewCart() {
        window.location.href = '/cart';
    }
}

if (typeof checkout === 'undefined') {
    function checkout() {
        window.location.href = '/checkout';
    }
}

// Cart page functions
if (typeof updateQuantity === 'undefined') {
    function updateQuantity(productId, newQuantity) {
        if (cartManager) {
            cartManager.updateQuantity(productId, parseInt(newQuantity));
            updateCartPage();
            // Also call the cart page's displayCart function if it exists
            if (typeof displayCart === 'function') {
                displayCart();
            }
        }
    }
}

if (typeof removeFromCart === 'undefined') {
    function removeFromCart(productId) {
        if (cartManager) {
            cartManager.removeFromCart(productId);
            updateCartPage();
            // Also call the cart page's displayCart function if it exists
            if (typeof displayCart === 'function') {
                displayCart();
            }
        }
    }
}

if (typeof updateCartPage === 'undefined') {
    function updateCartPage() {
        console.log('updateCartPage called');
        if (!cartManager) {
            console.log('No cartManager available');
            return;
        }
        
        const cartItems = cartManager.getCartItems();
        console.log('Cart items:', cartItems);
        console.log('Cart items length:', cartItems.length);
        
        const container = document.getElementById('cart-items-container');
        const summaryContainer = document.getElementById('cart-summary-items');
        const checkoutBtn = document.getElementById('checkout-btn');
        const clearCartBtn = document.getElementById('clear-cart-btn');
        
        console.log('Container found:', !!container);
        console.log('Summary container found:', !!summaryContainer);
        
        if (!container) {
            console.log('Not on cart page - no cart-items-container found');
            return; // Not on cart page
        }
        
        if (cartItems.length === 0) {
            console.log('Cart is empty, showing empty message');
            // Show empty cart
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
            
            if (summaryContainer) summaryContainer.innerHTML = '';
            if (checkoutBtn) checkoutBtn.disabled = true;
            if (clearCartBtn) clearCartBtn.style.display = 'none';
            
            // Update totals
            const subtotalEl = document.getElementById('cart-subtotal');
            const taxEl = document.getElementById('cart-tax');
            const totalEl = document.getElementById('cart-total');
            if (subtotalEl) subtotalEl.textContent = `${window.currencySymbol}0.00`;
            if (taxEl) taxEl.textContent = `${window.currencySymbol}0.00`;
            if (totalEl) totalEl.textContent = `${window.currencySymbol}0.00`;
            
            return;
        }
        
        console.log('Rendering cart items...');
        // Show cart items
        let cartItemsHtml = '';
        let summaryItemsHtml = '';
        let subtotal = 0;
        
        cartItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            cartItemsHtml += `
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
                            <p class="text-sm text-gray-500">${window.currencySymbol}${item.price.toFixed(2)} each</p>
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
                            <p class="text-sm font-bold text-[#6E0D25]">${window.currencySymbol}${itemTotal.toFixed(2)}</p>
                            <button onclick="removeFromCart('${item.id}')" 
                                    class="text-xs text-red-600 hover:text-red-800 transition-colors mt-1">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            summaryItemsHtml += `
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">${item.name} × ${item.quantity}</span>
                    <span class="font-medium text-gray-900">${window.currencySymbol}${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });
        
        console.log('Setting container HTML...');
        container.innerHTML = cartItemsHtml;
        if (summaryContainer) summaryContainer.innerHTML = summaryItemsHtml;
        
        // Calculate totals
        const deliveryFee = subtotal >= 25 ? 0 : 5;
        const tax = subtotal * 0.13;
        const total = subtotal + deliveryFee + tax;
        
        console.log('Totals - Subtotal:', subtotal, 'Tax:', tax, 'Total:', total);
        
        // Update totals
        const subtotalEl = document.getElementById('cart-subtotal');
        const taxEl = document.getElementById('cart-tax');
        const totalEl = document.getElementById('cart-total');
        const deliveryEl = document.getElementById('cart-delivery');
        
        if (subtotalEl) subtotalEl.textContent = `${window.currencySymbol}${subtotal.toFixed(2)}`;
        if (taxEl) taxEl.textContent = `${window.currencySymbol}${tax.toFixed(2)}`;
        if (totalEl) totalEl.textContent = `${window.currencySymbol}${total.toFixed(2)}`;
        if (deliveryEl) deliveryEl.textContent = deliveryFee === 0 ? 'Free' : `${window.currencySymbol}${deliveryFee.toFixed(2)}`;
        
        // Enable/disable buttons
        if (checkoutBtn) checkoutBtn.disabled = false;
        if (clearCartBtn) clearCartBtn.style.display = 'block';
        
        console.log('Cart page updated successfully');
    }
}

if (typeof proceedToCheckout === 'undefined') {
    function proceedToCheckout() {
        window.location.href = '/checkout';
    }
}

if (typeof clearCart === 'undefined') {
    function clearCart() {
        if (cartManager) {
            cartManager.clearCart();
            updateCartPage();
            // Also call the cart page's displayCart function if it exists
            if (typeof displayCart === 'function') {
                displayCart();
            }
        }
    }
}

// Add to cart button enhancement
if (typeof enhanceAddToCartButtons === 'undefined') {
    function enhanceAddToCartButtons() {
        document.querySelectorAll('[data-add-to-cart]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productPrice = this.dataset.productPrice;
                const productImage = this.dataset.productImage;
                
                // Add loading state
                const originalText = this.innerHTML;
                this.innerHTML = `
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                        <span>Adding...</span>
                    </div>
                `;
                this.disabled = true;
                
                // Simulate API call delay
                setTimeout(() => {
                    addToCart(productId, productName, productPrice, productImage);
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 500);
            });
        });
    }
}

// Initialize enhanced buttons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof enhanceAddToCartButtons === 'function') {
        enhanceAddToCartButtons();
    }
});

// Expose offer methods globally
window.applyCartOffer = function(offer) {
    if (cartManager) {
        cartManager.applyOffer(offer);
    }
}
window.removeCartOffer = function() {
    if (cartManager) {
        cartManager.removeOffer();
    }
}

// Apply claimed offer to cart (moved from special-offers.js)
if (typeof applyClaimedOffer === 'undefined') {
    function applyClaimedOffer(claimId, button) {
        // Add loading state
        const originalText = button.innerHTML;
        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs">Applying...</span>
            </div>
        `;
        button.disabled = true;

        // Make API call to apply the claimed offer
        fetch('/offers/apply', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                claim_id: claimId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Offer applied successfully:', data.offer);
                
                // Always store offer in localStorage, regardless of cartManager availability
                const offerData = {
                    code: data.offer.code,
                    discount: data.offer.discount,
                    title: data.offer.title,
                    applied_at: new Date().toISOString()
                };
                
                // Store in localStorage
                localStorage.setItem('applied_offer', JSON.stringify(offerData));
                console.log('Offer stored in localStorage:', offerData);
                
                // Also try to use cartManager if available
                if (window.cartManager && typeof window.cartManager.applyOffer === 'function') {
                    window.cartManager.applyOffer(offerData);
                    console.log('Offer also applied via cartManager');
                } else {
                    console.log('cartManager not available, using localStorage only');
                }
                
                // Update button to show applied state with "Go to Cart" option
                button.innerHTML = `
                    <div class="flex flex-col items-center space-y-1">
                        <span class="text-xs text-green-100">✓ Applied!</span>
                        <a href="/cart" class="text-xs bg-white bg-opacity-20 text-white px-2 py-1 rounded hover:bg-opacity-30 transition-colors">
                            Go to Cart
                        </a>
                    </div>
                `;
                button.className = 'w-full bg-green-600 text-white px-3 py-2 rounded-md text-xs font-semibold cursor-not-allowed';
                button.disabled = true;
                
                // Show beautiful success notification with "Go to Cart" action
                showSuccessNotification(
                    `🎉 ${data.offer.title} applied successfully! You'll save ${data.offer.discount}% on your order.`,
                    'View Cart',
                    '/cart'
                );
                
                // Update cart summary if on cart page
                if (typeof displayCart === 'function') {
                    displayCart();
                    console.log('displayCart called to refresh cart');
                }
                
                // Dispatch a custom event to notify other components
                window.dispatchEvent(new CustomEvent('offerApplied', { detail: offerData }));
                
            } else {
                console.error('Failed to apply offer:', data.message);
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
                if (typeof showToast === 'function') {
                    showToast(data.message, 'error');
                } else {
                    alert(data.message || 'Failed to apply offer');
                }
            }
        })
        .catch(error => {
            console.error('Error applying offer:', error);
            button.innerHTML = originalText;
            button.disabled = false;
            if (typeof showToast === 'function') {
                showToast('Failed to apply offer. Please try again.', 'error');
            } else {
                alert('Failed to apply offer. Please try again.');
            }
        });
    }
}

// Expose applyClaimedOffer globally
window.applyClaimedOffer = applyClaimedOffer;

// Claim offer function (moved from special-offers.js)
if (typeof claimOffer === 'undefined') {
    function claimOffer(code, button) {
        // Add loading state
        const originalText = button.innerHTML;
        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs">Claiming...</span>
            </div>
        `;
        button.disabled = true;

        // Make real API call to claim the offer
        fetch('/offers/claim', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                offer_code: code
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button to show claimed state
                button.innerHTML = `
                    <span class="relative z-10">Claimed Successfully!</span>
                `;
                button.className = 'w-full bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold cursor-not-allowed';
                button.disabled = true;
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast(data.message, 'success');
                } else {
                    alert('Offer claimed successfully!');
                }
                
                // Reload the page after a short delay to update the modal
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
                if (typeof showToast === 'function') {
                    showToast(data.message, 'error');
                } else {
                    alert(data.message || 'Failed to claim offer');
                }
            }
        })
        .catch(error => {
            console.error('Error claiming offer:', error);
            button.innerHTML = originalText;
            button.disabled = false;
            if (typeof showToast === 'function') {
                showToast('Failed to claim offer. Please try again.', 'error');
            } else {
                alert('Failed to claim offer. Please try again.');
            }
        });
    }
}

// Expose claimOffer globally
window.claimOffer = claimOffer;

// Beautiful success notification function
if (typeof showSuccessNotification === 'undefined') {
    function showSuccessNotification(message, actionText = null, actionUrl = null) {
        // Remove any existing notifications
        const existingNotification = document.querySelector('.success-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = 'success-notification fixed top-4 right-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50 transform transition-all duration-500 translate-x-full max-w-sm';
        
        let notificationContent = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-white hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        // Add action button if provided
        if (actionText && actionUrl) {
            notificationContent += `
                <div class="mt-3 pt-3 border-t border-white border-opacity-20">
                    <a href="${actionUrl}" class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 text-white text-sm font-semibold rounded-lg hover:bg-opacity-30 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                        </svg>
                        ${actionText}
                    </a>
                </div>
            `;
        }
        
        notification.innerHTML = notificationContent;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 10);
        
        // Auto remove after 5 seconds (unless action button is clicked)
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('translate-x-full');
                notification.classList.remove('translate-x-0');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 500);
            }
        }, 5000);
    }
}

// Expose success notification globally
window.showSuccessNotification = showSuccessNotification;








