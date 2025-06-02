@extends('desktop.admin.layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Order #{{ $order->order_number }}</h3>
                        <div>
                            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'received' ? 'success' : 'danger') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Order Number:</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td>{{ $order->ordered_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>{{ ucfirst($order->status) }}</td>
                                </tr>
                                @if($order->received_at)
                                <tr>
                                    <th>Received At:</th>
                                    <td>{{ $order->received_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Supplier Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $order->supplier->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $order->supplier->email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $order->supplier->phone }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->inventoryItem->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>${{ number_format($item->total_price, 2) }}</td>
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

                    @if($order->notes)
                    <div class="mt-4">
                        <h5>Notes</h5>
                        <p>{{ $order->notes }}</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin.supply.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        
                        @if($order->status === 'pending')
                        <form action="{{ route('admin.supply.orders.update', $order) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="received">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Mark as Received
                            </button>
                        </form>
                        <form action="{{ route('admin.supply.orders.update', $order) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="mb-3">
                        <form action="{{ route('admin.supply.orders.send', $order) }}" method="POST" style="display:inline-block">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-envelope"></i> Send Order to Supplier
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 