@extends('layouts.app')

@section('title', 'Investor Dashboard')

@section('content')
<style>
/* Mobile-specific styles for investor dashboard */
@media (max-width: 768px) {
    /* Mobile header adjustments */
    .mobile-header {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .mobile-header h1 {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    
    .mobile-header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    /* Mobile summary cards */
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
    
    /* Mobile projected earnings */
    .mobile-earnings-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-earnings-card {
        padding: 1rem;
        border-radius: 0.75rem;
    }
    
    .mobile-earnings-card .text-2xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    
    /* Mobile branch performance */
    .mobile-branch-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-branch-card {
        padding: 1rem;
        border-radius: 0.75rem;
    }
    
    /* Mobile roadmap */
    .mobile-roadmap {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .mobile-roadmap-phase {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .mobile-roadmap-timeline {
        flex-direction: column;
        align-items: center;
    }
    
    .mobile-roadmap-line {
        width: 0.125rem;
        height: 2rem;
    }
    
    /* Mobile impact tracker */
    .mobile-impact-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .mobile-impact-item .text-3xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    
    /* Mobile referral section */
    .mobile-referral-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-referral-card {
        padding: 1rem;
        border-radius: 0.75rem;
    }
    
    /* Mobile statement generator */
    .mobile-statement-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-statement-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        min-height: 44px;
    }
    
    /* Mobile navigation cards */
    .mobile-nav-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .mobile-nav-card {
        padding: 1rem;
        border-radius: 0.75rem;
    }
    
    /* Mobile form elements */
    .mobile-form-input {
        font-size: 16px;
        padding: 0.75rem;
        border-radius: 0.5rem;
        min-height: 44px;
    }
    
    .mobile-form-textarea {
        font-size: 16px;
        padding: 0.75rem;
        border-radius: 0.5rem;
        min-height: 44px;
    }
    
    /* Mobile spacing */
    .mobile-section {
        margin-bottom: 1rem;
    }
    
    .mobile-p-4 {
        padding: 1rem;
    }
    
    .mobile-p-6 {
        padding: 1rem;
    }
    
    /* Mobile text sizes */
    .mobile-text-lg {
        font-size: 1.125rem;
        line-height: 1.75rem;
    }
    
    .mobile-text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }
    
    /* Mobile touch targets */
    .mobile-touch-target {
        min-height: 44px;
        min-width: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Mobile safe areas */
    .mobile-safe-area {
        padding-left: env(safe-area-inset-left);
        padding-right: env(safe-area-inset-right);
        padding-bottom: env(safe-area-inset-bottom);
    }
    
    /* Mobile scroll behavior */
    .mobile-scroll-smooth {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Mobile loading states */
    .mobile-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 200px;
    }
    
    /* Mobile haptic feedback */
    .mobile-haptic:active {
        transform: scale(0.95);
        transition: transform 0.1s ease;
    }
    
    /* Mobile focus states */
    .mobile-focus-visible:focus-visible {
        outline: 2px solid #6E0D25;
        outline-offset: 2px;
    }
    
    /* Mobile utility classes */
    .mobile-hidden {
        display: none;
    }
    
    .mobile-block {
        display: block;
    }
    
    .mobile-flex {
        display: flex;
    }
    
    .mobile-flex-col {
        flex-direction: column;
    }
    
    .mobile-items-center {
        align-items: center;
    }
    
    .mobile-justify-between {
        justify-content: space-between;
    }
    
    .mobile-w-full {
        width: 100%;
    }
    
    .mobile-text-center {
        text-align: center;
    }
    
    .mobile-rounded-lg {
        border-radius: 0.5rem;
    }
    
    .mobile-shadow {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    /* Mobile animations */
    @keyframes mobile-fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .mobile-animate-fade-in {
        animation: mobile-fade-in 0.3s ease-out;
    }
    
    /* Mobile responsive images */
    .mobile-img-responsive {
        max-width: 100%;
        height: auto;
        display: block;
    }
    
    /* Mobile text truncation */
    .mobile-text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .mobile-text-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .mobile-text-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
}

/* Mobile landscape adjustments */
@media (max-width: 768px) and (orientation: landscape) {
    .mobile-landscape-hide {
        display: none;
    }
    
    .mobile-landscape-compact {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }
}

/* Mobile portrait adjustments */
@media (max-width: 768px) and (orientation: portrait) {
    .mobile-portrait-full {
        height: 100vh;
        min-height: 100vh;
    }
}

/* Mobile high DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .mobile-retina-img {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}

/* Mobile dark mode support */
@media (prefers-color-scheme: dark) {
    .mobile-dark {
        background-color: #1f2937;
        color: #f9fafb;
    }
    
    .mobile-dark-card {
        background-color: #374151;
        border-color: #4b5563;
    }
}

/* Mobile reduced motion */
@media (prefers-reduced-motion: reduce) {
    .mobile-reduced-motion * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6 mobile-header">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mobile-header-title">Investor Dashboard</h1>
                    <p class="text-gray-600">Welcome back, {{ $investor->name }}</p>
                </div>
                <div class="flex items-center space-x-4 mobile-header-actions">
                    <a href="{{ route('investor.profile') }}" class="text-blue-600 hover:text-blue-800 mobile-touch-target">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 mobile-touch-target">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mobile-safe-area">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mobile-summary-grid">
            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card mobile-animate-fade-in">
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

            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card mobile-animate-fade-in">
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

            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card mobile-animate-fade-in">
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

            <div class="bg-white rounded-lg shadow p-6 mobile-summary-card mobile-animate-fade-in">
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
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">💰 Projected Earnings Calculator</h2>
                <p class="text-sm text-gray-600">Simulate your returns with different scenarios</p>
            </div>
            <div class="p-6 mobile-p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mobile-earnings-grid">
                    <div class="bg-blue-50 rounded-lg p-4 mobile-earnings-card">
                        <h3 class="font-medium text-blue-900 mb-2">Current Monthly Payout</h3>
                        <div class="text-2xl font-bold text-blue-600">Rs {{ number_format($monthlyPayout, 2) }}</div>
                        <p class="text-sm text-blue-700 mt-1">Based on current performance</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 mobile-earnings-card">
                        <h3 class="font-medium text-green-900 mb-2">With 20% Growth</h3>
                        <div class="text-2xl font-bold text-green-600">Rs {{ number_format($monthlyPayout * 1.2, 2) }}</div>
                        <p class="text-sm text-green-700 mt-1">If sales grow by 20%</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 mobile-earnings-card">
                        <h3 class="font-medium text-purple-900 mb-2">With Reinvestment</h3>
                        <div class="text-2xl font-bold text-purple-600">Rs {{ number_format($monthlyPayout * 1.5, 2) }}</div>
                        <p class="text-sm text-purple-700 mt-1">If you reinvest payouts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Live Branch Performance Feed -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">📊 Live Branch Performance</h2>
                <p class="text-sm text-gray-600">Real-time performance indicators</p>
            </div>
            <div class="p-6 mobile-p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mobile-branch-grid">
                    @foreach($liveBranchPerformance as $branch)
                    <div class="border rounded-lg p-4 mobile-branch-card">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-gray-900 mobile-text-clamp-2">{{ $branch['branch_name'] }}</h3>
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
                        <button class="w-full mt-3 text-sm text-blue-600 hover:text-blue-800 mobile-touch-target mobile-haptic">View Details</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- AmaKo Momo Launch Roadmap -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">🚀 AmaKo Momo Launch Roadmap</h2>
                <p class="text-sm text-gray-600 mobile-text-clamp-3">Our strategic 4-month path to revolutionizing Nepal's momo scene with a November 1, 2025 grand opening</p>
            </div>
            <div class="p-6 mobile-p-4">
                @php
                    $roadmap = [
                        [
                            'month' => 'JULY',
                            'title' => 'Investor Lockdown & Final Readiness',
                            'date' => '2025-07-01',
                            'milestones' => [
                                'Finalize all investor commitments by July 30',
                                'Prepare legal contracts and holding company structure',
                                'Create project tracking system and hiring documents',
                            ],
                        ],
                        [
                            'month' => 'AUGUST',
                            'title' => 'Full Execution Begins',
                            'date' => '2025-08-01',
                            'milestones' => [
                                'Launch digital infrastructure (domain, hosting, web app)',
                                'Begin location hunt for Branch and Central Kitchen',
                                'Finalize branding elements and hire key management team',
                                'Internal version of investor portal launching August 10',
                            ],
                        ],
                        [
                            'month' => 'SEPTEMBER',
                            'title' => 'Capital Deployment & Setup',
                            'date' => '2025-09-01',
                            'milestones' => [
                                'Secure Branch and Central Kitchen locations',
                                'Begin kitchen buildout and equipment installation',
                                'Launch public recipe testing campaigns in key areas',
                                'Complete staff hiring and system testing',
                            ],
                        ],
                        [
                            'month' => 'OCTOBER',
                            'title' => 'Pre-Launch Hype Campaign',
                            'date' => '2025-10-01',
                            'milestones' => [
                                'Launch Creator Race viral marketing initiative',
                                'Complete staff training and inventory delivery',
                                'Execute influencer strategy and VIP pre-launch event',
                                'Conduct operational dry runs before grand opening',
                            ],
                        ],
                        [
                            'month' => 'NOVEMBER',
                            'title' => 'Grand Opening Day',
                            'date' => '2025-11-01',
                            'milestones' => [
                                'Launch flagship Koteshwor location with all systems live',
                                'Activate influencer coverage and social media blitz',
                                'Begin investor reporting and performance tracking',
                                'Kick off "Saving Dogs, One Momo at a Time" campaign',
                            ],
                        ],
                    ];
                    $today = date('Y-m-d');
                    $currentPhase = 0;
                    foreach ($roadmap as $i => $phase) {
                        if ($today >= $phase['date']) {
                            $currentPhase = $i;
                        }
                    }
                @endphp
                <div class="flex flex-col md:flex-row md:space-x-8 mobile-roadmap">
                    @foreach($roadmap as $i => $phase)
                        <div class="flex-1 mb-8 md:mb-0 mobile-roadmap-phase">
                            <div class="relative flex flex-col items-center mobile-roadmap-timeline">
                                <div class="w-10 h-10 flex items-center justify-center rounded-full border-4 {{ $i < $currentPhase ? 'border-green-400 bg-green-100' : ($i == $currentPhase ? 'border-blue-500 bg-blue-100' : 'border-gray-300 bg-gray-100') }}">
                                    <span class="font-bold text-lg {{ $i <= $currentPhase ? 'text-green-700' : 'text-gray-500' }}">{{ $i+1 }}</span>
                                </div>
                                @if($i < count($roadmap) - 1)
                                    <div class="h-16 w-1 {{ $i < $currentPhase ? 'bg-green-400' : 'bg-gray-300' }} mobile-roadmap-line"></div>
                                @endif
                            </div>
                            <div class="mt-4 text-center">
                                <div class="text-sm font-semibold uppercase {{ $i == $currentPhase ? 'text-blue-600' : 'text-gray-500' }}">{{ $phase['month'] }}</div>
                                <div class="font-bold text-gray-900 mobile-text-clamp-2">{{ $phase['title'] }}</div>
                                <ul class="mt-2 text-sm text-gray-700 space-y-1">
                                    @foreach($phase['milestones'] as $milestone)
                                        <li class="flex items-start">
                                            <span class="mr-2 mt-1 w-2 h-2 rounded-full {{ $i < $currentPhase ? 'bg-green-400' : ($i == $currentPhase ? 'bg-blue-400' : 'bg-gray-300') }}"></span>
                                            <span class="mobile-text-clamp-3">{{ $milestone }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 text-center">
                    <span class="inline-block px-4 py-2 rounded bg-blue-100 text-blue-700 font-semibold">Grand Opening: November 1, 2025</span>
                </div>
            </div>
        </div>

        <!-- 3. Investment Timeline -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">📅 Investment Timeline</h2>
                <p class="text-sm text-gray-600">Track your investment journey milestones</p>
            </div>
            <div class="p-6 mobile-p-4">
                @foreach($investmentTimeline as $timeline)
                <div class="mb-8">
                    <h3 class="font-medium text-gray-900 mb-4 mobile-text-clamp-2">{{ $timeline['branch_name'] }} - Rs {{ number_format($timeline['investment_amount'], 2) }}</h3>
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
                                <div class="font-medium text-gray-900 mobile-text-clamp-2">{{ $milestone['title'] }}</div>
                                <div class="text-sm text-gray-500">{{ $milestone['date'] }}</div>
                                <div class="text-sm text-gray-600 mobile-text-clamp-3">{{ $milestone['description'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 4. Branch Updates Feed -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">📰 Branch Updates</h2>
                <p class="text-sm text-gray-600">Latest news and updates from your branches</p>
            </div>
            <div class="p-6 mobile-p-4">
                <div class="space-y-4">
                    @foreach($branchUpdates as $update)
                    <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl">{{ $update['icon'] }}</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-gray-900 mobile-text-clamp-2">{{ $update['title'] }}</h4>
                                <span class="text-sm text-gray-500">{{ $update['date'] }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 mobile-text-clamp-3">{{ $update['content'] }}</p>
                            <div class="text-xs text-blue-600 mt-2">{{ $update['branch_name'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 5. Risk Alerts -->
        @if(count($riskAlerts) > 0)
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">⚠️ Risk Alerts</h2>
                <p class="text-sm text-gray-600">Important updates and alerts</p>
            </div>
            <div class="p-6 mobile-p-4">
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
                            <h4 class="font-medium text-gray-900 mobile-text-clamp-2">{{ $alert['title'] }}</h4>
                            <p class="text-sm text-gray-600 mt-1 mobile-text-clamp-3">{{ $alert['message'] }}</p>
                            <div class="text-xs text-gray-500 mt-2">{{ $alert['date'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- 6. Impact Tracker -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">🐾 Impact Tracker</h2>
                <p class="text-sm text-gray-600">Your social contribution through investments</p>
            </div>
            <div class="p-6 mobile-p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mobile-impact-grid">
                    <div class="text-center mobile-impact-item">
                        <div class="text-3xl font-bold text-green-600">{{ number_format($impactStats['monthly_donation'], 2) }}</div>
                        <div class="text-sm text-gray-600">Monthly Donation (Rs)</div>
                    </div>
                    <div class="text-center mobile-impact-item">
                        <div class="text-3xl font-bold text-blue-600">{{ $impactStats['donation_percentage'] }}%</div>
                        <div class="text-sm text-gray-600">Of Sales Donated</div>
                    </div>
                    <div class="text-center mobile-impact-item">
                        <div class="text-3xl font-bold text-purple-600">{{ number_format($impactStats['plates_funded']) }}</div>
                        <div class="text-sm text-gray-600">Plates Funded</div>
                    </div>
                    <div class="text-center mobile-impact-item">
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($impactStats['dogs_saved']) }}</div>
                        <div class="text-sm text-gray-600">Dogs Saved</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Referral & Reinvestment Tools -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">🔗 Referral & Reinvestment</h2>
                <p class="text-sm text-gray-600">Grow your network and reinvest earnings</p>
            </div>
            <div class="p-6 mobile-p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mobile-referral-grid">
                    <div class="bg-blue-50 rounded-lg p-4 mobile-referral-card">
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
                            <input type="text" value="{{ $referralStats['referral_link'] }}" readonly class="flex-1 text-sm border rounded px-2 py-1 mobile-form-input">
                            <button class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 mobile-touch-target mobile-haptic">Copy</button>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 mobile-referral-card">
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
                        <button class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 mobile-touch-target mobile-haptic">Reinvest Now</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Downloadable Statement Generator -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">📄 Statement Generator</h2>
                <p class="text-sm text-gray-600">Download your financial statements</p>
            </div>
            <div class="p-6 mobile-p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mobile-statement-grid">
                    <div class="text-center">
                        <button class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 mobile-statement-btn mobile-touch-target mobile-haptic">
                            📊 Monthly Statement
                        </button>
                    </div>
                    <div class="text-center">
                        <button class="w-full bg-green-600 text-white py-3 rounded hover:bg-green-700 mobile-statement-btn mobile-touch-target mobile-haptic">
                            💰 Payout History
                        </button>
                    </div>
                    <div class="text-center">
                        <button class="w-full bg-purple-600 text-white py-3 rounded hover:bg-purple-700 mobile-statement-btn mobile-touch-target mobile-haptic">
                            📈 Investment Summary
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9. Investor Q&A Section -->
        <div class="bg-white rounded-lg shadow mb-8 mobile-section">
            <div class="px-6 py-4 border-b border-gray-200 mobile-p-4">
                <h2 class="text-lg font-semibold text-gray-900 mobile-text-lg">❓ Questions & Suggestions</h2>
                <p class="text-sm text-gray-600">Submit private questions to the founders</p>
            </div>
            <div class="p-6 mobile-p-4">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 mobile-form-input mobile-focus-visible">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 mobile-form-textarea mobile-focus-visible" placeholder="Ask a question or share a suggestion..."></textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mobile-touch-target mobile-haptic">Submit Question</button>
                </form>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mobile-nav-grid">
            <a href="{{ route('investor.investments') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow mobile-nav-card mobile-haptic">
                <div class="flex items-center">
                    <div class="text-blue-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mobile-text-clamp-2">My Investments</h3>
                        <p class="text-gray-500 mobile-text-clamp-2">View all your investments</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('investor.payouts') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow mobile-nav-card mobile-haptic">
                <div class="flex items-center">
                    <div class="text-green-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mobile-text-clamp-2">Payouts</h3>
                        <p class="text-gray-500 mobile-text-clamp-2">View payout history</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('investor.reports') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow mobile-nav-card mobile-haptic">
                <div class="flex items-center">
                    <div class="text-purple-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mobile-text-clamp-2">Reports</h3>
                        <p class="text-gray-500 mobile-text-clamp-2">Download reports</p>
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
    
    // Mobile-specific enhancements
    if (window.innerWidth <= 768) {
        // Add mobile scroll behavior
        document.body.classList.add('mobile-scroll-smooth');
        
        // Add mobile gesture support
        document.body.classList.add('mobile-gesture');
        
        // Add mobile loading states
        const loadingElements = document.querySelectorAll('.mobile-loading');
        loadingElements.forEach(el => {
            el.style.display = 'flex';
        });
        
        // Add mobile haptic feedback
        const hapticElements = document.querySelectorAll('.mobile-haptic');
        hapticElements.forEach(el => {
            el.addEventListener('touchstart', function() {
                // Add haptic feedback if supported
                if ('vibrate' in navigator) {
                    navigator.vibrate(10);
                }
            });
        });
        
        // Add mobile focus management
        const focusElements = document.querySelectorAll('.mobile-focus-visible');
        focusElements.forEach(el => {
            el.addEventListener('focus', function() {
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    }
    
    // Handle mobile orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            // Recalculate mobile layout
            if (window.innerWidth <= 768) {
                document.body.classList.add('mobile-scroll-smooth');
            } else {
                document.body.classList.remove('mobile-scroll-smooth');
            }
        }, 100);
    });
    
    // Handle mobile resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('mobile-scroll-smooth');
        } else {
            document.body.classList.remove('mobile-scroll-smooth');
        }
    });
});
</script>
@endsection 