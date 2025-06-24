<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Report - {{ $type }} - {{ $month }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #4f46e5;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            color: #6b7280;
            font-size: 16px;
        }
        .report-info {
            margin-bottom: 30px;
        }
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-info td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }
        .report-info td:first-child {
            font-weight: bold;
            background-color: #f9fafb;
            width: 200px;
        }
        .summary-stats {
            margin-bottom: 30px;
        }
        .summary-stats h3 {
            color: #4f46e5;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .stats-grid .stat-item {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .stats-grid .stat-item:nth-child(2) {
            background-color: #fef2f2;
        }
        .stats-grid .stat-item:nth-child(3) {
            background-color: #fffbeb;
        }
        .stats-grid .stat-item:nth-child(4) {
            background-color: #f0fdf4;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
        }
        .trends-section {
            margin-bottom: 30px;
        }
        .trends-section h3 {
            color: #4f46e5;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .trends-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .trends-table th,
        .trends-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: center;
        }
        .trends-table th {
            background-color: #4f46e5;
            color: white;
            font-weight: bold;
        }
        .trends-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .discrepancies-section {
            margin-bottom: 30px;
        }
        .discrepancies-section h3 {
            color: #4f46e5;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .discrepancies-table {
            width: 100%;
            border-collapse: collapse;
        }
        .discrepancies-table th,
        .discrepancies-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        .discrepancies-table th {
            background-color: #4f46e5;
            color: white;
            font-weight: bold;
        }
        .discrepancies-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .no-data {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }
        .positive {
            color: #16a34a;
        }
        .negative {
            color: #dc2626;
        }
        .neutral {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Audit Report</h1>
        <h2>{{ ucfirst($type) }} Report - {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h2>
        @if($branch)
            <h3>{{ $branch->name }}</h3>
        @else
            <h3>All Branches</h3>
        @endif
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td>Report Type:</td>
                <td>{{ ucfirst($type) }} Audit Report</td>
            </tr>
            <tr>
                <td>Period:</td>
                <td>{{ \Carbon\Carbon::parse($month)->format('F Y') }}</td>
            </tr>
            <tr>
                <td>Branch:</td>
                <td>{{ $branch ? $branch->name : 'All Branches' }}</td>
            </tr>
            <tr>
                <td>Generated On:</td>
                <td>{{ now()->format('F d, Y \a\t H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-stats">
        <h3>Summary Statistics</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ number_format($auditData['total_items_checked'] ?? 0) }}</div>
                <div class="stat-label">Total Items Checked</div>
            </div>
            <div class="stat-item">
                <div class="stat-value negative">{{ number_format($auditData['total_discrepancies'] ?? 0) }}</div>
                <div class="stat-label">Total Discrepancies</div>
                <div class="stat-label">Rs. {{ number_format($auditData['total_discrepancy_value'] ?? 0, 2) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ number_format(($auditData['damaged_items'] ?? 0) + ($auditData['missing_items'] ?? 0)) }}</div>
                <div class="stat-label">Damaged/Missing</div>
                <div class="stat-label">{{ $auditData['damaged_items'] ?? 0 }} damaged, {{ $auditData['missing_items'] ?? 0 }} missing</div>
            </div>
            <div class="stat-item">
                <div class="stat-value positive">{{ number_format($auditData['matching_items'] ?? 0) }}</div>
                <div class="stat-label">Matching Items</div>
                <div class="stat-label">{{ number_format($auditData['total_value_checked'] ?? 0, 2) }} Rs. checked</div>
            </div>
        </div>
    </div>

    @if(isset($trends) && count($trends) > 0)
        <div class="trends-section">
            <h3>Monthly Trends (Last 6 Months)</h3>
            <table class="trends-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Items Checked</th>
                        <th>Discrepancies</th>
                        <th>Discrepancy Value</th>
                        <th>Damaged/Missing</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trends as $trend)
                        <tr>
                            <td>{{ $trend['month'] }}</td>
                            <td>{{ number_format($trend['total_items']) }}</td>
                            <td class="{{ $trend['discrepancies'] > 0 ? 'negative' : 'neutral' }}">
                                {{ number_format($trend['discrepancies']) }}
                            </td>
                            <td class="{{ $trend['discrepancy_value'] > 0 ? 'negative' : 'neutral' }}">
                                Rs. {{ number_format($trend['discrepancy_value'], 2) }}
                            </td>
                            <td>{{ number_format($trend['damaged_missing']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(isset($topDiscrepancies) && $topDiscrepancies->count() > 0)
        <div class="discrepancies-section">
            <h3>Top Discrepancies</h3>
            <table class="discrepancies-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Expected Quantity</th>
                        <th>Actual Quantity</th>
                        <th>Discrepancy</th>
                        <th>Value Impact</th>
                        <th>Last Checked</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topDiscrepancies as $discrepancy)
                        <tr>
                            <td>{{ $discrepancy->inventoryItem->name ?? 'Unknown Item' }}</td>
                            <td>{{ number_format($discrepancy->expected_quantity) }}</td>
                            <td>{{ number_format($discrepancy->actual_quantity) }}</td>
                            <td class="{{ $discrepancy->discrepancy_amount < 0 ? 'negative' : ($discrepancy->discrepancy_amount > 0 ? 'positive' : 'neutral') }}">
                                {{ $discrepancy->discrepancy_amount > 0 ? '+' : '' }}{{ number_format($discrepancy->discrepancy_amount) }}
                            </td>
                            <td class="{{ $discrepancy->discrepancy_value < 0 ? 'negative' : ($discrepancy->discrepancy_value > 0 ? 'positive' : 'neutral') }}">
                                {{ $discrepancy->discrepancy_value > 0 ? '+' : '' }}Rs. {{ number_format($discrepancy->discrepancy_value, 2) }}
                            </td>
                            <td>{{ $discrepancy->checked_at ? \Carbon\Carbon::parse($discrepancy->checked_at)->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="discrepancies-section">
            <h3>Top Discrepancies</h3>
            <div class="no-data">No discrepancies found for this period.</div>
        </div>
    @endif

    <div class="footer">
        <p><strong>{{ config('app.name') }}</strong></p>
        <p>This is a computer-generated audit report. Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
        <p>For questions or concerns, please contact your system administrator.</p>
    </div>
</body>
</html> 