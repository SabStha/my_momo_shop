<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .logo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .order-number {
            font-weight: bold;
            font-size: 14px;
        }
        .date-time {
            color: #666;
        }
        .items {
            margin-bottom: 15px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .item-name {
            flex: 1;
        }
        .item-qty {
            margin: 0 10px;
        }
        .item-price {
            text-align: right;
            min-width: 60px;
        }
        .divider {
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .customer-info {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .receipt {
                border: none;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="logo">MoMo Shop</div>
            <div>Delicious Food & Drinks</div>
            <div>Thank you for your order!</div>
        </div>

        <div class="order-info">
            <div class="order-number">Order #{{ $order->order_number }}</div>
            <div class="date-time">{{ $order->created_at->format('M d, Y g:i A') }}</div>
            <div>Status: {{ ucfirst($order->status) }}</div>
            <div>Payment: {{ ucfirst($order->payment_status) }}</div>
        </div>

        <div class="items">
            @foreach($order->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->item_name }}</div>
                <div class="item-qty">x{{ $item->quantity }}</div>
                <div class="item-price">Rs. {{ number_format($item->subtotal, 2) }}</div>
            </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="totals">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>Rs. {{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="total-line">
                <span>Tax (13%):</span>
                <span>Rs. {{ number_format($order->tax_amount, 2) }}</span>
            </div>
            @if($order->discount_amount && $order->discount_amount > 0)
            <div class="total-line">
                <span>Discount:</span>
                <span>-Rs. {{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-line grand-total">
                <span>Total:</span>
                <span>Rs. {{ number_format($order->grand_total, 2) }}</span>
            </div>
        </div>

        @if($order->customer_name || $order->customer_phone)
        <div class="customer-info">
            @if($order->customer_name)
            <div>Customer: {{ $order->customer_name }}</div>
            @endif
            @if($order->customer_phone)
            <div>Phone: {{ $order->customer_phone }}</div>
            @endif
            @if($order->delivery_address)
            <div>Address: 
                @if(is_array($order->delivery_address))
                    @if(isset($order->delivery_address['building_name']))
                        {{ $order->delivery_address['building_name'] }},
                    @endif
                    @if(isset($order->delivery_address['area_locality']))
                        {{ $order->delivery_address['area_locality'] }},
                    @endif
                    @if(isset($order->delivery_address['city']))
                        {{ $order->delivery_address['city'] }}
                    @endif
                @else
                    {{ $order->delivery_address }}
                @endif
            </div>
            @endif
        </div>
        @endif

        <div class="footer">
            <div>Thank you for choosing MoMo Shop!</div>
            <div>Please visit us again</div>
            <div>For support: support@momoshop.com</div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html> 