@extends('layouts.admin')
@section('title', 'Daily Inventory Count')
@section('content')
<div class="container py-3">
    <h2>Daily Inventory Count</h2>
    <div class="alert alert-info">No items to count yet. Add stock items to get started!</div>
    <form action="{{ route('admin.inventory.count') }}" method="GET" class="mb-3">
        <div class="form-group">
            <label for="date">Select Date:</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ request('date', now()->format('Y-m-d')) }}">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Cost</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->cost }}</td>
                <td>{{ $item->expiry }}</td>
                <td>
                    <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No stock items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('admin.inventory.add') }}" class="btn btn-success">Add New Item</a>
    <a href="{{ route('admin.inventory.index') }}" class="btn btn-primary">Back to Dashboard</a>
</div>
@endsection 
