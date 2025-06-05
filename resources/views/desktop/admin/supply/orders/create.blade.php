@extends('desktop.admin.layouts.admin')

@section('title', 'Create Supply Order')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Create Supply Order</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.supply.orders.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Order Items</label>
                    <div id="items-container">
                        <div class="item-row mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="items[0][inventory_item_id]" class="form-control @error('items.0.inventory_item_id') is-invalid @enderror" required>
                                        <option value="">Select Item</option>
                                        @foreach($inventoryItems as $item)
                                            <option value="{{ $item->id }}" {{ old('items.0.inventory_item_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.0.inventory_item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[0][quantity]" class="form-control @error('items.0.quantity') is-invalid @enderror" placeholder="Qty" min="1" value="{{ old('items.0.quantity') }}" required>
                                    @error('items.0.quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[0][unit_price]" class="form-control @error('items.0.unit_price') is-invalid @enderror" placeholder="Unit Price" min="0" step="0.01" value="{{ old('items.0.unit_price') }}" required>
                                    @error('items.0.unit_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-item" style="display: none;">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="add-item">Add Item</button>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Order</button>
                    <a href="{{ route('admin.supply.orders.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('items-container');
    const addItemButton = document.getElementById('add-item');
    let itemCount = 1;

    addItemButton.addEventListener('click', function() {
        const template = itemsContainer.querySelector('.item-row').cloneNode(true);
        const inputs = template.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[0]', `[${itemCount}]`));
                input.value = '';
            }
        });

        template.querySelector('.remove-item').style.display = 'block';
        itemsContainer.appendChild(template);
        itemCount++;
    });

    itemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });
});
</script>
@endpush
@endsection 