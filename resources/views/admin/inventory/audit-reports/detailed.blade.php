@extends('layouts.admin')

@section('title', 'Detailed Audit Report')

@section('content')
<div class="container">
    <!-- Back Button -->
    <a href="{{ route('admin.inventory.audit-reports.index') }}" style="display:inline-block; margin-bottom: 1.5em; background:#e5e7eb; color:#374151; padding:8px 18px; border-radius:5px; text-decoration:none; font-weight:500; transition:background 0.2s; border:1px solid #d1d5db;">
        &larr; Back to Audit Reports
    </a>
    <h1>Detailed Audit Report</h1>
    <h2>{{ ucfirst($type) }} &mdash; {{ $branch ? $branch->name : 'All Branches' }} &mdash; {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h2>

    <!-- Summary Section -->
    <div class="summary">
        <div class="summary-card">
            <div class="summary-label">Total Items Checked</div>
            <div class="summary-value">{{ $checks->count() }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Discrepancies</div>
            <div class="summary-value">
                {{ $checks->where('discrepancy_amount', '!=', 0)->count() }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Value Checked</div>
            <div class="summary-value">
                Rs. {{ number_format($checks->sum('expected_value'), 2) }}
            </div>
        </div>
    </div>

    <!-- Table Section -->
    @if($checks->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Expected Qty</th>
                    <th>Actual Qty</th>
                    <th>Discrepancy</th>
                    <th>Value Impact</th>
                    <th>Checked By</th>
                    <th>Checked At</th>
                </tr>
            </thead>
            <tbody>
            @foreach($checks as $check)
                <tr>
                    <td>{{ $check->inventoryItem->name ?? 'Unknown' }}</td>
                    <td>{{ number_format($check->expected_quantity) }}</td>
                    <td>{{ number_format($check->actual_quantity) }}</td>
                    <td>
                        @php $d = $check->discrepancy_amount; @endphp
                        <span class="{{ $d > 0 ? 'discrepancy-pos' : ($d < 0 ? 'discrepancy-neg' : 'discrepancy-zero') }}">
                            {{ $d > 0 ? '+' : '' }}{{ $d }}
                        </span>
                    </td>
                    <td>
                        @php $v = $check->discrepancy_value; @endphp
                        <span class="{{ $v > 0 ? 'discrepancy-pos' : ($v < 0 ? 'discrepancy-neg' : 'discrepancy-zero') }}">
                            {{ $v > 0 ? '+' : '' }}Rs. {{ number_format($v, 2) }}
                        </span>
                    </td>
                    <td>{{ $check->user->name ?? 'N/A' }}</td>
                    <td>{{ $check->checked_at ? \Carbon\Carbon::parse($check->checked_at)->format('M d, Y H:i') : 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No audit checks found for this period.</div>
    @endif
</div>
@endsection

<style>
    .container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e5e7eb; padding: 32px; }
    h1 { color: #4f46e5; font-size: 2em; margin-bottom: 0.2em; }
    h2 { color: #6b7280; font-size: 1.2em; margin-bottom: 1.5em; }
    .summary { display: flex; gap: 2em; margin-bottom: 2em; }
    .summary-card { background: #f3f4f6; border-radius: 6px; padding: 1em 2em; text-align: center; flex: 1; }
    .summary-label { color: #6b7280; font-size: 0.95em; }
    .summary-value { font-size: 1.5em; font-weight: bold; color: #4f46e5; }
    table { width: 100%; border-collapse: collapse; margin-top: 1.5em; }
    th, td { padding: 10px 8px; border: 1px solid #e5e7eb; }
    th { background: #4f46e5; color: #fff; font-weight: 600; }
    tr:nth-child(even) { background: #f9fafb; }
    .discrepancy-pos { color: #16a34a; font-weight: bold; }
    .discrepancy-neg { color: #dc2626; font-weight: bold; }
    .discrepancy-zero { color: #6b7280; }
    .no-data { text-align: center; color: #6b7280; font-style: italic; padding: 2em 0; }
    @media print {
        .container { box-shadow: none; border-radius: 0; }
    }
</style> 