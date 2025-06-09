<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supply Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-info td {
            padding: 5px;
        }
        .order-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supply Order</h1>
        <h2>Order #{{ $order->order_number }}</h2>
    </div>

    <div class="order-info">
        <table>
            <tr>
                <td>Supplier:</td>
                <td>{{ $order->supplier->name }}</td>
            </tr>
            <tr>
                <td>Order Date:</td>
                <td>{{ $order->ordered_at->format('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <td>Status:</td>
                <td>{{ ucfirst($order->status) }}</td>
            </tr>
            @if($order->notes)
            <tr>
                <td>Notes:</td>
                <td>{{ $order->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="items-table">
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
                <td>{{ $item->quantity }} {{ $item->inventoryItem->unit }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Amount: ${{ number_format($order->total_amount, 2) }}
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html> 