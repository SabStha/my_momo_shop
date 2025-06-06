<template>
  <div class="payment-manager">
    <div v-if="isDayClosed" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h4 class="text-xl font-semibold mb-4">Day Closed</h4>
        <p class="text-gray-600 mb-4">The day's account has been closed.</p>
        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" @click="startNewDay">Start New Day</button>
      </div>
    </div>
    <!-- Employee Auth Modal -->
    <div v-if="!isAuthenticated" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h5 class="text-xl font-semibold mb-4">Employee Authentication</h5>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID or Email</label>
            <input 
              v-model="employeeId" 
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
              placeholder="Enter your ID or email"
              @keyup.enter="verifyEmployee"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input 
              v-model="employeePassword" 
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
              placeholder="Enter your password"
              @keyup.enter="verifyEmployee"
            />
          </div>
          <div v-if="authError" class="text-red-600 text-sm">{{ authError }}</div>
        </div>
        <button 
          class="w-full mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          @click="verifyEmployee"
        >
          Login
        </button>
      </div>
    </div>
    <div v-if="isAuthenticated && !isDayClosed">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <h2 class="text-2xl font-bold mr-4">Payment Manager</h2>
          <div class="text-sm text-gray-600">
            <div>{{ formattedDate }}</div>
            <div>{{ formattedTime }}</div>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span>Logged in as: <b>{{ employeeName }}</b> <span v-if="isAdmin" class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Admin</span></span>
          <button class="px-3 py-1 border border-red-500 text-red-500 rounded hover:bg-red-50" @click="quickLogout">
            <i class="fas fa-lock"></i> Lock
          </button>
          <button class="px-3 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-50" @click="openCloseDayModal">
            <i class="fas fa-calendar-check"></i> Close Day
          </button>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div class="md:col-span-5">
          <!-- Unpaid Shop Orders Table -->
          <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b">
              <h4 class="text-lg font-semibold">Unpaid Shop Orders</h4>
            </div>
            <div class="p-6 max-h-[600px] overflow-y-auto">
              <div v-if="loading" class="flex justify-center my-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="order in unpaidShopOrders" :key="order.id">
                    <td class="px-4 py-2">{{ order.order_number }}</td>
                    <td class="px-4 py-2">{{ order.table ? order.table.name : '-' }}</td>
                    <td class="px-4 py-2">{{ order.type }}</td>
                    <td class="px-4 py-2">₦{{ order.grand_total }}</td>
                    <td class="px-4 py-2">
                      <button class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" @click="selectOrder(order)">Pay</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="unpaidShopOrders.length === 0" class="text-center text-gray-500 my-4">
                No unpaid shop orders
              </div>
            </div>
          </div>
          <!-- Unpaid Online Orders Table -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
              <h4 class="text-lg font-semibold">Unpaid Online Orders</h4>
            </div>
            <div class="p-6 max-h-[600px] overflow-y-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="order in unpaidOnlineOrders" :key="order.id">
                    <td class="px-4 py-2">{{ order.order_number }}</td>
                    <td class="px-4 py-2">₦{{ order.grand_total }}</td>
                    <td class="px-4 py-2">
                      <button class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" @click="selectOrder(order)">Pay</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="unpaidOnlineOrders.length === 0" class="text-center text-gray-500 my-4">
                No unpaid online orders
              </div>
            </div>
          </div>
          <!-- Paid Orders Table -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
              <h4 class="text-lg font-semibold">Paid Orders</h4>
            </div>
            <div class="p-6 max-h-[600px] overflow-y-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="order in paidOrders" :key="order.id">
                    <td class="px-4 py-2">{{ order.order_number }}</td>
                    <td class="px-4 py-2">{{ order.table ? order.table.name : '-' }}</td>
                    <td class="px-4 py-2">{{ order.type }}</td>
                    <td class="px-4 py-2">₦{{ order.grand_total }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="paidOrders.length === 0" class="text-center text-gray-500 my-4">
                No paid orders
              </div>
            </div>
          </div>
        </div>
        <div class="md:col-span-7">
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
              <h4 class="text-lg font-semibold">Payment</h4>
            </div>
            <div class="p-6">
              <div v-if="selectedOrder">
                <div class="mb-4"><b>Order #{{ selectedOrder.order_number }}</b></div>
                <table class="min-w-full divide-y divide-gray-200 mb-4">
                  <thead>
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="item in selectedOrder.items" :key="item.id">
                      <td class="px-4 py-2">{{ item.product ? item.product.name : item.item_name }}</td>
                      <td class="px-4 py-2">{{ item.quantity }}</td>
                      <td class="px-4 py-2">₦{{ item.price }}</td>
                      <td class="px-4 py-2">₦{{ item.subtotal }}</td>
                    </tr>
                  </tbody>
                </table>
                <div class="mb-4">Total: <b>₦{{ selectedOrder.grand_total }}</b></div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                  <select v-model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="qr">QR</option>
                  </select>
                </div>
                <div v-if="paymentMethod === 'cash'" class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Amount Received</label>
                  <input type="number" v-model.number="amountReceived" :min="selectedOrder.grand_total" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  <span class="ml-2 text-gray-500 text-sm">Change: <b>₦{{ changeAmount }}</b></span>
                  <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Received Notes/Coins</label>
                    <div class="flex flex-wrap gap-2">
                      <div v-for="denom in denominations" :key="'recv-' + denom" class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ denom }}</label>
                        <input type="number" min="0" v-model.number="receivedNotes[denom]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </div>
                        </div>
                      </div>
                    </div>
                <div v-if="paymentMethod === 'cash'" class="mt-4">
                  <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Change Given</label>
                    <button type="button" class="px-3 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-50" @click="canEditChange = !canEditChange">
                            <i :class="canEditChange ? 'fas fa-lock' : 'fas fa-edit'"></i>
                            {{ canEditChange ? 'Lock' : 'Edit' }}
                          </button>
                        </div>
                  <div class="mt-2">
                    <div class="flex flex-wrap gap-2">
                      <div v-for="denom in denominations" :key="'chg-' + denom" class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ denom }}</label>
                        <input type="number" min="0" v-model.number="changeNotes[denom]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" :readonly="!canEditChange" />
                          </div>
                        </div>
                      </div>
                    </div>
                <button class="w-full mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="!canPay" @click="processPayment">Mark as Paid</button>
                  </div>
              <div v-else class="text-gray-500 text-center">Select an order to process payment.</div>
                </div>
          </div>
        </div>
      </div>
      <!-- Floating Cash Drawer Panel -->
      <div class="cash-drawer-floating" :class="{ open: showDrawer }">
        <div class="cash-drawer-header d-flex align-items-center justify-content-between" @click="showDrawer = !showDrawer">
          <div class="d-flex align-items-center">
            <i class="fas fa-cash-register me-2"></i>
            <span>Cash Drawer</span>
            <span class="ms-2 total-amount">₦{{ totalCashDrawer() }}</span>
          </div>
          <button class="btn btn-sm btn-light ms-2" @click.stop="showDrawer = !showDrawer">
            <i :class="showDrawer ? 'fas fa-chevron-down' : 'fas fa-chevron-up'"></i>
          </button>
        </div>
        <transition name="slide">
          <div v-if="showDrawer" class="cash-drawer-body">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Denomination</th>
                  <th>Starting</th>
                  <th>Current</th>
                  <th class="text-center">Adjust</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="denom in denominations" :key="denom" 
                    :class="{
                      'table-warning': cashDrawerAlerts.low_denominations.includes(denom),
                      'table-danger': cashDrawerAlerts.excess_denominations.includes(denom)
                    }">
                  <td>
                    <div class="d-flex align-items-center">
                      <span class="denomination-value">₦{{ denom }}</span>
                      <span v-if="cashDrawerAlerts.low_denominations.includes(denom)" 
                            class="badge bg-warning ms-2">
                        <i class="fas fa-exclamation-circle me-1"></i>Low
                      </span>
                      <span v-if="cashDrawerAlerts.excess_denominations.includes(denom)" 
                            class="badge bg-danger ms-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>High
                      </span>
                    </div>
                  </td>
                  <td>{{ startingCash[denom] }}</td>
                  <td>{{ cashDrawer[denom] }}</td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <button @click.stop="secureDecrementDenomination(denom)" 
                              class="btn btn-outline-danger" 
                              :disabled="cashDrawer[denom] <= 0">
                        <i class="fas fa-minus"></i>
                      </button>
                      <button @click.stop="secureIncrementDenomination(denom)" 
                              class="btn btn-outline-success">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="table-light">
                  <td><strong>Total</strong></td>
                  <td><strong>₦{{ denominations.reduce((sum, denom) => sum + (startingCash[denom] * denom), 0) }}</strong></td>
                  <td><strong>₦{{ totalCashDrawer() }}</strong></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </transition>
      </div>
    </div>
    <!-- Close Day Modal -->
    <div v-if="showCloseDayModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h5 class="text-xl font-semibold mb-4">Close Day</h5>
        <div class="space-y-4">
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
                <td>₦{{ denom }}</td>
                    <td>{{ cashDrawer[denom] }}</td>
                <td>₦{{ cashDrawer[denom] * denom }}</td>
                  </tr>
                </tbody>
              </table>
          <div class="fw-bold text-end mb-2">Total: <span class="text-success">₦{{ totalCashDrawer() }}</span></div>
              <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Closing Notes (optional)</label>
            <textarea v-model="closeDayNotes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="2" placeholder="Enter any notes..."></textarea>
              </div>
              <div v-if="hasUnpaidOrders" class="alert alert-warning mb-2">
                You must settle all unpaid orders before closing the day.
              </div>
            </div>
        <div class="mt-4 space-x-2">
              <button class="btn btn-secondary" @click="closeCloseDayModal">Cancel</button>
              <button class="btn btn-primary" :disabled="hasUnpaidOrders" @click="confirmCloseDay">Confirm & Close Day</button>
        </div>
      </div>
    </div>
    <!-- Password Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h5 class="text-xl font-semibold mb-4">Enter Password to Adjust Cash Drawer</h5>
        <div class="space-y-4">
          <input v-model="passwordInput" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Password" />
          <div v-if="passwordError" class="text-red-600 text-sm">{{ passwordError }}</div>
            </div>
        <div class="mt-4 space-x-2">
              <button class="btn btn-secondary" @click="showPasswordModal = false">Cancel</button>
              <button class="btn btn-primary" @click="verifyAdjustmentPassword">Verify</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

