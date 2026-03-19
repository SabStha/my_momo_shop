// Utility Functions
function formatCurrency(amount) {
    return window.currencySymbol + ' ' + new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function calculateTotal() {
    return cart.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
}

// Global variables
let products = [];
let cart = [];
let activeOrders = [];
let currentEditingOrder = null;

// Order method and table selection
let currentOrderMethod = null;
let selectedTableId   = null;
let selectedTableName = null; // set by tableSelected browser event from PosTableGrid

// Initialize the POS system
document.addEventListener('DOMContentLoaded', function() {
    // Initialize total dropdown
    const totalHeader = document.getElementById('totalHeader');
    const totalDetails = document.getElementById('totalDetails');
    const totalArrow = document.getElementById('totalArrow');

    if (totalHeader && totalDetails && totalArrow) {
        totalHeader.addEventListener('click', function() {
            totalDetails.classList.toggle('show');
            totalArrow.classList.toggle('rotate');
        });
    }

    // Load products
    loadProducts();
    
    // Update business status indicator
    updateBusinessStatus();
    
    // Set up search functionality
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Search across all products
            const searchTerm = this.value.toLowerCase();
            if (searchTerm === '') {
                // If search is empty, show the current category
                const activeButton = document.querySelector('.category-btn.active');
                if (activeButton && activeButton.onclick) {
                    const match = activeButton.onclick.toString().match(/'([^']+)'/);
                    if (match) {
                        filterProducts(match[1]);
                        return;
                    }
                }
                filterProducts('combo'); // Default to combos
                return;
            }
            
            const filteredProducts = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                (product.description && product.description.toLowerCase().includes(searchTerm))
            );
            
            renderProducts(filteredProducts);
        });
    }

    loadActiveOrders();
    setupEventListeners();
    
    // Refresh active orders every 30 seconds
    setInterval(() => {
        loadActiveOrders();
    }, 30000);
});

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredProducts = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm)
            );
            renderProducts(filteredProducts);
        });
    }
}

// Load products from the API
async function loadProducts() {
    try {
        const response = await fetch('/api/pos/products');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        products = Array.isArray(data) ? data : [];
        
        // Group products by categories and subcategories (same as home menu)
        window.productCategories = {
            combos: products.filter(p => p.tag === 'combos'),
            foods: products.filter(p => p.tag === 'foods'),
            drinks: products.filter(p => p.tag === 'drinks'),
            desserts: products.filter(p => p.tag === 'desserts'),
            buffItems: products.filter(p => p.tag === 'foods' && p.category === 'buff'),
            chickenItems: products.filter(p => p.tag === 'foods' && p.category === 'chicken'),
            vegItems: products.filter(p => p.tag === 'foods' && p.category === 'veg'),
            mainItems: products.filter(p => p.tag === 'foods' && p.category === 'main'),
            sideSnacks: products.filter(p => p.tag === 'foods' && p.category === 'side'),
            hotDrinks: products.filter(p => p.tag === 'drinks' && p.category === 'hot'),
            coldDrinks: products.filter(p => p.tag === 'drinks' && p.category === 'cold'),
            bobaDrinks: products.filter(p => p.tag === 'drinks' && p.category === 'boba')
        };
        
        // Show combos by default
        filterProducts('combo');
    } catch (error) {
        console.error('Error loading products:', error);
        // Show error message to user
        const productsSection = document.getElementById('productsSection');
        if (productsSection) {
            productsSection.innerHTML = `
                <div class="col-span-full text-center p-4">
                    <p class="text-red-500">Failed to load products. Please try again.</p>
                </div>
            `;
        }
    }
}

// Render products in the grid
function renderProducts(products) {
    const productsGrid = document.getElementById('productsGrid');
    if (!productsGrid) return;

    if (products.length === 0) {
        productsGrid.innerHTML = `
            <div class="col-span-full text-center p-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No products found</h3>
                <p class="text-gray-500">Try adjusting your search or browse different categories</p>
            </div>
        `;
        return;
    }

    // Clear existing products
    productsGrid.innerHTML = '';

    products.forEach((product, index) => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card cursor-pointer group';
        productCard.style.animationDelay = `${index * 0.1}s`;
        productCard.onclick = () => addToCart(product);
        
        productCard.innerHTML = `
            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden rounded-t-lg">
                <img src="${product.image ? '/storage/' + product.image : '/images/no-image.svg'}" 
                     alt="${product.name}" 
                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                     onerror="this.src='/images/no-image.svg'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute top-1 right-1 bg-white/90 backdrop-blur-sm rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <i class="fas fa-plus text-blue-600 text-xs"></i>
                </div>
            </div>
            <div class="p-2 bg-white">
                <h3 class="font-semibold text-xs text-gray-800 mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">${product.name}</h3>
                <p class="text-xs text-gray-600 mb-2 line-clamp-1 hidden">${product.description || 'Delicious item available now'}</p>
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="font-bold text-sm text-blue-600">Rs ${parseFloat(product.price).toFixed(2)}</span>
                        ${product.stock !== undefined ? `<span class="text-xs text-gray-500">Stock: ${product.stock}</span>` : ''}
                    </div>
                    <button class="btn-primary px-2 py-1 text-xs font-semibold rounded-lg flex items-center space-x-1 hover:shadow-md transition-all duration-200">
                        <i class="fas fa-plus text-xs"></i>
                        <span class="hidden sm:inline">Add</span>
                    </button>
                </div>
            </div>
        `;
        
        // Add fade-in animation
        productCard.classList.add('fade-in');
        productsGrid.appendChild(productCard);
    });

    // Scroll to top with smooth animation
    if (typeof smoothScrollToTop === 'function') {
        smoothScrollToTop();
    }
}

