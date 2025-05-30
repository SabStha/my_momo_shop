@extends('desktop.admin.layouts.admin')
@section('content')
<div class="container py-3">
    <h2>Inventory Orders</h2>
    <div class="alert alert-info">Orders data will be displayed here.</div>
</div>

@if(isset($orders) && count($orders) > 0)
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Supplier</th>
                <th>Items</th>
                <th>Expected Delivery</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->supplier->name }}</td>
                <td>{{ $order->items_count }} items</td>
                <td>{{ $order->expected_delivery->format('Y-m-d') }}</td>
                <td>
                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : 'info') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-order" data-id="{{ $order->id }}">
                            <i class="fas fa-eye"></i>
                        </button>
                        @if($order->status === 'pending')
                        <button class="btn btn-sm btn-success complete-order" data-id="{{ $order->id }}">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger cancel-order" data-id="{{ $order->id }}">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="alert alert-info">
    No pending orders found.
</div>
@endif

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetails"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    // View order details
    $('.view-order').on('click', function() {
        const id = $(this).data('id');
        $.get(`/admin/inventory/orders/${id}`, function(response) {
            $('#orderDetails').html(response);
            $('#orderDetailsModal').modal('show');
        });
    });

    // Complete order
    $('.complete-order').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to mark this order as completed?')) {
            $.ajax({
                url: `/admin/inventory/orders/${id}/complete`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Order marked as completed');
                        location.reload();
                    }
                },
                error: function() {
                    toastr.error('Failed to complete order');
                }
            });
        }
    });

    // Cancel order
    $('.cancel-order').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to cancel this order?')) {
            $.ajax({
                url: `/admin/inventory/orders/${id}/cancel`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Order cancelled successfully');
                        location.reload();
                    }
                },
                error: function() {
                    toastr.error('Failed to cancel order');
                }
            });
        }
    });
});
</script>
@endpush 