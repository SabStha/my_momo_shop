@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #{{ $order->id }}</h2>
        <div>
            <a href="{{ route('admin.inventory.orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            @if($order->status === 'pending')
                <button class="btn btn-success confirm-order" data-id="{{ $order->id }}">
                    <i class="fas fa-check"></i> Confirm Order
                </button>
                <button class="btn btn-danger cancel-order" data-id="{{ $order->id }}">
                    <i class="fas fa-times"></i> Cancel Order
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($order->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($order->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>{{ $order->supplier_name }}</td>
                        </tr>
                        <tr>
                            <th>Contact</th>
                            <td>{{ $order->supplier_contact }}</td>
                        </tr>
                        <tr>
                            <th>Expected Delivery</th>
                            <td>{{ $order->expected_delivery->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if($order->completed_at)
                            <tr>
                                <th>Completed At</th>
                                <td>{{ $order->completed_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endif
                        @if($order->cancelled_at)
                            <tr>
                                <th>Cancelled At</th>
                                <td>{{ $order->cancelled_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endif
                        @if($order->notes)
                            <tr>
                                <th>Notes</th>
                                <td>{{ $order->notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Total Amount:</h6>
                        <h6>Rs {{ number_format($order->total_amount, 2) }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Order Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->stockItem->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }} {{ $item->stockItem->unit }}</td>
                                <td>Rs {{ number_format($item->unit_price, 2) }}</td>
                                <td>Rs {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