// Add this function before loadActiveOrders
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Load active orders
async function loadActiveOrders() {
    try {
        // Get branch ID — meta tag first, then localStorage fallback
        let branchId = document.querySelector('meta[name="branch-id"]')?.content;
        if (!branchId) {
            const posData = JSON.parse(localStorage.getItem('pos_branch') || '{}');
            branchId = posData.id;
        }
        if (!branchId) {
            console.error('Branch ID not found in meta tag or localStorage');
            return;
        }

        // Show loading state
        const activeOrdersContainer = document.getElementById('activeOrders');
        if (!activeOrdersContainer) return;
        
        activeOrdersContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                <p class="text-sm text-gray-500 mt-2">Loading orders...</p>
            </div>
        `;

        const response = await fetch(`/admin/orders/json?branch=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            console.error('Failed to load active orders:', response.status, response.statusText);
            throw new Error(`Failed to load active orders: ${response.status} ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('Active orders data received:', data);
        
        if (data.success && data.orders) {
            // Filter for pending orders only
            activeOrders = data.orders.filter(order => 
                order.status === 'pending' || order.status === 'preparing' || order.status === 'prepared'
            );
            console.log('Filtered pending orders:', activeOrders.length);
        } else {
            activeOrders = [];
            console.error('Invalid response format:', data);
        }

        activeOrdersContainer.innerHTML = '';

        if (activeOrders.length === 0) {
            activeOrdersContainer.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-green-400 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">All caught up!</p>
                    <p class="text-xs text-gray-500 mt-1">No pending orders</p>
                </div>
            `;
            // Update the order count display
            const countElement = document.getElementById('activeOrdersCount');
            if (countElement) {
                countElement.textContent = '(0)';
            }
            return;
        }

        // Sort orders by creation date (newest first)
        activeOrders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        // Get existing header and content elements
        const activeOrdersHeader = document.getElementById('activeOrdersHeader');
        const activeOrdersContent = document.getElementById('activeOrdersContent');
        const activeOrdersSection = document.getElementById('activeOrders');
        const toggleIcon = document.getElementById('ordersDropdownToggle');

        // Create container for all orders
        const allOrdersContainer = document.createElement('div');
        allOrdersContainer.className = 'space-y-2 p-2';

        // Add click handler for dropdown toggle (use .onclick to avoid duplicate listeners)
        if (activeOrdersHeader) {
            activeOrdersHeader.onclick = function() {
                const isExpanded = activeOrdersContent.style.height !== '0px';
                
                if (isExpanded) {
                    // Collapse
                    activeOrdersContent.style.height = '0px';
                    if (toggleIcon) toggleIcon.style.transform = 'rotate(0deg)';
                    if (activeOrdersSection) {
                        const defaultHeight = window.innerWidth <= 768 ? '50px' : '60px';
                        activeOrdersSection.style.height = defaultHeight;
                    }
                } else {
                    // Expand
                    const orderHeight = 60;
                    const maxHeight = window.innerWidth <= 768 ? 300 : 350;
                    const calculatedHeight = Math.min(activeOrders.length * orderHeight + 80, maxHeight);
                    
                    activeOrdersContent.style.height = `${calculatedHeight}px`;
                    if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)';
                    if (activeOrdersSection) {
                        const baseHeight = window.innerWidth <= 768 ? 50 : 60;
                        const totalHeight = baseHeight + calculatedHeight;
                        activeOrdersSection.style.height = `${totalHeight}px`;
                    }
                }
            };
        }

        // Function to create order element
        const createOrderElement = (order) => {
            // Calculate totals from items if not available
            let activeOrderSubtotal = 0;
            let activeOrderTax = 0;
            let activeOrderTotal = 0;

            // Calculate subtotal from items
            if (order.items && Array.isArray(order.items)) {
                activeOrderSubtotal = order.items.reduce((sum, item) => {
                    const itemTotal = parseFloat(item.price || 0) * parseInt(item.quantity || 0);
                    return sum + (isNaN(itemTotal) ? 0 : itemTotal);
                }, 0);
            } else if (order.subtotal) {
                activeOrderSubtotal = parseFloat(order.subtotal);
            }

            // Calculate tax (13%)
            activeOrderTax = activeOrderSubtotal * 0.13;

            // Calculate total
            activeOrderTotal = activeOrderSubtotal + activeOrderTax;

            // Ensure all values are numbers
            activeOrderSubtotal = isNaN(activeOrderSubtotal) ? 0 : activeOrderSubtotal;
            const finalTax = isNaN(activeOrderTax) ? 0 : activeOrderTax;
            activeOrderTotal = isNaN(activeOrderTotal) ? 0 : activeOrderTotal;

            // Format currency values
            const formattedSubtotal = activeOrderSubtotal.toFixed(2);
            const formattedTax = finalTax.toFixed(2);
            const formattedTotal = activeOrderTotal.toFixed(2);

            // Format dates
            const orderDate = new Date(order.created_at);
            const formattedDate = orderDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Determine order type color
            const orderTypeColor = order.order_type === 'dine_in' ? 'bg-blue-100 border-blue-300' : 'bg-green-100 border-green-300';
            const orderTypeText = order.order_type === 'dine_in' ? 'Dine In' : 'Takeaway';

            const orderElement = document.createElement('div');
            orderElement.className = `border rounded-lg p-2 bg-white shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer border-l-4 ${order.order_type === 'dine_in' ? 'border-l-blue-500' : 'border-l-green-500'}`;
            orderElement.innerHTML = `
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-gray-900 text-sm truncate">#${order.order_number}</h3>
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full ${order.order_type === 'dine_in' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'}">
                                ${orderTypeText}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 space-y-0.5">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-clock text-xs"></i>
                                <span>${formattedDate}</span>
                            </div>
                            ${order.order_type === 'dine_in' && order.table ? `
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-chair text-xs"></i>
                                    <span>Table ${order.table.name}</span>
                                </div>
                            ` : ''}
                            <div class="flex items-center gap-1">
                                <i class="fas fa-shopping-bag text-xs"></i>
                                <span>${order.items ? order.items.length : 0} items</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right ml-2">
                        <div class="text-lg font-bold text-gray-900">Rs ${formattedTotal}</div>
                        <div class="text-xs text-gray-500 flex items-center gap-1">
                            <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                            <span>Pending</span>
                        </div>
                    </div>
                </div>
            `;
            return orderElement;
        };

        // Add all orders to the dropdown container
        activeOrders.forEach(order => {
            allOrdersContainer.appendChild(createOrderElement(order));
        });

        // Clear existing content and add orders container
        if (activeOrdersContent) {
            activeOrdersContent.innerHTML = '';
            activeOrdersContent.appendChild(allOrdersContainer);
        }

        // Update the order count display
        const countElement = document.getElementById('activeOrdersCount');
        if (countElement) {
            countElement.textContent = `(${activeOrders.length})`;
        }

    } catch (error) {
        console.error('Error loading active orders:', error);
        showToast('Failed to load active orders', 'error');
        
        // Show error state in the container
        const activeOrdersContainer = document.getElementById('activeOrders');
        if (activeOrdersContainer) {
            activeOrdersContainer.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-sm text-red-500">Failed to load orders</p>
                    <p class="text-sm text-gray-500 mt-2">Please try again later</p>
        </div>
        `;
        }
    }
}

function formatOrderMethod(method) {
    if (!method) return 'N/A';
    
    // Handle different possible formats
    const methodMap = {
        'dine-in': 'Dine In',
        'dine_in': 'Dine In',
        'takeaway': 'Takeaway',
        'take-away': 'Takeaway',
        'take_away': 'Takeaway',
        'takeaway': 'Takeaway',
        'dinein': 'Dine In',
        'dine in': 'Dine In',
        'take away': 'Takeaway',
        'dine_in': 'Dine In',
        'take_away': 'Takeaway'
    };
    
    // Convert to lowercase for comparison
    const lowerMethod = method.toLowerCase().trim();
    
    // Check if we have a direct mapping
    if (methodMap[lowerMethod]) {
        return methodMap[lowerMethod];
    }
    
    // If no direct mapping, format the string
    return method.split(/[-_\s]/).map(word => 
        word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    ).join(' ');
}

// Cart functions
function addToCart(productOrId) {
    let product;
    
    // Handle both product object and product ID
    if (typeof productOrId === 'object' && productOrId.id) {
        product = productOrId;
    } else {
        product = products.find(p => p.id === productOrId);
    }
    
    if (!product) {
        showToast('Product not found', 'error');
        return;
    }

    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            image: product.image
        });
    }
    
    updateCart();
    showToast(`${product.name} added to cart`, 'success');
}

function removeFromCart(index) {
    if (index > -1) {
        cart.splice(index, 1);
        updateCart();
        showToast('Product removed from cart', 'info');
    }
}

function updateCartItemQuantity(index, change) {
    if (index > -1 && cart[index]) {
        cart[index].quantity = Math.max(1, cart[index].quantity + change);
        updateCart();
    }
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTax = document.getElementById('cartTax');
    const cartItemCount = document.getElementById('cartItemCount');
    const emptyCartIcon = document.getElementById('emptyCartIcon');
    
    cartItems.innerHTML = '';
    let subtotal = 0;
    let totalItems = 0;

    if (cart.length === 0) {
        emptyCartIcon.classList.remove('hidden');
        cartTotal.textContent = 'Rs 0.00';
        if (cartSubtotal) cartSubtotal.textContent = 'Rs 0.00';
        if (cartTax) cartTax.textContent = 'Rs 0.00';
        if (cartItemCount) cartItemCount.textContent = '0 items';
        
        // Set cart to compact mode when empty
        setCartSize('compact');
        return;
    }

    emptyCartIcon.classList.add('hidden');

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        totalItems += item.quantity;

        const itemElement = document.createElement('div');
        itemElement.className = 'flex justify-between items-center p-2 bg-white rounded-lg border border-gray-100 shadow-sm';
        itemElement.innerHTML = `
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-sm text-gray-800 truncate">${item.name}</div>
                <div class="text-xs text-gray-500">Rs ${item.price.toFixed(2)} each</div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-1 bg-gray-50 rounded-lg p-1">
                    <button onclick="updateCartItemQuantity(${index}, -1)" 
                            class="w-6 h-6 flex items-center justify-center bg-white rounded hover:bg-gray-100 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-minus text-xs text-gray-600"></i>
                    </button>
                    <span class="w-8 text-center text-sm font-medium text-gray-800">${item.quantity}</span>
                    <button onclick="updateCartItemQuantity(${index}, 1)" 
                            class="w-6 h-6 flex items-center justify-center bg-white rounded hover:bg-gray-100 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-plus text-xs text-gray-600"></i>
                    </button>
                </div>
                <div class="text-right">
                    <div class="font-bold text-sm text-gray-800">Rs ${itemTotal.toFixed(2)}</div>
                </div>
                <button onclick="removeFromCart(${index})" class="text-gray-400 hover:text-red-500 transition-colors duration-200 p-1">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
        cartItems.appendChild(itemElement);
    });

    // Calculate tax and total
    const tax = subtotal * 0.10; // 10% tax
    const total = subtotal + tax;

    // Update all cart displays
    cartTotal.textContent = `Rs ${total.toFixed(2)}`;
    const cartTotalFull = document.getElementById('cartTotalFull');
    if (cartTotalFull) cartTotalFull.textContent = `Rs ${total.toFixed(2)}`;
    if (cartSubtotal) cartSubtotal.textContent = `Rs ${subtotal.toFixed(2)}`;
    if (cartTax) cartTax.textContent = `Rs ${tax.toFixed(2)}`;
    if (cartItemCount) cartItemCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;

    // Set cart to expanded mode when items are added
    setCartSize('expanded', totalItems);
}

