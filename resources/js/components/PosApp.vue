<template>
  <div class="pos-app">
    <!-- Employee Auth Modal -->
    <div v-if="!isAuthenticated" class="modal-backdrop-custom">
      <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.4);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Employee Authentication</h5>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Employee ID or Email</label>
                <input v-model="employeeId" class="form-control" placeholder="Enter your ID or email" />
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input v-model="employeePassword" type="password" class="form-control" placeholder="Enter your password" />
              </div>
              <div v-if="authError" class="alert alert-danger py-2">{{ authError }}</div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary w-100" @click="verifyEmployee">Login</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="isAuthenticated">
      <div class="quick-lock-bar d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center">
          <h2 class="mb-0 me-3">Momo Shop POS</h2>
          <div class="date-time-display">
            <div class="date">{{ formattedDate }}</div>
            <div class="time">{{ formattedTime }}</div>
          </div>
        </div>
        <div>
          <span>Logged in as: <b>{{ employeeName }}</b></span>
          <button class="btn btn-outline-danger btn-sm ms-2" @click="quickLogout">
            <i class="fas fa-lock"></i> Lock
          </button>
        </div>
      </div>
      <div v-if="notification" :class="['alert', notification.type === 'success' ? 'alert-success' : 'alert-danger']">
        {{ notification.message }}
      </div>
      <div class="row pos-main-row">
        <!-- Product List -->
        <div class="col-md-4 col-12 mb-3">
          <div class="card h-100">
            <div class="card-body">
              <h4 class="card-title">Menu</h4>
              <input v-model="search" class="form-control mb-3" placeholder="Search products..." />
              <div class="product-list-scroll">
                <transition-group name="fade" tag="div">
                  <div v-for="product in filteredProducts" :key="product.id" class="product-card d-flex align-items-center mb-3 p-2" :class="{ 'added-flash': flashProductId === product.id }">
                    <img v-if="product.image" :src="`/storage/${product.image}`" alt="" class="product-img me-3" />
                    <div class="flex-grow-1">
                      <div class="fw-bold">{{ product.name }}</div>
                      <div class="text-muted">Rs. {{ product.price }}</div>
                    </div>
                    <button class="btn btn-success btn-circle ms-2" @click="addToCart(product)">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </transition-group>
              </div>
            </div>
          </div>
        </div>
        <!-- Cart Panel -->
        <div class="col-md-4 col-12 mb-3">
          <div class="card order-cart-card">
            <div class="card-body order-cart-body">
              <h4 class="card-title">Order Cart</h4>
              <div v-if="cart.length === 0" class="text-muted text-center my-5">Cart is empty</div>
              <transition-group name="cart-fade" tag="ul" class="cart-list cart-list-flex list-unstyled">
                <li v-for="item in cart" :key="item.product.id" class="cart-item d-flex align-items-center mb-3">
                  <img v-if="item.product.image" :src="`/storage/${item.product.image}`" alt="" class="cart-img me-2" />
                  <div class="flex-grow-1">
                    <div class="fw-bold">{{ item.product.name }}</div>
                    <div class="text-muted small">Rs. {{ item.product.price }} x {{ item.quantity }}</div>
                  </div>
                  <div class="d-flex align-items-center ms-2">
                    <button class="btn btn-outline-secondary btn-sm" @click="decrementQty(item.product.id)">-</button>
                    <span class="mx-2">{{ item.quantity }}</span>
                    <button class="btn btn-outline-secondary btn-sm" @click="incrementQty(item.product.id)">+</button>
                  </div>
                  <span class="ms-3">Rs. {{ item.product.price * item.quantity }}</span>
                  <button class="btn btn-outline-danger btn-sm ms-2" @click="removeFromCart(item.product.id)"><i class="fas fa-trash"></i></button>
                </li>
              </transition-group>
              <div class="fw-bold mt-3">Total: Rs. {{ cartTotal }}</div>
            </div>
            <div class="card-footer order-cart-footer">
              <div class="mb-2">
                <label class="form-label mb-1">Order Type</label>
                <select v-model="orderType" class="form-select mb-2">
                  <option value="dine-in">Dine-In</option>
                  <option value="takeaway">Takeaway</option>
                  <option value="online">Online</option>
                </select>
                <div v-if="orderType === 'dine-in'">
                  <label class="form-label">Select Table:</label>
                  <select v-model="selectedTable" class="form-select mb-2">
                    <option v-for="table in tables" :key="table.id" :value="table.id">
                      {{ table.name }} ({{ table.status }})
                    </option>
                  </select>
                </div>
              </div>
              <button class="btn btn-success w-100" @click="handleSubmitOrder" :disabled="cart.length === 0">Submit Order</button>
            </div>
          </div>
        </div>
        <!-- Orders Panel -->
        <div class="col-md-4 col-12 mb-3">
          <div class="card h-100">
            <div class="card-body">
              <h4 class="card-title">Open Orders</h4>
              <ul class="order-list list-unstyled">
                <li v-for="order in openOrders" :key="order.id" class="order-item mb-3">
                  <span>
                    Order #{{ order.id }}
                    <span v-if="order.type === 'dine-in' && order.table"> - Table: {{ order.table.name }}</span>
                    <span class="ms-2 badge bg-secondary">{{ order.type }}</span>
                  </span>
                  <ul class="mb-1">
                    <li v-for="item in order.items" :key="item.id">
                      {{ item.item_name }} x {{ item.quantity }}
                    </li>
                  </ul>
                  <div class="mt-2">
                    <button class="btn btn-sm btn-primary me-1" @click="editOrder(order)"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-sm btn-danger me-1" @click="deleteOrder(order)"><i class="fas fa-trash"></i> Delete</button>
                    <button class="btn btn-sm btn-success" @click="addOrder"><i class="fas fa-plus"></i> Add</button>
                    <button v-if="order.status === 'completed'" class="btn btn-sm btn-dark me-1" @click="printReceipt(order.id)"><i class="fas fa-print"></i> Print</button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div v-if="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <!-- Edit Order Modal -->
      <div v-if="showEditModal" class="modal fade show" style="display: block;">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Order #{{ editingOrder.id }}</h5>
              <button type="button" class="btn-close" @click="closeEditModal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Order Type</label>
                <select v-model="editingOrder.type" class="form-select">
                  <option value="dine-in">Dine-In</option>
                  <option value="takeaway">Takeaway</option>
                  <option value="online">Online</option>
                </select>
              </div>
              <div v-if="editingOrder.type === 'dine-in'" class="mb-3">
                <label class="form-label">Select Table</label>
                <select v-model="editingOrder.table_id" class="form-select">
                  <option v-for="table in tables" :key="table.id" :value="table.id">
                    {{ table.name }} ({{ table.status }})
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Order Items</label>
                <ul class="list-unstyled">
                  <li v-for="item in editingOrder.items" :key="item.id" class="d-flex align-items-center mb-2">
                    <span class="flex-grow-1">{{ item.item_name }}</span>
                    <div class="d-flex align-items-center">
                      <button v-if="item.quantity > 1" class="btn btn-sm btn-outline-secondary me-2" @click="decrementEditQty(item)">-</button>
                      <button v-else class="btn btn-sm btn-outline-danger me-2" @click="removeEditItem(item)"><i class="fas fa-trash"></i></button>
                      <span class="mx-2">{{ item.quantity }}</span>
                      <button class="btn btn-sm btn-outline-secondary" @click="incrementEditQty(item)">+</button>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeEditModal">Close</button>
              <button type="button" class="btn btn-primary" @click="updateOrder">Save changes</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Add Order Modal -->
      <div v-if="showAddModal" class="modal fade show" style="display: block;">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Order</h5>
              <button type="button" class="btn-close" @click="closeAddModal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Order Type</label>
                <select v-model="newOrder.type" class="form-select">
                  <option value="dine-in">Dine-In</option>
                  <option value="takeaway">Takeaway</option>
                  <option value="online">Online</option>
                </select>
              </div>
              <div v-if="newOrder.type === 'dine-in'" class="mb-3">
                <label class="form-label">Select Table</label>
                <select v-model="newOrder.table_id" class="form-select">
                  <option v-for="table in tables" :key="table.id" :value="table.id">
                    {{ table.name }} ({{ table.status }})
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Add Products</label>
                <ul class="list-unstyled">
                  <li v-for="product in products" :key="product.id" class="d-flex align-items-center mb-2">
                    <span class="flex-grow-1">{{ product.name }}</span>
                    <div class="d-flex align-items-center">
                      <button class="btn btn-sm btn-outline-secondary" @click="decrementNewQty(product)">-</button>
                      <span class="mx-2">{{ newOrder.items.find(item => item.product_id === product.id)?.quantity || 0 }}</span>
                      <button class="btn btn-sm btn-outline-secondary" @click="incrementNewQty(product)">+</button>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeAddModal">Close</button>
              <button type="button" class="btn btn-primary" @click="submitNewOrder">Submit Order</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Delete Item Modal -->
      <div v-if="showDeleteItemModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Remove Item</h5>
              <button type="button" class="btn-close" @click="cancelDeleteItem"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to remove <strong>{{ itemToDelete?.item_name }}</strong> from the order?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="cancelDeleteItem">Cancel</button>
              <button type="button" class="btn btn-danger" @click="confirmDeleteItem">Remove</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Delete Order Modal -->
      <div v-if="showDeleteOrderModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Delete Order</h5>
              <button type="button" class="btn-close" @click="cancelDeleteOrder"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete <strong>Order #{{ orderToDelete?.id }}</strong>?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="cancelDeleteOrder">Cancel</button>
              <button type="button" class="btn btn-danger" @click="confirmDeleteOrder">Delete</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Submit Order Confirmation Modal -->
      <div v-if="showSubmitOrderModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Order Submission</h5>
              <button type="button" class="btn-close" @click="cancelSubmitOrder"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to submit this order?</p>
              <ul class="list-unstyled mb-2">
                <li><strong>Order Type:</strong> {{ orderType }}</li>
                <li v-if="orderType === 'dine-in' && selectedTable"><strong>Table:</strong> {{ tables.find(t => t.id === selectedTable)?.name }}</li>
                <li><strong>Total:</strong> Rs. {{ cartTotal }}</li>
              </ul>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="cancelSubmitOrder">Cancel</button>
              <button type="button" class="btn btn-success" @click="confirmSubmitOrder">Submit</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Print Order Modal -->
      <div v-if="showPrintModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Order Submitted</h5>
              <button type="button" class="btn-close" @click="closePrintModal"></button>
            </div>
            <div class="modal-body">
              <p>Your order has been submitted successfully.</p>
              <p>Would you like to print the receipts now?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closePrintModal">Print Later</button>
              <button type="button" class="btn btn-success" @click="printNow">Print Now</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
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
const editingOrder = ref(null);
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

