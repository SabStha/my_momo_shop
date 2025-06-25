// Utility Functions
function formatCurrency(amount) {
    return 'Rs. ' + new Intl.NumberFormat('en-IN', {
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
let selectedTableId = null;

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
    
    // Set up search functionality
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterProducts('all');
        });
    }

    loadActiveOrders();
    setupEventListeners();
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
        renderProducts(products);
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
    const productsSection = document.getElementById('productsSection');
    if (!productsSection) return;
    
    if (products.length === 0) {
        productsSection.innerHTML = `
            <div class="text-center p-4">
                <p class="text-gray-500">No products found.</p>
            </div>
        `;
        return;
    }

    productsSection.innerHTML = products.map(product => `
        <div onclick="addToCart(${product.id})" class="flex items-center justify-between py-1 px-1.5 hover:bg-gray-50 cursor-pointer product-card" data-tag="${product.tag || ''}">
            <div class="flex items-center flex-1 min-w-0">
                <div class="w-6 h-6 flex-shrink-0">
                    <img src="${product.image ? '/storage/' + product.image : '/images/no-image.png'}" 
                         alt="${product.name}" 
                         class="w-full h-full object-cover rounded">
                </div>
                <div class="ml-1.5 min-w-0">
                    <h3 class="font-medium text-gray-800 text-xs truncate">${product.name}</h3>
                </div>
            </div>
            <div class="ml-1.5 flex-shrink-0">
                <span class="text-xs font-medium text-gray-900">Rs ${parseFloat(product.price).toFixed(2)}</span>
            </div>
        </div>
    `).join('');
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
        const branchData = JSON.parse(localStorage.getItem('pos_branch'));
        if (!branchData || !branchData.id) {
            console.error('Branch information not found');
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

        const response = await fetch(`/api/pos/orders?branch_id=${branchData.id}&status=pending`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Branch-ID': branchData.id
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load active orders');
        }
        
        const data = await response.json();
        activeOrders = Array.isArray(data) ? data : (data.orders || []);

        activeOrdersContainer.innerHTML = '';

        if (activeOrders.length === 0) {
            activeOrdersContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No active orders</p>';
            return;
        }

        // Sort orders by creation date (newest first)
        activeOrders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        // Create container for all orders
        const allOrdersContainer = document.createElement('div');
        allOrdersContainer.className = 'space-y-3';
        allOrdersContainer.style.display = 'none'; // Hide initially

        // Create container for most recent order
        const recentOrderContainer = document.createElement('div');
        recentOrderContainer.className = 'mb-3';

        // Create dropdown header
        const dropdownHeader = document.createElement('div');
        dropdownHeader.className = 'flex items-center justify-between mb-2 cursor-pointer hover:bg-gray-50 p-2 rounded';
        dropdownHeader.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700">Active Orders</span>
                <span class="text-xs text-gray-500">(${activeOrders.length})</span>
            </div>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-chevron-down"></i>
            </button>
        `;

        // Add click handler for dropdown
        dropdownHeader.addEventListener('click', () => {
            const isExpanded = allOrdersContainer.style.display !== 'none';
            allOrdersContainer.style.display = isExpanded ? 'none' : 'block';
            dropdownHeader.querySelector('i').className = isExpanded ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
        });

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
            orderElement.className = `border rounded-lg p-3 ${orderTypeColor} hover:shadow-md transition-shadow`;
            orderElement.innerHTML = `
                <div class="flex justify-between items-start">
                <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900 text-sm">#${order.order_number}</h3>
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full ${order.order_type === 'dine_in' ? 'bg-blue-200 text-blue-800' : 'bg-green-200 text-green-800'}">
                                ${orderTypeText}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">${formattedDate}</p>
                        ${order.order_type === 'dine_in' && order.table ? `
                            <p class="text-xs text-gray-600">Table: ${order.table.name}</p>
                        ` : ''}
                </div>
                    <div class="text-right">
                        <span class="text-base font-bold text-gray-900">Rs ${formattedTotal}</span>
                        <p class="text-xs text-gray-600">${order.items ? order.items.length : 0} items</p>
                        <p class="text-xs text-gray-500">Subtotal: Rs ${formattedSubtotal} + Tax: Rs ${formattedTax}</p>
                    </div>
                </div>
                <div class="mt-2 flex justify-end">
                    <button onclick="editOrder(${order.id})" class="px-2 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                        Edit
                </button>
            </div>
            `;
            return orderElement;
        };

        // Add most recent order to recent container
        if (activeOrders.length > 0) {
            recentOrderContainer.appendChild(createOrderElement(activeOrders[0]));
        }

        // Add all orders to all orders container
        activeOrders.forEach(order => {
            allOrdersContainer.appendChild(createOrderElement(order));
        });

        // Assemble the final structure
        activeOrdersContainer.appendChild(dropdownHeader);
        activeOrdersContainer.appendChild(recentOrderContainer);
        activeOrdersContainer.appendChild(allOrdersContainer);

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
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) {
        showToast('Product not found', 'error');
        return;
    }

    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1
        });
    }
    
    updateCart();
    showToast('Product added to cart', 'success');
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
    const emptyCartIcon = document.getElementById('emptyCartIcon');
    cartItems.innerHTML = '';
    let cartSubtotal = 0;

    if (cart.length === 0) {
        emptyCartIcon.classList.remove('hidden');
        cartTotal.textContent = 'Rs 0.00';
        return;
    }

    emptyCartIcon.classList.add('hidden');

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        cartSubtotal += itemTotal;

        const itemElement = document.createElement('div');
        itemElement.className = 'flex justify-between items-center p-1.5 border-b text-sm';
        itemElement.innerHTML = `
            <div class="flex-1">
                <div class="font-medium text-sm">${item.name}</div>
                <div class="text-sm text-gray-500">Rs ${item.price.toFixed(2)} each</div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-1">
                    <button onclick="updateCartItemQuantity(${index}, -1)" 
                            class="w-5 h-5 flex items-center justify-center bg-gray-100 rounded hover:bg-gray-200">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="w-6 text-center text-sm">${item.quantity}</span>
                    <button onclick="updateCartItemQuantity(${index}, 1)" 
                            class="w-5 h-5 flex items-center justify-center bg-gray-100 rounded hover:bg-gray-200">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </div>
                <span class="font-medium text-sm">Rs ${itemTotal.toFixed(2)}</span>
                <button onclick="removeFromCart(${index})" class="text-gray-400 hover:text-red-500">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
        cartItems.appendChild(itemElement);
    });

    // Add subtotal and tax breakdown
    const tax = cartSubtotal * 0.13;
    const total = cartSubtotal + tax;
    
    const totalElement = document.createElement('div');
    totalElement.className = 'p-2 border-t text-sm';
    totalElement.innerHTML = `
        <div class="flex justify-between text-gray-600">
            <span>Subtotal:</span>
            <span>Rs ${cartSubtotal.toFixed(2)}</span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Tax (13%):</span>
            <span>Rs ${tax.toFixed(2)}</span>
        </div>
        <div class="flex justify-between font-semibold mt-1">
            <span>Total:</span>
            <span>$${total.toFixed(2)}</span>
        </div>
    `;
    cartItems.appendChild(totalElement);

    // Update the cart total display
    cartTotal.textContent = `Rs ${total.toFixed(2)}`;
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
        dineInBtn.classList.add('bg-blue-600', 'text-white');
        dineInBtn.classList.remove('bg-gray-200', 'text-gray-700');
        takeawayBtn.classList.add('bg-gray-200', 'text-gray-700');
        takeawayBtn.classList.remove('bg-blue-600', 'text-white');
        tableSelection.classList.remove('hidden');
        // Load tables when dine-in is selected
        loadTables();
    } else {
        takeawayBtn.classList.add('bg-blue-600', 'text-white');
        takeawayBtn.classList.remove('bg-gray-200', 'text-gray-700');
        dineInBtn.classList.add('bg-gray-200', 'text-gray-700');
        dineInBtn.classList.remove('bg-blue-600', 'text-white');
        tableSelection.classList.add('hidden');
        selectedTableId = null;
    }
    
    console.log('Order method set to:', method);
    console.log('Selected table ID:', selectedTableId);
}