// Handle window resize for cart sizing
window.addEventListener('resize', function() {
    if (cart && cart.length > 0) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        setCartSize('expanded', totalItems);
    } else {
        setCartSize('compact');
    }
});

// Dynamic cart sizing function
function setCartSize(mode, itemCount = 0) {
    const cartSection = document.getElementById('cartSection');
    const cartContentArea = document.querySelector('#cartSection .flex-1');
    const cartSummary = document.getElementById('cartSummary');
    const cartActions = document.getElementById('cartActions');
    
    if (!cartSection) return;
    
    if (mode === 'compact') {
        // Compact mode - minimal height when empty
        cartSection.style.height = '150px';
        if (cartContentArea) cartContentArea.style.height = '50px';
        if (cartSummary) {
            cartSummary.style.height = '60px';
            cartSummary.style.opacity = '0.7';
            // Show compact summary, hide full summary
            const compactSummary = document.getElementById('compactSummary');
            const fullSummary = document.getElementById('fullSummary');
            if (compactSummary) compactSummary.classList.remove('hidden');
            if (fullSummary) fullSummary.classList.add('hidden');
        }
        if (cartActions) {
            cartActions.style.height = '40px';
            cartActions.style.opacity = '0.7';
        }
        cartSection.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)';
        
        // Mobile adjustments
        if (window.innerWidth <= 768) {
            cartSection.style.height = '130px';
        }
        if (window.innerWidth <= 480) {
            cartSection.style.height = '120px';
        }
    } else if (mode === 'expanded') {
        // Expanded mode - grows based on number of items
        const baseHeight = 250;
        const itemHeight = 60; // Height per item
        const maxHeight = 400; // Maximum height before scrolling
        
        // Calculate dynamic height based on items
        const calculatedHeight = Math.min(baseHeight + (itemCount * itemHeight), maxHeight);
        const contentHeight = Math.max(120, calculatedHeight - 110); // Reserve space for header, summary, buttons
        
        cartSection.style.height = `${calculatedHeight}px`;
        if (cartContentArea) cartContentArea.style.height = `${contentHeight}px`;
        if (cartSummary) {
            cartSummary.style.height = '80px';
            cartSummary.style.opacity = '1';
            cartSummary.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
            cartSummary.style.borderTop = '2px solid #3b82f6';
            // Show full summary, hide compact summary
            const compactSummary = document.getElementById('compactSummary');
            const fullSummary = document.getElementById('fullSummary');
            if (compactSummary) compactSummary.classList.add('hidden');
            if (fullSummary) fullSummary.classList.remove('hidden');
        }
        if (cartActions) {
            cartActions.style.height = '50px';
            cartActions.style.opacity = '1';
        }
        cartSection.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
        
        // Mobile adjustments
        if (window.innerWidth <= 768) {
            const mobileHeight = Math.min(calculatedHeight - 30, 350);
            cartSection.style.height = `${mobileHeight}px`;
        }
        if (window.innerWidth <= 480) {
            const smallHeight = Math.min(calculatedHeight - 50, 320);
            cartSection.style.height = `${smallHeight}px`;
        }
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } shadow-lg z-50 transition-opacity duration-300`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showConfirmationModal(message, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-question text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">${message}</h3>
                <div class="flex justify-center space-x-4">
                    <button id="confirmNo" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmYes" class="px-4 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add fade-in animation
    modal.style.opacity = '0';
    modal.style.transition = 'opacity 0.3s ease-in-out';
    setTimeout(() => modal.style.opacity = '1', 10);

    return new Promise((resolve) => {
        document.getElementById('confirmYes').onclick = () => {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
                resolve(true);
            }, 300);
        };

        document.getElementById('confirmNo').onclick = () => {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
                resolve(false);
            }, 300);
        };
    });
}

function showSuccessModal(message, details = '') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">${message}</h3>
                    ${details ? `
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600">${details}</p>
                        </div>
                    ` : ''}
                    <div class="flex justify-center">
                        <button id="closeSuccess" 
                                class="px-6 py-2 bg-green-500 text-white text-sm font-medium rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Add fade-in animation
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease-in-out';
        setTimeout(() => modal.style.opacity = '1', 10);

        // Handle close button click
        const closeButton = document.getElementById('closeSuccess');
        if (closeButton) {
            closeButton.onclick = () => {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                    resolve();
                }, 300);
            };
        }

        // Handle click outside modal
        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                    resolve();
                }, 300);
            }
        };

        // Handle escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.remove();
                    resolve();
                }, 300);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    });
}

// Order method and table selection
function setOrderMethod(method) {
    const el = document.getElementById('tableSelection');
    if (!el) return;

    console.log('Setting order method to:', method);

    // Update radio button
    const radio = document.querySelector(`input[name="order_method"][value="${method}"]`);
    if (radio) {
        radio.checked = true;
    }
    
    // Update button styles
    const dineInBtn = document.getElementById('dineInBtn');
    const takeawayBtn = document.getElementById('takeawayBtn');
    const tableSelection = document.getElementById('tableSelection');
    
    if (method === 'dine-in') {
        if (dineInBtn) {
            dineInBtn.classList.add('bg-blue-600', 'text-white');
            dineInBtn.classList.remove('bg-gray-200', 'text-gray-700');
        }
        if (takeawayBtn) {
            takeawayBtn.classList.add('bg-gray-200', 'text-gray-700');
            takeawayBtn.classList.remove('bg-blue-600', 'text-white');
        }
        if (tableSelection) {
            tableSelection.classList.remove('hidden');
        }
        // Load tables when dine-in is selected
        loadTables();
    } else {
        if (takeawayBtn) {
            takeawayBtn.classList.add('bg-blue-600', 'text-white');
            takeawayBtn.classList.remove('bg-gray-200', 'text-gray-700');
        }
        if (dineInBtn) {
            dineInBtn.classList.add('bg-gray-200', 'text-gray-700');
            dineInBtn.classList.remove('bg-blue-600', 'text-white');
        }
        if (tableSelection) {
            tableSelection.classList.add('hidden');
        }
        selectedTableId = null;
    }
    
    console.log('Order method set to:', method);
    console.log('Selected table ID:', selectedTableId);
}

async function loadTables() {
    // Table selection is now handled by the PosTableGrid Livewire component.
    // If the legacy <select id="tableSelect"> no longer exists in the DOM,
    // there is nothing to populate here — the Livewire grid manages itself.
    if (!document.getElementById('tableSelect')) return;

    try {
        const branchData = JSON.parse(localStorage.getItem('pos_branch'));
        if (!branchData || !branchData.id) {
            throw new Error('Branch information not found');
        }

        console.log('Loading tables for branch:', branchData.id);

        const response = await fetch(`/api/pos/tables`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Branch-ID': branchData.id
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Received tables data:', data);

        const tableSelect = document.getElementById('tableSelect');
        if (!tableSelect) {
            throw new Error('Table select element not found');
        }

        // Clear existing options
        tableSelect.innerHTML = '<option value="">Select a table</option>';

        // Add new options
        if (data && data.length > 0) {
            data.forEach(table => {
                const option = document.createElement('option');
                option.value = table.id;
                option.textContent = `Table ${table.name} (${table.capacity} seats)`;
                const tableStatus = table.status === 'occupied' 
                    ? `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-300">Occupied</span>`
                    : `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-300">Available</span>`;
                option.innerHTML = `${table.name} (${table.capacity} seats) - ${tableStatus}`;
                option.disabled = table.status === 'occupied';
                tableSelect.appendChild(option);
            });
        } else {
            console.log('No tables found for branch');
            tableSelect.innerHTML = '<option value="">No tables available</option>';
        }

        // Show table selection
        const tableSelection = document.getElementById('tableSelection');
        if (tableSelection) {
            tableSelection.classList.remove('hidden');
        }

    } catch (error) {
        console.error('Error loading tables:', error);
        const tableSelect = document.getElementById('tableSelect');
        if (tableSelect) {
            tableSelect.innerHTML = '<option value="">Error loading tables</option>';
        }
        // Show error message to user
        showToast('Failed to load tables. Please try again.', 'error');
    }
}

