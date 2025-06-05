@extends('desktop.admin.layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #{{ $order->id }}</h2>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <button type="button" 
                    class="btn btn-primary dropdown-toggle" 
                    data-bs-toggle="dropdown">
                Update Status
            </button>
            <ul class="dropdown-menu">
                <li>
                    <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="dropdown-item">Mark as Pending</button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="dropdown-item">Mark as Processing</button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="dropdown-item">Mark as Completed</button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="dropdown-item text-danger">Mark as Cancelled</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order->user->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->user->email }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p class="mb-1"><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : 
                                    ($order->status === 'processing' ? 'info' : 
                                    ($order->status === 'completed' ? 'success' : 'danger')) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product->image)
                                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                                     alt="{{ $item->product->name }}" 
                                                     class="me-2"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <div>{{ $item->product->name }}</div>
                                                <small class="text-muted">{{ $item->product->sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>${{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                    <td>${{ number_format($order->tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td>${{ number_format($order->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Placed</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($order->status === 'processing')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Processing</h6>
                                <small class="text-muted">{{ $order->updated_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($order->status === 'completed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Completed</h6>
                                <small class="text-muted">{{ $order->updated_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($order->status === 'cancelled')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Cancelled</h6>
                                <small class="text-muted">{{ $order->updated_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 7px;
    top: 15px;
    height: calc(100% + 5px);
    width: 1px;
    background-color: #e9ecef;
}
</style>
@endsection 