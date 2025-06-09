@extends('layouts.admin')

@section('title', 'Supply Orders List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Supply Orders</h1>
        <div class="flex space-x-4">
            <button id="bulkReceiveBtn" class="hidden bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <i class="fas fa-check-circle mr-2"></i> Receive Selected Orders
            </button>
            <button id="bulkSendBtn" class="hidden bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <i class="fas fa-paper-plane mr-2"></i> Send Selected Orders
            </button>
            <a href="{{ route('admin.supply.orders.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> New Order
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div class="flex space-x-4">
                    <button id="selectAllPendingBtn" class="text-sm text-gray-600 hover:text-gray-900">
                        <i class="fas fa-check-square mr-1"></i> Select All Pending
                    </button>
                    <button id="selectAllSentBtn" class="text-sm text-gray-600 hover:text-gray-900">
                        <i class="fas fa-check-square mr-1"></i> Select All Sent
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search orders..." class="w-64 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="absolute right-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            @forelse($ordersBySupplier as $supplierId => $supplierOrders)
                <div class="mb-8">
                    <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $supplierOrders->first()->supplier->name ?? 'Unknown Supplier' }}
                        </h3>
                        <div class="flex space-x-4">
                            <button onclick="filterOrders('{{ $supplierId }}', 'all')" 
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                <i class="fas fa-list mr-1.5"></i>
                                All ({{ $supplierOrders->count() }})
                            </button>
                            <button onclick="filterOrders('{{ $supplierId }}', 'pending')" 
                                    class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <i class="fas fa-clock mr-1.5"></i>
                                Pending ({{ $supplierOrders->where('status', 'pending')->count() }})
                            </button>
                            <button onclick="filterOrders('{{ $supplierId }}', 'sent')" 
                                    class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-paper-plane mr-1.5"></i>
                                Sent ({{ $supplierOrders->where('status', 'sent')->count() }})
                            </button>
                            <button onclick="filterOrders('{{ $supplierId }}', 'received')" 
                                    class="inline-flex items-center px-3 py-1.5 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-check-circle mr-1.5"></i>
                                Received ({{ $supplierOrders->where('status', 'received')->count() }})
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Select
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="orders-{{ $supplierId }}">
                                @foreach($supplierOrders as $order)
                                <tr class="order-row" data-supplier-id="{{ $supplierId }}" data-status="{{ $order->status }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($order->status === 'pending')
                                        <input type="checkbox" class="order-checkbox" data-order-id="{{ $order->id }}" data-supplier-id="{{ $supplierId }}">
                                        @elseif($order->status === 'sent')
                                        <input type="checkbox" class="receive-checkbox" data-order-id="{{ $order->id }}" data-supplier-id="{{ $supplierId }}">
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->ordered_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->items->count() }} items</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($order->status === 'sent' ? 'bg-blue-100 text-blue-800' : 
                                               ($order->status === 'received' ? 'bg-green-100 text-green-800' : 
                                               'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-3">
                                            <a href="{{ route('admin.supply.orders.show', $order) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($order->status === 'pending')
                                                <button onclick="sendOrder('{{ $order->id }}')"
                                                        class="text-green-600 hover:text-green-900" title="Send">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <a href="{{ route('admin.supply.orders.edit', $order) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.supply.orders.destroy', $order) }}" 
                                                      method="POST" 
                                                      class="inline-block"
                                                      onsubmit="return confirm('Are you sure you want to delete this order?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @elseif($order->status === 'sent')
                                                <button onclick="receiveOrder('{{ $order->id }}')"
                                                        class="text-green-600 hover:text-green-900" title="Receive Order">
                                                    <i class="fas fa-check-circle"></i> Receive
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    No orders found.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-question text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="modalConfirmBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Confirm
                </button>
                <button id="modalCancelBtn" class="ml-3 px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receive Order Modal -->
<div id="receiveOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Receive Orders</h3>
                <button onclick="closeReceiveModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="receiveOrderForm" class="space-y-4">
                <div id="receiveOrdersContainer" class="space-y-6">
                    <!-- Orders will be populated here -->
                </div>
                <div class="mt-4">
                    <label for="receiveNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="receiveNotes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="mt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeReceiveModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Confirm Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentOrderId = null;
    let selectedOrders = new Set();
    let selectedReceiveOrders = new Set();

    function showLoading() {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white p-4 rounded-lg shadow-lg flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="text-gray-700">Processing...</span>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    function hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    function showConfirmationModal(title, message, onConfirm) {
        const modal = document.getElementById('confirmationModal');
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('modalConfirmBtn').onclick = () => {
            modal.classList.add('hidden');
            onConfirm();
        };
        document.getElementById('modalCancelBtn').onclick = () => {
            modal.classList.add('hidden');
        };
        modal.classList.remove('hidden');
    }

    function sendOrder(orderId) {
        showConfirmationModal(
            'Send Order',
            'Are you sure you want to send this order to the supplier?',
            () => {
                showLoading();
                fetch(`/admin/supply/orders/${orderId}/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showAlert(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('Failed to send order: ' + error.message, 'error');
                });
            }
        );
    }

    function receiveOrder(orderId) {
        if (orderId) {
            selectedReceiveOrders = new Set([orderId]);
        }
        showLoading();
        
        // Fetch items for all selected orders
        const promises = Array.from(selectedReceiveOrders).map(orderId => 
            fetch(`/admin/supply/orders/${orderId}/items`, {
                headers: {
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
        );

        Promise.all(promises)
            .then(results => {
                hideLoading();
                const container = document.getElementById('receiveOrdersContainer');
                container.innerHTML = '';

                results.forEach((data, index) => {
                    if (data.success) {
                        const orderId = Array.from(selectedReceiveOrders)[index];
                        const orderDiv = document.createElement('div');
                        orderDiv.className = 'border rounded-lg p-4';
                        orderDiv.setAttribute('data-order-id', orderId);
                        orderDiv.innerHTML = `
                            <h4 class="text-lg font-medium mb-4">Order #${data.order_number}</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordered Qty</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received Qty</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${data.items.map(item => `
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.inventory_item.name}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.quantity}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="number" name="items[${item.id}][actual_received_quantity]" 
                                                           value="${item.quantity}" min="0" step="0.01"
                                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.inventory_item.unit}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                        container.appendChild(orderDiv);
                    }
                });

                document.getElementById('receiveOrderModal').classList.remove('hidden');
            })
            .catch(error => {
                hideLoading();
                showAlert('Failed to load order items: ' + error.message, 'error');
            });
    }

    function closeReceiveModal() {
        document.getElementById('receiveOrderModal').classList.add('hidden');
        selectedReceiveOrders.clear();
    }

    document.getElementById('receiveOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const notes = formData.get('notes');

        showLoading();
        const promises = Array.from(selectedReceiveOrders).map(orderId => {
            const data = {
                status: 'received',
                items: [],
                notes: notes
            };

            // Get all inputs for this order
            const orderDiv = document.querySelector(`#receiveOrdersContainer .border[data-order-id="${orderId}"]`);
            if (!orderDiv) {
                throw new Error(`Could not find order container for order ${orderId}`);
            }

            const inputs = orderDiv.querySelectorAll('input[name^="items["]');
            inputs.forEach(input => {
                const itemId = input.name.match(/\[(\d+)\]/)[1];
                data.items.push({
                    id: itemId,
                    actual_received_quantity: input.value
                });
            });

            return fetch(`/admin/supply/orders/${orderId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify(data)
            }).then(response => response.json());
        });

        Promise.all(promises)
            .then(results => {
                hideLoading();
                const success = results.every(r => r.success);
                if (success) {
                    showAlert(`Successfully received ${selectedReceiveOrders.size} order(s)`);
                    selectedReceiveOrders.clear();
                    closeReceiveModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const errors = results.filter(r => !r.success).map(r => r.message).join(', ');
                    showAlert('Some orders failed to be received: ' + errors, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showAlert('Failed to receive orders: ' + error.message, 'error');
            });
    });

    function filterOrders(supplierId, status) {
        const rows = document.querySelectorAll(`[data-supplier-id="${supplierId}"]`);
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Select All functionality
    document.getElementById('selectAllPendingBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
            if (!allChecked) {
                selectedOrders.add(checkbox.dataset.orderId);
            } else {
                selectedOrders.delete(checkbox.dataset.orderId);
            }
        });
        
        updateBulkSendButton();
    });

    document.getElementById('selectAllSentBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.receive-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
            if (!allChecked) {
                selectedReceiveOrders.add(checkbox.dataset.orderId);
            } else {
                selectedReceiveOrders.delete(checkbox.dataset.orderId);
            }
        });
        
        updateBulkReceiveButton();
    });

    // Remove the bulk selection code
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedOrders.add(this.dataset.orderId);
            } else {
                selectedOrders.delete(this.dataset.orderId);
            }
            updateBulkSendButton();
        });
    });

    document.querySelectorAll('.receive-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedReceiveOrders.add(this.dataset.orderId);
            } else {
                selectedReceiveOrders.delete(this.dataset.orderId);
            }
            updateBulkReceiveButton();
        });
    });

    function updateBulkSendButton() {
        const bulkSendBtn = document.getElementById('bulkSendBtn');
        if (selectedOrders.size > 0) {
            bulkSendBtn.classList.remove('hidden');
        } else {
            bulkSendBtn.classList.add('hidden');
        }
    }

    document.getElementById('bulkSendBtn').addEventListener('click', function() {
        if (selectedOrders.size === 0) return;

        showConfirmationModal(
            'Send Multiple Orders',
            `Are you sure you want to send ${selectedOrders.size} order(s) to the supplier(s)?`,
            () => {
                showLoading();
                const promises = Array.from(selectedOrders).map(orderId => 
                    fetch(`/admin/supply/orders/${orderId}/send`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(response => response.json())
                );

                Promise.all(promises)
                    .then(results => {
                        hideLoading();
                        const success = results.every(r => r.success);
                        if (success) {
                            showAlert(`Successfully sent ${selectedOrders.size} order(s)`);
                            selectedOrders.clear();
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showAlert('Some orders failed to send', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        showAlert('Failed to send orders: ' + error.message, 'error');
                    });
            }
        );
    });

    function updateBulkReceiveButton() {
        const bulkReceiveBtn = document.getElementById('bulkReceiveBtn');
        if (selectedReceiveOrders.size > 0) {
            bulkReceiveBtn.classList.remove('hidden');
        } else {
            bulkReceiveBtn.classList.add('hidden');
        }
    }

    document.getElementById('bulkReceiveBtn').addEventListener('click', function() {
        if (selectedReceiveOrders.size === 0) return;
        receiveOrder();
    });
</script>
@endpush
@endsection