// Listen for table selection from the Livewire PosTableGrid component
window.addEventListener('tableSelected', function(e) {
    console.log('[POS] tableSelected event received:', e.detail);
    selectedTableId        = e.detail.tableId;
    selectedTableName      = e.detail.tableName || ('Table ' + e.detail.tableId);
    window.continuingOrderId = null; // fresh order
    console.log('[POS] selectedTableId set to:', selectedTableId, '| name:', selectedTableName);
});
window.addEventListener('continueOrder', function(e) {
    console.log('[POS] continueOrder event received:', e.detail);
    selectedTableId          = e.detail.tableId;
    selectedTableName        = e.detail.tableName || ('Table ' + e.detail.tableId);
    window.continuingOrderId = e.detail.orderId;
    showContinueOrderBanner(e.detail.orderNumber);
});
window.addEventListener('tableDeselected', function() {
    selectedTableId          = null;
    selectedTableName        = null;
    window.continuingOrderId = null;
    hideContinueOrderBanner();
});
window.addEventListener('tableStatusChanged', function() {
    selectedTableId          = null;
    selectedTableName        = null;
    window.continuingOrderId = null;
    hideContinueOrderBanner();
});

function showContinueOrderBanner(orderNumber) {
    hideContinueOrderBanner();
    const banner = document.createElement('div');
    banner.id = 'continueOrderBanner';
    banner.style.cssText = 'background:#1d4ed8;color:white;padding:8px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;border-radius:8px;margin:6px 8px 0;';
    banner.innerHTML = `<i class="fas fa-plus-circle"></i> Adding items to order <strong>${orderNumber}</strong> <button onclick="cancelContinueOrder()" style="margin-left:auto;background:rgba(255,255,255,0.2);border:none;color:white;border-radius:4px;padding:2px 8px;cursor:pointer;font-size:12px;">Cancel</button>`;
    const cartEl = document.getElementById('cart') || document.querySelector('.cart-section');
    if (cartEl) cartEl.insertAdjacentElement('beforebegin', banner);
    else document.body.prepend(banner);
}

