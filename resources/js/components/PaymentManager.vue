<template>
  <div class="payment-manager">
    <div v-if="isDayClosed" class="day-closed-overlay">
      <div class="day-closed-modal">
        <h4>Day Closed</h4>
        <p>The day's account has been closed.</p>
        <button class="btn btn-primary" @click="startNewDay">Start New Day</button>
      </div>
    </div>
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
    <div v-if="isAuthenticated && !isDayClosed">
      <div class="quick-lock-bar d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center">
          <h2 class="mb-0 me-3">Payment Manager</h2>
          <div class="date-time-display">
            <div class="date">{{ formattedDate }}</div>
            <div class="time">{{ formattedTime }}</div>
          </div>
        </div>
        <div>
          <span>Logged in as: <b>{{ employeeName }}</b> <span v-if="isAdmin" class="badge bg-primary ms-2">Admin</span></span>
          <button class="btn btn-outline-danger btn-sm ms-2" @click="quickLogout">
            <i class="fas fa-lock"></i> Lock
          </button>
          <button class="btn btn-outline-primary btn-sm ms-2" @click="openCloseDayModal">
            <i class="fas fa-calendar-check"></i> Close Day
          </button>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <!-- Unpaid Shop Orders Table -->
          <div class="card mb-4">
            <div class="card-header">
              <h4>Unpaid Shop Orders</h4>
            </div>
            <div class="card-body order-list">
              <div v-if="loading" class="text-center my-4">
                <div class="spinner-border text-primary" role="status"></div>
              </div>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Table</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in unpaidShopOrders" :key="order.id">
                    <td>{{ order.order_number }}</td>
                    <td>{{ order.table ? order.table.name : '-' }}</td>
                    <td>{{ order.type }}</td>
                    <td>Rs. {{ order.grand_total }}</td>
                    <td>
                      <button class="btn btn-primary btn-sm" @click="selectOrder(order)">Pay</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="unpaidShopOrders.length === 0" class="text-center text-muted my-4">
                No unpaid shop orders
              </div>
            </div>
          </div>
          <!-- Unpaid Online Orders Table -->
          <div class="card mb-4">
            <div class="card-header">
              <h4>Unpaid Online Orders</h4>
            </div>
            <div class="card-body order-list">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Total</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in unpaidOnlineOrders" :key="order.id">
                    <td>{{ order.order_number }}</td>
                    <td>Rs. {{ order.grand_total }}</td>
                    <td>
                      <button class="btn btn-primary btn-sm" @click="selectOrder(order)">Pay</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="unpaidOnlineOrders.length === 0" class="text-center text-muted my-4">
                No unpaid online orders
              </div>
            </div>
          </div>
          <!-- Paid Orders Table -->
          <div class="card">
            <div class="card-header">
              <h4>Paid Orders</h4>
            </div>
            <div class="card-body order-list">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Table</th>
                    <th>Type</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in paidOrders" :key="order.id">
                    <td>{{ order.order_number }}</td>
                    <td>{{ order.table ? order.table.name : '-' }}</td>
                    <td>{{ order.type }}</td>
                    <td>Rs. {{ order.grand_total }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="paidOrders.length === 0" class="text-center text-muted my-4">
                No paid orders
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="card">
            <div class="card-header">
              <h4>Payment</h4>
            </div>
            <div class="card-body">
              <div v-if="selectedOrder">
                <div class="mb-2"><b>Order #{{ selectedOrder.order_number }}</b></div>
                <table class="table table-sm mb-2">
                  <thead>
                    <tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in selectedOrder.items" :key="item.id">
                      <td>{{ item.product ? item.product.name : item.item_name }}</td>
                      <td>{{ item.quantity }}</td>
                      <td>{{ item.price }}</td>
                      <td>{{ item.subtotal }}</td>
                    </tr>
                  </tbody>
                </table>
                <div class="mb-2">Total: <b>Rs. {{ selectedOrder.grand_total }}</b></div>
                <div class="mb-2">
                  <label>Payment Method</label>
                  <select v-model="paymentMethod" class="form-select w-auto d-inline-block ms-2">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="qr">QR</option>
                  </select>
                </div>
                <div v-if="paymentMethod === 'cash'" class="mb-2">
                  <label>Amount Received</label>
                  <input type="number" v-model.number="amountReceived" :min="selectedOrder.grand_total" class="form-control w-auto d-inline-block ms-2" />
                  <span class="ms-2">Change: <b>{{ changeAmount }}</b></span>
                  <div class="row mt-3 g-2">
                    <div class="col-12 col-md-6">
                      <div class="card p-2 h-100">
                        <h6 class="mb-2">Received Notes/Coins</h6>
                        <div class="d-flex flex-wrap gap-2">
                          <div v-for="denom in denominations" :key="'recv-' + denom" class="flex-fill" style="min-width: 90px;">
                            <label class="form-label small">Rs. {{ denom }}</label>
                            <input type="number" min="0" v-model.number="receivedNotes[denom]" class="form-control form-control-sm" />
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <div class="card p-2 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                          <h6 class="mb-0">Change Given</h6>
                          <button type="button" class="btn btn-outline-primary btn-sm" @click="canEditChange = !canEditChange">
                            <i :class="canEditChange ? 'fas fa-lock' : 'fas fa-edit'"></i>
                            {{ canEditChange ? 'Lock' : 'Edit' }}
                          </button>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                          <div v-for="denom in denominations" :key="'chg-' + denom" class="flex-fill" style="min-width: 90px;">
                            <label class="form-label small">Rs. {{ denom }}</label>
                            <input type="number" min="0" v-model.number="changeNotes[denom]" class="form-control form-control-sm" :readonly="!canEditChange" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <button class="btn btn-success mt-2" :disabled="!canPay" @click="processPayment">Mark as Paid</button>
              </div>
              <div v-else class="text-muted text-center">Select an order to process payment.</div>
            </div>
          </div>
        </div>
      </div>
      <!-- Floating Cash Drawer Panel -->
      <div class="cash-drawer-floating" :class="{ open: showDrawer }">
        <div class="cash-drawer-header d-flex align-items-center justify-content-between" @click="showDrawer = !showDrawer">
          <span><i class="fas fa-cash-register me-2"></i>Cash Drawer</span>
          <div>
            <span v-if="cashDrawerAlerts.low_change" class="badge bg-warning me-1">Low Change</span>
            <span v-if="cashDrawerAlerts.excess_cash" class="badge bg-danger">Excess Cash</span>
          </div>
          <button class="btn btn-sm btn-light ms-2" @click.stop="showDrawer = !showDrawer">
            <i :class="showDrawer ? 'fas fa-chevron-down' : 'fas fa-chevron-up'"></i>
          </button>
        </div>
        <transition name="fade">
          <div v-if="showDrawer" class="cash-drawer-body">
            <table class="table table-bordered table-sm text-center align-middle mb-2">
              <thead class="table-light">
                <tr>
                  <th>Denomination</th>
                  <th>Starting</th>
                  <th>Current</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="denom in denominations" :key="denom">
                  <td><span class="badge bg-secondary fs-6">Rs. {{ denom }}</span></td>
                  <td>{{ startingCash[denom] }}</td>
                  <td>
                    <button @click.stop="secureDecrementDenomination(denom)" class="btn btn-sm btn-outline-danger me-1">-</button>
                    {{ cashDrawer[denom] }}
                    <button @click.stop="secureIncrementDenomination(denom)" class="btn btn-sm btn-outline-success ms-1">+</button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="fw-bold text-end">Total in Drawer: <span class="text-success">Rs. {{ totalCashDrawer() }}</span></div>
          </div>
        </transition>
      </div>
    </div>
    <!-- Close Day Modal -->
    <div v-if="showCloseDayModal" class="modal-backdrop-custom">
      <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.4);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Close Day</h5>
            </div>
            <div class="modal-body">
              <h6>Cash Drawer Summary</h6>
              <table class="table table-bordered table-sm text-center align-middle mb-2">
                <thead class="table-light">
                  <tr>
                    <th>Denomination</th>
                    <th>Count</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="denom in denominations" :key="denom">
                    <td>Rs. {{ denom }}</td>
                    <td>{{ cashDrawer[denom] }}</td>
                    <td>Rs. {{ cashDrawer[denom] * denom }}</td>
                  </tr>
                </tbody>
              </table>
              <div class="fw-bold text-end mb-2">Total: <span class="text-success">Rs. {{ totalCashDrawer() }}</span></div>
              <div class="mb-3">
                <label class="form-label">Closing Notes (optional)</label>
                <textarea v-model="closeDayNotes" class="form-control" rows="2" placeholder="Enter any notes..."></textarea>
              </div>
              <div v-if="hasUnpaidOrders" class="alert alert-warning mb-2">
                You must settle all unpaid orders before closing the day.
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="closeCloseDayModal">Cancel</button>
              <button class="btn btn-primary" :disabled="hasUnpaidOrders" @click="confirmCloseDay">Confirm & Close Day</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Password Modal -->
    <div v-if="showPasswordModal" class="modal-backdrop-custom">
      <div class="modal d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Enter Password to Adjust Cash Drawer</h5>
            </div>
            <div class="modal-body">
              <input v-model="passwordInput" type="password" class="form-control" placeholder="Password" />
              <div v-if="passwordError" class="text-danger mt-2">{{ passwordError }}</div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showPasswordModal = false">Cancel</button>
              <button class="btn btn-primary" @click="verifyAdjustmentPassword">Verify</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const today = new Date();
