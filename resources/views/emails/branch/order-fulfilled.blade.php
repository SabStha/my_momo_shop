<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Fulfilled</title>
</head>
<body>
    <h2>Order Fulfilled Successfully</h2>
    <p>Dear {{ $branch->name }} Team,</p>
    <p>Great news! Your supply order has been processed and fulfilled by the Main Branch.</p>
    
    <h3>Order Details</h3>
    <ul>
        <li><strong>Order Number:</strong> {{ $order->order_number }}</li>
        <li><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</li>
        <li><strong>Fulfilled Date:</strong> {{ $order->received_at->format('M d, Y H:i') }}</li>
        <li><strong>Total Amount:</strong> Rs. {{ number_format($order->total_amount, 2) }}</li>
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
    
    <p><strong>Status:</strong> <span style="color: green; font-weight: bold;">FULFILLED</span></p>
    
    <p>The items have been added to the Main Branch inventory and are ready for distribution to your branch when needed.</p>
    
    <p>If you have any questions or need to arrange pickup/delivery, please contact the Main Branch directly.</p>
    
    <p>Thank you for using our centralized ordering system!</p>
    
    <p>Best regards,<br>
    Main Branch Team<br>
    Momo Shop</p>
    
    <hr>
    <p style="font-size: 12px; color: #666;">
        This is an automated notification. Please do not reply to this email.
    </p>
</body>
</html> 