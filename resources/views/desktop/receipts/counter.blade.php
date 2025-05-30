<!DOCTYPE html>
<html>
<head>
    <title>Counter Receipt - Order #{{ $order->id }}</title>
    <meta charset="utf-8">
    <style>
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 5mm;
                font-family: 'Courier New', monospace;
                font-size: 12px;
            }
            .no-print {
                display: none;
            }
        }
        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .order-info {
            margin-bottom: 10px;
        }
        .items {
            margin-bottom: 10px;
        }
        .item {
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-price {
            text-align: right;
            margin-left: 10px;
        }
        .totals {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
<div class="receipt">
        <div class="header">
            <h2>COUNTER RECEIPT</h2>
            <p>Order #{{ $order->id }}</p>
            <p>{{ $order->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
        
        <div class="order-info">
            <p>Type: {{ $order->type }}</p>
            @if($order->table_number)
                <p>Table: {{ $order->table_number }}</p>
            @endif
        </div>

        <div class="items">
        @foreach($order->items as $item)
                <div class="item">
                    <div class="item-details">
                        <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
                    </div>
                    <div class="item-price">
                        {{ number_format($item->price * $item->quantity, 2) }}
                    </div>
                </div>
        @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax ({{ $order->tax_rate }}%):</span>
                <span>{{ number_format($order->tax_amount, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>Total:</span>
                <span>{{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Payment Method: {{ $order->payment_method }}</p>
            <p>Thank you for your order!</p>
        </div>

        <div class="no-print">
            <button onclick="window.print()">Print Receipt</button>
        </div>
</div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html> 