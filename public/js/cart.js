// Cart Management System
class CartManager {
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

    // Get cart total
    getCartTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
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

        // Populate added item details
        addedItemDetails.innerHTML = `
            <div class="flex items-center gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                ${image ? `<img src="${image}" alt="${productName}" class="w-16 h-16 object-cover rounded-lg">` : 
                `<div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>`}
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">${productName}</h4>
                    <p class="text-sm text-gray-600">Quantity: ${quantity}</p>
                    <p class="text-lg font-bold text-[#6E0D25]">$${(price * quantity).toFixed(2)}</p>
                </div>
            </div>
        `;

        // Update cart summary
        cartTotalAmount.textContent = `$${this.getCartTotal().toFixed(2)}`;
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

    // Close cart modal
    closeCartModal() {
        const modal = document.getElementById('cart-modal');
        const modalContent = document.getElementById('cart-modal-content');
        
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
        
        const itemCount = this.getCartItemCount();
        const total = this.getCartTotal();
        
        cartCountElements.forEach(element => {
            element.textContent = itemCount;
            element.style.display = itemCount > 0 ? 'block' : 'none';
        });
        
        cartTotalElements.forEach(element => {
            element.textContent = `$${total.toFixed(2)}`;
        });
    }

    // Load quick add suggestions
    loadQuickAddSuggestions() {
        const suggestionsContainer = document.getElementById('quick-add-suggestions');
        
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
                <div class="text-xs text-[#6E0D25] font-bold">$${item.price}</div>
            </button>
        `).join('');
    }

    // Initialize event listeners
    initializeEventListeners() {
        // Close modal on backdrop click
        document.getElementById('cart-modal').addEventListener('click', (e) => {
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
}

// Global cart manager instance
let cartManager;

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    cartManager = new CartManager();
});

// Global functions for onclick handlers
function addToCart(productId, productName, price, image = null) {
    if (cartManager) {
        cartManager.addToCart(productId, productName, price, image);
    }
}

function closeCartModal() {
    if (cartManager) {
        cartManager.closeCartModal();
    }
}

function viewCart() {
    window.location.href = '/cart';
}

function checkout() {
    window.location.href = '/checkout';
}

// Cart page functions
function updateQuantity(productId, newQuantity) {
    if (cartManager) {
        cartManager.updateQuantity(productId, parseInt(newQuantity));
        updateCartPage();
    }
}

function removeFromCart(productId) {
    if (cartManager) {
        cartManager.removeFromCart(productId);
        updateCartPage();
    }
}

function updateCartPage() {
    if (window.location.pathname === '/cart') {
        location.reload();
    }
}

function clearCart() {
    if (cartManager) {
        cartManager.clearCart();
        updateCartPage();
    }
}

// Add to cart button enhancement
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

// Initialize enhanced buttons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    enhanceAddToCartButtons();
}); 