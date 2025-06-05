<template>
  <div>
    <!-- Loading Overlay -->
    <div v-if="isInitializing" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Employee Auth Modal -->
    <div v-else-if="!isAuthenticated" class="modal-backdrop-custom">
      <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.4);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header border-0">
              <h5 class="modal-title">Welcome to POS</h5>
            </div>
            <div class="modal-body">
              <div v-if="isVerifying" class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                  <span class="visually-hidden">Verifying...</span>
                </div>
                <p class="text-muted">Verifying your credentials...</p>
              </div>
              <div v-else>
                <div class="text-center mb-4">
                  <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                  <h4>Employee Login</h4>
                  <p class="text-muted">Please enter your credentials to continue</p>
                </div>
                <div class="mb-3">
                  <label class="form-label">Employee ID or Email</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input v-model="employeeId" class="form-control" placeholder="Enter your ID or email" />
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Password</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input v-model="employeePassword" type="password" class="form-control" placeholder="Enter your password" />
                  </div>
                </div>
                <div v-if="authError" class="alert alert-danger py-2 mb-3">
                  <i class="fas fa-exclamation-circle me-2"></i>
                  {{ authError }}
                </div>
              </div>
            </div>
            <div class="modal-footer border-0">
              <button class="btn btn-primary w-100 py-2" @click="verifyEmployee" :disabled="isVerifying">
                <i class="fas fa-sign-in-alt me-2"></i>
                {{ isVerifying ? 'Verifying...' : 'Login' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main POS Interface -->
    <div v-if="isAuthenticated" class="pos-interface">
      <!-- Top Bar -->
      <div class="top-bar mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <div class="employee-info me-4">
              <i class="fas fa-user-circle me-2"></i>
              <span>{{ employeeName }}</span>
            </div>
            <div class="order-type-selector">
              <div class="btn-group">
                <button 
                  v-for="type in orderTypes" 
                  :key="type.value"
                  class="btn" 
                  :class="orderType === type.value ? 'btn-primary' : 'btn-outline-primary'"
                  @click="orderType = type.value"
                >
                  <i :class="type.icon" class="me-2"></i>
                  {{ type.label }}
                </button>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <button class="btn btn-outline-danger me-2" @click="quickLogout">
              <i class="fas fa-lock me-2"></i> Lock
            </button>
            <button class="btn btn-outline-primary" @click="refreshData">
              <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Notification -->
      <div v-if="notification" 
           :class="['alert', notification.type === 'success' ? 'alert-success' : 'alert-danger', 'mb-4']"
           role="alert">
        <i :class="notification.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" class="me-2"></i>
        {{ notification.message }}
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="d-flex justify-content-center align-items-center" style="height:100px;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Main Content -->
      <div class="row g-4" style="min-height: calc(100vh - 200px);">
        <!-- Order Cart -->
        <div class="col-12 col-lg-4">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><i class="fas fa-shopping-cart me-2"></i>Order Cart</span>
              <span class="badge bg-primary">{{ cart.length }} items</span>
            </div>
            <div class="card-body d-flex flex-column">
              <div v-if="cart.length === 0" class="text-center my-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">Your cart is empty</p>
                <p class="text-muted small">Add items from the menu to start an order</p>
              </div>
              <div v-else class="cart-items scrollbar-custom">
                <div v-for="item in cart" :key="item.product.id" class="cart-item mb-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex">
                      <img 
                        :src="item.product.image || '/images/no-image.png'" 
                        :alt="item.product.name"
                        class="cart-item-img me-3"
                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                      >
                      <div>
                        <h6 class="mb-1">{{ item.product.name }}</h6>
                        <div class="text-muted small">Rs. {{ item.product.price }} × {{ item.quantity }}</div>
                        <div class="text-primary fw-bold">Rs. {{ (item.product.price * item.quantity).toFixed(2) }}</div>
                      </div>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-primary" @click="decrementQty(item.product.id)">
                          <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" @click="incrementQty(item.product.id)">
                          <i class="fas fa-plus"></i>
                        </button>
                      </div>
                      <button class="btn btn-sm btn-outline-danger" @click="removeFromCart(item.product.id)">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="cart-summary mt-auto">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span>Subtotal:</span>
                  <span class="fw-bold">Rs. {{ cartSubtotal }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span>Tax (13%):</span>
                  <span class="fw-bold">Rs. {{ cartTax }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="h5 mb-0">Total:</span>
                  <span class="h5 mb-0 text-primary">Rs. {{ cartTotal }}</span>
                </div>

                <div v-if="orderType === 'dine-in'" class="mb-3">
                  <label class="form-label">Select Table</label>
                  <select class="form-select" v-model="selectedTable">
                    <option value="">Choose a table...</option>
                    <option v-for="table in tables" :key="table.id" :value="table.id">
                      {{ table.name }} ({{ table.status }})
                    </option>
                  </select>
                </div>

                <button class="btn btn-primary w-100 py-3" 
                        @click="handleSubmitOrder" 
                        :disabled="cart.length === 0 || (orderType === 'dine-in' && !selectedTable)">
                  <i class="fas fa-check-circle me-2"></i>
                  Submit Order
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Menu Panel -->
        <div class="col-12 col-lg-4">
          <div class="card h-100">
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center">
                <span><i class="fas fa-utensils me-2"></i>Menu</span>
                <div class="search-box" style="width: 200px;">
                  <div class="input-group">
                    <span class="input-group-text border-0 bg-light">
                      <i class="fas fa-search text-muted"></i>
                    </span>
                    <input class="form-control border-0 bg-light" 
                           v-model="search" 
                           placeholder="Search products..."
                           @input="handleSearch">
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="menu-items scrollbar-custom" style="height: calc(100vh - 300px);">
                <div v-if="filteredProducts.length === 0" class="text-center my-5">
                  <i class="fas fa-search fa-3x text-muted mb-3"></i>
                  <p class="text-muted">No products found</p>
                </div>
                <div v-for="product in filteredProducts" 
                     :key="product.id" 
                     class="menu-item p-3 border-bottom">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex">
                      <img 
                        :src="product.image || '/images/no-image.png'" 
                        :alt="product.name"
                        class="menu-item-img me-3"
                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                      >
                      <div>
                        <h6 class="mb-1">{{ product.name }}</h6>
                        <p class="text-muted small mb-1">{{ product.description }}</p>
                        <div class="text-primary fw-bold">Rs. {{ product.price }}</div>
                      </div>
                    </div>
                    <button class="btn btn-primary" @click="addToCart(product)">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Open Orders Panel -->
        <div class="col-12 col-lg-4">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><i class="fas fa-receipt me-2"></i>Open Orders</span>
              <span class="badge bg-primary">{{ openOrders.length }} orders</span>
            </div>
            <div class="card-body p-0">
              <div class="orders-list scrollbar-custom" style="height: calc(100vh - 300px);">
                <div v-if="openOrders.length === 0" class="text-center my-5">
                  <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                  <p class="text-muted">No open orders</p>
                </div>
                <div v-for="order in openOrders" 
                     :key="order.id" 
                     class="order-item p-3 border-bottom">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <h6 class="mb-1">
                        #{{ order.order_number }}
                        <span v-if="order.type === 'dine-in' && order.table" class="ms-2 text-muted">
                          Table: {{ order.table.name }}
                        </span>
                      </h6>
                      <div class="text-muted small">
                        {{ formatDate(order.created_at) }}
                      </div>
                    </div>
                    <div>
                      <span :class="['badge', getStatusBadgeClass(order.status)]">
                        {{ order.status }}
                      </span>
                      <span class="badge bg-info ms-2 text-uppercase">{{ order.type }}</span>
                    </div>
                  </div>
                  
                  <div class="order-items mb-2">
                    <div v-for="item in order.items" 
                         :key="item.id" 
                         class="d-flex justify-content-between py-1">
                      <span>{{ item.item_name }}</span>
                      <span class="text-muted">x{{ item.quantity }}</span>
                    </div>
                  </div>

                  <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold">Total: Rs. {{ order.total_amount }}</div>
                    <div class="btn-group">
                      <button class="btn btn-sm btn-primary" @click="editOrder(order)">
                        <i class="fas fa-edit me-1"></i> Edit
                      </button>
                      <button class="btn btn-sm btn-danger" @click="deleteOrder(order)">
                        <i class="fas fa-trash me-1"></i> Delete
                      </button>
                      <button v-if="order.status === 'completed'" 
                              class="btn btn-sm btn-dark" 
                              @click="printReceipt(order.id)">
                        <i class="fas fa-print me-1"></i> Print
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Order Modal -->
    <div v-if="showAddModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-plus-circle me-2"></i>
              Add New Order
            </h5>
            <button type="button" class="btn-close" @click="closeAddModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Add your modal content here -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import axios from 'axios';

const products = ref([]);
const tables = ref([]);
const orders = ref([]);
const cart = ref([]);
const orderType = ref('dine-in');
const selectedTable = ref(null);
const search = ref('');
const notification = ref(null);
const loading = ref(false);
const flashProductId = ref(null);
const showEditModal = ref(false);
const editingOrder = ref({ items: [] });
const showAddModal = ref(false);
const newOrder = ref({ type: 'dine-in', table_id: null, items: [] });
const showDeleteItemModal = ref(false);
const itemToDelete = ref(null);
const showDeleteOrderModal = ref(false);
const orderToDelete = ref(null);
const showSubmitOrderModal = ref(false);
const showPrintModal = ref(false);
const lastOrderId = ref(null);
const isAuthenticated = ref(false);
const employeeId = ref('');
const employeePassword = ref('');
const employeeName = ref('');
const authError = ref('');
const isAdmin = ref(false);
const isCashier = ref(false);
const isInitializing = ref(true);
const isVerifying = ref(false);

const today = new Date();
const formattedDate = today.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

const formattedTime = today.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

const filteredProducts = computed(() => {
  if (!Array.isArray(products.value)) return [];
  if (!search.value) return products.value;
  return products.value.filter(p => p?.name?.toLowerCase().includes(search.value.toLowerCase()));
});

const cartTotal = computed(() => {
  if (!Array.isArray(cart.value)) return 0;
  return cart.value.reduce((sum, item) => sum + (item?.product?.price || 0) * (item?.quantity || 0), 0);
});

const openOrders = computed(() => {
  if (!Array.isArray(orders.value)) return [];
  return orders.value.filter(order => 
    order?.status && ['pending', 'preparing', 'prepared'].includes(order.status)
  );
});

function showNotification(message, type = 'success') {
  notification.value = { message, type };
  setTimeout(() => notification.value = null, 2500);
}

function addToCart(product) {
  const found = cart.value.find(i => i.product.id === product.id);
  if (found) {
    found.quantity++;
  } else {
    cart.value.push({ product, quantity: 1 });
  }
  flashProduct(product.id);
}

function flashProduct(productId) {
  flashProductId.value = productId;
  setTimeout(() => {
    flashProductId.value = null;
  }, 400);
}

function incrementQty(productId) {
  const found = cart.value.find(i => i.product.id === productId);
  if (found) found.quantity++;
}

function decrementQty(productId) {
  const found = cart.value.find(i => i.product.id === productId);
  if (found && found.quantity > 1) found.quantity--;
}

function removeFromCart(productId) {
  if (!Array.isArray(cart.value)) {
    cart.value = [];
    return;
  }
  cart.value = cart.value.filter(i => i?.product?.id !== productId);
}

async function fetchProducts() {
  try {
    loading.value = true;
    const res = await axios.get('/api/pos/products');
    if (Array.isArray(res.data)) {
      products.value = res.data;
    } else {
      console.error('Invalid products data received:', res.data);
      products.value = [];
      showNotification('Failed to load products: Invalid data format', 'danger');
    }
  } catch (e) {
    console.error('Error fetching products:', e);
    products.value = [];
    showNotification('Failed to load products', 'danger');
  } finally {
    loading.value = false;
  }
}

async function fetchTables() {
  try {
    loading.value = true;
    const res = await axios.get('/api/pos/tables');
    tables.value = res.data;
  } catch (e) {
    showNotification('Failed to load tables', 'danger');
  } finally {
    loading.value = false;
  }
}

async function fetchOrders() {
  try {
    loading.value = true;
    console.log('Fetching orders...');
    const res = await axios.get('/api/pos/orders');
    console.log('Orders response:', res.data);
    
    if (res.data.success && Array.isArray(res.data.orders)) {
      orders.value = res.data.orders;
      console.log('Orders loaded:', orders.value);
    } else {
      console.error('Invalid orders data received:', res.data);
      orders.value = [];
      showNotification('Failed to load orders: Invalid data format', 'danger');
    }
  } catch (error) {
    console.error('Error fetching orders:', error);
    orders.value = [];
    
    // Handle specific error cases
    if (error.response) {
      switch (error.response.status) {
        case 403:
          showNotification('You do not have permission to view orders. Please contact your administrator.', 'danger');
          break;
        case 401:
          showNotification('Your session has expired. Please log in again.', 'danger');
          // Optionally redirect to login
          isAuthenticated.value = false;
          break;
        default:
          showNotification(error.response.data?.message || 'Failed to load orders', 'danger');
      }
    } else {
      showNotification('Failed to load orders. Please check your connection.', 'danger');
    }
  } finally {
    loading.value = false;
  }
}

async function handleSubmitOrder() {
  try {
    loading.value = true;
    const payload = {
      type: orderType.value,
      table_id: orderType.value === 'dine-in' ? selectedTable.value : null,
      items: cart.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity
      }))
    };

    const response = await axios.post('/api/pos/orders', payload);
    
    if (response.data.success) {
      showNotification('Order submitted successfully!', 'success');
      cart.value = [];
      await Promise.all([
        fetchOrders(),
        orderType.value === 'dine-in' ? fetchTables() : Promise.resolve()
      ]);
    } else {
      showNotification(response.data.message || 'Failed to submit order', 'danger');
    }
  } catch (error) {
    console.error('Error submitting order:', error);
    showNotification(error.response?.data?.message || 'Failed to submit order', 'danger');
  } finally {
    loading.value = false;
  }
}

function editOrder(order) {
  editingOrder.value = { ...order };
  showEditModal.value = true;
}

function deleteOrder(order) {
  orderToDelete.value = order;
  showDeleteOrderModal.value = true;
}

function addOrder() {
  showAddModal.value = true;
}

function printReceipt(orderId) {
  window.open(`/orders/${orderId}/receipt`, '_blank');
}

function closeEditModal() {
  showEditModal.value = false;
  editingOrder.value = { items: [] };
}

function incrementEditQty(item) {
  item.quantity++;
}

function decrementEditQty(item) {
  if (item.quantity > 1) item.quantity--;
}

function removeEditItem(item) {
  itemToDelete.value = item;
  showDeleteItemModal.value = true;
}

function confirmDeleteItem() {
  if (!editingOrder.value) {
    editingOrder.value = { items: [] };
    return;
  }
  if (!Array.isArray(editingOrder.value.items)) {
    editingOrder.value.items = [];
    return;
  }
  editingOrder.value.items = editingOrder.value.items.filter(i => i !== itemToDelete.value);
  showDeleteItemModal.value = false;
  itemToDelete.value = null;
}

function cancelDeleteItem() {
  showDeleteItemModal.value = false;
  itemToDelete.value = null;
}

async function updateOrder() {
  try {
    loading.value = true;
    if (!editingOrder.value || !Array.isArray(editingOrder.value.items)) {
      showNotification('Invalid order data', 'danger');
      return;
    }
    const filteredItems = editingOrder.value.items
      .filter(item => item?.quantity > 0)
      .map(item => ({ product_id: item.product_id, quantity: item.quantity }));
    if (filteredItems.length === 0) {
      showNotification('Order must have at least one item.', 'danger');
      loading.value = false;
      return;
    }
    const payload = {
      type: editingOrder.value.type,
      table_id: editingOrder.value.type === 'dine-in' ? editingOrder.value.table_id : null,
      items: filteredItems,
    };
    await axios.put(`/api/pos/orders/${editingOrder.value.id}`, payload);
    showNotification('Order updated!', 'success');
    await fetchOrders();
    closeEditModal();
  } catch (e) {
    showNotification('Failed to update order', 'danger');
  } finally {
    loading.value = false;
  }
}

async function confirmDeleteOrder() {
  if (!orderToDelete.value) return;
  try {
    loading.value = true;
    await axios.delete(`/api/pos/orders/${orderToDelete.value.id}`);
    showNotification('Order deleted!', 'success');
    await fetchOrders();
  } catch (e) {
    showNotification('Failed to delete order', 'danger');
  } finally {
    loading.value = false;
    showDeleteOrderModal.value = false;
    orderToDelete.value = null;
  }
}

function cancelDeleteOrder() {
  showDeleteOrderModal.value = false;
  orderToDelete.value = null;
}

function incrementNewQty(product) {
  if (!Array.isArray(newOrder.value?.items)) {
    newOrder.value.items = [];
    return;
  }
  const item = newOrder.value.items.find(item => item?.product_id === product?.id);
  if (item) {
    item.quantity++;
  } else {
    newOrder.value.items.push({ product_id: product.id, quantity: 1 });
  }
}

function decrementNewQty(product) {
  if (!newOrder.value) {
    newOrder.value = { type: 'dine-in', table_id: null, items: [] };
    return;
  }
  if (!Array.isArray(newOrder.value.items)) {
    newOrder.value.items = [];
    return;
  }
  const item = newOrder.value.items.find(item => item?.product_id === product?.id);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item) {
    newOrder.value.items = newOrder.value.items.filter(i => i?.product_id !== product?.id);
  }
}

