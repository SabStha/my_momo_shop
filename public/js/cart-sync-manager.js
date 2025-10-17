// Cart Sync Manager - Synchronizes cart between web and mobile
class CartSyncManager {
    constructor() {
        this.cart = [];
        this.isOnline = navigator.onLine;
        this.syncInProgress = false;
        this.lastSyncTime = null;
        this.baseUrl = '/api';
        this.authToken = this.isAuthenticated();
        
        this.initializeEventListeners();
        this.loadCartFromLocalStorage();
        
        // Try to sync with server if user is authenticated
        if (this.isAuthenticated()) {
            this.loadFromServer();
        }
    }

    // Check if user is authenticated
    isAuthenticated() {
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        return userIdMeta && userIdMeta.getAttribute('content');
    }

    // Get user ID
    getUserId() {
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        return userIdMeta ? userIdMeta.getAttribute('content') : null;
    }

    // Initialize event listeners
    initializeEventListeners() {
        // Online/offline status
        window.addEventListener('online', () => {
            this.isOnline = true;
            if (this.authToken) {
                this.loadFromServer();
            }
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
        });

        // Auth status changes (for login/logout)
        const observer = new MutationObserver(() => {
            const isAuth = this.isAuthenticated();
            if (isAuth !== !!this.authToken) {
                this.authToken = isAuth;
                if (isAuth) {
                    this.loadFromServer();
                } else {
                    // User logged out, clear cart
                    this.cart = [];
                    this.saveToLocalStorage();
                    this.updateCartDisplay();
                }
            }
        });
        
        observer.observe(document.head, { childList: true, subtree: true });
    }

    // Load cart from localStorage
    loadCartFromLocalStorage() {
        const savedCart = localStorage.getItem('momo_cart');
        if (savedCart) {
            try {
                this.cart = JSON.parse(savedCart);
                this.updateCartDisplay();
            } catch (e) {
                console.error('Error loading cart from localStorage:', e);
                this.cart = [];
            }
        }
    }

    // Save cart to localStorage
    saveToLocalStorage() {
        localStorage.setItem('momo_cart', JSON.stringify(this.cart));
        this.updateCartDisplay();
    }

    // Add item to cart
    async addToCart(productId, productName, price, image = null, quantity = 1) {
        const existingItem = this.cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                id: productId,
                name: productName,
                price: parseFloat(price),
                image: image,
                quantity: quantity,
                type: 'product'
            });
        }
        
        // Save locally first
        this.saveToLocalStorage();
        
        // Sync with server if authenticated
        if (this.isAuthenticated() && this.isOnline) {
            await this.syncWithServer();
        }
        
        this.showCartModal(productId, productName, price, image, quantity);
        this.showCartToast(`${productName} added to cart!`);
        
        // Add haptic feedback on mobile
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }

    // Remove item from cart
    async removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        
        // Save locally first
        this.saveToLocalStorage();
        
        // Sync with server if authenticated
        if (this.isAuthenticated() && this.isOnline) {
            await this.syncWithServer();
        }
    }

    // Update item quantity
    async updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                await this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                
                // Save locally first
                this.saveToLocalStorage();
                
                // Sync with server if authenticated
                if (this.authToken && this.isOnline) {
                    await this.syncWithServer();
                }
            }
        }
    }

    // Clear entire cart
    async clearCart() {
        this.cart = [];
        
        // Save locally first
        this.saveToLocalStorage();
        
        // Sync with server if authenticated
        if (this.isAuthenticated() && this.isOnline) {
            await this.syncWithServer();
        }
    }

    // Sync cart with server
    async syncWithServer() {
        if (!this.isAuthenticated() || this.syncInProgress || !this.isOnline) {
            return;
        }

        this.syncInProgress = true;
        
        try {
            const response = await fetch(`${this.baseUrl}/cart/sync`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    items: this.cart
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.lastSyncTime = new Date();
                    console.log('✅ Cart synced with server successfully');
                } else {
                    console.error('❌ Cart sync failed:', data.message);
                }
            } else {
                console.error('❌ Cart sync HTTP error:', response.status);
            }
        } catch (error) {
            console.error('❌ Cart sync error:', error);
        } finally {
            this.syncInProgress = false;
        }
    }

    // Load cart from server
    async loadFromServer() {
        if (!this.isAuthenticated() || this.syncInProgress || !this.isOnline) {
            return;
        }

        this.syncInProgress = true;
        
        try {
            const response = await fetch(`${this.baseUrl}/cart`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Merge server cart with local cart (server takes precedence)
                    const serverCart = data.cart.items || [];
                    
                    // If server has items and local cart is empty or different, use server cart
                    if (serverCart.length > 0) {
                        this.cart = serverCart;
                        this.saveToLocalStorage();
                        console.log('✅ Cart loaded from server successfully');
                    }
                    
                    this.lastSyncTime = new Date();
                } else {
                    console.error('❌ Cart load failed:', data.message);
                }
            } else {
                console.error('❌ Cart load HTTP error:', response.status);
            }
        } catch (error) {
            console.error('❌ Cart load error:', error);
        } finally {
            this.syncInProgress = false;
        }
    }

    // Get cart items
    getCartItems() {
        return this.cart;
    }

    // Get cart item count
    getCartItemCount() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }

    // Get cart subtotal
    getCartSubtotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Check if cart is empty
    isCartEmpty() {
        return this.cart.length === 0;
    }

    // Update cart display (existing method from CartManager)
    updateCartDisplay() {
        // Update cart count in navigation
        const cartCountElements = document.querySelectorAll('.cart-count, [data-cart-count]');
        const itemCount = this.getCartItemCount();
        
        cartCountElements.forEach(element => {
            if (itemCount > 0) {
                element.textContent = itemCount;
                element.style.display = 'inline';
            } else {
                element.style.display = 'none';
            }
        });

        // Update cart total
        const cartTotalElements = document.querySelectorAll('.cart-total, [data-cart-total]');
        const subtotal = this.getCartSubtotal();
        
        cartTotalElements.forEach(element => {
            element.textContent = `Rs. ${subtotal.toFixed(2)}`;
        });

        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('cartUpdated', {
            detail: {
                items: this.cart,
                itemCount: itemCount,
                subtotal: subtotal
            }
        }));
    }

    // Show cart modal (existing method from CartManager)
    showCartModal(productId, productName, price, image, quantity) {
        // Implementation from original CartManager
        // This would show the cart modal when items are added
        console.log('Cart modal would show here:', { productId, productName, price, image, quantity });
    }

    // Show cart toast (existing method from CartManager)
    showCartToast(message) {
        // Implementation from original CartManager
        // This would show a toast notification
        console.log('Cart toast:', message);
    }

    // Get sync status
    getSyncStatus() {
        return {
            isOnline: this.isOnline,
            syncInProgress: this.syncInProgress,
            lastSyncTime: this.lastSyncTime,
            hasAuthToken: this.isAuthenticated()
        };
    }
}

// Initialize cart sync manager
window.cartSyncManager = new CartSyncManager();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CartSyncManager;
}
