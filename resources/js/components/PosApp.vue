<template>
  <div class="pos-interface">
    <!-- Loading Overlay -->
    <div v-if="isInitializing" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Auth Modal -->
    <div v-else-if="!isAuthenticated" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h5 class="text-xl font-semibold mb-4">POS Login</h5>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID or Email</label>
            <input v-model="employeeId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your ID or email" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input v-model="employeePassword" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your password" />
          </div>
          <div v-if="authError" class="text-red-600 text-sm">{{ authError }}</div>
        </div>
        <button class="w-full mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" @click="login">Login</button>
      </div>
    </div>

    <!-- Main POS Interface -->
    <div v-if="isAuthenticated" class="grid grid-cols-12 gap-6">
      <!-- Left Sidebar - Products -->
      <div class="col-span-3 bg-white rounded-lg shadow p-4">
        <div class="mb-4">
          <input type="text" v-model="searchQuery" placeholder="Search products..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div class="space-y-2">
          <button v-for="category in categories" :key="category.id" 
                  @click="selectedCategory = category"
                  :class="['w-full px-4 py-2 text-left rounded', selectedCategory?.id === category.id ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100']">
            {{ category.name }}
          </button>
        </div>
      </div>

      <!-- Main Content - Products Grid -->
      <div class="col-span-6 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-3 gap-4">
          <div v-for="product in filteredProducts" :key="product.id" 
               @click="addToOrder(product)"
               class="bg-white border rounded-lg p-4 cursor-pointer hover:shadow-md transition-shadow">
            <img :src="product.image_url" :alt="product.name" class="w-full h-32 object-cover rounded mb-2" />
            <h3 class="font-semibold">{{ product.name }}</h3>
            <p class="text-gray-600">₦{{ product.price }}</p>
          </div>
        </div>
      </div>

      <!-- Right Sidebar - Current Order -->
      <div class="col-span-3 bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-bold mb-4">Current Order</h2>
        <div class="space-y-4">
          <div v-for="item in currentOrder.items" :key="item.id" class="flex items-center justify-between">
            <div>
              <h4 class="font-medium">{{ item.name }}</h4>
              <p class="text-sm text-gray-600">₦{{ item.price }} x {{ item.quantity }}</p>
            </div>
            <div class="flex items-center space-x-2">
              <button @click="decrementItem(item)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">-</button>
              <span>{{ item.quantity }}</span>
              <button @click="incrementItem(item)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">+</button>
              <button @click="removeItem(item)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="mt-6 border-t pt-4">
          <div class="flex justify-between mb-2">
            <span>Subtotal:</span>
            <span>₦{{ orderSubtotal }}</span>
          </div>
          <div class="flex justify-between mb-2">
            <span>Tax ({{ taxRate }}%):</span>
            <span>₦{{ orderTax }}</span>
          </div>
          <div class="flex justify-between font-bold text-lg">
            <span>Total:</span>
            <span>₦{{ orderTotal }}</span>
          </div>
        </div>

        <div class="mt-6 space-y-2">
          <button @click="processOrder" 
                  :disabled="!canProcessOrder"
                  class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Process Order
          </button>
          <button @click="clearOrder" 
                  class="w-full bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
            Clear Order
          </button>
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
  @apply min-h-screen bg-gray-100 p-6;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner-border {
  width: 4rem;
  height: 4rem;
}

.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.bg-opacity-50 {
  background-color: rgba(0, 0, 0, 0.5);
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.justify-center {
  justify-content: center;
}

.rounded-lg {
  border-radius: 0.75rem;
}

.p-6 {
  padding: 1.5rem;
}

.max-w-md {
  max-width: 28rem;
}

.text-xl {
  font-size: 1.25rem;
}

.font-semibold {
  font-weight: 600;
}

.space-y-4 {
  margin-bottom: 1rem;
}

.text-gray-700 {
  color: #4b5563;
}

.text-sm {
  font-size: 0.875rem;
}

.text-red-600 {
  color: #dc2626;
}

.w-full {
  width: 100%;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.border {
  border-width: 1px;
}

.border-gray-300 {
  border-color: #d1d5db;
}

.focus\:outline-none {
  outline: none;
}

.focus\:ring-2 {
  ring-width: 2px;
}

.focus\:ring-blue-500 {
  ring-color: #3b82f6;
}

.bg-white {
  background-color: white;
}

.bg-blue-600 {
  background-color: #3b82f6;
}

.text-white {
  color: white;
}

.hover\:bg-blue-700 {
  background-color: #2563eb;
}

.disabled\:opacity-50 {
  opacity: 0.5;
}

.disabled\:cursor-not-allowed {
  cursor: not-allowed;
}

.grid {
  display: grid;
}

.grid-cols-12 {
  grid-template-columns: repeat(12, minmax(0, 1fr));
}

.gap-6 {
  gap: 1.5rem;
}

.bg-white {
  background-color: white;
}

.rounded-lg {
  border-radius: 0.75rem;
}

.shadow {
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.p-4 {
  padding: 1rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.text-center {
  text-align: center;
}

.my-5 {
  margin-top: 1.25rem;
  margin-bottom: 1.25rem;
}

.text-muted {
  color: #6b7280;
}

.text-primary {
  color: #1d4ed8;
}

.fw-bold {
  font-weight: 700;
}

.h5 {
  font-size: 1.25rem;
  font-weight: 700;
}

.h5:last-child {
  margin-bottom: 0;
}

.text-primary:last-child {
  margin-bottom: 0;
}

.text-primary:last-child:after {
  content: '';
  display: block;
  width: 100%;
  height: 1px;
  background-color: #d1d5db;
  margin-top: 0.5rem;
}

.btn-group {
  display: flex;
  align-items: center;
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