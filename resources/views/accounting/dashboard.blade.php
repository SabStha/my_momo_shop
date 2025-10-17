@extends('layouts.investor')

@section('title', 'Accounting Dashboard')

@section('content')
<style>
/* Mobile-specific styles for accounting dashboard */
@media (max-width: 768px) {
    .mobile-summary-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-summary-card {
        padding: 1rem;
        border-radius: 0.75rem;
    }
    
    .mobile-summary-card .text-2xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    
    .mobile-chart-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .mobile-table-responsive table {
        min-width: 600px;
    }
}
</style>

<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Accounting Dashboard</h1>
                    <p class="text-gray-600">Financial expense tracking and analysis</p>
                </div>
                <div class="flex items-center space-x-4">
                    @role('admin')
                    <a href="{{ route('accounting.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Add Expense
                    </a>
                    @endrole
                    <a href="{{ route('accounting.spreadsheet') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        📊 Spreadsheet View
                    </a>
                    <a href="{{ route('accounting.index') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        View All
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mobile-summary-grid">
            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Total Expenses</div>
                        <div class="text-2xl font-bold text-gray-900">Rs {{ number_format($totalExpenses, 2) }}</div>
                    </div>
                    <div class="text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Total Transactions</div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($expenseCount) }}</div>
                    </div>
                    <div class="text-blue-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Categories</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $categoryBreakdown->count() }}</div>
                    </div>
                    <div class="text-green-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Avg per Transaction</div>
                        <div class="text-2xl font-bold text-gray-900">Rs {{ $expenseCount > 0 ? number_format($totalExpenses / $expenseCount, 2) : '0.00' }}</div>
                    </div>
                    <div class="text-purple-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 mobile-chart-grid">
            <!-- Category Breakdown -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Expenses by Category</h3>
                <div class="space-y-3">
                    @foreach($categoryBreakdown->sortByDesc('total') as $category => $data)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $loop->index % 2 == 0 ? '#3B82F6' : '#10B981' }}"></div>
                            <span class="text-sm font-medium text-gray-700">{{ $category }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">Rs {{ number_format($data['total'], 2) }}</div>
                            <div class="text-xs text-gray-500">{{ $data['percentage'] }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Method Breakdown -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
                <div class="space-y-3">
                    @foreach($paymentMethodBreakdown->sortByDesc('total') as $method => $data)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $loop->index % 2 == 0 ? '#F59E0B' : '#EF4444' }}"></div>
                            <span class="text-sm font-medium text-gray-700">{{ $method }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">Rs {{ number_format($data['total'], 2) }}</div>
                            <div class="text-xs text-gray-500">{{ $data['percentage'] }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Expenses Table -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Expenses</h3>
            </div>
            <div class="overflow-x-auto mobile-table-responsive">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentExpenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $expense->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                {{ $expense->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                Rs {{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $expense->paid_by }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($expense->status === 'approved') bg-green-100 text-green-800
                                    @elseif($expense->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Expenses -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Expenses by Amount</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($topExpenses as $expense)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">{{ $expense->description }}</div>
                            <div class="text-sm text-gray-500">{{ $expense->category }} • {{ $expense->date->format('M d, Y') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-gray-900">Rs {{ number_format($expense->amount, 2) }}</div>
                            <div class="text-sm text-gray-500">{{ $expense->paid_by }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Expense Trends</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($monthlyTrends as $monthData)
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-700">
                            {{ Carbon\Carbon::createFromFormat('Y-m', $monthData['month'])->format('F Y') }}
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">{{ $monthData['count'] }} transactions</div>
                            <div class="text-sm font-semibold text-gray-900">Rs {{ number_format($monthData['total'], 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @role('admin')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('accounting.create') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="text-green-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Add New Expense</h3>
                        <p class="text-gray-500">Record a new expense transaction</p>
                    </div>
                </div>
            </a>

        </div>
        @endrole
    </div>
</div>
@endsection