const formattedDate = today.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
const formattedTime = today.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

const orders = ref([]);
const loading = ref(false);
const selectedOrder = ref(null);
const paymentMethod = ref('cash');
const amountReceived = ref(0);

const denominations = [1000, 500, 100, 50, 20, 10, 5, 1];
const startingCash = {
  1000: 10,
  500: 10,
  100: 10,
  50: 10,
  20: 10,
  10: 10,
  5: 10,
  1: 10
};
const cashDrawer = ref({
  1000: 0,
  500: 0,
  100: 0,
  50: 0,
  20: 0,
  10: 0,
  5: 0,
  1: 0
});

const receivedNotes = ref({});
const changeNotes = ref({});
const canEditChange = ref(false);

const showDrawer = ref(false);

const isAuthenticated = ref(false);
const employeeId = ref('');
const employeePassword = ref('');
const authError = ref('');
const employeeName = ref('');
const isAdmin = ref(false);
const isCashier = ref(false);

const showCloseDayModal = ref(false);
const closeDayNotes = ref('');
const isDayClosed = ref(false);

const hasUnpaidOrders = computed(() => unpaidOrders.value.length > 0);

const showPasswordModal = ref(false);
const passwordInput = ref('');
const passwordError = ref('');
const allowAdjustmentUntil = ref(0);
let pendingAdjustment = null;

