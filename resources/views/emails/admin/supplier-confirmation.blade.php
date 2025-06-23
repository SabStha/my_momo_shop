<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Confirmation Notification</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { background: #fff; max-width: 600px; margin: 30px auto; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; }
        .order-number { background: #2563eb; color: #fff; padding: 8px 18px; border-radius: 5px; font-weight: bold; display: inline-block; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 10px 0; }
        .status-full { background: #d1fae5; color: #065f46; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th, .items-table td { padding: 10px; border-bottom: 1px solid #e3e3e3; text-align: left; }
        .items-table th { background: #f8f9fa; }
        .highlight { background: #fef3c7; padding: 2px 4px; border-radius: 3px; }
        .footer { text-align: center; color: #666; font-size: 13px; margin-top: 30px; border-top: 1px solid #e3e3e3; padding-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="order-number">Order #{{ $order->order_number }}</div>
        <h2>Supplier Confirmation Notification</h2>
        @if($isFullConfirmation)
            <div class="status-badge status-full">✅ SUPPLIER CONFIRMED FULL ORDER</div>
        @else
            <div class="status-badge status-partial">⚠️ SUPPLIER CONFIRMED PARTIAL ORDER</div>
        @endif
    </div>
    <p>Dear Admin,</p>
    @if($isFullConfirmation)
        <p>The supplier <strong>{{ $order->supplier->name }}</strong> has confirmed the <strong>full order</strong> for <strong>{{ $order->branch->name }}</strong>. All items and quantities are available and will be delivered as requested.</p>
    @else
        <p>The supplier <strong>{{ $order->supplier->name }}</strong> has <strong>partially confirmed</strong> the order for <strong>{{ $order->branch->name }}</strong>. Some items/quantities cannot be fulfilled. Please review the details below and consider reordering missing quantities from another supplier.</p>
        <h3>Items Not Fully Confirmed</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Ordered Qty</th>
                    <th>Supplier Confirmed Qty</th>
                    <th>Missing Qty</th>
                </tr>
            </thead>
            <tbody>
            @foreach($partialItems as $item)
                <tr>
                    <td><strong>{{ $item['name'] }}</strong><br><small style="color:#666;">{{ $item['sku'] }}</small></td>
                    <td>{{ $item['ordered_qty'] }} {{ $item['unit'] }}</td>
                    <td>{{ $item['confirmed_qty'] }} {{ $item['unit'] }}</td>
                    <td><span class="highlight">{{ $item['ordered_qty'] - $item['confirmed_qty'] }} {{ $item['unit'] }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p style="color: #92400e; font-weight: bold;">⚠️ Please arrange for the missing items as soon as possible.</p>
    @endif
    <div class="footer">
        <p><strong>Momo Shop Inventory Management System</strong></p>
        <p>This is an automated notification. Please do not reply to this email.</p>
    </div>
</div>
</body>
</html> 