async function submitNewOrder() {
  try {
    loading.value = true;
    const payload = {
      type: newOrder.value.type,
      table_id: newOrder.value.type === 'dine-in' ? newOrder.value.table_id : null,
      items: newOrder.value.items,
    };
    await axios.post('/api/pos/orders', payload);
    showNotification('New order submitted!', 'success');
    fetchOrders();
    closeAddModal();
  } catch (e) {
    showNotification('Failed to submit new order', 'danger');
  } finally {
    loading.value = false;
  }
}

function closeAddModal() {
  showAddModal.value = false;
  newOrder.value = { type: 'dine-in', table_id: null, items: [] };
}

function printNow() {
  if (lastOrderId.value) {
    printReceipt(lastOrderId.value);
  }
  closePrintModal();
}

function quickLogout() {
  localStorage.removeItem('pos_token');
  delete axios.defaults.headers.common['Authorization'];
  isAuthenticated.value = false;
  employeeName.value = '';
  authError.value = '';
}

async function verifyEmployee() {
  try {
    isVerifying.value = true;
    authError.value = '';
    
    const response = await axios.post('/pos-login', {
      identifier: employeeId.value,
      password: employeePassword.value
    });

    if (response.data.success) {
      // Set the token in axios defaults
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
      
      // Store token in localStorage
      localStorage.setItem('pos_token', response.data.token);
      
      isAuthenticated.value = true;
      employeeName.value = response.data.user.name;
      isAdmin.value = response.data.user.roles.includes('admin');
      isCashier.value = response.data.user.roles.includes('employee.cashier');
      
      // Fetch initial data
      await Promise.all([
        fetchProducts(),
        fetchTables(),
        fetchOrders()
      ]);
      
      showNotification('Welcome back, ' + response.data.user.name);
    }
  } catch (error) {
    console.error('Login error:', error);
    authError.value = error.response?.data?.message || 'Authentication failed';
  } finally {
    isVerifying.value = false;
  }
}