function hideContinueOrderBanner() {
    const b = document.getElementById('continueOrderBanner');
    if (b) b.remove();
}

function cancelContinueOrder() {
    window.continuingOrderId = null;
    hideContinueOrderBanner();
    if (window.livewire) window.livewire.emit('refreshTableGrid');
}
// Bridge Livewire component notifications to the POS toast system
window.addEventListener('posNotify', function(e) {
    if (typeof showToast === 'function') {
        showToast(e.detail.message, e.detail.type || 'info');
    }
});

function clearCart() {
    cart = [];
    updateCart();
    // Reset order method and table selection
    currentOrderMethod = null;
    selectedTableId    = null;
    selectedTableName  = null;
    if (window.livewire) window.livewire.emit('refreshTableGrid');
    // Update UI to reflect reset
    document.querySelectorAll('.order-method-btn').forEach(btn => {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    document.getElementById('tableSelection').classList.add('hidden');
}

async function createOrder() {
    try {
        // Check if business is open (cash drawer status)
        const branchId = document.querySelector('meta[name="branch-id"]')?.content || 1;
        const businessStatusResponse = await fetch(`/api/business/status/${branchId}`);
        const businessStatus = await businessStatusResponse.json();
        
        if (!businessStatus.is_open) {
            Swal.fire({
                title: '🏪 Business Closed',
                html: `
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-3">
                                <i class="fas fa-store-slash text-red-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">We're Currently Closed</h3>
                            <p class="text-gray-600 mb-4">Sorry, we're not accepting orders at the moment.</p>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-center mb-2">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <span class="font-medium text-blue-800">How to Open for Business</span>
                            </div>
                            <p class="text-sm text-blue-700">
                                To start taking orders, please open the cash drawer first in the payment manager.
                            </p>
                        </div>
                        
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-clock mr-1"></i>
                            Check back during our regular business hours
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: `
                    <i class="fas fa-check mr-2"></i>
                    Understood
                `,
                confirmButtonColor: '#3b82f6',
                confirmButtonAriaLabel: 'OK',
                customClass: {
                    popup: 'swal2-popup-business-closed',
                    title: 'swal2-title-business-closed',
                    htmlContainer: 'swal2-html-container-business-closed',
                    confirmButton: 'swal2-confirm-business-closed'
                },
                width: '420px',
                padding: '0',
                showCloseButton: true,
                closeButtonHtml: '<i class="fas fa-times"></i>',
                allowOutsideClick: true,
                allowEscapeKey: true,
                focusConfirm: true,
                backdrop: true,
                timer: null,
                timerProgressBar: false
            });
            return;
        }

        // Validate order method
        const orderMethod = document.querySelector('input[name="order_method"]:checked');
        console.log('Selected order method element:', orderMethod); // Debug log

        if (!orderMethod || !orderMethod.value) {
            Swal.fire({
                title: '🍽️ Order Method Required',
                html: `
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-3">
                                <i class="fas fa-utensils text-orange-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Select Order Type</h3>
                            <p class="text-gray-600 mb-4">Please choose how you'd like to serve this order.</p>
                        </div>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-center mb-2">
                                <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                                <span class="font-medium text-gray-800">Choose One:</span>
                            </div>
                            <div class="text-sm text-gray-700 space-y-1">
                                <div class="flex items-center">
                                    <i class="fas fa-store mr-2 text-blue-600"></i>
                                    <span><strong>Dine-in:</strong> Customer eats at the restaurant</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-bag mr-2 text-green-600"></i>
                                    <span><strong>Takeaway:</strong> Customer takes food to go</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: `
                    <i class="fas fa-check mr-2"></i>
                    Got It
                `,
                confirmButtonColor: '#f59e0b',
                customClass: {
                    popup: 'swal2-popup-business-closed',
                    title: 'swal2-title-business-closed',
                    htmlContainer: 'swal2-html-container-business-closed',
                    confirmButton: 'swal2-confirm-business-closed'
                },
                width: '420px',
                padding: '0',
                showCloseButton: true,
                allowOutsideClick: true,
                allowEscapeKey: true
            });
            return;
        }

        // Validate table selection for dine-in
        if (orderMethod.value === 'dine-in' && !selectedTableId) {
            Swal.fire({
                icon: 'error',
                title: 'Table Required',
                text: 'Please select a table for dine-in orders'
            });
            return;
        }

        // Validate items
        if (cart.length === 0) {
            Swal.fire({
                title: '🛒 Empty Cart',
                html: `
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-3">
                                <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Your Cart is Empty</h3>
                            <p class="text-gray-600 mb-4">Add some delicious items to get started!</p>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-center mb-2">
                                <i class="fas fa-lightbulb text-green-600 mr-2"></i>
                                <span class="font-medium text-green-800">Quick Tip</span>
                            </div>
                            <p class="text-sm text-green-700">
                                Browse our menu and tap the <i class="fas fa-plus text-green-600 mx-1"></i> button on any item to add it to your cart.
                            </p>
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: `
                    <i class="fas fa-utensils mr-2"></i>
                    Browse Menu
                `,
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'swal2-popup-business-closed',
                    title: 'swal2-title-business-closed',
                    htmlContainer: 'swal2-html-container-business-closed',
                    confirmButton: 'swal2-confirm-business-closed'
                },
                width: '420px',
                padding: '0',
                showCloseButton: true,
                allowOutsideClick: true,
                allowEscapeKey: true
            });
            return;
        }

        // Calculate totals
        const subtotal = calculateTotal();
        const TAX_RATE = 0.13; // 13% VAT
        const tax = subtotal * TAX_RATE;
        const total = subtotal + tax;

        // Build itemized rows
        const fmt = (n) => 'Rs ' + parseFloat(n).toFixed(2);
        const itemRows = cart.map(item => {
            const lineTotal = item.price * item.quantity;
            return `
                <div class="flex justify-between items-start py-2 border-b border-gray-100 last:border-0">
                    <div class="flex-1 min-w-0 mr-3">
                        <div class="font-medium text-gray-800 text-sm leading-tight">${item.name}</div>
                        <div class="text-xs text-gray-400 mt-0.5">${fmt(item.price)} &times; ${item.quantity}</div>
                    </div>
                    <div class="font-semibold text-gray-800 text-sm whitespace-nowrap">${fmt(lineTotal)}</div>
                </div>`;
        }).join('');

        const tableLabel = orderMethod.value === 'dine-in'
            ? `<div class="flex justify-between text-sm mb-1">
                   <span class="text-gray-500">Table</span>
                   <span class="font-medium text-gray-800">${selectedTableName || ('Table #' + selectedTableId)}</span>
               </div>`
            : '';

        // Show confirmation dialog
        const result = await Swal.fire({
            title: '<div class="flex items-center gap-2 text-base font-bold text-gray-800"><i class="fas fa-check-circle text-green-500"></i> Confirm Order</div>',
            html: `
                <div class="text-left" style="font-size:14px;">
                    ${tableLabel}
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-gray-500">Type</span>
                        <span class="font-medium text-gray-800">${orderMethod.value === 'dine-in' ? 'Dine-in' : 'Takeaway'}</span>
                    </div>

                    <div class="border border-gray-200 rounded-lg px-3 pt-1 pb-0 mb-3">
                        ${itemRows}
                    </div>

                    <div class="bg-gray-50 rounded-lg px-3 py-2 space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-800">${fmt(subtotal)}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tax (13% VAT)</span>
                            <span class="text-gray-800">${fmt(tax)}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold pt-1 border-t border-gray-200">
                            <span class="text-gray-900">Total</span>
                            <span class="text-green-700">${fmt(total)}</span>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Place Order',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            width: '400px',
            customClass: {
                title: 'text-left px-6 pt-5 pb-0',
                htmlContainer: 'px-6 pb-2',
                actions: 'px-6 pb-5 gap-3'
            }
        });

        if (!result.isConfirmed) {
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Creating Order',
            text: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const orderData = {
            items: cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price,
                notes: item.notes
            })),
            order_type: orderMethod.value === 'dine-in' ? 'dine_in' : 'takeaway',
            table_id: orderMethod.value === 'dine-in' ? selectedTableId : null,
            subtotal: subtotal,
            tax: tax,
            total: total,
            payment_status: 'pending',
            status: 'pending'
        };

        console.log('Sending order data:', orderData); // Debug log

        // If continuing an existing order, add items to it; otherwise create new
        let response, data;
        if (window.continuingOrderId) {
            response = await fetch(`/api/pos/orders/${window.continuingOrderId}/add-items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Branch-ID': document.querySelector('meta[name="branch-id"]').content
                },
                body: JSON.stringify({ items: orderData.items })
            });
        } else {
            response = await fetch('/api/pos/pos-orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Branch-ID': document.querySelector('meta[name="branch-id"]').content
                },
                body: JSON.stringify(orderData)
            });
        }

        data = await response.json();
        console.log('Server response:', data);

        if (!response.ok) {
            throw new Error(data.error || data.message || 'Failed to submit order');
        }

        // Close loading state
        Swal.close();

        const orderId = data.order?.id ?? window.continuingOrderId;
        const isAddition = !!window.continuingOrderId;

        // Clear continue-order state
        window.continuingOrderId = null;
        hideContinueOrderBanner();

        // Open kitchen receipt immediately
        window.open(`/receipts/print/${orderId}?type=kitchen`, '_blank', 'width=400,height=600');

        // Show success message
        Swal.fire({
            icon: 'success',
            title: isAddition ? 'Items Added' : 'Order Created',
            text: isAddition
                ? `Items added to order #${orderId} successfully`
                : `Order #${orderId} has been created successfully`,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            showCancelButton: false
        });

        // Clear cart and reset UI
        cart = [];
        updateCart();
        if (orderMethod.value === 'dine-in') {
            selectedTableId   = null;
            selectedTableName = null;
            // Tell the Livewire table grid to reset selection and reload statuses
            if (window.livewire) window.livewire.emit('posOrderCreated');
        }
        // Reset order method to takeaway
        const takeawayRadio = document.querySelector('input[name="order_method"][value="takeaway"]');
        if (takeawayRadio) {
            takeawayRadio.checked = true;
            setOrderMethod('takeaway');
        }
    } catch (error) {
        console.error('Error creating order:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to create order'
        });
    }
}

