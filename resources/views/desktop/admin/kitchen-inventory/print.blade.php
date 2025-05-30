<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .order-info {
            margin-bottom: 30px;
        }
        .order-info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .notes {
            margin-top: 30px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kitchen Inventory Order</h1>
        <h2>Order #{{ $order->id }}</h2>
    </div>

    <div class="order-info">
        <div style="float: left; width: 50%;">
            <p><strong>Supplier:</strong> {{ $order->supplier_name }}</p>
            <p><strong>Contact:</strong> {{ $order->supplier_contact }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        </div>
        <div style="float: right; width: 50%;">
            <p><strong>Expected Delivery:</strong> {{ $order->expected_delivery->format('Y-m-d') }}</p>
            <p><strong>Created At:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
            @if($order->completed_at)
                <p><strong>Completed At:</strong> {{ $order->completed_at->format('Y-m-d H:i') }}</p>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
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
                <td colspan="3" class="total">Total Amount:</td>
                <td>${{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($order->notes)
        <div class="notes">
            <h4>Notes:</h4>
            <p>{{ $order->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Order</button>
    </div>
</body>
</html> 