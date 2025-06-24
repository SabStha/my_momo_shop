@extends('layouts.admin')

@section('title', 'Inventory Item Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Item Details</h2>
        </div>
        <div class="col-md-6 text-end">
            @if($item)
                <a href="{{ route('admin.inventory.edit', ['inventory' => $item->id]) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Item
                </a>
            @endif
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
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

    @if($item)
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Item Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>SKU:</th>
                                <td>{{ $item->sku }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $item->name }}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $item->category->name ?? 'Uncategorized' }}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $item->description ?? 'No description' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $item->status === 'active' ? 'success' : ($item->status === 'inactive' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Stock Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Current Quantity:</th>
                                <td>
                                    <span class="{{ $item->needsRestock() ? 'text-danger' : '' }}">
                                        {{ $item->quantity }} {{ $item->unit }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Unit Price:</th>
                                <td>Rs {{ number_format($item->unit_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Reorder Point:</th>
                                <td>{{ $item->reorder_point }} {{ $item->unit }}</td>
                            </tr>
                            <tr>
                                <th>Safety Stock:</th>
                                <td>{{ $item->safety_stock }} {{ $item->unit }}</td>
                            </tr>
                            <tr>
                                <th>Location:</th>
                                <td>{{ $item->location ?? 'Not specified' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Supplier Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Supplier:</th>
                                <td>{{ $item->supplier ?? 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <th>Contact:</th>
                                <td>{{ $item->supplier_contact ?? 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <th>Last Restock:</th>
                                <td>{{ $item->last_restock_date ? $item->last_restock_date->format('M d, Y') : 'Never' }}</td>
                            </tr>
                            <tr>
                                <th>Next Restock:</th>
                                <td>{{ $item->next_restock_date ? $item->next_restock_date->format('M d, Y') : 'Not scheduled' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Stock Adjustments</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                            <i class="fas fa-plus"></i> Adjust Stock
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Notes</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->type === 'purchase' ? 'success' : 
                                                    ($transaction->type === 'sale' ? 'danger' : 
                                                    ($transaction->type === 'return' ? 'info' : 
                                                    ($transaction->type === 'waste' ? 'warning' : 'secondary'))) }}">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->quantity }} {{ $item->unit }}</td>
                                            <td>Rs {{ number_format($transaction->unit_price, 2) }}</td>
                                            <td>Rs {{ number_format($transaction->total_amount, 2) }}</td>
                                            <td>{{ $transaction->notes ?? '-' }}</td>
                                            <td>{{ $transaction->user->name ?? 'System' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger mt-4">Inventory item not found or missing required parameter.</div>
    @endif
</div>

<!-- Adjust Stock Modal -->
@if($item)
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.inventory.adjust', ['inventory' => $item->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Adjustment Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                            <option value="return">Return</option>
                            <option value="waste">Waste</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection 
