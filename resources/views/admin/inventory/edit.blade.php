@extends('layouts.admin')
@section('title', 'Edit Inventory Item')
@section('content')
<div class="container py-3">
    <h2>Edit Inventory Item</h2>
    <form action="{{ route('admin.inventory.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Item Name:</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $item->name }}" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" class="form-control" value="{{ $item->category }}" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" class="form-control" value="{{ $item->quantity }}" required>
        </div>
        <div class="form-group">
            <label for="unit">Unit:</label>
            <input type="text" name="unit" id="unit" class="form-control" value="{{ $item->unit }}" required>
        </div>
        <div class="form-group">
            <label for="cost">Cost:</label>
            <input type="number" name="cost" id="cost" class="form-control" value="{{ $item->cost }}" required>
        </div>
        <div class="form-group">
            <label for="expiry">Expiry Date:</label>
            <input type="date" name="expiry" id="expiry" class="form-control" value="{{ $item->expiry }}" required>
        </div>
        <button type="submit" class="btn btn-warning">Update Item</button>
    </form>
</div>
@endsection 