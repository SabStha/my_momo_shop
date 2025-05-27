<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Receipt - Order #{{ $order->order_number }}</title>
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
            margin-bottom: 5px;
        }
        .quantity {
            font-weight: bold;
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
            <h2>KITCHEN ORDER</h2>
            <p>Order #{{ $order->order_number }}</p>
        </div>

        <div class="order-info">
            <p><strong>Type:</strong> {{ ucfirst($order->type) }}</p>
            @if($order->type === 'dine_in' && $order->table)
                <p><strong>Table:</strong> {{ $order->table->name }}</p>
            @endif
            <p><strong>Time:</strong> {{ $order->created_at->format('H:i:s') }}</p>
        </div>

        <div class="items">
            @foreach($order->items as $item)
                <div class="item">
                    <span class="quantity">{{ $item->quantity }}x</span>
                    {{ $item->product->name }}
                </div>
            @endforeach
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