async function loadTables() {
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

// Add event listener for table selection
document.getElementById('tableSelect').addEventListener('change', function(e) {
    selectedTableId = e.target.value;
});

function clearCart() {
    cart = [];
    updateCart();
    // Reset order method and table selection
    currentOrderMethod = null;
    selectedTableId = null;
    // Update UI to reflect reset
    document.querySelectorAll('.order-method-btn').forEach(btn => {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    document.getElementById('tableSelection').classList.add('hidden');
}

async function createOrder() {
    try {
        // Validate order method
        const orderMethod = document.querySelector('input[name="order_method"]:checked');
        console.log('Selected order method element:', orderMethod); // Debug log

        if (!orderMethod || !orderMethod.value) {
            Swal.fire({
                icon: 'error',
                title: 'Order Method Required',
                text: 'Please select an order method (Dine-in or Takeaway)'
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
                icon: 'error',
                title: 'Empty Cart',
                text: 'Please add items to the cart before creating an order'
            });
            return;
        }

        // Calculate totals
        const subtotal = calculateTotal();
        const tax = subtotal * 0.1; // 10% tax
        const total = subtotal + tax;

        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Confirm Order',
            html: `
                <div class="text-left">
                    <p><strong>Order Method:</strong> ${orderMethod.value === 'dine-in' ? 'Dine-in' : 'Takeaway'}</p>
                    ${orderMethod.value === 'dine-in' ? `<p><strong>Table:</strong> ${selectedTableId ? document.querySelector(`option[value="${selectedTableId}"]`).textContent : 'N/A'}</p>` : ''}
                    <p><strong>Total Items:</strong> ${cart.length}</p>
                    <p><strong>Subtotal:</strong> ${formatCurrency(subtotal)}</p>
                    <p><strong>Tax (10%):</strong> ${formatCurrency(tax)}</p>
                    <p><strong>Total Amount:</strong> ${formatCurrency(total)}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Create Order',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
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

        const response = await fetch('/api/pos/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Branch-ID': document.querySelector('meta[name="branch-id"]').content
            },
            body: JSON.stringify(orderData)
        });

        const data = await response.json();
        console.log('Server response:', data); // Debug log

        if (!response.ok) {
            throw new Error(data.message || 'Failed to create order');
        }

        // Close loading state
        Swal.close();

        // Open kitchen receipt immediately
        window.open(`/receipts/print/${data.order.id}?type=kitchen`, '_blank', 'width=400,height=600');

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Order Created',
            text: `Order #${data.order.id} has been created successfully`,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            showCancelButton: false
        });

        // Clear cart and reset UI
        cart = [];
        updateCart();
        if (orderMethod.value === 'dine-in') {
            selectedTableId = null;
            document.getElementById('tableSelect').value = '';
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
    console.log('Filtering products with tag:', filter);
    console.log('Available products:', products);

    // Update active state of category buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.trim() === filter || 
            (filter === 'all' && btn.textContent.trim() === 'All Items')) {
            btn.classList.add('active');
        }
    });

    // Filter products
    const filteredProducts = filter === 'all' 
        ? products 
        : products.filter(product => {
            console.log('Comparing product:', product.name, 'tag:', product.tag, 'with filter:', filter);
            return product.tag === filter;
        });

    console.log('Filtered products:', filteredProducts);

    // Render filtered products
    renderProducts(filteredProducts);
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
    modal.classList.add('hidden');
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