<template>
  <div class="container-fluid px-3">
    <!-- Notification Area -->
    <div v-if="notification" :class="['alert', notification.type === 'success' ? 'alert-success' : 'alert-danger', 'mb-4']">
      {{ notification.message }}
    </div>
    <!-- Loading Spinner -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <div class="row g-4 flex-wrap">

      <!-- Menu Panel -->
      <div class="col-lg-4 col-md-6 d-flex flex-column" style="min-height: 80vh;">
        <div class="card flex-grow-1 d-flex flex-column overflow-hidden shadow-sm">
          <div class="card-body overflow-auto">
            <h4 class="text-lg font-semibold mb-4">Menu</h4>
            <div class="mb-4">
              <label for="search-products" class="form-label">Search products</label>
              <input id="search-products" name="search-products" v-model="search" class="form-control form-control-lg" placeholder="Search products..." autocomplete="off" />
            </div>
            <div>
              <transition-group name="fade" tag="div">
                <div
                  v-for="product in filteredProducts"
                  :key="product.id"
                  class="product-card d-flex align-items-center p-3 mb-3 rounded shadow-sm"
                  :class="{ 'added-flash': flashProductId === product.id }"
                >
                  <img v-if="product.image" :src="`/storage/${product.image}`" class="product-img me-3 rounded" :alt="product.name" />
                  <div class="flex-grow-1">
                    <div class="fw-bold mb-1">{{ product.name }}</div>
                    <div class="text-muted">Rs. {{ product.price }}</div>
                  </div>
                  <button class="btn btn-success rounded-circle" @click="addToCart(product)" :aria-label="`Add ${product.name} to cart`">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </transition-group>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Cart Panel -->
      <div class="col-lg-4 col-md-6 d-flex flex-column" style="min-height: 80vh;">
        <div class="card flex-grow-1 d-flex flex-column overflow-hidden shadow-sm">
          <div class="card-body overflow-auto">
            <h4 class="text-lg font-semibold mb-4">Order Cart</h4>

            <div v-if="cart.length === 0" class="text-muted text-center my-5">
              <i class="fas fa-shopping-cart fa-3x mb-3"></i>
              <p>Cart is empty</p>
            </div>

            <transition-group name="cart-fade" tag="ul" class="list-unstyled">
              <li
                v-for="item in cart"
                :key="item.product.id"
                class="cart-item d-flex align-items-center p-3 mb-3 rounded shadow-sm"
              >
                <img v-if="item.product.image" :src="`/storage/${item.product.image}`" class="cart-img me-3 rounded" :alt="item.product.name" />
                <div class="flex-grow-1">
                  <div class="fw-bold mb-1">{{ item.product.name }}</div>
                  <div class="text-muted small">Rs. {{ item.product.price }} x {{ item.quantity }}</div>
                </div>
                <div class="d-flex align-items-center ms-3">
                  <label :for="`cart-qty-${item.product.id}`" class="visually-hidden">Quantity for {{ item.product.name }}</label>
                  <button class="btn btn-outline-secondary btn-sm" @click="decrementQty(item.product.id)" :aria-label="`Decrease quantity of ${item.product.name}`">-</button>
                  <input :id="`cart-qty-${item.product.id}`" :name="`cart-qty-${item.product.id}`" type="number" class="form-control form-control-sm mx-2" :value="item.quantity" readonly style="width: 48px; text-align: center;" aria-live="polite" />
                  <button class="btn btn-outline-secondary btn-sm" @click="incrementQty(item.product.id)" :aria-label="`Increase quantity of ${item.product.name}`">+</button>
                </div>
                <span class="ms-3 fw-bold">Rs. {{ item.product.price * item.quantity }}</span>
                <button class="btn btn-outline-danger btn-sm ms-3" @click="removeFromCart(item.product.id)" :aria-label="`Remove ${item.product.name} from cart`">
                  <i class="fas fa-trash"></i>
                </button>
              </li>
            </transition-group>

            <div class="cart-total mt-4 p-3 bg-light rounded">
              <div class="fw-bold fs-5">Total: Rs. {{ cartTotal }}</div>
            </div>
          </div>

          <div class="card-footer bg-white border-top-0">
            <div class="mb-4">
              <label for="order-type" class="form-label">Order Type</label>
              <select id="order-type" name="order-type" v-model="orderType" class="form-select form-select-lg mb-3" autocomplete="off">
                <option value="dine-in">Dine-In</option>
                <option value="takeaway">Takeaway</option>
                <option value="online">Online</option>
              </select>
              <div v-if="orderType === 'dine-in'">
                <label for="select-table" class="form-label">Select Table:</label>
                <select id="select-table" name="select-table" v-model="selectedTable" class="form-select form-select-lg" autocomplete="off">
                  <option v-for="table in tables" :key="table.id" :value="table.id">
                    {{ table.name }} ({{ table.status }})
                  </option>
                </select>
              </div>
            </div>
            <button class="btn btn-success btn-lg w-100" @click="handleSubmitOrder" :disabled="cart.length === 0">
              Submit Order
            </button>
          </div>
        </div>
      </div>

      <!-- Open Orders Panel -->
      <div class="col-lg-4 col-md-12 d-flex flex-column" style="min-height: 80vh;">
        <div class="card flex-grow-1 d-flex flex-column overflow-hidden shadow-sm">
          <div class="card-body overflow-auto">
            <h4 class="text-lg font-semibold mb-4">Open Orders</h4>
            <div v-for="order in openOrders" :key="order.id" class="order-item p-3 mb-3 rounded shadow-sm">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold">
                  Order #{{ order.id }}
                  <span v-if="order.type === 'dine-in' && order.table"> - Table: {{ order.table.name }}</span>
                  <span
                    class="ms-2 badge"
                    :class="{
                      'bg-primary': order.type === 'dine-in',
                      'bg-success': order.type === 'takeaway',
                      'bg-info': order.type === 'online'
                    }"
                  >{{ order.type }}</span>
                </span>
              </div>
              <ul class="list-unstyled mb-3">
                <li
                  v-for="item in order.items"
                  :key="item.id"
                  class="d-flex justify-content-between align-items-center py-1"
                >
                  <span>{{ item.item_name }}</span>
                  <span class="text-muted">x{{ item.quantity }}</span>
                </li>
              </ul>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary" @click="editOrder(order)">
                  <i class="fas fa-edit me-1"></i>Edit
                </button>
                <button class="btn btn-sm btn-danger" @click="deleteOrder(order)">
                  <i class="fas fa-trash me-1"></i>Delete
                </button>
                <button class="btn btn-sm btn-success" @click="addOrder">
                  <i class="fas fa-plus me-1"></i>Add
                </button>
                <button v-if="order.status === 'completed'" class="btn btn-sm btn-dark" @click="printReceipt(order.id)">
                  <i class="fas fa-print me-1"></i>Print
                </button>
              </div>
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

