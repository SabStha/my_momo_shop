@extends('desktop.admin.layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Orders</h2>
        <div>
            <a href="{{ route('admin.inventory.orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Order
            </a>
            <a href="{{ route('admin.inventory.orders.export') }}" class="btn btn-success">
                <i class="fas fa-file-export"></i> Export
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Supplier</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Expected Delivery</th>
                            <th>Total Amount</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->supplier_name }}</td>
                                <td>{{ $order->supplier_contact }}</td>
                                <td>
                                    @if($order->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($order->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $order->expected_delivery->format('Y-m-d') }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.inventory.orders.show', $order->id) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->status === 'pending')
                                        <button class="btn btn-sm btn-success confirm-order" 
                                                data-id="{{ $order->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger cancel-order" 
                                                data-id="{{ $order->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Confirm order
    $('.confirm-order').click(function() {
        const orderId = $(this).data('id');
        if (confirm('Are you sure you want to confirm this order?')) {
            $.ajax({
                url: `{{ route('admin.inventory.orders.update', '') }}/${orderId}`,
                method: 'PUT',
                data: {
                    status: 'confirmed',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error confirming order: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error confirming order: ' + xhr.responseJSON.message);
                }
            });
        }
    });

    // Cancel order
    $('.cancel-order').click(function() {
        const orderId = $(this).data('id');
        if (confirm('Are you sure you want to cancel this order?')) {
            $.ajax({
                url: `{{ route('admin.inventory.orders.update', '') }}/${orderId}`,
                method: 'PUT',
                data: {
                    status: 'cancelled',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error cancelling order: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error cancelling order: ' + xhr.responseJSON.message);
                }
            });
        }
    });
});
</script>
@endpush
@endsection 