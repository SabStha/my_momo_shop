<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supply Order #{{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background: #f5f5f5; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Supply Order #{{ $order->order_number }}</h2>
        <p><strong>Date:</strong> {{ $order->ordered_at ? $order->ordered_at->format('M d, Y') : '' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    </div>
    <div>
        <h4>Supplier Information</h4>
        <p>
            <strong>Name:</strong> {{ $order->supplier->name }}<br>
            @if($order->supplier->email)
                <strong>Email:</strong> {{ $order->supplier->email }}<br>
            @endif
            @if($order->supplier->phone)
                <strong>Phone:</strong> {{ $order->supplier->phone }}<br>
            @endif
            @if($order->supplier->address)
                <strong>Address:</strong> {{ $order->supplier->address }}<br>
            @endif
        </p>
    </div>
    <div>
        <h4>Order Items</h4>
        <table class="table">
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
                        <td>{{ $item->inventoryItem->name ?? '' }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="right"><strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
    </div>
    @if($order->notes)
        <div>
            <h4>Notes</h4>
            <p>{{ $order->notes }}</p>
        </div>
    @endif
</body>
</html> 