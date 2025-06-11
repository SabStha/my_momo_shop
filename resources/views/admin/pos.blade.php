@extends('layouts.pos')

@section('content')
<!-- Success Notification -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out">
    <div class="flex items-center">
        <i class="fas fa-check-circle text-xl mr-2"></i>
        <span id="successMessage">Order created successfully!</span>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <div class="text-center">
            <i class="fas fa-question-circle text-blue-500 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Confirm Order Creation</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to create this order?</p>
            <div class="flex space-x-4">
                <button onclick="closeConfirmationModal()" class="flex-1 bg-gray-200 text-gray-800 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmOrderCreation()" class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Create Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <div class="text-center">
            <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Order Created Successfully!</h3>
            <p id="successModalMessage" class="text-gray-600 mb-6"></p>
            <button onclick="closeSuccessModal()" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
                OK
            </button>
        </div>
    </div>
</div>

<div class="flex h-screen bg-gray-100">
    <!-- Left Sidebar - Products -->
    <div class="w-1/4 bg-white shadow-lg overflow-y-auto">
        <!-- Products Section -->
        <div class="flex-1 overflow-hidden flex flex-col">
            <!-- Category Filter -->
            <div class="bg-white p-4 border-b">
                <div class="flex space-x-2 overflow-x-auto pb-2">
                    <button onclick="filterProducts('all')" class="category-btn active px-4 py-2 rounded-full bg-blue-500 text-white whitespace-nowrap">
                        All Items
                    </button>
                    <button onclick="filterProducts('beverages')" class="category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 whitespace-nowrap">
                        Beverages
                    </button>
                    <button onclick="filterProducts('food')" class="category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 whitespace-nowrap">
                        Food
                    </button>
                    <button onclick="filterProducts('snacks')" class="category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 whitespace-nowrap">
                        Snacks
                    </button>
                    <button onclick="filterProducts('desserts')" class="category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 whitespace-nowrap">
                        Desserts
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="p-4 border-b">
                <div class="relative">
                    <input type="text" id="productSearch" placeholder="Search products..." 
                           class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="productGrid" class="flex-1 overflow-y-auto p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <!-- Products will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Main Content - Cart and Orders -->
    <div class="flex-1 flex flex-col">
        <!-- Top Bar -->
        <div class="bg-white shadow-sm p-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h1 class="text-xl font-semibold">POS System</h1>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                    Branch: {{ $branch->name }}
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <button id="newOrderBtn" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-plus mr-2"></i>New Order
                </button>
                <button id="viewOrdersBtn" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-list mr-2"></i>View Orders
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Cart Section -->
            <div class="w-1/2 bg-white shadow-lg m-4 rounded-lg flex flex-col">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">Current Order</h2>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4">
                    <div id="cartItems" class="space-y-4">
                        <!-- Cart items will be loaded here -->
                    </div>
                </div>

                <div class="p-4 border-t bg-gray-50">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal" class="font-semibold">$0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (10%):</span>
                            <span id="tax" class="font-semibold">$0.00</span>
                        </div>
                        <div class="flex justify-between text-lg">
                            <span class="font-semibold">Total:</span>
                            <span id="total" class="font-bold text-blue-600">$0.00</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 space-y-2">
                        <button id="createOrderBtn" class="w-full px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-shopping-cart mr-2"></i>Create Order
                        </button>
                        <button id="clearCartBtn" class="w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-trash mr-2"></i>Clear Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div class="w-1/2 bg-white shadow-lg m-4 rounded-lg flex flex-col">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Active Orders</h2>
                    <button onclick="loadActiveOrders()" class="text-blue-500 hover:text-blue-600">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4">
                    <div id="activeOrders" class="space-y-4">
                        <!-- Active orders will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State management
    let cart = [];
    let products = [];
    let activeOrders = [];
    let pendingOrderCreation = false;
    let currentCategory = 'all';
    
    // DOM Elements
    const productGrid = document.getElementById('productGrid');
    const cartItems = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const createOrderBtn = document.getElementById('createOrderBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const productSearch = document.getElementById('productSearch');
    const successNotification = document.getElementById('successNotification');
    const successMessage = document.getElementById('successMessage');
    const successModal = document.getElementById('successModal');
    const successModalMessage = document.getElementById('successModalMessage');
    const confirmationModal = document.getElementById('confirmationModal');
    
    // Show success notification
    function showSuccessNotification(message) {
        if (!successNotification || !successMessage) return;
        
        successMessage.textContent = message;
        successNotification.classList.remove('translate-x-full');
        
        // Ensure the notification is visible
        successNotification.style.display = 'block';
        
        setTimeout(() => {
            successNotification.classList.add('translate-x-full');
            // Hide the notification after animation
            setTimeout(() => {
                successNotification.style.display = 'none';
            }, 300);
        }, 3000);
    }
    
    // Show confirmation modal
    function showConfirmationModal() {
        if (!confirmationModal) return;
        confirmationModal.classList.remove('hidden');
        confirmationModal.style.display = 'flex';
    }

    // Close confirmation modal
    function closeConfirmationModal() {
        if (!confirmationModal) return;
        confirmationModal.classList.add('hidden');
        confirmationModal.style.display = 'none';
        pendingOrderCreation = false;
    }

    // Confirm order creation
    async function confirmOrderCreation() {
        if (!pendingOrderCreation) return;
        closeConfirmationModal();
        await createOrder();
    }

    // Show success modal
    function showSuccessModal(message) {
        if (!successModal || !successModalMessage) return;
        successModalMessage.textContent = message;
        successModal.classList.remove('hidden');
    }

    // Close success modal
    function closeSuccessModal() {
        if (!successModal) return;
        successModal.classList.add('hidden');
    }
    
    // Filter products by category
    function filterProducts(category) {
        currentCategory = category;
        
        // Update category buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
            if (btn.textContent.toLowerCase().includes(category)) {
                btn.classList.remove('bg-gray-200', 'text-gray-700');
                btn.classList.add('bg-blue-500', 'text-white');
            } else {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            }
        });

        // Filter and render products
        const filteredProducts = category === 'all' 
            ? products 
            : products.filter(p => p.category.toLowerCase() === category);
        renderProducts(filteredProducts);
    }
    
    // Load products
    async function loadProducts() {
        try {
            const response = await fetch('/api/pos/products');
            if (!response.ok) {
                throw new Error('Failed to load products');
            }
            products = await response.json();
            filterProducts(currentCategory); // Render with current category filter
        } catch (error) {
            console.error('Failed to load products:', error);
            if (productGrid) {
                productGrid.innerHTML = `
                    <div class="col-span-full text-center text-red-500 py-4">
                        <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                        <p>Failed to load products</p>
                        <button onclick="loadProducts()" class="mt-2 text-blue-500 hover:text-blue-600">
                            <i class="fas fa-sync-alt mr-1"></i> Retry
                        </button>
                    </div>
                `;
            }
        }
    }
    
    // Render products
    function renderProducts(productsToRender) {
        if (!productGrid) return;

        if (!productsToRender || productsToRender.length === 0) {
            productGrid.innerHTML = `
                <div class="col-span-full text-center text-gray-500 py-4">
                    <i class="fas fa-box-open text-4xl mb-2"></i>
                    <p>No products found</p>
                </div>
            `;
            return;
        }

        productGrid.innerHTML = productsToRender.map(product => `
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
                 onclick="addToCart(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                <div class="relative pb-[100%]">
                    <img src="${product.image ? '/storage/' + product.image : '/storage/products/placeholder.png'}" 
                         alt="${product.name}"
                         class="absolute inset-0 w-full h-full object-cover"
                         onerror="this.src='/storage/products/placeholder.png'">
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-1">${product.name}</h3>
                    <p class="text-sm text-gray-500 mb-2">${product.category}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-blue-500 font-semibold">$${parseFloat(product.price).toFixed(2)}</span>
                        <button class="text-blue-500 hover:text-blue-600">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Add to cart with feedback
    function addToCart(product) {
        // Add visual feedback
        const productElement = event.currentTarget;
        productElement.classList.add('scale-95', 'opacity-75');
        setTimeout(() => {
            productElement.classList.remove('scale-95', 'opacity-75');
        }, 200);

        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1
            });
        }
        updateCart();
    }
    
    // Update cart
    function updateCart() {
        if (!cartItems || !subtotalEl || !taxEl || !totalEl) return;
        
        cartItems.innerHTML = cart.map(item => `
            <div class="flex justify-between items-center py-2">
                <div>
                    <h4 class="font-medium">${item.name}</h4>
                    <p class="text-sm text-gray-500">$${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 ml-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        // Calculate totals
        const subtotal = cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
        const tax = subtotal * 0.1; // 10% tax
        const total = subtotal + tax;

        // Update totals with proper number formatting
        subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
        taxEl.textContent = `$${tax.toFixed(2)}`;
        totalEl.textContent = `$${total.toFixed(2)}`;

        // Update create order button state
        if (createOrderBtn) {
            createOrderBtn.disabled = cart.length === 0;
            createOrderBtn.classList.toggle('opacity-50', cart.length === 0);
            createOrderBtn.classList.toggle('cursor-not-allowed', cart.length === 0);
        }
    }
    
    // Update item quantity
    function updateQuantity(productId, newQuantity) {
        if (newQuantity < 1) {
            removeFromCart(productId);
            return;
        }
        
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = newQuantity;
            updateCart();
        }
    }
    
    // Remove item from cart
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateCart();
    }
    
    // Clear cart
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            cart = [];
            updateCart();
            showSuccessNotification('Cart cleared');
        });
    }
    
    // Product search
    if (productSearch) {
        productSearch.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredProducts = products.filter(p => 
                p.name.toLowerCase().includes(searchTerm) || 
                p.category.toLowerCase().includes(searchTerm)
            );
            renderProducts(filteredProducts);
        });
    }
    
    // Create order button click handler
    if (createOrderBtn) {
        createOrderBtn.addEventListener('click', () => {
            if (cart.length === 0) {
                showSuccessModal('Cart is empty');
                return;
            }
            pendingOrderCreation = true;
            showConfirmationModal();
        });
    }
    
    // Create order
    async function createOrder() {
        if (cart.length === 0) {
            showSuccessModal('Cart is empty');
            return;
        }

        try {
            const response = await fetch('/api/pos/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    items: cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        price: item.price
                    }))
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to create order');
            }

            // Clear cart
            cart = [];
            updateCart();
            
            // Show success modal with order number
            if (result.order && result.order.order_number) {
                showSuccessModal(`Order #${result.order.order_number} has been created successfully!`);
            } else {
                showSuccessModal('Order has been created successfully!');
            }
            
            // Refresh active orders
            await loadActiveOrders();
        } catch (error) {
            console.error('Failed to create order:', error);
            showSuccessModal(error.message || 'Failed to create order');
        }
    }
    
    // Load active orders
    window.loadActiveOrders = async function() {
        try {
            const response = await fetch('/api/pos/orders');
            if (!response.ok) {
                throw new Error('Failed to load orders');
            }
            const orders = await response.json();
            renderActiveOrders(orders);
        } catch (error) {
            console.error('Failed to load active orders:', error);
            const activeOrdersEl = document.getElementById('activeOrders');
            if (activeOrdersEl) {
                activeOrdersEl.innerHTML = `
                    <div class="text-center text-red-500 py-4">
                        <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                        <p>Failed to load orders</p>
                        <button onclick="loadActiveOrders()" class="mt-2 text-blue-500 hover:text-blue-600">
                            <i class="fas fa-sync-alt mr-1"></i> Retry
                        </button>
                    </div>
                `;
            }
        }
    }
    
    // Render active orders
    function renderActiveOrders(orders) {
        const activeOrdersEl = document.getElementById('activeOrders');
        if (!activeOrdersEl) return;

        if (!orders || orders.length === 0) {
            activeOrdersEl.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-clipboard-list text-4xl mb-2"></i>
                    <p>No active orders</p>
                </div>
            `;
            return;
        }

        // Sort orders by creation date, most recent first
        orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        activeOrdersEl.innerHTML = orders.map(order => `
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-semibold text-gray-800">Order #${order.order_number}</h3>
                        <p class="text-sm text-gray-500">${new Date(order.created_at).toLocaleString()}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-sm ${getStatusColor(order.status)}">
                        ${order.status}
                    </span>
                </div>
                <div class="space-y-2">
                    ${order.items.map(item => `
                        <div class="flex justify-between text-sm">
                            <span>${item.item_name} x ${item.quantity}</span>
                            <span>$${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-2 pt-2 border-t">
                    <div class="flex justify-between font-semibold">
                        <span>Total:</span>
                        <span>$${parseFloat(order.total_amount).toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Get status color
    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'processing':
                return 'bg-blue-100 text-blue-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
        // Set branch ID in session
        const branchId = new URLSearchParams(window.location.search).get('branch');
        if (branchId) {
            try {
                const response = await fetch('/api/pos/verify-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ branch_id: branchId })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to verify branch access');
                }
            } catch (error) {
                console.error('Failed to verify branch access:', error);
                alert('Failed to verify branch access. Please try logging in again.');
                window.location.href = '/pos/login?branch=' + branchId;
                return;
            }
        }

        // Load initial data
        loadProducts();
        loadActiveOrders();

        // Refresh active orders every 30 seconds
        setInterval(loadActiveOrders, 30000);
    });
</script>
@endpush
