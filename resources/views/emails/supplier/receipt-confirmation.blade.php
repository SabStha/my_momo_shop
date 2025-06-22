<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt Confirmation</title>
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
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-full {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
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
        .missing-items {
            background-color: #fee2e2;
            border: 1px solid #dc2626;
            color: #991b1b;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .missing-items h3 {
            margin-top: 0;
            color: #dc2626;
        }
        .received-items {
            background-color: #d1fae5;
            border: 1px solid #059669;
            color: #065f46;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .received-items h3 {
            margin-top: 0;
            color: #059669;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e3e3;
            color: #666;
            font-size: 14px;
        }
        .highlight {
            background-color: #fef3c7;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Momo Shop</div>
            <div class="order-number">Order #{{ $order->order_number }}</div>
            <p>Order Receipt Confirmation</p>
        </div>

        <p>Dear <strong>{{ $order->supplier->contact_person }}</strong>,</p>

        @if($isFullReceipt)
            <div class="status-badge status-full">
                ✅ FULL ORDER RECEIVED
            </div>
            <p><strong>Great news!</strong> We have successfully received the complete order as confirmed. All items have been received in full quantities.</p>
        @else
            <div class="status-badge status-partial">
                ⚠️ PARTIAL ORDER RECEIVED
            </div>
            <p><strong>Partial receipt confirmed.</strong> We have received some items from your order. Please review the details below for items that were not received in full quantities.</p>
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
            <div class="detail-row">
                <span><strong>Receipt Date:</strong></span>
                <span>{{ $receiptData['receipt_date'] ?? now()->format('M d, Y') }}</span>
            </div>
            @if($order->supplier_confirmed_at)
            <div class="detail-row">
                <span><strong>Supplier Confirmed:</strong></span>
                <span>{{ $order->supplier_confirmed_at->format('M d, Y H:i') }}</span>
            </div>
            @endif
        </div>

        @if($isFullReceipt)
            <div class="received-items">
                <h3>✅ All Items Received Successfully</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Ordered Qty</th>
                            <th>Received Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        @php
                            $receivedQty = $receiptData['received_quantities'][$item->id] ?? $item->quantity;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $item->item->name }}</strong><br>
                                <small style="color: #666;">{{ $item->item->sku }}</small>
                            </td>
                            <td>{{ $item->original_quantity ?? $item->quantity }} {{ $item->item->unit }}</td>
                            <td>{{ $receivedQty }} {{ $item->item->unit }}</td>
                            <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td>Rs. {{ number_format($receivedQty * $item->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="received-items">
                <h3>✅ Items Successfully Received</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Ordered Qty</th>
                            <th>Received Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        @php
                            $receivedQty = $receiptData['received_quantities'][$item->id] ?? 0;
                            $orderedQty = $item->original_quantity ?? $item->quantity;
                            $isComplete = $receivedQty >= $orderedQty;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $item->item->name }}</strong><br>
                                <small style="color: #666;">{{ $item->item->sku }}</small>
                            </td>
                            <td>{{ $orderedQty }} {{ $item->item->unit }}</td>
                            <td>{{ $receivedQty }} {{ $item->item->unit }}</td>
                            <td>
                                @if($isComplete)
                                    <span style="color: #059669;">✅ Complete</span>
                                @else
                                    <span style="color: #dc2626;">⚠️ Partial</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(count($missingItems) > 0)
            <div class="missing-items">
                <h3>❌ Missing Items - Action Required</h3>
                <p><strong>Please order the following missing items from another supplier:</strong></p>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Ordered Qty</th>
                            <th>Received Qty</th>
                            <th>Missing Qty</th>
                            <th>Unit Price</th>
                            <th>Missing Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missingItems as $missingItem)
                        <tr>
                            <td>
                                <strong>{{ $missingItem['name'] }}</strong><br>
                                <small style="color: #666;">{{ $missingItem['sku'] }}</small>
                            </td>
                            <td>{{ $missingItem['ordered_qty'] }} {{ $missingItem['unit'] }}</td>
                            <td>{{ $missingItem['received_qty'] }} {{ $missingItem['unit'] }}</td>
                            <td><span class="highlight">{{ $missingItem['missing_qty'] }} {{ $missingItem['unit'] }}</span></td>
                            <td>Rs. {{ number_format($missingItem['unit_price'], 2) }}</td>
                            <td><strong>Rs. {{ number_format($missingItem['total_missing_value'], 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <p style="margin-top: 15px;">
                    <strong>Total Missing Value:</strong> 
                    <span class="highlight">Rs. {{ number_format(collect($missingItems)->sum('total_missing_value'), 2) }}</span>
                </p>
            </div>
            @endif
        @endif

        @if(!empty($receiptData['receipt_notes']))
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #2563eb;">Receipt Notes:</h4>
            <p style="margin-bottom: 0;">{{ $receiptData['receipt_notes'] }}</p>
        </div>
        @endif

        <div class="footer">
            <p><strong>Momo Shop Inventory Management System</strong></p>
            <p>This receipt confirmation has been recorded in our system.</p>
            @if(!$isFullReceipt && count($missingItems) > 0)
            <p style="color: #dc2626; font-weight: bold;">⚠️ Please arrange for the missing items from another supplier.</p>
            @endif
        </div>
    </div>
</body>
</html> 