denominations.forEach(denom => {
  receivedNotes.value[denom] = 0;
  changeNotes.value[denom] = 0;
});

function resetReceivedAndChange() {
  denominations.forEach(denom => {
    receivedNotes.value[denom] = 0;
    changeNotes.value[denom] = 0;
  });
}

function totalCashDrawer() {
  return denominations.reduce((sum, denom) => sum + (cashDrawer.value[denom] * denom), 0);
}

function sumReceivedNotes() {
  return denominations.reduce((sum, denom) => sum + (receivedNotes.value[denom] * denom), 0);
}

const fetchOrders = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/pos/orders');
    console.log('Orders response:', res.data);
    orders.value = res.data.data || res.data;
  } finally {
    loading.value = false;
  }
};

const unpaidOrders = computed(() => orders.value.filter(
  o => o.payment_status === 'unpaid' || o.payment_status === 'pending'
));

const unpaidShopOrders = computed(() => unpaidOrders.value.filter(o => o.type === 'dine-in' || o.type === 'takeaway'));
const unpaidOnlineOrders = computed(() => unpaidOrders.value.filter(o => o.type === 'online'));

const paidOrders = computed(() => orders.value.filter(o => o.payment_status === 'paid'));

function selectOrder(order) {
  selectedOrder.value = order;
  paymentMethod.value = 'cash';
  amountReceived.value = order.grand_total;
}

const changeAmount = computed(() => {
  if (!selectedOrder.value || paymentMethod.value !== 'cash') return 0;
  return (amountReceived.value - selectedOrder.value.grand_total).toFixed(2);
});

const canPay = computed(() => {
  if (!selectedOrder.value) return false;
  if (selectedOrder.value.payment_status === 'paid') return false;
  if (paymentMethod.value === 'cash') {
    // Require received notes sum to exactly match amountReceived
    return (
      amountReceived.value >= selectedOrder.value.grand_total &&
      sumReceivedNotes() === amountReceived.value
    );
  }
  return true;
});

async function processPayment() {
  if (!selectedOrder.value) return;
  if (paymentMethod.value === 'cash' && sumReceivedNotes() !== amountReceived.value) {
    alert('The sum of received notes must exactly match the Amount Received.');
    return;
  }
  try {
    loading.value = true;
    await axios.post(`/orders/${selectedOrder.value.id}/pay`, {
      payment_method: paymentMethod.value,
      amount_received: amountReceived.value,
      paid_by: employeeId.value
    });
    updateCashDrawer();
    resetReceivedAndChange();
    await fetchOrders();
    selectedOrder.value = null;
    alert('Payment processed!');
  } finally {
    loading.value = false;
  }
}

