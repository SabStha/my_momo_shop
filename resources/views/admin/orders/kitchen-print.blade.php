<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            background: white;
            color: black;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .order-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .items {
            margin-bottom: 20px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .item-name {
            font-weight: bold;
        }
        .item-quantity {
            font-weight: bold;
            color: #d32f2f;
        }
        .total {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .urgent {
            background: #ffebee;
            border: 2px solid #f44336;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
            color: #d32f2f;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍜 MOMO KITCHEN ORDER</h1>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="order-info">
        <h2>Order #{{ $order->order_number }}</h2>
        <div class="order-details">
            <div>
                <strong>Customer:</strong> {{ $order->customer_name }}<br>
                <strong>Phone:</strong> {{ $order->customer_phone }}<br>
                <strong>Type:</strong> {{ ucfirst($order->order_type) }}
            </div>
            <div>
                <strong>Order Time:</strong> {{ $order->created_at->format('H:i:s') }}<br>
                <strong>Payment:</strong> {{ ucfirst($order->payment_method) }}<br>
                <strong>Status:</strong> {{ ucfirst($order->status) }}
            </div>
        </div>
    </div>

    @if($order->order_type === 'online')
        <div class="urgent">
            ⚡ ONLINE ORDER - PRIORITY ⚡
        </div>
    @endif

    <div class="items">
        <h3>📋 ORDER ITEMS:</h3>
        @foreach($order->items as $item)
            <div class="item">
                <span class="item-name">{{ $item->product->name ?? $item->item_name }}</span>
                <span class="item-quantity">Qty: {{ $item->quantity }}</span>
            </div>
        @endforeach
    </div>

    <div class="total">
        <strong>TOTAL: Rs. {{ number_format($order->grand_total, 2) }}</strong>
    </div>

    @if($order->delivery_address)
        @php
            // Decode JSON address if it's a string
            $deliveryAddress = is_string($order->delivery_address) 
                ? json_decode($order->delivery_address, true) 
                : $order->delivery_address;
        @endphp
        
        @if($deliveryAddress && is_array($deliveryAddress))
            <div class="delivery-info">
                <h3>📍 DELIVERY ADDRESS:</h3>
                <p>
                    {{ $deliveryAddress['area_locality'] ?? '' }}<br>
                    Ward {{ $deliveryAddress['ward_number'] ?? '' }}, 
                    {{ $deliveryAddress['city'] ?? '' }}<br>
                    @if(!empty($deliveryAddress['building_name']))
                        {{ $deliveryAddress['building_name'] }}<br>
                    @endif
                    @if(!empty($deliveryAddress['detailed_directions']))
                        {{ $deliveryAddress['detailed_directions'] }}
                    @endif
                </p>
            </div>
        @endif
    @endif

    <div class="footer">
        <p>--- End of Order ---</p>
        <p>Printed at: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Print Order
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            ❌ Close
        </button>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>


