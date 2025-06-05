@extends('desktop.admin.layouts.admin')

@section('title', 'Inventory Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Inventory Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Item
            </a>
            <a href="{{ route('admin.inventory.categories') }}" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-info">
                <i class="fas fa-truck"></i> Manage Suppliers
            </a>
            <a href="{{ route('admin.inventory.checks.index') }}" class="btn btn-warning">
                <i class="fas fa-clipboard-check"></i> Daily Stock Check
            </a>
            <a href="{{ route('admin.inventory.manage') }}" class="btn btn-success">
                <i class="fas fa-shopping-cart"></i> Manage Inventory
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Items</h5>
                    <h2 class="card-text">{{ $lowStockCount }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category->name ?? 'Uncategorized' }}</td>
                                <td>
                                    <span class="{{ $item->needsRestock() ? 'text-danger' : '' }}">
                                        {{ $item->quantity }} {{ $item->unit }}
                                    </span>
                                </td>
                                <td>{{ $item->unit }}</td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->status === 'active' ? 'success' : ($item->status === 'inactive' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.inventory.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 