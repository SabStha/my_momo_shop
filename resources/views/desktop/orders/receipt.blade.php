<!DOCTYPE html>
<html>
<head>
    <title>Receipt - Order #{{ $order->order_number }}</title>
    <style>
        @media print {
            body {
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.2;
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
            .receipt {
                width: 80mm;
                margin: 0 auto;
            }
        }
        .receipt {
            width: 80mm;
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .order-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .items {
            margin-bottom: 10px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-price {
            text-align: right;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .grand-total {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 0.9em;
        }
        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>MOMO SHOP</h2>
            <p>Order #{{ $order->order_number }}</p>
        </div>

        <div class="order-info">
            <p><strong>Type:</strong> {{ ucfirst($order->type) }}</p>
            @if($order->type === 'dine_in' && $order->table)
                <p><strong>Table:</strong> {{ $order->table->name }}</p>
            @endif
            <p><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
        </div>

        <div class="items">
            @foreach($order->items as $item)
                <div class="item">
                    <div class="item-details">
                        {{ $item->quantity }}x {{ $item->product->name }}
                    </div>
                    <div class="item-price">
                        {{ number_format($item->subtotal, 2) }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax (13%):</span>
                <span>{{ number_format($order->tax_amount, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>Total:</span>
                <span>{{ number_format($order->grand_total, 2) }}</span>
            </div>
            @if($order->payment_method === 'cash')
                <div class="total-row">
                    <span>Amount Received:</span>
                    <span>{{ number_format($order->amount_received, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Change:</span>
                    <span>{{ number_format($order->change, 2) }}</span>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>Payment Method: {{ strtoupper($order->payment_method) }}</p>
            <p>Thank you for your order!</p>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">Print Receipt</button>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html> 