function autoFillChangeNotes(change) {
  let remaining = Math.round(change);
  denominations.forEach(denom => {
    const count = Math.floor(remaining / denom);
    changeNotes.value[denom] = count;
    remaining -= count * denom;
  });
}

watch([amountReceived, selectedOrder, paymentMethod], ([newAmount, newOrder, newMethod]) => {
  if (!newOrder || newMethod !== 'cash') return;
  const change = newAmount - newOrder.grand_total;
  if (change > 0) {
    autoFillChangeNotes(change);
  } else {
    denominations.forEach(denom => changeNotes.value[denom] = 0);
  }
});

function quickLogout() {
  isAuthenticated.value = false;
  employeeId.value = '';
  employeePassword.value = '';
  employeeName.value = '';
  isAdmin.value = false;
  isCashier.value = false;
  localStorage.removeItem('paymentManagerAuth');
}

async function verifyEmployee() {
  try {
    const response = await axios.post('/api/employee/verify', {
      identifier: employeeId.value,
      password: employeePassword.value
    });
    
    if (response.data.success) {
      isAuthenticated.value = true;
      employeeName.value = response.data.name;
      isAdmin.value = response.data.is_admin;
      isCashier.value = response.data.is_cashier;
      authError.value = '';
      // Store auth state in localStorage
      localStorage.setItem('paymentManagerAuth', JSON.stringify({
        name: employeeName.value,
        isAdmin: isAdmin.value,
        isCashier: isCashier.value
      }));
    }
  } catch (error) {
    console.error('Auth error:', error);
    authError.value = error.response?.data?.message || 'Authentication failed';
  }
}

// Persist cash drawer in localStorage
function saveCashDrawer() {
  localStorage.setItem('cashDrawer', JSON.stringify(cashDrawer.value));
}

function loadCashDrawer() {
  const saved = localStorage.getItem('cashDrawer');
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      denominations.forEach(denom => {
        if (typeof parsed[denom] === 'number') {
          cashDrawer.value[denom] = parsed[denom];
        }
      });
    } catch {}
  }
}

function updateCashDrawer() {
  denominations.forEach(denom => {
    cashDrawer.value[denom] += receivedNotes.value[denom];
    cashDrawer.value[denom] -= changeNotes.value[denom];
  });
  saveCashDrawer();
}

function openCloseDayModal() {
  showCloseDayModal.value = true;
}
function closeCloseDayModal() {
  showCloseDayModal.value = false;
  closeDayNotes.value = '';
}
async function confirmCloseDay() {
  // Prepare summary
  const summary = {
    cash_drawer: { ...cashDrawer.value },
    total: totalCashDrawer(),
    notes: closeDayNotes.value,
    employee_id: employeeId.value,
    closed_at: new Date().toISOString(),
  };
  // Send to backend (dummy for now)
  try {
    await axios.post('/api/close-day', summary);
  } catch {}
  // Reset drawer
  denominations.forEach(denom => cashDrawer.value[denom] = 0);
  saveCashDrawer();
  isDayClosed.value = true;
  closeCloseDayModal();
}
function startNewDay() {
  isDayClosed.value = false;
}

// Cash drawer alert logic
const cashDrawerAlerts = computed(() => {
  // Prepare cashDrawer as {denom: amount}
  const drawer = {};
  denominations.forEach(denom => {
    drawer[denom] = cashDrawer.value[denom] || 0;
  });
  // Call backend helper via API or implement logic here
  // For now, hardcode thresholds to match backend config
  const smallDenoms = [1, 5, 10, 20, 50, 100];
  const largeDenoms = [500, 1000];
  const lowChangeThreshold = 500;
  const excessCashThreshold = 8000;
  const largeDenomThreshold = 7000;
  let smallTotal = 0, largeTotal = 0, overallTotal = 0;
  for (const denom of denominations) {
    const amount = drawer[denom] || 0;
    overallTotal += amount;
    if (smallDenoms.includes(denom)) smallTotal += amount;
    if (largeDenoms.includes(denom)) largeTotal += amount;
  }
  return {
    low_change: smallTotal < lowChangeThreshold,
    excess_cash: overallTotal > excessCashThreshold || largeTotal > largeDenomThreshold
  };
});

function canAdjust() {
  return Date.now() < allowAdjustmentUntil.value;
}

