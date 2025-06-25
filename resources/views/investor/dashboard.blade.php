@extends('layouts.app')

@section('title', 'Investor Dashboard')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Investor Dashboard</h1>
                    <p class="text-gray-600">Welcome back, {{ $investor->name }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('investor.profile') }}" class="text-blue-600 hover:text-blue-800">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Total Investment</div>
                        <div class="text-2xl font-bold text-gray-900">Rs {{ number_format($totalInvestment, 2) }}</div>
                    </div>
                    <div class="text-blue-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Total Payouts</div>
                        <div class="text-2xl font-bold text-gray-900">Rs {{ number_format($totalPayouts, 2) }}</div>
                    </div>
                    <div class="text-green-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Current Value</div>
                        <div class="text-2xl font-bold text-gray-900">Rs {{ number_format($currentValue, 2) }}</div>
                    </div>
                    <div class="text-purple-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">ROI</div>
                        <div class="text-2xl font-bold {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($roi, 2) }}%
                        </div>
                    </div>
                    <div class="{{ $roi >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW FEATURES SECTION -->
        
        <!-- 1. Projected Earnings Calculator -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">💰 Projected Earnings Calculator</h2>
                <p class="text-sm text-gray-600">Simulate your returns with different scenarios</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="font-medium text-blue-900 mb-2">Current Monthly Payout</h3>
                        <div class="text-2xl font-bold text-blue-600">Rs {{ number_format($monthlyPayout, 2) }}</div>
                        <p class="text-sm text-blue-700 mt-1">Based on current performance</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="font-medium text-green-900 mb-2">With 20% Growth</h3>
                        <div class="text-2xl font-bold text-green-600">Rs {{ number_format($monthlyPayout * 1.2, 2) }}</div>
                        <p class="text-sm text-green-700 mt-1">If sales grow by 20%</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h3 class="font-medium text-purple-900 mb-2">With Reinvestment</h3>
                        <div class="text-2xl font-bold text-purple-600">Rs {{ number_format($monthlyPayout * 1.5, 2) }}</div>
                        <p class="text-sm text-purple-700 mt-1">If you reinvest payouts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Live Branch Performance Feed -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">📊 Live Branch Performance</h2>
                <p class="text-sm text-gray-600">Real-time performance indicators</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($liveBranchPerformance as $branch)
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-gray-900">{{ $branch['branch_name'] }}</h3>
                            <div class="w-4 h-4 rounded-full 
                                @if($branch['status'] === 'green') bg-green-500
                                @elseif($branch['status'] === 'yellow') bg-yellow-500
                                @else bg-red-500
                                @endif">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Today's Sales:</span>
                                <span class="font-medium">Rs {{ number_format($branch['today_sales'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Break-even:</span>
                                <span class="font-medium">Rs {{ number_format($branch['break_even_point'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Change:</span>
                                <span class="font-medium {{ $branch['change_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($branch['change_percentage'], 1) }}%
                                </span>
                            </div>
                        </div>
                        <button class="w-full mt-3 text-sm text-blue-600 hover:text-blue-800">View Details</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Investment Timeline -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">📅 Investment Timeline</h2>
                <p class="text-sm text-gray-600">Track your investment journey milestones</p>
            </div>
            <div class="p-6">
                @foreach($investmentTimeline as $timeline)
                <div class="mb-8">
                    <h3 class="font-medium text-gray-900 mb-4">{{ $timeline['branch_name'] }} - Rs {{ number_format($timeline['investment_amount'], 2) }}</h3>
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        @foreach($timeline['milestones'] as $milestone)
                        <div class="relative flex items-center mb-4">
                            <div class="absolute left-2 w-4 h-4 rounded-full 
                                @if($milestone['status'] === 'completed') bg-green-500
                                @else bg-gray-300
                                @endif">
                            </div>
                            <div class="ml-8">
                                <div class="font-medium text-gray-900">{{ $milestone['title'] }}</div>
                                <div class="text-sm text-gray-500">{{ $milestone['date'] }}</div>
                                <div class="text-sm text-gray-600">{{ $milestone['description'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 4. Branch Updates Feed -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">📰 Branch Updates</h2>
                <p class="text-sm text-gray-600">Latest news and updates from your branches</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($branchUpdates as $update)
                    <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl">{{ $update['icon'] }}</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-gray-900">{{ $update['title'] }}</h4>
                                <span class="text-sm text-gray-500">{{ $update['date'] }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $update['content'] }}</p>
                            <div class="text-xs text-blue-600 mt-2">{{ $update['branch_name'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 5. Risk Alerts -->
        @if(count($riskAlerts) > 0)
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">⚠️ Risk Alerts</h2>
                <p class="text-sm text-gray-600">Important updates and alerts</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($riskAlerts as $alert)
                    <div class="flex items-start space-x-3 p-4 
                        @if($alert['severity'] === 'high') bg-red-50 border border-red-200
                        @elseif($alert['severity'] === 'medium') bg-yellow-50 border border-yellow-200
                        @else bg-blue-50 border border-blue-200
                        @endif rounded-lg">
                        <div class="text-xl">
                            @if($alert['severity'] === 'high') 🔴
                            @elseif($alert['severity'] === 'medium') 🟡
                            @else 🔵
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $alert['title'] }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $alert['message'] }}</p>
                            <div class="text-xs text-gray-500 mt-2">{{ $alert['date'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- 6. Impact Tracker -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">🐾 Impact Tracker</h2>
                <p class="text-sm text-gray-600">Your social contribution through investments</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ number_format($impactStats['monthly_donation'], 2) }}</div>
                        <div class="text-sm text-gray-600">Monthly Donation (Rs)</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $impactStats['donation_percentage'] }}%</div>
                        <div class="text-sm text-gray-600">Of Sales Donated</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ number_format($impactStats['plates_funded']) }}</div>
                        <div class="text-sm text-gray-600">Plates Funded</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($impactStats['dogs_saved']) }}</div>
                        <div class="text-sm text-gray-600">Dogs Saved</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Referral & Reinvestment Tools -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">🔗 Referral & Reinvestment</h2>
                <p class="text-sm text-gray-600">Grow your network and reinvest earnings</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="font-medium text-blue-900 mb-3">Refer an Investor</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total Referrals:</span>
                                <span class="font-medium">{{ $referralStats['total_referrals'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Successful:</span>
                                <span class="font-medium">{{ $referralStats['successful_referrals'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Earnings:</span>
                                <span class="font-medium">Rs {{ number_format($referralStats['referral_earnings'], 2) }}</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <input type="text" value="{{ $referralStats['referral_link'] }}" readonly class="flex-1 text-sm border rounded px-2 py-1">
                            <button class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Copy</button>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="font-medium text-green-900 mb-3">Reinvestment</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Wallet Balance:</span>
                                <span class="font-medium">Rs {{ number_format($referralStats['wallet_balance'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Opportunities:</span>
                                <span class="font-medium">{{ $referralStats['reinvestment_opportunities'] }}</span>
                            </div>
                        </div>
                        <button class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Reinvest Now</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Downloadable Statement Generator -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">📄 Statement Generator</h2>
                <p class="text-sm text-gray-600">Download your financial statements</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <button class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700">
                            📊 Monthly Statement
                        </button>
                    </div>
                    <div class="text-center">
                        <button class="w-full bg-green-600 text-white py-3 rounded hover:bg-green-700">
                            💰 Payout History
                        </button>
                    </div>
                    <div class="text-center">
                        <button class="w-full bg-purple-600 text-white py-3 rounded hover:bg-purple-700">
                            📈 Investment Summary
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9. Investor Q&A Section -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">❓ Questions & Suggestions</h2>
                <p class="text-sm text-gray-600">Submit private questions to the founders</p>
            </div>
            <div class="p-6">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Ask a question or share a suggestion..."></textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Question</button>
                </form>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('investor.investments') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="text-blue-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">My Investments</h3>
                        <p class="text-gray-500">View all your investments</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('investor.payouts') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="text-green-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Payouts</h3>
                        <p class="text-gray-500">View payout history</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('investor.reports') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="text-purple-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Reports</h3>
                        <p class="text-gray-500">Download reports</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
// Copy referral link functionality
document.addEventListener('DOMContentLoaded', function() {
    const copyButtons = document.querySelectorAll('button');
    copyButtons.forEach(button => {
        if (button.textContent === 'Copy') {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                input.select();
                document.execCommand('copy');
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = 'Copy';
                }, 2000);
            });
        }
    });
});
</script>
@endsection 