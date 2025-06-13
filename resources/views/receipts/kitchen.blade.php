<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
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
        .notes {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        @media print {
            body {
                width: 80mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>KITCHEN ORDER</h2>
        <p>Order #{{ $order->order_number }}</p>
        <p>{{ $order->created_at->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="order-info">
        <p><strong>Type:</strong> {{ ucfirst($order->order_type) }}</p>
        @if($order->table)
            <p><strong>Table:</strong> {{ $order->table->name }}</p>
        @endif
    </div>

    <div class="items">
        <h3>ITEMS:</h3>
        @foreach($order->items as $item)
            <div class="item">
                <span class="quantity">{{ $item->quantity }}x</span>
                {{ $item->item_name }}
                @if($item->notes)
                    <br><small>Note: {{ $item->notes }}</small>
                @endif
            </div>
        @endforeach
    </div>

    <div class="notes">
        <p><strong>Special Instructions:</strong></p>
        <p>{{ $order->notes ?? 'No special instructions' }}</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Kitchen Order
        </button>
    </div>

    <script>
        // Automatically trigger print when the page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500); // Add a small delay to ensure the page is fully loaded
        };
    </script>
</body>
</html> 