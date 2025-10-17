@extends('layouts.admin')

@section('title', 'Investor Details - ' . $investor->name)

@section('content')
<div class="px-4 py-6 mx-auto max-w-7xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $investor->name }}</h1>
            <p class="text-sm text-gray-600">{{ $investor->email }} • {{ $investor->phone }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.investors.dashboard', $investor) }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white rounded-lg shadow hover:bg-cyan-700 transition duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('admin.investors.edit', $investor) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg shadow hover:bg-yellow-700 transition duration-200">
                Edit
            </a>
            <a href="{{ route('admin.investors.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition duration-200">
                Back
            </a>
        </div>
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

    <!-- Investor Details -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Investor Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-600">Name</label>
                    <p class="mt-1 text-gray-900">{{ $investor->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Email</label>
                    <p class="mt-1 text-gray-900">{{ $investor->email }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Phone</label>
                    <p class="mt-1 text-gray-900">{{ $investor->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Type</label>
                    <p class="mt-1 text-gray-900">{{ ucfirst($investor->investment_type) }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p class="mt-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{
                            $investor->status === 'active' ? 'bg-green-100 text-green-800' :
                            ($investor->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')
                        }}">
                            {{ ucfirst($investor->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Joined</label>
                    <p class="mt-1 text-gray-900">{{ $investor->created_at->format('M d, Y') }}</p>
                </div>
                @if($investor->company_name)
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-600">Company</label>
                    <p class="mt-1 text-gray-900">{{ $investor->company_name }}</p>
                </div>
                @endif
                @if($investor->notes)
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-600">Notes</label>
                    <p class="mt-1 text-gray-900">{{ $investor->notes }}</p>
                </div>
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

    <!-- Investments -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Investments</h2>
        </div>
        <div class="overflow-x-auto">
            @if($investor->investments->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ownership</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($investor->investments as $investment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $investment->branch->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rs {{ number_format($investment->investment_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $investment->ownership_percentage }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $investment->investment_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{
                                $investment->status === 'active' ? 'bg-green-100 text-green-800' :
                                'bg-gray-100 text-gray-800'
                            }}">
                                {{ ucfirst($investment->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-500 text-center py-8">No investments found</p>
            @endif
        </div>
    </div>

    <!-- Recent Payouts -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Payouts</h2>
        </div>
        <div class="overflow-x-auto">
            @if($recentPayouts->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentPayouts as $payout)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $payout->branch->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                            Rs {{ number_format($payout->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ucfirst($payout->payout_type) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payout->payout_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{
                                $payout->status === 'paid' ? 'bg-green-100 text-green-800' :
                                ($payout->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')
                            }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-500 text-center py-8">No payouts found</p>
            @endif
        </div>
    </div>
</div>
@endsection

