@extends('layouts.admin')

@section('title', 'Investor Dashboard - ' . $investor->name)

@section('content')
<div class="px-4 py-6 mx-auto max-w-7xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $investor->name }}'s Dashboard</h1>
            <p class="text-sm text-gray-600">{{ $investor->email }}</p>
        </div>
        <a href="{{ route('admin.investors.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Investors
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Total Investment</div>
            <div class="text-3xl font-bold text-gray-900">Rs {{ number_format($totalInvestment, 2) }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Total Payouts</div>
            <div class="text-3xl font-bold text-gray-900">Rs {{ number_format($totalPayouts, 2) }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-xs font-semibold text-cyan-600 uppercase tracking-wide mb-1">Current Value</div>
            <div class="text-3xl font-bold text-gray-900">Rs {{ number_format($currentValue, 2) }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-xs font-semibold {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }} uppercase tracking-wide mb-1">ROI</div>
            <div class="text-3xl font-bold {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($roi, 2) }}%</div>
        </div>
    </div>

    <!-- Investments & Payouts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Recent Investments -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Investments</h2>
            </div>
            <div class="p-6">
                @if($recentInvestments->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentInvestments as $investment)
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 last:border-0">
                            <div>
                                <div class="font-medium text-gray-900">{{ $investment->branch->name }}</div>
                                <div class="text-sm text-gray-500">{{ $investment->investment_date->format('M d, Y') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">Rs {{ number_format($investment->investment_amount, 2) }}</div>
                                <div class="text-sm text-gray-500">{{ $investment->ownership_percentage }}% ownership</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No investments yet</p>
                @endif
            </div>
        </div>

        <!-- Recent Payouts -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Payouts</h2>
            </div>
            <div class="p-6">
                @if($recentPayouts->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentPayouts as $payout)
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 last:border-0">
                            <div>
                                <div class="font-medium text-gray-900">{{ $payout->branch->name }}</div>
                                <div class="text-sm text-gray-500">{{ $payout->payout_date->format('M d, Y') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-green-600">Rs {{ number_format($payout->amount, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($payout->payout_type) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No payouts yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Branch Performance -->
    @if(isset($branchPerformance) && count($branchPerformance) > 0)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Branch Performance</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($branchPerformance as $branch)
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $branch->branch_name }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Investment:</span>
                            <span class="font-medium">Rs {{ number_format($branch->investment_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ownership:</span>
                            <span class="font-medium">{{ $branch->ownership_percentage }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monthly Revenue:</span>
                            <span class="font-medium text-green-600">Rs {{ number_format($branch->monthly_revenue, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Your Share:</span>
                            <span class="font-medium text-blue-600">Rs {{ number_format($branch->investor_share, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('admin.investors.show', $investor) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition duration-200">
            View Full Details
        </a>
        <a href="{{ route('admin.investors.edit', $investor) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg shadow hover:bg-yellow-700 transition duration-200">
            Edit Investor
        </a>
    </div>
</div>
@endsection