const formattedTime = today.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

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
  cart.value = cart.value.filter(i => i.product.id !== productId);
}

async function fetchProducts() {
  try {
    loading.value = true;
    const res = await axios.get('/api/pos/products');
    products.value = res.data;
  } catch (e) {
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
    const res = await axios.get('/api/pos/orders');
    orders.value = res.data;
  } catch (e) {
    showNotification('Failed to load orders', 'danger');
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
      items: cart.value.map(item => ({ product_id: item.product.id, quantity: item.quantity })),
    };
    await axios.post('/api/pos/orders', payload);
    showNotification('Order submitted!', 'success');
    cart.value = [];
    fetchOrders();
    if (orderType.value === 'dine-in') fetchTables();
  } catch (e) {
    showNotification('Failed to submit order', 'danger');
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
.pos-container {
  padding: 1rem;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.pos-header {
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
}

.date-time-display {
  font-size: 0.9rem;
  color: #6c757d;
}

.product-list-scroll {
  max-height: calc(100vh - 300px);
  overflow-y: auto;
  padding-right: 0.5rem;
}

.product-list-scroll::-webkit-scrollbar {
  width: 6px;
}

.product-list-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.product-list-scroll::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.product-list-scroll::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.product-card {
  background: white;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-img {
  width: 60px;
  height: 60px;
  object-fit: cover;
}

.cart-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
}

.cart-list {
  max-height: calc(100vh - 400px);
  overflow-y: auto;
  padding-right: 0.5rem;
}

.cart-list::-webkit-scrollbar {
  width: 6px;
}

.cart-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.cart-list::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.cart-list::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.cart-item {
  background: white;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}

.cart-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cart-total {
  background-color: #f8f9fa;
  border-radius: 0.5rem;
}

.order-list {
  max-height: calc(100vh - 200px);
  overflow-y: auto;
  padding-right: 0.5rem;
}

.order-list::-webkit-scrollbar {
  width: 6px;
}

.order-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.order-list::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.order-list::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.order-item {
  background: white;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}

.order-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5) !important;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000 !important;
}

.modal.fade.show {
  display: flex !important;
  align-items: center;
  justify-content: center;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 2100 !important;
}

.modal-dialog {
  margin: 0 auto !important;
  max-width: 500px;
  width: 100%;
}

.modal-content {
  border-radius: 0.5rem;
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.cart-fade-enter-active,
.cart-fade-leave-active {
  transition: all 0.3s ease;
}

.cart-fade-enter-from,
.cart-fade-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.added-flash {
  animation: flash 0.5s ease;
}

@keyframes flash {
  0% {
    background-color: #fff;
  }
  50% {
    background-color: #d4edda;
  }
  100% {
    background-color: #fff;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .pos-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .date-time-display {
    justify-content: center;
  }

  .product-list-scroll,
  .cart-list,
  .order-list {
    max-height: 400px;
  }
}

/* Button styles */
.btn-circle {
  width: 32px;
  height: 32px;
  padding: 0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  color: white;
}

/* Table styles */
.table {
  margin-bottom: 0;
}

.table th {
  border-top: none;
  background-color: #f8f9fa;
  font-weight: 600;
}

.table td {
  vertical-align: middle;
}

/* Form control styles */
.form-control, .form-select {
  background: #fff !important;
  color: #212529 !important;
  border: 1px solid #ced4da !important;
  border-radius: 0.375rem !important;
  font-size: 1rem;
  box-shadow: none;
}

.form-control:focus, .form-select:focus {
  background: #fff !important;
  color: #212529 !important;
  border-color: #80bdff !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}

/* Badge styles */
.badge {
  padding: 0.5em 0.75em;
  font-weight: 500;
}

/* Card styles */
.card {
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
  background-color: transparent;
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

/* Modal styles */
.modal-content {
  border: none;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  border-bottom: 1px solid #e9ecef;
}

.modal-footer {
  border-top: 1px solid #e9ecef;
}
</style> 