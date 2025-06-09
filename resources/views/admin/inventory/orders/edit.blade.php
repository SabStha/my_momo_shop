@extends('layouts.admin')

@section('title', "Edit Order #{$order->order_number}")

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Edit Order #{{ $order->order_number }}</h2>
                    <p class="text-sm text-gray-600">Update order details and items</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('admin.inventory.orders.update', $order) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $order->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                           value="{{ old('expected_delivery_date', $order->expected_delivery_date->format('Y-m-d')) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    @error('expected_delivery_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" id="notes" rows="3" 
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('notes', $order->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                <div id="order-items">
                    @foreach($order->items as $index => $orderItem)
                    <div class="order-item bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Item</label>
                                <select name="items[{{ $index }}][inventory_item_id]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                                data-price="{{ $item->unit_price }}"
                                                {{ old("items.{$index}.inventory_item_id", $orderItem->inventory_item_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" name="items[{{ $index }}][quantity]" min="1" step="1" 
                                       value="{{ old("items.{$index}.quantity", $orderItem->quantity) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                <input type="number" name="items[{{ $index }}][unit_price]" step="0.01" 
                                       value="{{ old("items.{$index}.unit_price", $orderItem->unit_price) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-item text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" id="add-item" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i> Add Item
                </button>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.inventory.orders.show', $order) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Order
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderItems = document.getElementById('order-items');
    const addItemButton = document.getElementById('add-item');
    let itemCount = {{ count($order->items) }};

    // Add new item row
    addItemButton.addEventListener('click', function() {
        const template = orderItems.querySelector('.order-item').cloneNode(true);
        const inputs = template.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            input.value = '';
            input.name = input.name.replace(/\[\d+\]/, `[${itemCount}]`);
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
});
</script>
@endpush

@endsection 