<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AmaKo Momo Launch Roadmap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .timeline-step {
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .timeline-step.current {
            box-shadow: 0 0 0 4px #3b82f6;
            border-color: #3b82f6;
        }
        .timeline-step.next {
            box-shadow: 0 0 0 4px #f59e42;
            border-color: #f59e42;
        }
        @media (max-width: 768px) {
            .timeline-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .timeline-inner {
                min-width: 600px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-4 md:py-8">
        <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-2 sm:p-4 md:p-8">
            @php
                $roadmap = [
                    [
                        'month' => 'JULY',
                        'color' => 'blue',
                        'border' => 'border-blue-500',
                        'bg' => 'bg-blue-50',
                        'icon' => '📜',
                        'title' => 'Investor Lockdown & Legal Setup',
                        'milestones' => [
                            ['icon' => '📜', 'text' => 'Finalize investor commitments by July 30 (all funds confirmed)'],
                            ['icon' => '📜', 'text' => 'Legal structure and shareholder agreements signed'],
                            ['icon' => '📜', 'text' => 'Build hiring system + project tracker for execution'],
                            ['icon' => '📜', 'text' => 'Investor contracts + fund disbursement checkpoints'],
                        ],
                        'investor_value' => 'Legal protection, deal confirmation, execution kickoff',
                        'note' => 'See appendix for buyback/profit share/silent investor model details.'
                    ],
                    [
                        'month' => 'AUGUST',
                        'color' => 'orange',
                        'border' => 'border-orange-500',
                        'bg' => 'bg-orange-50',
                        'icon' => '💻',
                        'title' => 'Digital Launch & Location Lock',
                        'milestones' => [
                            ['icon' => '💻', 'text' => 'Website + app live (Aug 10, public launch)'],
                            ['icon' => '💻', 'text' => 'Ordering system ready for launch (not just website UI)'],
                            ['icon' => '📍', 'text' => 'Secure branch + kitchen locations, initiate lease & setup'],
                            ['icon' => '🖼', 'text' => 'Branding finalized, management team hired'],
                            ['icon' => '📍', 'text' => 'Contingency: If location not secured by Aug 15, activate Plan B (alternate site or digital-only soft launch)'],
                        ],
                        'investor_value' => 'Public visibility, digital asset ownership, operational trust',
                        'note' => null
                    ],
                    [
                        'month' => 'SEPTEMBER',
                        'color' => 'orange',
                        'border' => 'border-orange-500',
                        'bg' => 'bg-orange-50',
                        'icon' => '🏗️',
                        'title' => 'Kitchen Build & Recipe Testing',
                        'milestones' => [
                            ['icon' => '🏗️', 'text' => 'Begin kitchen setup, equipment install'],
                            ['icon' => '👨‍🍳', 'text' => 'Chef recruitment/confirmation'],
                            ['icon' => '🥟', 'text' => 'Public tasting & quality validation (200+ tasters via events, schools, partners)'],
                            ['icon' => '👥', 'text' => 'Hire & test operations team, system dry run'],
                            ['icon' => '📊', 'text' => 'Food cost estimation, waste testing, menu stress test'],
                        ],
                        'investor_value' => 'Tangible asset creation, food quality proof, team readiness',
                        'note' => null
                    ],
                    [
                        'month' => 'OCTOBER',
                        'color' => 'purple',
                        'border' => 'border-purple-500',
                        'bg' => 'bg-purple-50',
                        'icon' => '📢',
                        'title' => 'Marketing Blitz & Dry Runs',
                        'milestones' => [
                            ['icon' => '📢', 'text' => 'Launch Creator Race (50+ influencers, 1M+ reach goal, 3 weeks; top wins cash + lifetime meals)'],
                            ['icon' => '📢', 'text' => 'Paid ads and local media campaign'],
                            ['icon' => '🚚', 'text' => 'Inventory prep, staff fully trained'],
                            ['icon' => '🎫', 'text' => 'Run full dress rehearsal, VIP pre-launch event'],
                            ['icon' => '📢', 'text' => 'Fallback: If Creator Race underperforms, shift to paid ads + local events'],
                        ],
                        'investor_value' => 'Market excitement, soft launch test results, brand value growth',
                        'note' => null
                    ],
                    [
                        'month' => 'NOVEMBER',
                        'color' => 'green',
                        'border' => 'border-green-500',
                        'bg' => 'bg-green-50',
                        'icon' => '🏁',
                        'title' => 'Grand Opening & ROI Start',
                        'milestones' => [
                            ['icon' => '🏁', 'text' => 'Koteshwor flagship launch, all systems live'],
                            ['icon' => '🎥', 'text' => 'Social media blitz & performance reports'],
                            ['icon' => '🐾', 'text' => '"Saving Dogs" campaign goes live'],
                            ['icon' => '📈', 'text' => 'Daily sales/ROI dashboard for investors'],
                            ['icon' => '🥟', 'text' => 'Target: 100 plates/day breakeven, 250 plates/day profit goal'],
                        ],
                        'investor_value' => 'Revenue generation, brand proof, first returns',
                        'note' => null
                    ],
                ];
                $colorMap = [
                    'blue' => 'text-blue-700',
                    'orange' => 'text-orange-700',
                    'purple' => 'text-purple-700',
                    'green' => 'text-green-700',
                ];
                $today = date('Y-m-d');
                $phaseDates = ['2025-07-01','2025-08-01','2025-09-01','2025-10-01','2025-11-01'];
                $currentPhase = 0;
                foreach ($phaseDates as $i => $date) {
                    if ($today >= $date) $currentPhase = $i;
                }
                $nextPhase = $currentPhase < count($roadmap) - 1 ? $currentPhase + 1 : null;
            @endphp
            <!-- Summary Banner -->
            <div class="mb-6 flex flex-col items-center justify-center">
                <div class="flex flex-col md:flex-row items-center gap-4 w-full">
                    <div class="px-4 py-2 rounded-full bg-blue-100 text-blue-800 font-bold text-base md:text-lg flex items-center w-full md:w-auto justify-center">
                        <span class="mr-2">🟦</span>Current Phase: <span class="ml-2">{{ $roadmap[$currentPhase]['title'] }} ({{ $roadmap[$currentPhase]['month'] }})</span>
                    </div>
                    @if($nextPhase !== null)
                    <div class="px-4 py-2 rounded-full bg-orange-100 text-orange-800 font-bold text-base md:text-lg flex items-center w-full md:w-auto justify-center">
                        <span class="mr-2">🟧</span>Next: <span class="ml-2">{{ $roadmap[$nextPhase]['title'] }} ({{ $roadmap[$nextPhase]['month'] }})</span>
                    </div>
                    @endif
                </div>
            </div>
            <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 mb-2 text-center">🚀 AmaKo Momo Launch Roadmap</h1>
            <p class="text-base md:text-lg text-gray-600 mb-6 md:mb-8 text-center">See how your investment creates value at every step on our journey to Nepal's #1 momo brand.</p>
            <!-- Timeline Steps -->
            <div class="timeline-scroll w-full mb-8 md:mb-10">
                <div class="timeline-inner flex flex-col md:flex-row items-center md:items-start w-full md:justify-between relative">
                    <div class="hidden md:block absolute left-0 right-0 top-1/2 h-1 bg-gray-200 z-0" style="z-index:0;"></div>
                    @foreach($roadmap as $i => $phase)
                        <div class="flex flex-col items-center z-10 w-full md:w-1/5 mb-6 md:mb-0 relative">
                            <div class="timeline-step rounded-full border-4 {{ $phase['border'] }} bg-white w-14 h-14 md:w-16 md:h-16 flex items-center justify-center text-2xl md:text-3xl font-bold mb-2
                                @if($i == $currentPhase) current @endif
                                @if($i == $nextPhase) next @endif
                            ">
                                {{ $phase['icon'] }}
                                @if($i == $currentPhase)
                                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded bg-blue-600 text-white text-xs font-bold shadow whitespace-nowrap">You Are Here</span>
                                @elseif($i == $nextPhase)
                                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded bg-orange-500 text-white text-xs font-bold shadow whitespace-nowrap">Next Up</span>
                                @endif
                            </div>
                            <div class="text-xs font-bold uppercase tracking-wide {{ $colorMap[$phase['color']] }} text-center">{{ $phase['month'] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-col md:flex-row w-full md:justify-between mt-2">
                    @foreach($roadmap as $i => $phase)
                        <div class="w-full md:w-1/5 text-center text-xs text-gray-500 font-semibold mb-2 md:mb-0">{{ $phase['title'] }}</div>
                    @endforeach
                </div>
            </div>
            <!-- Phase Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5 mb-10">
                @foreach($roadmap as $i => $phase)
                    <div class="flex flex-col h-full p-4 md:p-5 rounded-xl shadow-md {{ $phase['bg'] }} border-t-8 {{ $phase['border'] }}
                        @if($i == $currentPhase) ring-4 ring-blue-200 @endif
                        @if($i == $nextPhase) ring-4 ring-orange-200 @endif
                    ">
                        <div class="flex flex-col items-center mb-3 relative">
                            <div class="text-2xl md:text-3xl mb-1">{{ $phase['icon'] }}</div>
                            <div class="text-base md:text-lg font-bold {{ $colorMap[$phase['color']] }}">{{ $phase['month'] }}</div>
                            <div class="text-sm md:text-base font-semibold text-gray-900 text-center">{{ $phase['title'] }}</div>
                            @if($i == $currentPhase)
                                <span class="absolute top-0 right-0 px-2 py-0.5 rounded bg-blue-600 text-white text-xs font-bold shadow whitespace-nowrap">You Are Here</span>
                            @elseif($i == $nextPhase)
                                <span class="absolute top-0 right-0 px-2 py-0.5 rounded bg-orange-500 text-white text-xs font-bold shadow whitespace-nowrap">Next Up</span>
                            @endif
                        </div>
                        <ul class="mb-4 space-y-2">
                            @foreach($phase['milestones'] as $milestone)
                                <li class="flex items-start text-gray-800 text-xs md:text-sm">
                                    <span class="mr-2 text-lg">{{ $milestone['icon'] }}</span>
                                    <span>{{ $milestone['text'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-auto pt-3">
                            <div class="font-bold text-yellow-700 text-xs md:text-sm flex items-center mb-1">
                                <span class="mr-2 text-lg">🟡</span>Investor Value
                            </div>
                            <div class="text-gray-900 text-xs md:text-base font-semibold leading-snug bg-yellow-50 border-l-4 border-yellow-400 pl-3 py-2 rounded">
                                → {{ $phase['investor_value'] }}
                            </div>
                            @if(!empty($phase['note']))
                                <div class="mt-2 text-xs text-gray-500 italic">{{ $phase['note'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Legend -->
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-center gap-2 md:gap-4 text-xs md:text-sm">
                    <div class="flex items-center"><span class="text-2xl mr-2">📜</span>Legal</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">💻</span>Digital Launch</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">📍</span>Location</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🏗️</span>Kitchen Build</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">👨‍🍳</span>Chef/Recruitment</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🥟</span>Recipe Testing</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">👥</span>Team/Staff</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">📢</span>Marketing</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🚚</span>Inventory/Prep</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🎫</span>Dress Rehearsal</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🏁</span>Grand Opening</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🎥</span>Social Media</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">🐾</span>Saving Dogs</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">📈</span>ROI Dashboard</div>
                    <div class="flex items-center"><span class="text-2xl mr-2">📊</span>Food Cost/Menu Test</div>
                </div>
            </div>
            <!-- Footer Bar -->
            <div class="mt-8">
                <div class="flex flex-col md:flex-row items-center justify-between bg-gray-50 border-t border-b border-gray-200 py-4 px-3 md:px-6 rounded text-xs md:text-sm">
                    <div class="mb-2 md:mb-0 flex items-center"><span class="mr-2">🔁</span> <span>Monthly burn estimate: <span class="font-semibold">Rs. 6 lakh/month</span> <span class="ml-1 text-gray-500">(includes salaries, rent, utilities, marketing, packaging, delivery, waste)</span></span></div>
                    <div class="mb-2 md:mb-0 flex items-center"><span class="mr-2">🔒</span> <span>Investment unlock per phase: <span class="font-semibold">25% infra + kitchen, 30% staffing + branding, 25% marketing + influencer, 20% buffer + working capital</span></span></div>
                    <div class="flex items-center"><span class="mr-2">🧮</span> <span>Expected ROI start: <span class="font-semibold">3 months post-launch</span></span></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 