@extends('layouts.investor')

@section('title', 'Investment Statement')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Investment Statement</h1>
                    <p class="text-gray-600">{{ $startDate->format('F Y') }} - {{ $endDate->format('F Y') }}</p>
                </div>
                <button onclick="window.print()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    🖨️ Print/Download PDF
                </button>
            </div>
            
            <div class="border-t pt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Investor Name</p>
                        <p class="font-semibold">{{ $investor->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-semibold">{{ $investor->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Statement Date</p>
                        <p class="font-semibold">{{ now()->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Investor ID</p>
                        <p class="font-semibold">#{{ $investor->id }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Total Investment</div>
                <div class="text-2xl font-bold text-blue-600">Rs {{ number_format($totalInvestment, 2) }}</div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Total Payouts</div>
                <div class="text-2xl font-bold text-green-600">Rs {{ number_format($totalPayouts, 2) }}</div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">ROI</div>
                <div class="text-2xl font-bold {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($roi, 2) }}%</div>
            </div>
        </div>

        <!-- Monthly Payouts -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-900">Payout History</h2>
            </div>
            <div class="p-6">
                @if($monthlyPayouts->count() > 0)
                    @foreach($monthlyPayouts as $month => $payouts)
                    <div class="mb-6 last:mb-0">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ $month }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Branch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payouts as $payout)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $payout->payout_date->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $payout->branch->name }}</td>
                                        <td class="px-4 py-2 text-sm">{{ ucfirst($payout->payout_type) }}</td>
                                        <td class="px-4 py-2 text-sm text-right font-semibold text-green-600">Rs {{ number_format($payout->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-gray-50 font-semibold">
                                        <td colspan="3" class="px-4 py-2 text-sm">{{ $month }} Total</td>
                                        <td class="px-4 py-2 text-sm text-right text-green-600">Rs {{ number_format($payouts->sum('amount'), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center py-8">No payouts in the selected period.</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4 mb-8 print:hidden">
            <a href="{{ route('investor.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    .print\:hidden {
        display: none !important;
    }
    body {
        background: white;
    }
}
</style>
@endsection

