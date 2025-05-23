@extends('layouts.admin')
@section('title', 'Add Inventory Item')
@section('content')
<div class="container py-3">
    <h2>Add Inventory Item</h2>
    <form action="{{ route('admin.inventory.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Item Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="unit">Unit:</label>
            <input type="text" name="unit" id="unit" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="cost">Cost:</label>
            <input type="number" name="cost" id="cost" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="expiry">Expiry Date:</label>
            <input type="date" name="expiry" id="expiry" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Item</button>
    </form>
</div>
@endsection 