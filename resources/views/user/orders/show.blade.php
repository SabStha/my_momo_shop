@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Order #{{ $order->id }}</h2>
    <div class="mb-3">
        <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($order->status) }}</span>
        <span class="ms-3">Placed on: {{ $order->created_at->format('M d, Y H:i') }}</span>
    </div>
    <div class="card mb-4">
        <div class="card-header">Order Items</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            @if($item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" style="width:40px;" class="me-2 rounded">
                            @endif
                            {{ $item->product->name }}
                        </td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                        <td>
                            @if($order->status === 'completed' && $item->product->canBeRatedBy(auth()->user()))
                                <a href="{{ route('products.show', $item->product) }}#rate" class="btn btn-sm btn-outline-primary">Would you like to rate?</a>
                            @elseif($item->product->ratings()->where('user_id', auth()->id())->exists())
                                <span class="text-success">Rated</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('dashboard.orders') }}" class="btn btn-secondary">Back to Orders</a>
</div>
@endsection 