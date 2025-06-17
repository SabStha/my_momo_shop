@php
    $order = $payment->order;
    $user = $order->user;
    $method = $payment->paymentMethod;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .receipt-container { max-width: 480px; margin: 40px auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 2px 8px #e5e7eb; padding: 32px; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h2 { margin: 0; color: #4f46e5; }
        .details { margin-bottom: 16px; }
        .details dt { font-weight: bold; color: #374151; }
        .details dd { margin: 0 0 8px 0; color: #4b5563; }
        .amount { font-size: 1.5em; color: #16a34a; font-weight: bold; text-align: right; margin-top: 24px; }
        .footer { text-align: center; color: #6b7280; font-size: 0.9em; margin-top: 32px; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h2>Payment Receipt</h2>
            <div>#{{ $payment->id }}</div>
        </div>
        <dl class="details">
            <dt>Order Number:</dt>
            <dd>{{ $order->order_number ?? $order->id }}</dd>
            <dt>Customer:</dt>
            <dd>{{ $user->name ?? 'Guest' }}</dd>
            <dt>Payment Method:</dt>
            <dd>{{ $method->name }}</dd>
            <dt>Date:</dt>
            <dd>{{ $payment->created_at->format('M d, Y H:i') }}</dd>
            <dt>Status:</dt>
            <dd>{{ ucfirst($payment->status) }}</dd>
        </dl>
        <div class="amount">
            Amount Paid: {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
        </div>
        <div class="footer">
            Thank you for your payment!<br>
            {{ config('app.name') }}
        </div>
    </div>
</body>
</html> 