// Configure axios defaults
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.withCredentials = true

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

async function fetchOrders() {
  loading.value = true;
  try {
    console.log('Fetching orders...');
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
    console.log('Auth headers:', axios.defaults.headers.common);

    const response = await axios.get('/api/orders');
    console.log('Orders response:', response.data);
    
    if (response.data.success && Array.isArray(response.data.orders)) {
      // Filter orders based on payment status
      unpaidShopOrders.value = response.data.orders.filter(order => 
        order.payment_status === 'unpaid' && order.type === 'shop'
      );
      
      unpaidOnlineOrders.value = response.data.orders.filter(order => 
        order.payment_status === 'unpaid' && order.type === 'online'
      );
      
      paidOrders.value = response.data.orders.filter(order => 
        order.payment_status === 'paid'
      );

      console.log('Filtered orders:', {
        unpaidShop: unpaidShopOrders.value,
        unpaidOnline: unpaidOnlineOrders.value,
        paid: paidOrders.value
      });
    } else {
      console.error('Unexpected API response structure:', response.data);
    }
  } catch (error) {
    console.error('Error fetching orders:', error);
    console.error('Error response:', error.response?.data);
    console.error('Error status:', error.response?.status);
  } finally {
    loading.value = false;
  }
}

const unpaidOrders = computed(() => {
  if (!Array.isArray(orders.value)) return [];
  return orders.value.filter(
  o => o.payment_status === 'unpaid' || o.payment_status === 'pending'
  );
});