const today = new Date();
const formattedDate = today.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

const filteredProducts = computed(() => {
  if (!search.value) return products.value;
  return products.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()));
});

const cartTotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
});

const openOrders = computed(() => orders.value.filter(order =>
  ['pending', 'preparing', 'prepared'].includes(order.status)
));

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
  cart.value = cart.value.filter(i => i.product.id !== productId);
}

function showNotification(message, type = 'success') {
  notification.value = { message, type };
  setTimeout(() => notification.value = null, 2500);
}

function statusBadgeClass(status) {
  switch (status) {
    case 'pending': return 'bg-warning';
    case 'preparing': return 'bg-info';
    case 'prepared': return 'bg-primary';
    case 'completed': return 'bg-success';
    default: return 'bg-secondary';
  }
}

async function fetchProducts() {
  loading.value = true;
  const res = await axios.get('/api/pos/products');
  products.value = res.data;
  loading.value = false;
}

async function fetchTables() {
  loading.value = true;
  const res = await axios.get('/api/pos/tables');
  tables.value = res.data;
  loading.value = false;
}

async function fetchOrders() {
  loading.value = true;
  const res = await axios.get('/api/pos/orders');
  orders.value = res.data;
  loading.value = false;
}

async function submitOrder() {
  try {
    loading.value = true;
    const typeMap = {
      'dine-in': 'dine-in',
      'takeaway': 'takeaway',
      'online': 'online'
    };
    const payload = {
      type: typeMap[orderType.value],
      table_id: orderType.value === 'dine-in' ? selectedTable.value : null,
      items: cart.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity
      })),
      payment_method: 'cash',
      amount_received: 0,
      guest_name: '',
      guest_email: '',
      created_by: employeeId.value
    };

    const res = await axios.post('/api/pos/orders', payload);
    
    cart.value = [];
    showNotification('Order submitted successfully!', 'success');
    fetchOrders();
    
    if (orderType.value === 'dine-in') fetchTables();

    // Show print modal if order is valid
    if (res.data && res.data.order && res.data.order.id) {
      lastOrderId.value = res.data.order.id;
      showPrintModal.value = true;
    } else {
      showNotification('Order was not saved correctly. Please try again.', 'danger');
    }
    
  } catch (error) {
    console.error('Error submitting order:', error);
    showNotification('Error submitting order. Please try again.', 'error');
  } finally {
    loading.value = false;
  }
}

