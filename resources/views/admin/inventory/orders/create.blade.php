@extends('layouts.admin')

@section('title', 'Create Inventory Order')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Create New Order</h2>
                    <p class="text-sm text-gray-600">Add items to your inventory order</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.orders.index', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(isset($isRegularBranch) && $isRegularBranch)
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">
                            <strong>Centralized Order Management:</strong> Orders created from this branch will be automatically routed to the 
                            <strong>{{ $mainBranch->name }}</strong> for supplier processing. This ensures centralized supplier management 
                            and better coordination across all locations.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form id="createOrderForm" class="p-6">
            @csrf
            @if(isset($branch))
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <div id="supplier_error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <div>
                    <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                           value="{{ old('expected_delivery_date') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <div id="delivery_date_error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" id="notes" rows="3" 
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                <div id="notes_error" class="mt-1 text-sm text-red-600 hidden"></div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                <div id="order-items">
                    <div class="order-item bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Item</label>
                                <select name="items[0][inventory_item_id]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" data-price="{{ $item->unit_price }}">
                                            {{ $item->name }} ({{ $item->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" name="items[0][quantity]" min="1" step="1" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                <input type="number" name="items[0][unit_price]" step="0.01" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-item text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-item" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i> Add Item
                </button>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.inventory.orders.index', isset($branch) ? ['branch' => $branch->id] : []) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Order
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
        <div class="p-6 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <!-- Success Title -->
            <h3 class="text-lg font-semibold text-gray-900 mb-2" id="successTitle">Order Created Successfully!</h3>
            
            <!-- Success Message -->
            <p class="text-sm text-gray-600 mb-6" id="successMessage">
                Your inventory order has been created and is ready for processing.
            </p>
            
            <!-- Order Details (if available) -->
            <div id="orderDetails" class="bg-gray-50 rounded-lg p-4 mb-6 hidden">
                <div class="text-left">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Order Number:</span>
                        <span class="text-sm text-gray-900" id="orderNumber"></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Total Items:</span>
                        <span class="text-sm text-gray-900" id="totalItems"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Amount:</span>
                        <span class="text-sm font-semibold text-green-600" id="totalAmount"></span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button type="button" onclick="closeSuccessModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                    Continue
                </button>
                <button type="button" id="viewOrderBtn" onclick="viewOrderDetails()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    View Order
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderItems = document.getElementById('order-items');
    const addItemButton = document.getElementById('add-item');
    const form = document.getElementById('createOrderForm');
    let itemCount = 1;

    // Add new item row
    addItemButton.addEventListener('click', function() {
        const template = orderItems.querySelector('.order-item').cloneNode(true);
        const inputs = template.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            input.value = '';
            input.name = input.name.replace('[0]', `[${itemCount}]`);
        });

        orderItems.appendChild(template);
        itemCount++;
    });

    // Remove item row
    orderItems.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            if (orderItems.querySelectorAll('.order-item').length > 1) {
                e.target.closest('.order-item').remove();
            }
        }
    });

    // Update unit price when item is selected
    orderItems.addEventListener('change', function(e) {
        if (e.target.tagName === 'SELECT' && e.target.name.includes('inventory_item_id')) {
            const option = e.target.options[e.target.selectedIndex];
            const price = option.dataset.price;
            const priceInput = e.target.closest('.order-item').querySelector('input[name$="[unit_price]"]');
            if (price) {
                priceInput.value = price;
            }
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Order...';
        submitBtn.disabled = true;

        // Collect form data
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.includes('[')) {
                // Handle array fields
                const matches = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
                if (matches) {
                    const [, field, index, subfield] = matches;
                    if (!data[field]) data[field] = [];
                    if (!data[field][index]) data[field][index] = {};
                    data[field][index][subfield] = value;
                } else {
                    data[key] = value;
                }
            } else {
                data[key] = value;
            }
        }

        // Convert items array to proper format
        if (data.items) {
            data.items = Object.values(data.items).filter(item => item.inventory_item_id && item.quantity && item.unit_price);
        }

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Make AJAX request
        fetch('{{ route("admin.inventory.orders.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal(data);
            } else {
                // Show validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorElement = document.getElementById(field + '_error');
                        if (errorElement) {
                            errorElement.textContent = data.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    });
                } else {
                    alert('Error creating order: ' + (data.message || 'Unknown error'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Please try again.');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Confirmation dialog on submit
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.addEventListener('click', function(e) {
        // Only show confirmation if not already submitting
        if (!submitBtn.disabled) {
            const confirmed = confirm('Are you sure you want to create this order?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }
    });
});

function clearErrors() {
    const errorElements = document.querySelectorAll('[id$="_error"]');
    errorElements.forEach(element => {
        element.classList.add('hidden');
        element.textContent = '';
    });
}

function showSuccessModal(data) {
    // Set modal content based on response data
    const title = document.getElementById('successTitle');
    const message = document.getElementById('successMessage');
    const orderDetails = document.getElementById('orderDetails');
    const orderNumber = document.getElementById('orderNumber');
    const totalItems = document.getElementById('totalItems');
    const totalAmount = document.getElementById('totalAmount');
    const viewOrderBtn = document.getElementById('viewOrderBtn');
    
    // Update title and message
    title.textContent = data.message || 'Order Created Successfully!';
    message.textContent = data.order ? 
        'Your inventory order has been created and is ready for processing.' :
        'Your inventory order has been created successfully.';
    
    // Show order details if available
    if (data.order) {
        orderNumber.textContent = data.order.order_number || 'N/A';
        totalItems.textContent = data.order.items ? data.order.items.length : 'N/A';
        totalAmount.textContent = data.order.total_amount ? 
            `Rs. ${parseFloat(data.order.total_amount).toFixed(2)}` : 'N/A';
        orderDetails.classList.remove('hidden');
        
        // Show view order button if order_id is available
        if (data.order_id) {
            viewOrderBtn.classList.remove('hidden');
            viewOrderBtn.onclick = () => {
                const branchId = {{ isset($branch) ? $branch->id : 'null' }};
                const url = '/admin/inventory/orders/' + data.order_id + (branchId ? `?branch=${branchId}` : '');
                window.location.href = url;
            };
        } else {
            viewOrderBtn.classList.add('hidden');
        }
    } else {
        orderDetails.classList.add('hidden');
        viewOrderBtn.classList.add('hidden');
    }
    
    // Store order data for view button
    window._lastCreatedOrder = data;
    
    // Show modal with animation
    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    
    // Trigger close animation
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function viewOrderDetails() {
    if (window._lastCreatedOrder && window._lastCreatedOrder.order_id) {
        const branchId = {{ isset($branch) ? $branch->id : 'null' }};
        const url = '/admin/inventory/orders/' + window._lastCreatedOrder.order_id + (branchId ? `?branch=${branchId}` : '');
        window.location.href = url;
    } else {
        // Fallback to orders list
        const branchId = {{ isset($branch) ? $branch->id : 'null' }};
        window.location.href = '{{ route("admin.inventory.orders.index") }}' + (branchId ? `?branch=${branchId}` : '');
    }
}

// Add keyboard support for success modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSuccessModal();
    }
});

// Add click outside to close functionality for success modal
document.getElementById('successModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeSuccessModal();
    }
});
</script>
@endpush

@endsection 