// Function to print kitchen receipt
function printKitchenReceipt(orderId) {
    const printWindow = window.open(`/receipts/print/${orderId}?type=kitchen`, '_blank', 'width=400,height=600');
    if (printWindow) {
        printWindow.focus();
    } else {
        alert('Please allow popups to print kitchen orders');
    }
}

// Order functions
async function continueOrder(orderId) {
    try {
        const response = await fetch(`/api/pos/orders/${orderId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            cart = data.order.items.map(item => ({
                id: item.product_id,
                name: item.item_name,
                price: item.price,
                quantity: item.quantity
            }));
            updateCart();
        } else {
            throw new Error(data.message || 'Failed to load order');
        }
    } catch (error) {
        console.error('Error continuing order:', error);
        alert('Failed to load order. Please try again.');
    }
}

async function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    try {
        const response = await fetch(`/api/pos/orders/${orderId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            loadActiveOrders();
        } else {
            throw new Error(data.message || 'Failed to cancel order');
        }
    } catch (error) {
        console.error('Error canceling order:', error);
        alert('Failed to cancel order. Please try again.');
    }
}

// Category filter
function filterProducts(filter) {
    console.log('Filtering products with category:', filter);
    console.log('Available product categories:', window.productCategories);

    // Update active state of main category buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.onclick && btn.onclick.toString().includes(`'${filter}'`)) {
            btn.classList.add('active');
        }
    });

    let filteredProducts = [];
    let showSubCategories = false;
    let subCategoriesToShow = [];
    
    // Handle main categories
    switch (filter) {
        case 'combo':
            filteredProducts = window.productCategories.combos || [];
            break;
        case 'food':
            filteredProducts = window.productCategories.foods || [];
            showSubCategories = true;
            subCategoriesToShow = ['buff', 'chicken', 'veg', 'others'];
            updateSubCategories(subCategoriesToShow);
            break;
        case 'drinks':
            filteredProducts = window.productCategories.drinks || [];
            showSubCategories = true;
            subCategoriesToShow = ['hot', 'cold'];
            updateSubCategories(subCategoriesToShow);
            break;
        case 'desserts':
            filteredProducts = window.productCategories.desserts || [];
            break;
        default:
            // Handle sub-categories
            if (filter === 'buff') {
                filteredProducts = window.productCategories.buffItems || [];
                showSubCategories = true;
                subCategoriesToShow = ['buff', 'chicken', 'veg', 'others'];
                updateSubCategories(subCategoriesToShow, 'buff');
            } else if (filter === 'chicken') {
                filteredProducts = window.productCategories.chickenItems || [];
                showSubCategories = true;
                subCategoriesToShow = ['buff', 'chicken', 'veg', 'others'];
                updateSubCategories(subCategoriesToShow, 'chicken');
            } else if (filter === 'veg') {
                filteredProducts = window.productCategories.vegItems || [];
                showSubCategories = true;
                subCategoriesToShow = ['buff', 'chicken', 'veg', 'others'];
                updateSubCategories(subCategoriesToShow, 'veg');
            } else if (filter === 'others') {
                filteredProducts = [
                    ...(window.productCategories.mainItems || []),
                    ...(window.productCategories.sideSnacks || [])
                ];
                showSubCategories = true;
                subCategoriesToShow = ['buff', 'chicken', 'veg', 'others'];
                updateSubCategories(subCategoriesToShow, 'others');
            } else if (filter === 'hot') {
                filteredProducts = window.productCategories.hotDrinks || [];
                showSubCategories = true;
                subCategoriesToShow = ['hot', 'cold'];
                updateSubCategories(subCategoriesToShow, 'hot');
            } else if (filter === 'cold') {
                filteredProducts = [
                    ...(window.productCategories.coldDrinks || []),
                    ...(window.productCategories.bobaDrinks || [])
                ];
                showSubCategories = true;
                subCategoriesToShow = ['hot', 'cold'];
                updateSubCategories(subCategoriesToShow, 'cold');
            }
            break;
    }
    
    // Show/hide sub-category filter
    const subCategoryFilter = document.getElementById('subCategoryFilter');
    if (subCategoryFilter) {
        if (showSubCategories) {
            subCategoryFilter.classList.remove('hidden');
        } else {
            subCategoryFilter.classList.add('hidden');
        }
    }
    
    console.log('Filtered products:', filteredProducts);
    renderProducts(filteredProducts);
}

