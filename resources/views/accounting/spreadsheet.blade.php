@extends('layouts.investor')

@section('title', 'Expense Spreadsheet')

@section('content')
<style>
/* Excel-like styling */
.excel-table {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border-collapse: collapse;
    width: 100%;
    background: white;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.excel-table th {
    background: #4472C4;
    color: white;
    font-weight: 600;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #8FA4D3;
    font-size: 14px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.excel-table td {
    padding: 10px 8px;
    border: 1px solid #D9D9D9;
    font-size: 13px;
    vertical-align: top;
}

.excel-table tbody tr:nth-child(even) {
    background-color: #F2F2F2;
}

.excel-table tbody tr:nth-child(odd) {
    background-color: white;
}

.excel-table tbody tr:hover {
    background-color: #E7F3FF;
}

/* Excel-like column styling */
.col-date { width: 100px; text-align: center; }
.col-type { width: 80px; text-align: center; }
.col-category { width: 120px; }
.col-description { width: 300px; }
.col-method { width: 100px; }
.col-amount { width: 120px; text-align: right; font-weight: 600; }
.col-paid-by { width: 100px; }

/* Total row styling */
.total-row {
    background: #D9E2F3 !important;
    font-weight: bold;
    border-top: 2px solid #4472C4;
}

.total-row td {
    font-weight: bold;
    font-size: 14px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .excel-table {
        min-width: 800px;
    }
    
    .excel-table th,
    .excel-table td {
        padding: 8px 6px;
        font-size: 12px;
    }
    
    .col-description {
        width: 250px;
    }
}

/* Excel-like filters */
.excel-filters {
    background: #F8F9FA;
    border: 1px solid #D9D9D9;
    border-bottom: none;
    padding: 15px;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.excel-filter-group {
    display: flex;
    align-items: center;
    gap: 5px;
}

.excel-filter-group label {
    font-weight: 600;
    font-size: 12px;
    color: #333;
}

.excel-filter-group select,
.excel-filter-group input {
    border: 1px solid #D9D9D9;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}

/* Excel-like toolbar */
.excel-toolbar {
    background: #F8F9FA;
    border: 1px solid #D9D9D9;
    border-bottom: none;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.excel-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.excel-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.excel-btn {
    background: #4472C4;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 3px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
}

.excel-btn:hover {
    background: #365899;
}

.excel-btn.secondary {
    background: #6C757D;
}

.excel-btn.secondary:hover {
    background: #545B62;
}

/* Summary cards */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.summary-card {
    background: white;
    border: 1px solid #D9D9D9;
    padding: 15px;
    border-radius: 4px;
}

.summary-card h4 {
    margin: 0 0 8px 0;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-card .value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

.summary-card .label {
    font-size: 11px;
    color: #999;
    margin: 4px 0 0 0;
}
</style>

<div class="min-h-screen bg-gray-50">
    <!-- Excel-like Toolbar -->
    <div class="excel-toolbar">
        <h1 class="excel-title">📊 Expense Spreadsheet</h1>
        <div class="excel-actions">
            <button class="excel-btn secondary" onclick="exportToExcel()">
                📥 Export Excel
            </button>
            <button class="excel-btn secondary" onclick="printTable()">
                🖨️ Print
            </button>
            <a href="{{ route('accounting.dashboard') }}" class="excel-btn secondary">
                📈 Dashboard
            </a>
            <a href="{{ route('accounting.create') }}" class="excel-btn">
                ➕ Add Expense
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="bg-white p-6 border border-gray-200">
        <div class="summary-cards">
            <div class="summary-card">
                <h4>Total Expenses</h4>
                <p class="value">Rs {{ number_format($totalExpenses, 2) }}</p>
                <p class="label">{{ $expenseCount }} transactions</p>
            </div>
            <div class="summary-card">
                <h4>Average per Transaction</h4>
                <p class="value">Rs {{ $expenseCount > 0 ? number_format($totalExpenses / $expenseCount, 2) : '0.00' }}</p>
                <p class="label">Per expense</p>
            </div>
            <div class="summary-card">
                <h4>Categories</h4>
                <p class="value">{{ $categoryBreakdown->count() }}</p>
                <p class="label">Different types</p>
            </div>
            <div class="summary-card">
                <h4>Payment Methods</h4>
                <p class="value">{{ $paymentMethodBreakdown->count() }}</p>
                <p class="label">Different methods</p>
            </div>
        </div>
    </div>

    <!-- Excel-like Filters -->
    <div class="excel-filters">
        <form method="GET" action="{{ route('accounting.spreadsheet') }}" class="flex flex-wrap gap-4 items-center w-full">
            <div class="excel-filter-group">
                <label>Category:</label>
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="excel-filter-group">
                <label>Payment Method:</label>
                <select name="payment_method" onchange="this.form.submit()">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                            {{ $method }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="excel-filter-group">
                <label>Paid By:</label>
                <select name="paid_by" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach($paidByOptions as $person)
                        <option value="{{ $person }}" {{ request('paid_by') == $person ? 'selected' : '' }}>
                            {{ $person }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="excel-filter-group">
                <label>From:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>

            <div class="excel-filter-group">
                <label>To:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>

            <div class="excel-filter-group">
                <label>Search:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..." style="width: 200px;">
                <button type="submit" class="excel-btn">🔍</button>
            </div>

            <div class="excel-filter-group">
                <a href="{{ route('accounting.spreadsheet') }}" class="excel-btn secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Excel-like Table -->
    <div class="table-container">
        <table class="excel-table">
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-type">Type</th>
                    <th class="col-category">Category</th>
                    <th class="col-description">Description</th>
                    <th class="col-method">Payment Method</th>
                    <th class="col-amount">Amount (NPR)</th>
                    <th class="col-paid-by">Paid By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td class="col-date">{{ $expense->date->format('d/m/Y') }}</td>
                    <td class="col-type">{{ $expense->transaction_type }}</td>
                    <td class="col-category">{{ $expense->category }}</td>
                    <td class="col-description">{{ $expense->description }}</td>
                    <td class="col-method">{{ $expense->payment_method }}</td>
                    <td class="col-amount">Rs {{ number_format($expense->amount, 2) }}</td>
                    <td class="col-paid-by">{{ $expense->paid_by }}</td>
                </tr>
                @endforeach
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="5" style="text-align: center; font-weight: bold;">TOTAL</td>
                    <td class="col-amount">Rs {{ number_format($totalExpenses, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($expenses->hasPages())
    <div class="bg-white p-4 border border-gray-200 border-t-0">
        {{ $expenses->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<script>
// Excel-like functionality
function exportToExcel() {
    // Create a simple CSV export
    let csv = 'Date,Type,Category,Description,Payment Method,Amount (NPR),Paid By\n';
    
    @foreach($expenses as $expense)
    csv += '{{ $expense->date->format('d/m/Y') }},{{ $expense->transaction_type }},{{ $expense->category }},{{ addslashes($expense->description) }},{{ $expense->payment_method }},{{ $expense->amount }},{{ $expense->paid_by }}\n';
    @endforeach
    
    csv += 'TOTAL,,,,,{{ $totalExpenses }},\n';
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'expenses_{{ date('Y-m-d') }}.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function printTable() {
    window.print();
}

// Excel-like keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+P for print
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printTable();
    }
    
    // Ctrl+E for export
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        exportToExcel();
    }
});

// Auto-submit form on filter change
document.querySelectorAll('select[name="category"], select[name="payment_method"], select[name="paid_by"]').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>

<!-- Print Styles -->
<style media="print">
    .excel-toolbar,
    .excel-filters,
    .excel-actions,
    .summary-cards {
        display: none !important;
    }
    
    .excel-table {
        box-shadow: none !important;
    }
    
    .excel-table th {
        background: #4472C4 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    body {
        background: white !important;
    }
</style>
@endsection
