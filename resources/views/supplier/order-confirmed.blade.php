<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Momo Shop</title>
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
            text-align: center;
        }
        .success-icon {
            font-size: 64px;
            color: #059669;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #d1fae5;
            border: 1px solid #059669;
            color: #065f46;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
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
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e3e3;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        
        <h1 style="color: #059669; margin-bottom: 10px;">Order Confirmed!</h1>
        
        <div class="success-message">
            <strong>{{ $message }}</strong>
        </div>

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
            <div class="detail-row">
                <span><strong>Confirmed At:</strong></span>
                <span><?php echo e($order->received_at ? $order->received_at->format('M d, Y H:i') : 'Not yet verified by admin'); ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Total Amount:</strong></span>
                <span><strong>Rs. {{ number_format($order->total_amount, 2) }}</strong></span>
            </div>
        </div>

        <p>Thank you for confirming receipt of this order. The branch has been notified and will proceed with distribution.</p>

        <div class="footer">
            <p><strong>Momo Shop Inventory Management System</strong></p>
            <p>This confirmation has been recorded in our system.</p>
        </div>
    </div>
</body>
</html> 