// Update sub-categories dynamically
function updateSubCategories(subCategories, activeSubCategory = null) {
    const subCategoryFilter = document.getElementById('subCategoryFilter');
    if (!subCategoryFilter) return;
    
    const container = subCategoryFilter.querySelector('.flex');
    if (!container) return;
    
    container.innerHTML = '';
    
    subCategories.forEach(subCategory => {
        const button = document.createElement('button');
        button.onclick = () => filterProducts(subCategory);
        
        // Set active class if this is the active subcategory
        const isActive = activeSubCategory === subCategory;
        button.className = `category-btn whitespace-nowrap text-xs px-2 py-0.5 rounded-full transition-all duration-200 shadow-sm hover:shadow-md hover:scale-105 active:scale-95 ${
            isActive 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-500 text-white hover:bg-gray-600'
        }`;
        
        let displayName = subCategory.charAt(0).toUpperCase() + subCategory.slice(1);
        let icon = '';
        
        switch (subCategory) {
            case 'buff':
                icon = '🍖';
                displayName = 'Buff';
                break;
            case 'chicken':
                icon = '🍗';
                displayName = 'Chicken';
                break;
            case 'veg':
                icon = '🥬';
                displayName = 'Veg';
                break;
            case 'others':
                icon = '🍽️';
                displayName = 'Others';
                break;
            case 'hot':
                icon = '☕';
                displayName = 'Hot';
                break;
            case 'cold':
                icon = '🧊';
                displayName = 'Cold';
                break;
        }
        
        button.innerHTML = `${icon} ${displayName}`;
        container.appendChild(button);
    });
}

function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case 'pending':
            return 'text-yellow-600';
        case 'completed':
            return 'text-green-600';
        case 'cancelled':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
}

