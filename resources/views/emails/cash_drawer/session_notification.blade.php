@component('mail::message')
# Cash Drawer {{ ucfirst($eventType) }} Notification

**Branch:** {{ $session->branch->name ?? $session->branch_id }}
**User:** {{ $session->user->name ?? 'Unknown' }}
**Time:** {{ $eventType === 'opened' ? $session->opened_at : $session->closed_at }}

@if($eventType === 'opened')
## Opening Details
- **Opening Balance:** Rs {{ number_format($session->opening_balance, 2) }}
- **Opening Denominations:**
@foreach(($session->opening_denominations ?? []) as $denom => $count)
    - Rs {{ $denom }}: {{ $count }}
@endforeach
@else
## Closing Details
- **Opening Balance:** Rs {{ number_format($session->opening_balance, 2) }}
- **Closing Balance:** Rs {{ number_format($session->closing_balance, 2) }}
- **Discrepancy:** <strong>{{ $session->discrepancy >= 0 ? '+' : '' }}Rs {{ number_format($session->discrepancy, 2) }}</strong>
- **Session Duration:** {{ $session->session_duration }} minutes
- **Closing Denominations:**
@foreach(($session->closing_denominations ?? []) as $denom => $count)
    - Rs {{ $denom }}: {{ $count }}
@endforeach
@if($summary)
---
### Session Summary
- **Total Sales:** Rs {{ number_format($summary['total_sales'], 2) }} ({{ $summary['total_transactions'] }} transactions)
- **Cash Sales:** Rs {{ number_format($summary['cash_sales'], 2) }} ({{ $summary['cash_transactions'] }} transactions)
- **Opened By:** {{ $summary['opened_by'] }}
- **Opened At:** {{ $summary['opened_at'] }}
- **Closed At:** {{ $summary['closed_at'] }}
- **Payment Method Breakdown:**
@foreach($summary['payment_methods'] as $method)
    - {{ ucfirst($method->payment_method) }}: Rs {{ number_format($method->total, 2) }} ({{ $method->count }} txns)
@endforeach
@endif
@endif

@if($session->notes)
---
**Notes:**
{{ $session->notes }}
@endif

Thanks,
{{ config('app.name') }}
@endcomponent
