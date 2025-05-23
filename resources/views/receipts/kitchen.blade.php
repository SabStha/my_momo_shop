<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Receipt - Order #{{ $order->id }}</title>
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
        }
        .quantity {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="receipt">
        <div class="header">
            <h2>KITCHEN RECEIPT</h2>
            <p>Order #{{ $order->id }}</p>
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
                    <span class="quantity">{{ $item->quantity }}x</span>
                    {{ $item->product->name }}
                </div>
        @endforeach
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