function editOrder(order) {
  // Map items to ensure product_id is present
  const itemsWithProductId = order.items.map(item => ({
    ...item,
    product_id: item.product_id ?? item.id,
  }));
  editingOrder.value = { ...order, items: itemsWithProductId };
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  editingOrder.value = null;
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
    const filteredItems = editingOrder.value.items
      .filter(item => item.quantity > 0)
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
    fetchOrders();
    closeEditModal();
  } catch (e) {
    showNotification('Failed to update order', 'danger');
  } finally {
    loading.value = false;
  }
}

function deleteOrder(order) {
  orderToDelete.value = order;
  showDeleteOrderModal.value = true;
}

async function confirmDeleteOrder() {
  if (!orderToDelete.value) return;
  try {
    loading.value = true;
    await axios.delete(`/api/pos/orders/${orderToDelete.value.id}`);
    showNotification('Order deleted!', 'success');
    fetchOrders();
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

function addOrder() {
  newOrder.value = { type: 'dine-in', table_id: null, items: [] };
  showAddModal.value = true;
}

function closeAddModal() {
  showAddModal.value = false;
  newOrder.value = { type: 'dine-in', table_id: null, items: [] };
}

function incrementNewQty(product) {
  const item = newOrder.value.items.find(item => item.product_id === product.id);
  if (item) {
    item.quantity++;
  } else {
    newOrder.value.items.push({ product_id: product.id, quantity: 1 });
  }
}

function decrementNewQty(product) {
  const item = newOrder.value.items.find(item => item.product_id === product.id);
  if (item && item.quantity > 1) {
    item.quantity--;
  } else if (item) {
    newOrder.value.items = newOrder.value.items.filter(i => i.product_id !== product.id);
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

function handleSubmitOrder() {
  showSubmitOrderModal.value = true;
}

function cancelSubmitOrder() {
  showSubmitOrderModal.value = false;
}

function confirmSubmitOrder() {
  showSubmitOrderModal.value = false;
  submitOrder();
}

function printReceipt(orderId) {
  // Open kitchen receipt in new tab
  window.open(`/orders/${orderId}/kitchen-receipt`, '_blank');
  
  // Open counter receipt in new tab
  window.open(`/orders/${orderId}/receipt`, '_blank');
}

function closePrintModal() {
  showPrintModal.value = false;
  lastOrderId.value = null;
}

function printNow() {
  if (lastOrderId.value) {
    printReceipt(lastOrderId.value);
  }
  closePrintModal();
}

function quickLogout() {
  isAuthenticated.value = false;
  employeeId.value = '';
  employeePassword.value = '';
  employeeName.value = '';
  isAdmin.value = false;
}

async function verifyEmployee() {
  authError.value = '';
  try {
    const res = await axios.post('/api/employee/verify', {
      identifier: employeeId.value,
      password: employeePassword.value
    });
    if (res.data.success) {
      isAuthenticated.value = true;
      employeeName.value = res.data.name;
      isAdmin.value = res.data.is_admin;
    } else {
      authError.value = 'Invalid credentials';
    }
  } catch (e) {
    authError.value = 'Invalid credentials';
  }
}

onMounted(() => {
  fetchProducts();
  fetchTables();
  fetchOrders();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

:root {
  --bg-main: #6b3f1d;
  --bg-card: #8d5524;
  --bg-panel: #a9713d;
  --text-main: #ffd28f;
  --text-secondary: #fff2d8;
  --btn-edit: #3b82f6;
  --btn-delete: #ef4444;
  --btn-bill: #22c55e;
  --btn-bg: #a9713d;
  --btn-text: #fff2d8;
  --border-radius: 18px;
  --shadow: 0 2px 12px rgba(0,0,0,0.10);
}

.pos-app {
  font-family: 'Montserrat', Arial, sans-serif;
  background: var(--bg-main);
  min-height: 100vh;
  padding: 2rem 0;
  color: var(--text-main);
}

h2, .card-title {
  color: var(--text-main);
  font-weight: 700;
  letter-spacing: 1px;
}

.row.pos-main-row {
  display: flex;
  flex-wrap: nowrap;
  gap: 2rem;
  justify-content: center;
}

.col-md-4 {
  flex: 1 1 0;
  max-width: 33.3333%;
  min-width: 320px;
  display: flex;
  flex-direction: column;
}

.card {
  background: var(--bg-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  border: none;
}

.card-title {
  font-size: 1.6rem;
  margin-bottom: 1.2rem;
}

.product-list-scroll {
  max-height: 60vh;
  overflow-y: auto;
  padding-right: 4px;
}

.product-card {
  background: var(--bg-panel);
  border-radius: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  transition: box-shadow 0.2s, background 0.2s;
  cursor: pointer;
  min-height: 64px;
  align-items: center;
  display: flex;
  gap: 1rem;
}
.product-card:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
  background: #b07d4a;
}
.product-img {
  width: 56px;
  height: 56px;
  object-fit: cover;
  border-radius: 12px;
  background: #f8f9fa;
  border: 2px solid #c68642;
}
.btn-circle {
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  background: var(--btn-bg);
  color: var(--btn-text);
  border: none;
  font-size: 1.2rem;
}
.btn-circle:hover {
  background: #c68642;
}
.added-flash {
  animation: flash 0.4s;
}
@keyframes flash {
  0% { background: #d1e7dd; }
  100% { background: var(--bg-panel); }
}
.order-cart-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 420px;
  max-height: 90vh;
}
.order-cart-body {
  flex: 1 1 auto;
  min-height: 0;
  display: flex;
  flex-direction: column;
}
.order-cart-footer {
  margin-top: auto;
  background: var(--bg-card) !important;
  border-top: none !important;
  padding-top: 0.5rem;
  padding-bottom: 1.2rem;
}
.cart-list.cart-list-flex {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}
.cart-item {
  background: var(--bg-panel);
  border-radius: 10px;
  padding: 10px;
  align-items: center;
  display: flex;
  gap: 1rem;
}
.cart-img {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 8px;
  background: #fff;
  border: 2px solid #c68642;
}
.loading-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(107,63,29,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.order-list .order-item {
  background: var(--bg-panel);
  border-radius: 14px;
  padding: 1rem 1.2rem;
  margin-bottom: 1.2rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  color: var(--text-secondary);
}
.order-list .order-item span {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-main);
}
.order-list .order-item ul {
  margin: 0.5rem 0 0 0.5rem;
  padding: 0;
  color: var(--text-secondary);
}
.order-list .order-item .btn {
  border-radius: 12px;
  font-size: 1.15rem;
  font-weight: 700;
  margin-right: 0.7rem;
  min-width: 90px;
  padding: 0.5rem 1.1rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: transform 0.08s;
}
.order-list .order-item .btn:active {
  transform: scale(0.97);
}
.order-list .order-item .btn i {
  font-size: 1.2em;
  font-weight: bold;
}
.order-list .order-item .btn:last-child {
  margin-right: 0;
}
.order-list .order-item .mt-2 {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.form-control, .form-select {
  background: #7a4c24;
  color: var(--text-main);
  border: none;
  border-radius: 10px;
  font-size: 1.1rem;
  box-shadow: none;
}
.form-control:focus, .form-select:focus {
  background: #a9713d;
  color: var(--text-main);
  outline: none;
  box-shadow: 0 0 0 2px #ffd28f44;
}
.alert {
  border-radius: 10px;
  font-size: 1.1rem;
  font-weight: 600;
  background: #a9713d;
  color: #fff2d8;
  border: none;
}
.btn-close {
  filter: invert(1);
}
.modal-content {
  background: var(--bg-card);
  color: var(--text-main);
  border-radius: 16px;
}
.modal-header, .modal-footer {
  border: none;
}
.modal-title {
  color: var(--text-main);
  font-weight: 700;
}
@media (max-width: 900px) {
  .row.pos-main-row {
    flex-direction: column;
    flex-wrap: wrap;
    gap: 1.2rem;
  }
  .col-md-4 {
    max-width: 100%;
    flex: 1 1 100%;
    min-width: 0;
  }
}
.quick-lock-bar {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  border: 1px solid #e0e0e0;
  font-size: 1.05em;
}
.modal-backdrop-custom {
  position: fixed;
  z-index: 2000;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.25);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style> 