const unpaidShopOrders = ref([]);
const unpaidOnlineOrders = ref([]);
const paidOrders = ref([]);

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
    await axios.post(`/payment-manager/orders/${selectedOrder.value.id}/process-payment`, {
      payment_method: paymentMethod.value,
      amount_received: amountReceived.value,
      paid_by: employeeId.value
    });
    updateCashDrawer();
    resetReceivedAndChange();
    await fetchOrders();
    selectedOrder.value = null;
    alert('Payment processed!');
  } catch (error) {
    console.error('Payment error:', error);
    alert('Failed to process payment. Please try again.');
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
    console.log('Attempting to verify employee...');
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
    console.log('Request data:', {
      identifier: employeeId.value,
      password: employeePassword.value
    });

    const response = await axios.post('/api/employee/verify', {
      identifier: employeeId.value,
      password: employeePassword.value
    });
    
    console.log('Verification response:', response.data);
    
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

      // Fetch orders after successful authentication
      await fetchOrders();
    }
  } catch (error) {
    console.error('Auth error:', error);
    console.error('Error response:', error.response?.data);
    console.error('Error status:', error.response?.status);
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
  // Initialize cash drawer with starting amounts
  denominations.forEach(denom => {
    cashDrawer.value[denom] = startingCash[denom];
  });
  saveCashDrawer();
}

// Add new function to initialize drawer
function initializeCashDrawer() {
  // If drawer is empty, set to starting amounts
  const isEmpty = denominations.every(denom => !cashDrawer.value[denom]);
  if (isEmpty) {
    denominations.forEach(denom => {
      cashDrawer.value[denom] = startingCash[denom];
    });
    saveCashDrawer();
  }
}

