@extends('desktop.admin.layouts.admin')

@section('title', 'Edit Inventory Item')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Inventory Item</h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.inventory.update', $item) }}" method="POST" id="editForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Item Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $item->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                           id="sku" name="sku" value="{{ old('sku', $item->sku) }}" required>
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                            id="supplier_id" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="unit_price" class="form-label">Unit Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('unit_price') is-invalid @enderror" 
                                               id="unit_price" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}" required>
                                        @error('unit_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reorder_point" class="form-label">Reorder Point</label>
                                    <input type="number" class="form-control @error('reorder_point') is-invalid @enderror" 
                                           id="reorder_point" name="reorder_point" value="{{ old('reorder_point', $item->reorder_point) }}" required>
                                    @error('reorder_point')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="current_stock" class="form-label">Current Stock</label>
                                    <input type="number" class="form-control @error('current_stock') is-invalid @enderror" 
                                           id="current_stock" name="current_stock" value="{{ old('current_stock', $item->current_stock) }}" required>
                                    @error('current_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Item
                            </button>
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success') && session('show_links'))
<!-- Success Modal -->
<div class="modal fade" id="successModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Success!</h5>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                <h4>{{ session('success') }}</h4>
                <hr>
                <p class="mb-3">Where would you like to go next?</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Go to Inventory List
                    </a>
                    <a href="{{ route('admin.inventory.manage') }}" class="btn btn-info">
                        <i class="fas fa-cog"></i> Go to Manage Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show modal immediately and prevent form interaction
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        
        // Disable all form inputs
        var form = document.getElementById('editForm');
        var inputs = form.getElementsByTagName('input');
        var selects = form.getElementsByTagName('select');
        var textareas = form.getElementsByTagName('textarea');
        
        for(var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = true;
        }
        for(var i = 0; i < selects.length; i++) {
            selects[i].disabled = true;
        }
        for(var i = 0; i < textareas.length; i++) {
            textareas[i].disabled = true;
        }
    });
</script>
@endif
@endsection
