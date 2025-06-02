@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Supplier Details</h1>
        <div>
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Supplier
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Suppliers
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Supplier Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Name</th>
                            <td>{{ $supplier->name }}</td>
                        </tr>
                        <tr>
                            <th>Contact Person</th>
                            <td>{{ $supplier->contact ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $supplier->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $supplier->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $supplier->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $supplier->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Supplied Items</h5>
                </div>
                <div class="card-body">
                    @if($supplier->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>SKU</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->items as $item)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.inventory.show', $item) }}">
                                                    {{ $item->name }}
                                                </a>
                                            </td>
                                            <td>{{ $item->sku }}</td>
                                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : ($item->status === 'inactive' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No items supplied by this supplier.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 