// Cash drawer alert logic
const cashDrawerAlerts = computed(() => {
  // Prepare cashDrawer as {denom: amount}
  const drawer = {};
  denominations.forEach(denom => {
    drawer[denom] = cashDrawer.value[denom] || 0;
  });

  // Define thresholds for each denomination
  const denominationThresholds = {
    1: 10,    // Minimum 10 coins of Rs. 1
    5: 10,    // Minimum 10 coins of Rs. 5
    10: 10,   // Minimum 10 coins of Rs. 10
    20: 10,   // Minimum 10 notes of Rs. 20
    50: 10,   // Minimum 10 notes of Rs. 50
    100: 10,  // Minimum 10 notes of Rs. 100
    500: 5,   // Minimum 5 notes of Rs. 500
    1000: 5   // Minimum 5 notes of Rs. 1000
  };

  // Check each denomination
  const alerts = {
    low_denominations: [],
    excess_denominations: []
  };

  denominations.forEach(denom => {
    const amount = drawer[denom] || 0;
    if (amount < denominationThresholds[denom]) {
      alerts.low_denominations.push(denom);
  }
    // Consider excess if more than 3x the threshold
    if (amount > denominationThresholds[denom] * 3) {
      alerts.excess_denominations.push(denom);
    }
  });

  return alerts;
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
  console.log('Component mounted');
  console.log('Checking authentication state...');
  
  // Check if user is authenticated via Sanctum
  try {
    const response = await axios.get('/api/user');
    console.log('User authentication check:', response.data);
    
    if (response.data) {
      isAuthenticated.value = true;
      employeeName.value = response.data.name;
      isAdmin.value = response.data.roles?.includes('admin') || false;
      isCashier.value = response.data.roles?.includes('cashier') || false;
      
      // Store auth state in localStorage
      localStorage.setItem('paymentManagerAuth', JSON.stringify({
        name: employeeName.value,
        isAdmin: isAdmin.value,
        isCashier: isCashier.value
      }));

      // Fetch orders after successful authentication
      await fetchOrders();
    }
  } catch (error) {
    console.error('Auth check error:', error);
    console.error('Error response:', error.response?.data);
    console.error('Error status:', error.response?.status);
    
    // Check localStorage for saved auth state
    const savedAuth = localStorage.getItem('paymentManagerAuth');
    if (savedAuth) {
      const auth = JSON.parse(savedAuth);
      isAuthenticated.value = true;
      employeeName.value = auth.name;
      isAdmin.value = auth.isAdmin;
      isCashier.value = auth.isCashier;
    }
  }
  loadCashDrawer();
  initializeCashDrawer();
  fetchOrders();
  setInterval(fetchOrders, 60000); // Refresh every 60 seconds forever
});
</script>

<style scoped>
.payment-manager {
  @apply min-h-screen bg-gray-100;
}

/* Add any additional custom styles here */
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
  min-width: 320px;
  max-width: 500px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
  overflow: hidden;
}
.cash-drawer-header {
  background: #007bff;
  color: #fff;
  padding: 0.8rem 1.2rem;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
}
.total-amount {
  font-size: 1.1em;
  font-weight: bold;
  background: rgba(255,255,255,0.2);
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}
.cash-drawer-body {
  padding: 1rem;
  background: #fff;
}
.table {
  margin-bottom: 0;
}
.table th {
  background: #f8f9fa;
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
}
.table td {
  vertical-align: middle;
}
.denomination-value {
  font-weight: 600;
  color: #2c3e50;
}
.badge {
  font-size: 0.75em;
  padding: 0.3rem 0.5rem;
}
.btn-group-sm .btn {
  padding: 0.2rem 0.5rem;
  font-size: 0.875rem;
}
.table-warning {
  background-color: rgba(255, 193, 7, 0.1) !important;
}
.table-danger {
  background-color: rgba(220, 53, 69, 0.1) !important;
}
/* Slide transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
  max-height: 500px;
  opacity: 1;
}
.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  padding: 0;
}
/* Button styles */
.btn-outline-danger,
.btn-outline-success {
  padding: 0.2rem 0.5rem;
  font-size: 0.9em;
}
.btn-outline-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
/* Responsive adjustments */
@media (max-width: 768px) {
  .cash-drawer-floating {
    left: 10px;
    right: 10px;
    bottom: 10px;
    min-width: auto;
    max-width: none;
  }
  
  .table {
    font-size: 0.9rem;
  }
  
  .badge {
    font-size: 0.7em;
    padding: 0.2rem 0.4rem;
  }
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