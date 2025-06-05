@component('mail::message')
# {{ $type === 'received' ? 'Order Received' : 'New Order' }}

@if($type === 'received')
Dear {{ $order->supplier->name }},

We have received the following items from your recent order:

@component('mail::table')
| Item | Quantity | Unit |
|:-----|:---------|:-----|
@foreach($additionalData['received_items'] as $item)
| {{ $item->inventoryItem->name }} | {{ $item->actual_received_quantity }} | {{ $item->inventoryItem->unit }} |
@endforeach
@endcomponent

@if($additionalData['notes'])
**Notes:**
{{ $additionalData['notes'] }}
@endif

Thank you for your service.

@else
Dear {{ $order->supplier->name }},

We have placed a new order with you. Please find the details below:

**Order Number:** {{ $order->order_number }}  
**Order Date:** {{ $order->ordered_at->format('M d, Y') }}

@component('mail::table')
| Item | Quantity | Unit | Unit Price | Total |
|:-----|:---------|:-----|:-----------|:------|
@foreach($order->items as $item)
| {{ $item->inventoryItem->name }} | {{ $item->quantity }} | {{ $item->inventoryItem->unit }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->total_price, 2) }} |
@endforeach
@endcomponent

**Total Amount:** ${{ number_format($order->total_amount, 2) }}

@if($order->notes)
**Notes:**
{{ $order->notes }}
@endif

Please process this order at your earliest convenience.

@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent 