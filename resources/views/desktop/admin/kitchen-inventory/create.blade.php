@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Kitchen Inventory Order</h2>
        <a href="{{ route('admin.kitchen-inventory.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.kitchen-inventory.orders.store') }}" method="POST" id="orderForm">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_name">Supplier Name</label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                   value="{{ old('supplier_name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_contact">Supplier Contact</label>
                            <input type="text" class="form-control" id="supplier_contact" name="supplier_contact" 
                                   value="{{ old('supplier_contact') }}" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="expected_delivery">Expected Delivery Date</label>
                            <input type="date" class="form-control" id="expected_delivery" name="expected_delivery" 
                                   value="{{ old('expected_delivery') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div id="orderItems">
                            <div class="row mb-3 order-item">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Item</label>
                                        <select class="form-control item-select" name="items[0][stock_item_id]" required>
                                            <option value="">Select Item</option>
                                            @foreach($stockItems as $item)
                                                <option value="{{ $item->id }}" 
                                                        data-price="{{ $item->unit_price }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control quantity-input" 
                                               name="items[0][quantity]" min="0.01" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Unit Price</label>
                                        <input type="number" class="form-control price-input" 
                                               name="items[0][unit_price]" min="0.01" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Subtotal</label>
                                        <input type="text" class="form-control subtotal" readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-block remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary" id="addItem">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>Order Summary</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Items:</span>
                                    <span id="totalItems">0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Total Amount:</strong>
                                    <strong id="totalAmount">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let itemCount = 1;

    // Add new item row
    $('#addItem').click(function() {
        const newRow = $('.order-item:first').clone();
        newRow.find('select').attr('name', `items[${itemCount}][stock_item_id]`);
        newRow.find('.quantity-input').attr('name', `items[${itemCount}][quantity]`).val('');
        newRow.find('.price-input').attr('name', `items[${itemCount}][unit_price]`).val('');
        newRow.find('.subtotal').val('');
        $('#orderItems').append(newRow);
        itemCount++;
        updateTotals();
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('.order-item').length > 1) {
            $(this).closest('.order-item').remove();
            updateTotals();
        }
    });

    // Update subtotal when quantity or price changes
    $(document).on('input', '.quantity-input, .price-input', function() {
        const row = $(this).closest('.order-item');
        const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        const price = parseFloat(row.find('.price-input').val()) || 0;
        const subtotal = quantity * price;
        row.find('.subtotal').val('$' + subtotal.toFixed(2));
        updateTotals();
    });

    // Update price when item is selected
    $(document).on('change', '.item-select', function() {
        const price = $(this).find(':selected').data('price');
        $(this).closest('.order-item').find('.price-input').val(price);
        $(this).closest('.order-item').find('.quantity-input').trigger('input');
    });

    // Update totals
    function updateTotals() {
        let totalItems = 0;
        let totalAmount = 0;

        $('.order-item').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            totalItems += quantity;
            totalAmount += quantity * price;
        });

        $('#totalItems').text(totalItems.toFixed(2));
        $('#totalAmount').text('$' + totalAmount.toFixed(2));
    }

    // Form submission
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        
        // Validate at least one item
        if ($('.order-item').length === 0) {
            alert('Please add at least one item to the order.');
            return;
        }

        // Submit form
        this.submit();
    });
});
</script>
@endpush
@endsection 