async function checkAuth() {
  const token = localStorage.getItem('pos_token');
  if (token) {
    try {
      isVerifying.value = true;
      // Set the token in axios defaults
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // Verify token by making a request to a dedicated auth endpoint
      const response = await axios.get('/api/pos/verify-token');
      
      if (response.data.success) {
        isAuthenticated.value = true;
        employeeName.value = response.data.user.name;
        isAdmin.value = response.data.user.roles.includes('admin');
        isCashier.value = response.data.user.roles.includes('employee.cashier');
        
        // Fetch initial data
        await Promise.all([
          fetchProducts(),
          fetchTables(),
          fetchOrders()
        ]);
      } else {
        throw new Error('Invalid token');
      }
    } catch (error) {
      console.error('Auth check failed:', error);
      // Clear invalid token
      localStorage.removeItem('pos_token');
      delete axios.defaults.headers.common['Authorization'];
      isAuthenticated.value = false;
      showNotification('Your session has expired. Please log in again.', 'danger');
    } finally {
      isVerifying.value = false;
      isInitializing.value = false;
    }
  } else {
    isAuthenticated.value = false;
    isInitializing.value = false;
  }
}

onMounted(() => {
  checkAuth();
  // Set up polling for orders every 30 seconds
  const orderPolling = setInterval(fetchOrders, 30000);
  
  // Clean up on component unmount
  onUnmounted(() => {
    clearInterval(orderPolling);
  });
});

function getStatusBadgeClass(status) {
  return {
    'pending': 'bg-warning',
    'preparing': 'bg-info',
    'prepared': 'bg-success',
    'completed': 'bg-success',
    'cancelled': 'bg-danger'
  }[status] || 'bg-secondary';
}
</script>

<style scoped>
.pos-interface {
  padding: 1rem;
}

.top-bar {
  background: white;
  padding: 1rem;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.employee-info {
  font-weight: 500;
}

.cart-items, .menu-items, .orders-list {
  overflow-y: auto;
}

.cart-item, .menu-item, .order-item {
  transition: all 0.2s ease;
}

.cart-item:hover, .menu-item:hover, .order-item:hover {
  background-color: #f8fafc;
}

.cart-item-img, .menu-item-img {
  transition: transform 0.2s ease;
}

.cart-item-img:hover, .menu-item-img:hover {
  transform: scale(1.05);
}

.btn-group .btn {
  border-radius: 0.375rem;
  margin: 0 0.25rem;
}

.search-box .form-control {
  border-radius: 0.375rem;
}

.search-box .input-group-text {
  border-radius: 0.375rem 0 0 0.375rem;
}

.modal-backdrop-custom {
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}
</style> 