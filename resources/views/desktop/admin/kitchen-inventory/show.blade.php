@extends('desktop.admin.layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kitchen Order #{{ $order->id }}</h2>
        <div>
            <a href="{{ route('admin.kitchen-inventory.orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <a href="{{ route('admin.kitchen-inventory.orders.print', $order->id) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-print"></i> Print Order
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

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Supplier:</strong> {{ $order->supplier_name }}</p>
                            <p><strong>Contact:</strong> {{ $order->supplier_contact }}</p>
                            <p><strong>Status:</strong> 
                                @if($order->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($order->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Expected Delivery:</strong> {{ $order->expected_delivery->format('Y-m-d') }}</p>
                            <p><strong>Created At:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                            @if($order->completed_at)
                                <p><strong>Completed At:</strong> {{ $order->completed_at->format('Y-m-d H:i') }}</p>
                            @endif
                            @if($order->cancelled_at)
                                <p><strong>Cancelled At:</strong> {{ $order->cancelled_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    @if($order->notes)
                        <div class="mt-3">
                            <h6>Notes:</h6>
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
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
                                        <td>{{ number_format($item->quantity, 2) }}</td>
                                        <td>${{ number_format($item->unit_price, 2) }}</td>
                                        <td>${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total Amount:</th>
                                    <th>${{ number_format($order->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Items:</span>
                        <span>{{ $order->items->sum('quantity') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Unique Items:</span>
                        <span>{{ $order->items->count() }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Amount:</strong>
                        <strong>${{ number_format($order->total_amount, 2) }}</strong>
                    </div>
                </div>
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
        if (confirm('Are you sure you want to confirm this kitchen order?')) {
            $.ajax({
                url: `/admin/inventory/kitchen/orders/${orderId}/confirm`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error confirming kitchen order: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error confirming kitchen order: ' + xhr.responseJSON.message);
                }
            });
        }
    });

    // Cancel order
    $('.cancel-order').click(function() {
        const orderId = $(this).data('id');
        if (confirm('Are you sure you want to cancel this kitchen order?')) {
            $.ajax({
                url: `/admin/inventory/kitchen/orders/${orderId}/cancel`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error cancelling kitchen order: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error cancelling kitchen order: ' + xhr.responseJSON.message);
                }
            });
        }
    });
});
</script>
@endpush
@endsection 