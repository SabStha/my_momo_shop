@extends('layouts.admin')

@section('title', 'Supplier Order Management')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <!-- Header Section -->
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-truck text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Supplier Order Management</h2>
                    <p class="text-sm text-gray-600">Main Branch - Centralized Supply Chain Management</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.orders.index', ['branch' => $branch->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                </a>
                <a href="{{ route('admin.inventory.orders.create', ['branch' => $branch->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i> Create Order
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $suppliers->sum(function($s) { return $s->orders_by_status->get('pending', collect())->count(); }) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-paper-plane text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Sent to Suppliers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $suppliers->sum(function($s) { return $s->orders_by_status->get('sent', collect())->count(); }) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Received Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $suppliers->sum(function($s) { return $s->orders_by_status->get('received', collect())->count(); }) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Suppliers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $suppliers->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Suppliers Tabs -->
    <div class="bg-white shadow rounded-lg">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                @foreach($suppliers as $index => $supplier)
                    <button onclick="showSupplierTab('{{ $supplier->id }}')" 
                            class="supplier-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $index === 0 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            data-supplier="{{ $supplier->id }}">
                        {{ $supplier->name }}
                        <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                            {{ $supplier->orders->count() }}
                        </span>
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Supplier Content -->
        @foreach($suppliers as $index => $supplier)
            <div id="supplier-{{ $supplier->id }}" class="supplier-content {{ $index === 0 ? '' : 'hidden' }} p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $supplier->name }}</h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span><i class="fas fa-phone mr-1"></i>{{ $supplier->phone }}</span>
                        <span><i class="fas fa-envelope mr-1"></i>{{ $supplier->email }}</span>
                        <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $supplier->address }}</span>
                    </div>
                </div>

                <!-- Status Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Pending Orders -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900">Pending Orders</h4>
                            <div class="flex items-center space-x-2">
                                @php
                                    $pendingOrders = $supplier->orders_by_status->get('pending', collect());
                                    $branchOrders = $pendingOrders->filter(function($order) {
                                        return $order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id;
                                    });
                                    $regularOrders = $pendingOrders->filter(function($order) {
                                        return !($order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id);
                                    });
                                    $hasBranchOrders = $branchOrders->count() > 0;
                                    $hasRegularOrders = $regularOrders->count() > 0;
                                @endphp
                                @if($hasBranchOrders)
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        {{ $branchOrders->count() }} Auto-Sent
                                    </span>
                                @endif
                                @if($hasRegularOrders)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        {{ $regularOrders->count() }} Pending
                                    </span>
                                @endif
                                @if($pendingOrders->count() > 0)
                                    <button onclick="selectAllPendingOrders('{{ $supplier->id }}')" 
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Select All
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Bulk Action Bar -->
                        <div id="bulk-actions-{{ $supplier->id }}" class="mb-3 p-2 bg-blue-50 rounded border border-blue-200 hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-blue-800">
                                    <span id="selected-count-{{ $supplier->id }}">0</span> orders selected
                                </span>
                                <button onclick="bulkSendOrders('{{ $supplier->id }}')" 
                                        class="bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                                    Send Selected
                                </button>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($supplier->orders_by_status->get('pending', collect()) as $order)
                                @php
                                    $isBranchOrder = $order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id;
                                @endphp
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                                    <div class="flex items-start space-x-2">
                                        <input type="checkbox" 
                                               class="order-checkbox mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                               data-supplier="{{ $supplier->id }}"
                                               data-order="{{ $order->id }}"
                                               onchange="updateBulkActions('{{ $supplier->id }}')">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</span>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500">{{ $order->created_at->format('M d') }}</span>
                                                    @if($isBranchOrder)
                                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                            Auto-Sent
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-600 mb-2">
                                                {{ $order->items->count() }} items • Rs. {{ number_format($order->total_amount, 2) }}
                                            </div>
                                            <div class="flex space-x-2">
                                                @if($isBranchOrder)
                                                    <div class="flex-1 bg-green-600 text-white text-xs px-2 py-1 rounded text-center">
                                                        Auto-Sent to Main Branch
                                                    </div>
                                                @else
                                                    <button onclick="sendToSupplier({{ $order->id }})" 
                                                            class="flex-1 bg-blue-600 text-white text-xs px-2 py-1 rounded hover:bg-blue-700">
                                                        Send
                                                    </button>
                                                @endif
                                                <a href="{{ route('admin.inventory.orders.show', $order) }}" 
                                                   class="flex-1 bg-gray-600 text-white text-xs px-2 py-1 rounded hover:bg-gray-700 text-center">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-gray-500 text-sm">
                                    No pending orders
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Sent Orders -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900">Sent to Supplier</h4>
                            <div class="flex items-center space-x-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    {{ $supplier->orders_by_status->get('sent', collect())->count() }}
                                </span>
                                @if($supplier->orders_by_status->get('sent', collect())->count() > 0)
                                    <button onclick="selectAllSentOrders('{{ $supplier->id }}')" 
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Select All
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Bulk Action Bar for Sent Orders -->
                        <div id="bulk-actions-sent-{{ $supplier->id }}" class="mb-3 p-2 bg-green-50 rounded border border-green-200 hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-green-800">
                                    <span id="selected-count-sent-{{ $supplier->id }}">0</span> orders selected
                                </span>
                                <button onclick="bulkConfirmOrders('{{ $supplier->id }}')" 
                                        class="bg-green-600 text-white text-xs px-3 py-1 rounded hover:bg-green-700">
                                    Confirm Selected
                                </button>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($supplier->orders_by_status->get('sent', collect()) as $order)
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                                    <div class="flex items-start space-x-2">
                                        <input type="checkbox" 
                                               class="sent-order-checkbox mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                               data-supplier="{{ $supplier->id }}"
                                               data-order="{{ $order->id }}"
                                               onchange="updateBulkActionsSent('{{ $supplier->id }}')">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</span>
                                                <span class="text-xs text-gray-500">{{ $order->sent_at->format('M d') }}</span>
                                            </div>
                                            <div class="text-xs text-gray-600 mb-2">
                                                {{ $order->items->count() }} items • Rs. {{ number_format($order->total_amount, 2) }}
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="confirmSupplierDelivery({{ $order->id }})" 
                                                        class="flex-1 bg-green-600 text-white text-xs px-2 py-1 rounded hover:bg-green-700">
                                                    Confirm
                                                </button>
                                                <a href="{{ route('admin.inventory.orders.show', $order) }}" 
                                                   class="flex-1 bg-gray-600 text-white text-xs px-2 py-1 rounded hover:bg-gray-700 text-center">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-gray-500 text-sm">
                                    No sent orders
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Received Orders -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900">Received & Verified</h4>
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                {{ $supplier->orders_by_status->get('received', collect())->count() }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @forelse($supplier->orders_by_status->get('received', collect()) as $order)
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ $order->received_at ? $order->received_at->format('M d') : 'Not confirmed' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600 mb-2">
                                        {{ $order->items->count() }} items • Rs. {{ number_format($order->total_amount, 2) }}
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="distributeToBranches({{ $order->id }})" 
                                                class="flex-1 bg-purple-600 text-white text-xs px-2 py-1 rounded hover:bg-purple-700">
                                            Distribute
                                        </button>
                                        <a href="{{ route('admin.inventory.orders.show', $order) }}" 
                                           class="flex-1 bg-gray-600 text-white text-xs px-2 py-1 rounded hover:bg-gray-700 text-center">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-gray-500 text-sm">
                                    No received orders
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Cancelled Orders -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900">Cancelled Orders</h4>
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                {{ $supplier->orders_by_status->get('cancelled', collect())->count() }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @forelse($supplier->orders_by_status->get('cancelled', collect()) as $order)
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</span>
                                        <span class="text-xs text-gray-500">{{ $order->updated_at->format('M d') }}</span>
                                    </div>
                                    <div class="text-xs text-gray-600 mb-2">
                                        {{ $order->items->count() }} items • Rs. {{ number_format($order->total_amount, 2) }}
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.inventory.orders.show', $order) }}" 
                                           class="flex-1 bg-gray-600 text-white text-xs px-2 py-1 rounded hover:bg-gray-700 text-center">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-gray-500 text-sm">
                                    No cancelled orders
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Action Modals -->
<div id="actionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Confirm Action</h3>
            <p class="text-sm text-gray-600 mb-6" id="modalMessage">Are you sure you want to proceed with this action?</p>
            <div class="flex space-x-3">
                <button onclick="closeActionModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                    Cancel
                </button>
                <button id="confirmActionBtn" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showSupplierTab(supplierId) {
    // Hide all supplier content
    document.querySelectorAll('.supplier-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected supplier content
    document.getElementById('supplier-' + supplierId).classList.remove('hidden');
    
    // Update tab styling
    document.querySelectorAll('.supplier-tab').forEach(tab => {
        tab.classList.remove('border-indigo-500', 'text-indigo-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Highlight selected tab
    document.querySelector(`[data-supplier="${supplierId}"]`).classList.remove('border-transparent', 'text-gray-500');
    document.querySelector(`[data-supplier="${supplierId}"]`).classList.add('border-indigo-500', 'text-indigo-600');
}

function selectAllPendingOrders(supplierId) {
    const checkboxes = document.querySelectorAll(`input[data-supplier="${supplierId}"].order-checkbox`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    updateBulkActions(supplierId);
}

function updateBulkActions(supplierId) {
    const checkboxes = document.querySelectorAll(`input[data-supplier="${supplierId}"].order-checkbox:checked`);
    const bulkActions = document.getElementById(`bulk-actions-${supplierId}`);
    const selectedCount = document.getElementById(`selected-count-${supplierId}`);
    
    if (checkboxes.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checkboxes.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

function bulkSendOrders(supplierId) {
    const checkboxes = document.querySelectorAll(`input[data-supplier="${supplierId}"].order-checkbox:checked`);
    const orderIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-order'));
    
    if (orderIds.length === 0) {
        alert('Please select at least one order to send.');
        return;
    }
    
    showActionModal(
        'Send Multiple Orders',
        `Are you sure you want to send ${orderIds.length} order(s) to the supplier? This action cannot be undone.`,
        () => updateMultipleOrderStatus(orderIds, 'sent')
    );
}

function sendToSupplier(orderId) {
    showActionModal(
        'Send to Supplier',
        'Are you sure you want to send this order to the supplier? This action cannot be undone.',
        () => updateOrderStatus(orderId, 'sent')
    );
}

function confirmSupplierDelivery(orderId) {
    showActionModal(
        'Confirm Supplier Delivery',
        'Has the supplier confirmed delivery of all items? This will mark the order as supplier confirmed.',
        () => updateOrderStatus(orderId, 'supplier_confirmed')
    );
}

function distributeToBranches(orderId) {
    showActionModal(
        'Distribute to Branches',
        'Are you ready to distribute the received items to the requesting branches?',
        () => window.location.href = '/admin/inventory/orders/' + orderId
    );
}

function showActionModal(title, message, onConfirm) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('confirmActionBtn').onclick = onConfirm;
    document.getElementById('actionModal').classList.remove('hidden');
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
}

function updateOrderStatus(orderId, status) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmActionBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = 'Updating...';
    confirmBtn.disabled = true;
    
    fetch(`/admin/inventory/orders/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeActionModal();
            // Show success message
            showSuccessMessage(data.message || 'Order status updated successfully!');
            // Reload after a short delay to show the success message
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            alert('Error updating order status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    })
    .finally(() => {
        // Reset button state
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

function updateMultipleOrderStatus(orderIds, status) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmActionBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = 'Updating...';
    confirmBtn.disabled = true;
    
    fetch('/admin/inventory/orders/bulk-status-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            order_ids: orderIds, 
            status: status 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeActionModal();
            // Show success message
            showSuccessMessage(data.message || 'Orders updated successfully!');
            // Reload after a short delay to show the success message
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            alert('Error updating order statuses: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    })
    .finally(() => {
        // Reset button state
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

function showSuccessMessage(message) {
    // Create success message element
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    successDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(successDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv);
        }
    }, 3000);
}

function selectAllSentOrders(supplierId) {
    const checkboxes = document.querySelectorAll(`input[data-supplier="${supplierId}"].sent-order-checkbox`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    updateBulkActionsSent(supplierId);
}

function updateBulkActionsSent(supplierId) {
    const checkboxes = document.querySelectorAll(`input[data-supplier="${supplierId}"].sent-order-checkbox:checked`);
    const bulkActions = document.getElementById(`bulk-actions-sent-${supplierId}`);
    const selectedCount = document.getElementById(`selected-count-sent-${supplierId}`);
    
    if (checkboxes.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checkboxes.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

function bulkConfirmOrders(supplierId) {
    const checkboxes = document.querySelectorAll(`input[data-supplier="${supplierId}"].sent-order-checkbox:checked`);
    const orderIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-order'));
    
    if (orderIds.length === 0) {
        alert('Please select at least one order to confirm.');
        return;
    }
    
    showActionModal(
        'Confirm Multiple Orders',
        `Are you sure you want to confirm ${orderIds.length} order(s)? This action cannot be undone.`,
        () => updateMultipleOrderStatus(orderIds, 'supplier_confirmed')
    );
}

// Close modal when clicking outside
document.getElementById('actionModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeActionModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeActionModal();
    }
});
</script>
@endpush

@endsection 