async function editOrder(orderId) {
    try {
        // Get branch ID from localStorage
        const branchData = JSON.parse(localStorage.getItem('pos_branch'));
        if (!branchData || !branchData.id) {
            throw new Error('Branch information not found');
        }

        console.log('Editing order:', orderId);

        // Fetch the latest order data
        const response = await fetch(`/api/pos/orders/${orderId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Branch-ID': branchData.id
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to fetch order details');
        }

        const data = await response.json();
        console.log('Order details:', data);

        if (!data.success || !data.order) {
            throw new Error('Invalid order data received');
        }

        const order = data.order;
        currentEditingOrder = order;

        // Show edit modal
        const modal = document.getElementById('editOrderModal');
        const orderIdSpan = document.getElementById('editOrderId');
        const orderItemsContainer = document.getElementById('editOrderItems');

        if (orderIdSpan) orderIdSpan.textContent = order.order_number;
        
        if (orderItemsContainer) {
            orderItemsContainer.innerHTML = order.items.map(item => `
                <div class="order-item flex items-center justify-between p-2 bg-gray-50 rounded mb-2" data-id="${item.id}">
                    <div class="flex-1">
                        <div class="font-medium">${item.item_name}</div>
                        <div class="text-sm text-gray-500">Rs ${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity(${item.id}, -1)" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="${item.quantity}" min="0" 
                               class="w-12 text-center border rounded" 
                               onchange="updateQuantity(${item.id}, this.value - ${item.quantity})">
                        <button onclick="updateQuantity(${item.id}, 1)" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        if (!modal) {
            console.warn('editOrderModal not found in DOM');
            return;
        }
        modal.classList.remove('hidden');

    } catch (error) {
        console.error('Error editing order:', error);
        showToast(error.message || 'Failed to load order for editing', 'error');
    }
}

function updateQuantity(itemId, change) {
    const input = document.querySelector(`.order-item[data-id="${itemId}"] input`);
    if (!input) return;

    const currentValue = parseInt(input.value);
    const newValue = Math.max(0, currentValue + (typeof change === 'number' ? change : 0));
    input.value = newValue;

    // If setting to zero, show confirmation
    if (newValue === 0) {
        const itemName = input.closest('.order-item').querySelector('.font-medium').textContent;
        showToast(`Set ${itemName} quantity to 0 to remove it from the order`, 'info');
    }
}

function closeEditModal() {
    const modal = document.getElementById('editOrderModal');
    if (modal) modal.classList.add('hidden');
    currentEditingOrder = null;
}

async function saveOrderChanges() {
    try {
        const orderId = currentEditingOrder.id;
        
        // Get branch ID from localStorage
        const branchData = JSON.parse(localStorage.getItem('pos_branch'));
        if (!branchData || !branchData.id) {
            throw new Error('Branch information not found');
        }

        console.log('Current branch ID:', branchData.id);
        console.log('Current editing order:', currentEditingOrder);

        // Get all items from the edit form
        const itemElements = document.querySelectorAll('#editOrderItems .order-item');
        if (!itemElements.length) {
            throw new Error('No items found in the order');
        }

        // Map items with proper validation
        const items = Array.from(itemElements).map(item => {
            const itemId = parseInt(item.dataset.id);
            const quantityInput = item.querySelector('input[type="number"]');
            const quantity = parseInt(quantityInput.value);

            if (isNaN(itemId) || isNaN(quantity)) {
                throw new Error('Invalid item data');
            }

            // Find the original item to get product_id
            const originalItem = currentEditingOrder.items.find(i => i.id === itemId);
            if (!originalItem) {
                throw new Error('Item not found in order');
            }

            return {
                product_id: originalItem.product_id,
                quantity: quantity,
                price: originalItem.price
            };
        });

        // Check if all items have zero quantity
        const allZero = items.every(item => item.quantity === 0);
        if (allZero) {
            const confirmed = await showConfirmationModal(
                'Delete Order',
                'Are you sure you want to delete this order? This action cannot be undone.'
            );
            
            if (!confirmed) {
                return;
            }

            // First, fetch the latest order data
            const orderResponse = await fetch(`/api/pos/orders/${orderId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Branch-ID': branchData.id.toString()
                },
                credentials: 'same-origin'
            });

            if (!orderResponse.ok) {
                const errorData = await orderResponse.json();
                throw new Error(errorData.error || errorData.message || 'Failed to fetch order data');
            }

            const orderData = await orderResponse.json();
            console.log('Fetched order data:', orderData);
            
            // Verify order belongs to current branch
            const orderBranchId = orderData.order?.branch_id || orderData.branch_id;
            console.log('Order branch ID:', orderBranchId);
            console.log('Current branch ID:', branchData.id);
            
            if (!orderBranchId) {
                throw new Error('Order branch ID not found in response');
            }

            if (parseInt(orderBranchId) !== parseInt(branchData.id)) {
                throw new Error(`Order belongs to branch ${orderBranchId}, but current branch is ${branchData.id}`);
            }

            // Send delete request with branch ID in both header and body
            const response = await fetch(`/api/pos/orders/${orderId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Branch-ID': branchData.id.toString()
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    branch_id: branchData.id
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error('Delete error response:', errorData);
                throw new Error(errorData.error || errorData.message || 'Failed to delete order');
            }

            // Close modal and reload orders
            closeEditModal();
            await loadActiveOrders();
            await showSuccessModal('Order Deleted', 'The order has been successfully deleted.');
            return;
        }

        // Filter out items with zero quantity for normal update
        const validItems = items.filter(item => item.quantity > 0);
        if (validItems.length === 0) {
            throw new Error('Order must have at least one item');
        }

        // Get the current status from the order
        const status = currentEditingOrder.status || 'pending';

        console.log('Updating order with data:', { items: validItems, status });

        const response = await fetch(`/api/pos/orders/${orderId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Branch-ID': branchData.id
            },
            credentials: 'same-origin',
            body: JSON.stringify({ 
                items: validItems,
                status
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error response:', errorData);
            throw new Error(errorData.error || errorData.message || 'Failed to update order');
        }

        const data = await response.json();
        console.log('Order update response:', data);
        
        // Close the edit modal
        closeEditModal();
        
        // Reload orders
        await loadActiveOrders();
        
        // Show success message with order details
        const orderNumber = data.order.order_number;
        const total = parseFloat(data.order.total || data.order.grand_total || 0);
        const formattedTotal = isNaN(total) ? '0.00' : total.toFixed(2);
        
        await showSuccessModal(
            'Order Updated Successfully!',
            `Order #${orderNumber} has been updated with a new total of Rs ${formattedTotal}`
        );

    } catch (error) {
        console.error('Error updating order:', error);
        showToast(error.message || 'Failed to update order. Please try again.', 'error');
    }
}

async function deleteOrder(orderId) {
    try {
        const response = await fetch(`/api/pos/orders/${orderId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error('Failed to delete order');
        }

        // Reload active orders
        await loadActiveOrders();
    } catch (error) {
        console.error('Error deleting order:', error);
        alert('Failed to delete order');
    }
}

// Update business status indicator
async function updateBusinessStatus() {
    try {
        const branchId = document.querySelector('meta[name="branch-id"]')?.content || 1;
        const response = await fetch(`/api/business/status/${branchId}`);
        const businessStatus = await response.json();
        
        const statusIcon = document.getElementById('businessStatusIcon');
        const statusText = document.getElementById('businessStatusText');

        if (businessStatus.is_open) {
            if (statusIcon) statusIcon.className = 'fas fa-circle mr-1 text-green-400';
            if (statusText) { statusText.textContent = 'Open'; statusText.className = 'font-medium text-green-400'; }
        } else {
            if (statusIcon) statusIcon.className = 'fas fa-circle mr-1 text-red-400';
            if (statusText) { statusText.textContent = 'Closed'; statusText.className = 'font-medium text-red-400'; }
        }
    } catch (error) {
        console.error('Failed to update business status:', error);
        const statusIcon = document.getElementById('businessStatusIcon');
        const statusText = document.getElementById('businessStatusText');

        if (statusIcon) statusIcon.className = 'fas fa-circle mr-1 text-gray-400';
        if (statusText) { statusText.textContent = 'Unknown'; statusText.className = 'font-medium text-gray-400'; }
    }
}

// Make function globally accessible
window.updateBusinessStatus = updateBusinessStatus; 