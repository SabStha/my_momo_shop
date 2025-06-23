<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Supply Order from Branch</title>
</head>
<body>
    <h2>New Supply Order Request</h2>
    <p>Dear Main Branch,</p>
    <p>The branch <strong>{{ $branch->name }}</strong> has created a new supply order that requires your attention.</p>
    <h3>Order Details</h3>
    <ul>
        <li><strong>Order Number:</strong> {{ $order->order_number }}</li>
        <li><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</li>
        <li><strong>Notes:</strong> {{ $order->notes }}</li>
    </ul>
    <h4>Order Items</h4>
    <table border="1" cellpadding="5" cellspacing="0">
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
                <td>{{ $item->item->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                <td>Rs. {{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Total Amount:</strong> Rs. {{ number_format($order->total_amount, 2) }}</p>
    <p>Please review and process this order in the admin dashboard.</p>
    <p>Thank you,<br>Momo Shop Inventory System</p>
</body>
</html> 