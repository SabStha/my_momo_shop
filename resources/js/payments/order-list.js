// Order list management module

// Global vars
let orderPollingInterval;
let allOrders = []; // Store all orders
// Restore session start time from localStorage so page refreshes keep the right filter.
// payment-manager.js writes this key when the drawer is opened/closed.
if (!window.currentSessionStartTime) {
    const saved = localStorage.getItem('pmSessionStartTime');
    if (saved) window.currentSessionStartTime = new Date(saved);
}
// currentSessionStartTime lives on window so payment-manager.js can set it cross-file
let filteredOrders = {
    takeaway: { all: [], paid: [], unpaid: [] },
    dinein: { all: [], paid: [], unpaid: [] },
    online: { all: [], paid: [], unpaid: [] }
};
let currentFilters = {
    takeaway: 'unpaid',
    dinein: 'unpaid',
    online: 'all'  // Show all online orders by default (includes paid Amako Credits orders)
};

function startOrderPolling() {
    // Fetch orders immediately
    fetchOrders();

    // Then poll every 10 seconds
    orderPollingInterval = setInterval(fetchOrders, 10000);
}

function stopOrderPolling() {
    if (orderPollingInterval) {
        clearInterval(orderPollingInterval);
        orderPollingInterval = null;
    }
}

// Hide all orders when drawer is closed
function hideAllOrders() {
    // Clear all order grids
    const dineinGrid = document.getElementById('dineinOrdersGrid');
    const takeawayGrid = document.getElementById('takeawayOrdersGrid');
    const onlineGrid = document.getElementById('onlineOrdersGrid');

    if (dineinGrid) dineinGrid.innerHTML = '';
    if (takeawayGrid) takeawayGrid.innerHTML = '';
    if (onlineGrid) onlineGrid.innerHTML = '';

    // Clear order counts
    const dineinCount = document.getElementById('dineinCount');
    const takeawayCount = document.getElementById('takeawayCount');
    const onlineCount = document.getElementById('onlineCount');

    if (dineinCount) dineinCount.textContent = '(0)';
    if (takeawayCount) takeawayCount.textContent = '(0)';
    if (onlineCount) onlineCount.textContent = '(0)';

    // Clear stored orders
    allOrders = [];
    window.previousOrderIds = new Set();
}

// Show new session started message
function showNewSessionMessage() {
    const dineinGrid = document.getElementById('dineinOrdersGrid');
    const takeawayGrid = document.getElementById('takeawayOrdersGrid');
    const onlineGrid = document.getElementById('onlineOrdersGrid');

    const newSessionMessage = `
        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                    <i class="fas fa-store text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">New Session Started</h3>
                <p class="text-gray-600 mb-4">Cash drawer is open and ready for new orders.</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="font-medium text-blue-800">Fresh Start</span>
                </div>
                <p class="text-sm text-blue-700">
                    All previous order history has been cleared. New orders will appear here as they come in.
                </p>
            </div>

            <div class="text-xs text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                Session started at ${new Date().toLocaleTimeString()}
            </div>
        </div>
    `;

    if (dineinGrid) dineinGrid.innerHTML = newSessionMessage;
    if (takeawayGrid) takeawayGrid.innerHTML = newSessionMessage;
    if (onlineGrid) onlineGrid.innerHTML = newSessionMessage;
}

// Show drawer closed message
function showDrawerClosedMessage() {
    // Show the banner
    const banner = document.getElementById('drawerStatusBanner');
    if (banner) {
        banner.classList.remove('hidden');
    }

    const dineinGrid = document.getElementById('dineinOrdersGrid');
    const takeawayGrid = document.getElementById('takeawayOrdersGrid');
    const onlineGrid = document.getElementById('onlineOrdersGrid');

    const closedMessage = `
        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-3">
                    <i class="fas fa-store-slash text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Cash Drawer Closed</h3>
                <p class="text-gray-600 mb-4">Order history is hidden while the cash drawer is closed.</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 max-w-sm">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="font-medium text-blue-800">How to View Orders</span>
                </div>
                <p class="text-sm text-blue-700">
                    Open the cash drawer to view current order history and start a fresh session.
                </p>
            </div>

            <div class="text-xs text-gray-500">
                <i class="fas fa-database mr-1"></i>
                All order data is safely stored in the database
            </div>
        </div>
    `;

    if (dineinGrid) dineinGrid.innerHTML = closedMessage;
    if (takeawayGrid) takeawayGrid.innerHTML = closedMessage;
    if (onlineGrid) onlineGrid.innerHTML = closedMessage;
}

// Hide drawer closed message
function hideDrawerClosedMessage() {
    // Hide the banner
    const banner = document.getElementById('drawerStatusBanner');
    if (banner) {
        banner.classList.add('hidden');
    }

    // The populateOrderGrids function will replace the content with actual orders
}