function requestAdjustmentAuth(action, denom) {
  pendingAdjustment = { action, denom };
  showPasswordModal.value = true;
  passwordInput.value = '';
  passwordError.value = '';
}

async function verifyAdjustmentPassword() {
  if (passwordInput.value === '333122') {
    allowAdjustmentUntil.value = Date.now() + 2 * 60 * 1000; // 2 minutes
    showPasswordModal.value = false;
    passwordInput.value = '';
    passwordError.value = '';
    // Perform the pending adjustment
    if (pendingAdjustment) {
      if (pendingAdjustment.action === 'inc') incrementDenomination(pendingAdjustment.denom);
      if (pendingAdjustment.action === 'dec') decrementDenomination(pendingAdjustment.denom);
      pendingAdjustment = null;
    }
  } else {
    passwordError.value = 'Incorrect password';
  }
}

function secureIncrementDenomination(denom) {
  if (!canAdjust()) {
    requestAdjustmentAuth('inc', denom);
    return;
  }
  incrementDenomination(denom);
}
function secureDecrementDenomination(denom) {
  if (!canAdjust()) {
    requestAdjustmentAuth('dec', denom);
    return;
  }
  decrementDenomination(denom);
}

function incrementDenomination(denom) {
  cashDrawer.value[denom]++;
  if (typeof saveCashDrawer === 'function') saveCashDrawer();
}
function decrementDenomination(denom) {
  if (cashDrawer.value[denom] > 0) {
    cashDrawer.value[denom]--;
    if (typeof saveCashDrawer === 'function') saveCashDrawer();
  }
}

onMounted(async () => {
  const savedAuth = localStorage.getItem('paymentManagerAuth');
  if (savedAuth) {
    const auth = JSON.parse(savedAuth);
    isAuthenticated.value = true;
    employeeName.value = auth.name;
    isAdmin.value = auth.isAdmin;
    isCashier.value = auth.isCashier;
  }
  loadCashDrawer();
  await fetchOrders();
  setInterval(fetchOrders, 60000); // Refresh every 60 seconds forever
});
</script>

<style scoped>
.payment-manager { padding: 20px; }
.order-list { max-height: 600px; overflow-y: auto; }
.table th, .table td { vertical-align: middle; }
/* Cash drawer and payment UI improvements */
.card-header.bg-primary {
  background: #007bff !important;
  color: #fff !important;
}
.badge.bg-secondary {
  background: #6c757d !important;
}
.form-label.small {
  font-size: 0.95em;
  margin-bottom: 2px;
}
.form-control-sm {
  font-size: 0.95em;
  padding: 0.25rem 0.5rem;
}
.btn-outline-primary.btn-sm {
  padding: 0.15rem 0.7rem;
  font-size: 0.95em;
}
/* Floating Cash Drawer Styles */
.cash-drawer-floating {
  position: fixed;
  left: 20px;
  bottom: 20px;
  z-index: 1050;
  min-width: 270px;
  max-width: 350px;
  background: #fff;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 2px 12px rgba(0,0,0,0.18);
  border: 1px solid #e0e0e0;
  transition: box-shadow 0.2s;
  overflow: hidden;
}
.cash-drawer-header {
  background: #007bff;
  color: #fff;
  padding: 0.7rem 1rem;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
}
.cash-drawer-header .btn {
  background: #fff;
  color: #007bff;
  border: none;
  font-size: 1.1em;
  padding: 0.1rem 0.5rem;
  border-radius: 6px;
  box-shadow: none;
}
.cash-drawer-body {
  padding: 1rem 1rem 0.5rem 1rem;
  background: #f8f9fa;
}
.cash-drawer-floating:not(.open) .cash-drawer-body {
  display: none;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
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
.quick-lock-bar {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  border: 1px solid #e0e0e0;
  font-size: 1.05em;
}
.day-closed-overlay {
  position: fixed;
  z-index: 3000;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
}
.day-closed-modal {
  background: #fff;
  border-radius: 12px;
  padding: 2rem 2.5rem;
  box-shadow: 0 2px 16px rgba(0,0,0,0.18);
  text-align: center;
}
.date-time-display {
  background: #f8f9fa;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  text-align: center;
  min-width: 200px;
  border: 1px solid #e0e0e0;
}
.date-time-display .date {
  font-size: 0.9rem;
  color: #6c757d;
  margin-bottom: 0.2rem;
}
.date-time-display .time {
  font-size: 1.2rem;
  font-weight: bold;
  color: #212529;
}
</style> 