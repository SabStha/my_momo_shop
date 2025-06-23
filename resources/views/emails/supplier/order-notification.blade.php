<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Inventory Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e3e3e3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .order-number {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .order-details h3 {
            margin-top: 0;
            color: #2563eb;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e3e3e3;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e3e3e3;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-success {
            background-color: #059669;
            color: white;
        }
        .btn-success:hover {
            background-color: #047857;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e3e3;
            color: #666;
            font-size: 14px;
        }
        .urgent {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Momo Shop</div>
            <div class="order-number">Order #{{ $order->order_number }}</div>
            <p>New Inventory Order Received</p>
        </div>

        <p>Dear <strong>{{ $supplier->contact_person }}</strong>,</p>

        <p>We have received a new inventory order from <strong>{{ $order->branch->name }}</strong> that requires your attention.</p>

        @if($order->expected_delivery)
        <div class="urgent">
            <strong>⚠️ Expected Delivery:</strong> {{ \Carbon\Carbon::parse($order->expected_delivery)->format('M d, Y') }}
        </div>
        @endif

        <div class="order-details">
            <h3>Order Information</h3>
            <div class="detail-row">
                <span><strong>Order Number:</strong></span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Branch:</strong></span>
                <span>{{ $order->branch->name }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Order Date:</strong></span>
                <span>{{ $order->created_at->format('M d, Y H:i') }}</span>
            </div>
            @if($order->expected_delivery)
            <div class="detail-row">
                <span><strong>Expected Delivery:</strong></span>
                <span>{{ \Carbon\Carbon::parse($order->expected_delivery)->format('M d, Y') }}</span>
            </div>
            @endif
            @if($order->notes)
            <div class="detail-row">
                <span><strong>Notes:</strong></span>
                <span>{{ $order->notes }}</span>
            </div>
            @endif
        </div>

        <h3>Order Items</h3>
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
                    <td>
                        <strong>{{ $item->item->name }}</strong><br>
                        <small style="color: #666;">{{ $item->item->sku }}</small>
                    </td>
                    <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                    <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td>Rs. {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>Total Amount:</strong></td>
                    <td><strong>Rs. {{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="action-buttons">
            <a href="{{ route('supplier.order.view', ['order' => $order->id, 'token' => hash('sha256', $order->id . $order->created_at . config('app.key'))]) }}" class="btn btn-primary">
                📋 View Order Details & Manage
            </a>
        </div>

        <p><strong>Please review this order and take one of the following actions:</strong></p>
        <ul style="margin: 20px 0; padding-left: 20px;">
            <li><strong>✅ Confirm Full Order:</strong> If you have all items in stock</li>
            <li><strong>⚠️ Partial Confirmation:</strong> If you can only provide some items or quantities</li>
            <li><strong>❌ Reject Order:</strong> If you cannot fulfill this order</li>
        </ul>

        <p>Click the "View Order Details & Manage" button above to access these options.</p>

        <p>If you have any questions or need to discuss delivery arrangements, please contact us immediately.</p>

        <div class="footer">
            <p><strong>Momo Shop Inventory Management System</strong></p>
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p>If you need assistance, please contact the main branch directly.</p>
        </div>
    </div>
</body>
</html> 