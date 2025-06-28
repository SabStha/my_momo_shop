@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Investment Leaderboard</h1>
            <p class="text-lg text-gray-600">See the top investors and recent investments in Ama Ko Shop.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Top Investors</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount (₹)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topInvestors as $i => $investor)
                        <tr>
                            <td class="px-4 py-2">{{ $i+1 }}</td>
                            <td class="px-4 py-2 font-semibold text-gray-900">{{ $investor->name }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $investor->address }}</td>
                            <td class="px-4 py-2 text-blue-700 font-bold">₹{{ number_format($investor->total_invested, 2) }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $investor->investment_date ? $investor->investment_date->format('Y-m-d') : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Investments</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount (₹)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentInvestments as $investment)
                        <tr>
                            <td class="px-4 py-2 font-semibold text-gray-900">{{ $investment->investor->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $investment->investor->address ?? '-' }}</td>
                            <td class="px-4 py-2 text-blue-700 font-bold">₹{{ number_format($investment->investment_amount, 2) }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $investment->investment_date ? $investment->investment_date->format('Y-m-d') : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 