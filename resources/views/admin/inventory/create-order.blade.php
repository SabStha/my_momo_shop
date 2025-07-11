@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create New Order</h2>
        <a href="{{ route('admin.inventory.orders') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.inventory.orders.store') }}" method="POST" id="orderForm">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label">Supplier Name</label>
                            <input type="text" class="form-control @error('supplier_name') is-invalid @enderror" 
                                   id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}" required>
                            @error('supplier_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier_contact" class="form-label">Supplier Contact</label>
                            <input type="text" class="form-control @error('supplier_contact') is-invalid @enderror" 
                                   id="supplier_contact" name="supplier_contact" value="{{ old('supplier_contact') }}" required>
                            @error('supplier_contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expected_delivery" class="form-label">Expected Delivery Date</label>
                            <input type="date" class="form-control @error('expected_delivery') is-invalid @enderror" 
                                   id="expected_delivery" name="expected_delivery" value="{{ old('expected_delivery') }}" required>
                            @error('expected_delivery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4>Order Items</h4>
                    <div id="items-container">
                        <div class="item-row mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select stock-item" name="items[0][inventory_item_id]" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" 
                                                    data-unit="{{ $item->unit }}"
                                                    data-price="{{ $item->cost }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control quantity" 
                                           name="items[0][quantity]" placeholder="Qty" min="1" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control unit-price" 
                                           name="items[0][unit_price]" placeholder="Price" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control subtotal" readonly>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary mt-3" id="add-item">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="d-flex justify-content-between">
                            <h5>Total Amount:</h5>
                            <h5 id="total-amount">Rs 0.00</h5>
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
    $('#add-item').click(function() {
        const newRow = $('.item-row:first').clone();
        newRow.find('select').attr('name', `items[${itemCount}][inventory_item_id]`);
        newRow.find('.quantity').attr('name', `items[${itemCount}][quantity]`