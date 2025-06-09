@component('mail::message')
# {{ $type === 'sent' ? 'New Order' : 'Order Update' }} #{{ $order->order_number }}

Dear {{ $order->supplier->name }},

@if($type === 'sent')
We are pleased to place the following order with your company. Please find the order details attached in the PDF.

@elseif($type === 'received')
We have received the following items from your delivery. Please find the details below:

@if(isset($additionalData['received_items']))
@foreach($additionalData['received_items'] as $item)
- {{ $item->item->name }}: {{ $item->actual_received_quantity }} {{ $item->item->unit }}
@endforeach
@endif

@if(isset($additionalData['notes']))
**Notes:** {{ $additionalData['notes'] }}
@endif
@endif

**Order Details:**
- Order Number: {{ $order->order_number }}
- Order Date: {{ $order->ordered_at->format('Y-m-d H:i') }}
- Total Amount: ${{ number_format($order->total_amount, 2) }}

@if($order->notes)
**Additional Notes:**
{{ $order->notes }}
@endif

@if($type === 'sent')
Please confirm receipt of this order and provide an estimated delivery date.

@component('mail::button', ['url' => config('app.url') . '/supplier/orders/' . $order->id])
View Order Details
@endcomponent
@endif

Thank you for your business.

Best regards,<br>
{{ config('app.name') }}
@endcomponent 