@extends('desktop.admin.layouts.admin')
@section('title', 'Edit Inventory Item')
@section('content')
<div class="container py-3">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Inventory Item</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="name" value="{{ $item->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="current_quantity" class="form-label">Current Quantity</label>
                            <input type="number" step="0.01" class="form-control @error('current_quantity') is-invalid @enderror" 
                                   id="current_quantity" name="current_quantity" value="{{ old('current_quantity', $item->current_quantity) }}" required>
                            @error('current_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="avg_usage" class="form-label">Average Daily Usage</label>
                            <input type="number" step="0.01" class="form-control @error('avg_usage') is-invalid @enderror" 
                                   id="avg_usage" name="avg_usage" value="{{ old('avg_usage', $item->avg_usage) }}" required>
                            @error('avg_usage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="safety_stock" class="form-label">Safety Stock</label>
                            <input type="number" step="0.01" class="form-control @error('safety_stock') is-invalid @enderror" 
                                   id="safety_stock" name="safety_stock" value="{{ old('safety_stock', $item->safety_stock) }}" required>
                            @error('safety_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reorder_point" class="form-label">Reorder Point</label>
                            <input type="number" step="0.01" class="form-control @error('reorder_point') is-invalid @enderror" 
                                   id="reorder_point" name="reorder_point" value="{{ old('reorder_point', $item->reorder_point) }}" required>
                            @error('reorder_point')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit" value="{{ $item->unit }}" disabled>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.inventory.dashboard') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 