async function fetchOrders() {
    console.log('🔄 fetchOrders() called - fetching orders from backend...');
    try {
        const paymentApp = document.getElementById('paymentApp');
        if (!paymentApp) {
            console.error('❌ PaymentApp element not found');
            return;
        }

        const branchId = paymentApp.dataset.branchId;
        console.log('🏪 Branch ID:', branchId);

        // First check if cash drawer is open
        const drawerStatus = await checkCashDrawerStatus(branchId);
        console.log('💰 Cash drawer status:', drawerStatus.isOpen ? 'OPEN' : 'CLOSED');

        if (!drawerStatus.isOpen) {
            hideAllOrders();
            showDrawerClosedMessage();
            allOrders = [];
            return;
        }

        hideDrawerClosedMessage();

        // Drawer is open — fetch orders
        console.log('📡 Fetching orders from API...');
        const response = await fetch(`/admin/orders/json?branch=${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Failed to fetch orders');
        }

        const data = await response.json();
        console.log('📦 API response received:', data);

        if (data.success) {
            console.log(`📊 Total orders from API: ${data.orders?.length || 0}`);

            // Filter orders to current session or today (whichever is later)
            const todayMidnight = new Date();
            todayMidnight.setHours(0, 0, 0, 0);
            const sessionStart = window.currentSessionStartTime
                ? new Date(Math.max(window.currentSessionStartTime, todayMidnight))
                : todayMidnight; // fallback: only show today's orders

            let filteredOrders = data.orders.filter(order =>
                new Date(order.created_at) >= sessionStart
            );
            console.log(`✅ Session filter: showing ${filteredOrders.length} orders since ${sessionStart.toLocaleTimeString()} (sessionStartTime=${window.currentSessionStartTime?.toLocaleTimeString() || 'none'})`);

            console.log('📋 Orders to display:', filteredOrders);

            // Check for new orders by comparing order IDs (only from filtered orders)
            const previousOrderIds = window.previousOrderIds || new Set();
            const currentOrderIds = new Set(filteredOrders.map(order => order.id));

            // Find truly new orders (not just count-based)
            const newOrderIds = [...currentOrderIds].filter(id => !previousOrderIds.has(id));
            if (newOrderIds.length > 0) {
                const newOrders = filteredOrders.filter(order => newOrderIds.includes(order.id));
                console.log('🆕 New orders detected:', newOrders);
                showNewOrderNotification(newOrders);
            }

            window.previousOrderIds = currentOrderIds;
            console.log('🎨 Populating order grids...');
            populateOrderGrids(filteredOrders);
            console.log('✅ Orders populated successfully!');
        } else {
            console.error('❌ Failed to fetch orders:', data.message);
        }
    } catch (error) {
        console.error('❌ Error fetching orders:', error);
    }
}

function populateOrderGrids(orders) {
    // Store all orders
    allOrders = orders;

    // Separate orders by type and payment status
    const takeawayOrders = orders.filter(order => order.type === 'takeaway');
    const dineInOrders = orders.filter(order => order.type === 'dine_in');
    const onlineOrders = orders.filter(order => order.type === 'online' || order.type === 'delivery');

    // Store filtered orders
    filteredOrders.takeaway.all = takeawayOrders;
    filteredOrders.takeaway.paid = takeawayOrders.filter(order => order.payment_status === 'paid');
    filteredOrders.takeaway.unpaid = takeawayOrders.filter(order => order.payment_status !== 'paid');

    filteredOrders.dinein.all = dineInOrders;
    filteredOrders.dinein.paid = dineInOrders.filter(order => order.payment_status === 'paid');
    filteredOrders.dinein.unpaid = dineInOrders.filter(order => order.payment_status !== 'paid');

    filteredOrders.online.all = onlineOrders;
    filteredOrders.online.paid = onlineOrders.filter(order => order.payment_status === 'paid');
    filteredOrders.online.unpaid = onlineOrders.filter(order => order.payment_status !== 'paid');

    // Update counts (show total counts)
    document.getElementById('takeawayCount').textContent = takeawayOrders.length;
    document.getElementById('dineinCount').textContent = dineInOrders.length;
    document.getElementById('onlineCount').textContent = onlineOrders.length;

    // Apply current filters and populate grids
    applyFilters();
}

function applyFilters() {
    // Apply current filter for each section
    const takeawayFiltered = filteredOrders.takeaway[currentFilters.takeaway];
    const dineinFiltered = filteredOrders.dinein[currentFilters.dinein];
    const onlineFiltered = filteredOrders.online[currentFilters.online];

    // Populate grids with filtered orders
    populateOrderGrid('takeawayOrdersGrid', takeawayFiltered);
    populateOrderGrid('dineInOrdersGrid', dineinFiltered);
    populateOrderGrid('onlineOrdersGrid', onlineFiltered);
}

function setFilter(section, filter) {
    currentFilters[section] = filter;

    // Update button states
    updateFilterButtonStates(section, filter);

    // Apply the filter
    applyFilters();
}

function updateFilterButtonStates(section, activeFilter) {
    // Remove active class from all buttons in this section
    document.querySelectorAll(`[data-section="${section}"].order-filter-btn`).forEach(btn => {
        btn.classList.remove('bg-blue-100', 'text-blue-800');
        btn.classList.add('bg-gray-100', 'text-gray-600');
    });

    // Add active class to the clicked button
    const activeBtn = document.querySelector(`[data-section="${section}"][data-filter="${activeFilter}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-100', 'text-gray-600');
        activeBtn.classList.add('bg-blue-100', 'text-blue-800');
    }
}

function populateOrderGrid(gridId, orders) {
    const grid = document.getElementById(gridId);
    if (!grid) return;

    grid.innerHTML = '';

    if (orders.length === 0) {
        grid.innerHTML = '<div class="text-center text-gray-500 py-8">No orders</div>';
        return;
    }

    orders.forEach(order => {
        const orderCard = createOrderCard(order);
        grid.appendChild(orderCard);
    });
}

function createOrderCard(order) {
    const card = document.createElement('div');

    // Add special styling for paid orders (especially Amako Credits)
    const isPaid = order.payment_status === 'paid';
    const isAmakoCredits = order.payment_method === 'amako_credits';
    const borderClass = isPaid && isAmakoCredits
        ? 'border-l-4 border-l-green-500 bg-green-50'
        : isPaid
        ? 'border-l-4 border-l-green-400 bg-white'
        : 'border-gray-200 bg-white';

    card.className = `order-card ${borderClass} border rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow cursor-pointer`;
    card.onclick = () => selectOrder(order);

    const statusColor = getStatusColor(order.status);
    const paymentStatusColor = getPaymentStatusColor(order.payment_status);

    const itemsList = order.items.map(item =>
        `${item.item_name} x${item.quantity}`
    ).join(', ');

    // Add action buttons for online orders
    let actionButtons = '';
    if (order.type === 'online') {
        if (order.status === 'pending') {
            // Show accept/decline buttons for pending orders
            actionButtons = `
                <div class="mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                    <div class="flex gap-2">
                        <button onclick="acceptOrder(${order.id})"
                                class="flex-1 px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-1"></i> Accept
                        </button>
                        <button onclick="declineOrder(${order.id})"
                                class="flex-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-1"></i> Decline
                        </button>
                    </div>
                </div>
            `;
        } else if (order.status === 'confirmed' || order.status === 'preparing') {
            // Show mark as ready button for confirmed/preparing orders
            actionButtons = `
                <div class="mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                    <button onclick="markOrderAsReady(${order.id})"
                            class="w-full px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check-circle mr-1"></i> Mark as Ready
                    </button>
                </div>
            `;
        } else if (order.status === 'declined') {
            // Show reset button for declined orders
            actionButtons = `
                <div class="mt-3 pt-3 border-t border-gray-200" onclick="event.stopPropagation()">
                    <button onclick="resetOrderStatus(${order.id}, '${order.status}')"
                            class="w-full px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-undo mr-1"></i> Reset to Pending
                    </button>
                </div>
            `;
        }
    }

    // Add payment method badge for Amako Credits
    const paymentMethodBadge = isAmakoCredits && isPaid
        ? `<span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 ml-1">
            <i class="fas fa-wallet mr-1"></i>Amako Credits
           </span>`
        : '';

    // Session order number (D-0001 / T-0001 / O-0001) — prominently shown if available
    const displayNumber  = order.session_order_number || order.order_number;
    const orderTypeLower = (order.type || order.order_type || '').toLowerCase();
    const isDineIn       = orderTypeLower === 'dine_in';
    const numberColor    = orderTypeLower === 'dine_in'   ? '#3b82f6'   // blue
                         : orderTypeLower === 'takeaway'  ? '#f97316'   // orange
                         : '#16a34a';                                     // green (online)

    // Dine-in: hide kitchen status (preparing/ready) — cashier only cares about payment
    const statusBadge = isDineIn
        ? ''
        : `<span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${statusColor}">${order.status}</span>`;

    card.innerHTML = `
        <div class="flex justify-between items-start mb-1">
            <div style="font-size:20px;font-weight:900;color:${numberColor};line-height:1.1;">
                ${displayNumber}
            </div>
            <div class="text-right">
                ${statusBadge}
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${paymentStatusColor} ml-1">${order.payment_status}</span>
                ${paymentMethodBadge}
            </div>
        </div>
        <div class="mb-1">
            <p class="text-xs text-gray-500">${order.table ? `Table ${order.table.name}` : 'No table'}</p>
        </div>
        <div class="mb-2">
            <p class="text-xs text-gray-600 line-clamp-1">${itemsList}</p>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-lg font-bold text-gray-900">Rs ${parseFloat(order.total_amount).toFixed(2)}</span>
            <span class="text-xs text-gray-500">${formatTime(order.created_at)}</span>
        </div>
        ${actionButtons}
    `;

    return card;
}

function getStatusColor(status) {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'declined': 'bg-red-100 text-red-800',
        'preparing': 'bg-blue-100 text-blue-800',
        'ready': 'bg-green-100 text-green-800',
        'completed': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getPaymentStatusColor(paymentStatus) {
    const colors = {
        'unpaid': 'bg-red-100 text-red-800',
        'paid': 'bg-green-100 text-green-800',
        'partial': 'bg-yellow-100 text-yellow-800'
    };
    return colors[paymentStatus] || 'bg-gray-100 text-gray-800';
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    stopOrderPolling();
});
