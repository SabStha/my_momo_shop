@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Payment Manager</h1>
        <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-600">
                Branch: <span class="font-medium">{{ $currentBranch->name }}</span>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Orders</h3>
        </div>
        <div class="border-t border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Orders will be loaded here -->
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button id="prevPageMobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </button>
                    <button id="nextPageMobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span id="paginationStart" class="font-medium">1</span>
                            to
                            <span id="paginationEnd" class="font-medium">10</span>
                            of
                            <span id="paginationTotal" class="font-medium">0</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button id="prevPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button id="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Process Payment</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Order #<span id="modalOrderNumber"></span></p>
                    <p class="text-sm text-gray-500">Total: $<span id="modalTotal"></span></p>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" id="paymentAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select id="paymentMethod" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile Payment</option>
                        </select>
                    </div>
                    <div id="changeAmount" class="mt-4 hidden">
                        <label class="block text-sm font-medium text-gray-700">Change</label>
                        <p class="text-sm text-gray-500">$<span id="changeValue">0.00</span></p>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="paymentNotes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmPayment" class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Process Payment
                    </button>
                    <button id="cancelPayment" class="ml-3 px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <meta name="branch-id" content="{{ $currentBranch->id }}">
</div>

<script>
function loadData(page = 1) {
    const branchId = document.querySelector('meta[name="branch-id"]').content;
    const tbody = document.getElementById('ordersTableBody');
    
    // Show loading state
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center">
                    <div class="flex justify-center items-center">
                        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2">Loading orders...</span>
                    </div>
                </td>
            </tr>
        `;
    }

    // Build query parameters
    const params = new URLSearchParams({
        page: page,
        per_page: 10,
        branch: branchId
    });

    fetch(`/api/payments?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || 'Error loading orders');
                });
            }
            return response.json();
        })
        .then(data => {
            if (tbody) {
                renderOrders(data.items);
                updatePagination(data);
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-red-600">
                            ${error.message || 'Error loading orders. Please try again.'}
                        </td>
                    </tr>
                `;
            }
        });
}

function renderOrders(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;

    if (!orders || orders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                    No orders found
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = orders.map(order => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${order.order_number}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${order.type === 'dine-in' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                    ${order.type}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${order.user?.name || order.guest_name || 'Guest'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(order.created_at).toLocaleString()}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(order.grand_total || 0).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${order.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                    ${order.is_paid ? 'Paid' : 'Pending'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                ${!order.is_paid ? `
                    <button onclick="processPayment(${order.id})" class="text-indigo-600 hover:text-indigo-900">
                        Pay
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function processPayment(orderId) {
    fetch(`/api/orders/${orderId}`)
        .then(response => response.json())
        .then(order => {
            document.getElementById('modalOrderNumber').textContent = order.order_number;
            document.getElementById('modalTotal').textContent = parseFloat(order.grand_total || 0).toFixed(2);
            document.getElementById('paymentAmount').value = order.grand_total || 0;
            document.getElementById('paymentModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            alert('Error loading order details. Please try again.');
        });
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentAmount').value = '';
    document.getElementById('paymentMethod').value = 'cash';
    document.getElementById('paymentNotes').value = '';
    document.getElementById('changeAmount').classList.add('hidden');
}

function calculateChange() {
    const total = parseFloat(document.getElementById('modalTotal').textContent);
    const amount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const change = amount - total;
    
    const changeAmount = document.getElementById('changeAmount');
    const changeValue = document.getElementById('changeValue');
    
    if (change >= 0) {
        changeAmount.classList.remove('hidden');
        changeValue.textContent = change.toFixed(2);
    } else {
        changeAmount.classList.add('hidden');
    }
}

function updatePagination(data) {
    const { current_page, per_page, total } = data;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(start + per_page - 1, total);

    // Update pagination info
    document.getElementById('paginationStart').textContent = start;
    document.getElementById('paginationEnd').textContent = end;
    document.getElementById('paginationTotal').textContent = total;

    // Update pagination buttons
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    const prevPageMobile = document.getElementById('prevPageMobile');
    const nextPageMobile = document.getElementById('nextPageMobile');

    // Desktop buttons
    prevPage.disabled = current_page === 1;
    nextPage.disabled = current_page * per_page >= total;
    prevPage.classList.toggle('opacity-50', current_page === 1);
    nextPage.classList.toggle('opacity-50', current_page * per_page >= total);

    // Mobile buttons
    prevPageMobile.disabled = current_page === 1;
    nextPageMobile.disabled = current_page * per_page >= total;
    prevPageMobile.classList.toggle('opacity-50', current_page === 1);
    nextPageMobile.classList.toggle('opacity-50', current_page * per_page >= total);

    // Add click handlers
    prevPage.onclick = () => loadData(current_page - 1);
    nextPage.onclick = () => loadData(current_page + 1);
    prevPageMobile.onclick = () => loadData(current_page - 1);
    nextPageMobile.onclick = () => loadData(current_page + 1);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    loadData(1);

    document.getElementById('paymentAmount').addEventListener('input', calculateChange);
    document.getElementById('confirmPayment').addEventListener('click', function() {
        const orderId = document.getElementById('modalOrderNumber').textContent;
        const amount = document.getElementById('paymentAmount').value;
        const method = document.getElementById('paymentMethod').value;
        const notes = document.getElementById('paymentNotes').value;

        fetch('/api/payments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                order_id: orderId,
                amount: amount,
                payment_method: method,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            closePaymentModal();
            loadData(1);
        })
        .catch(error => {
            console.error('Error processing payment:', error);
            alert('Error processing payment. Please try again.');
        });
    });

    document.getElementById('cancelPayment').addEventListener('click', closePaymentModal);
});
</script>
@endsection 