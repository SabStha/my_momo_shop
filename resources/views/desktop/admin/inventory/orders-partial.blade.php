<div class="container py-3">
    <h2>Inventory Orders</h2>
    <div class="alert alert-info">Orders data will be displayed here.</div>
</div>

@if(isset($orders) && count($orders) > 0)
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                <td>{{ $order->supplier->name }}</td>
                <td>{{ $order->items_count }} items</td>
                <td>{{ number_format($order->total_amount, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'received' ? 'success' : 'info') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.inventory.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View
                    </a>
                    @if($order->status === 'pending')
                    <button class="btn btn-sm btn-success" onclick="markAsReceived({{ $order->id }})">
                        <i class="fas fa-check"></i> Receive
                    </button>
                    @endif
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

@push('scripts')
<script>
function markAsReceived(orderId) {
    if (confirm('Are you sure you want to mark this order as received?')) {
        $.ajax({
            url: `/admin/inventory/orders/${orderId}/receive`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error marking order as received');
                }
            },
            error: function() {
                alert('Error marking order as received');
            }
        });
    }
}
</script>
@endpush 