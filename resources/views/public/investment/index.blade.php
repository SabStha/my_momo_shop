<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invest in Ama Ko Shop</title>
    <link rel="icon" type="image/png" href="/storage/logo/momo_icon.png">
    @vite('resources/js/app.js')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=DM+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #FAF7F2; font-family: 'Inter', 'DM Sans', sans-serif; }
        .dashboard-card { background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 24px rgba(34,34,34,0.07); padding: 2rem; margin-bottom: 2rem; }
        .dashboard-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .dashboard-metric { background: #FCF6E8; border-radius: 1rem; padding: 1.5rem 1rem; text-align: center; box-shadow: 0 2px 8px rgba(218,165,32,0.07); }
        .brand-gold { color: #DAA520; }
        .brand-navy { color: #6E0D25; }
        .btn-gold { background: #DAA520; color: #fff; font-weight: 600; border-radius: 0.75rem; padding: 0.75rem 2rem; transition: box-shadow 0.2s, background 0.2s; }
        .btn-gold:hover { background: #bfa12a; box-shadow: 0 2px 12px #DAA52044; }
        .btn-navy { background: #6E0D25; color: #fff; font-weight: 600; border-radius: 0.75rem; padding: 0.75rem 2rem; transition: box-shadow 0.2s, background 0.2s; }
        .btn-navy:hover { background: #4a0818; box-shadow: 0 2px 12px #6E0D2544; }
        .fade-in { animation: fadeIn 1.2s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(30px);} to { opacity: 1; transform: none; } }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 py-2 sm:py-4 md:py-8 lg:py-12">
    <div class="max-w-5xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8 py-6 w-full">
        <!-- Hero Section -->
        <div class="dashboard-card flex flex-col md:flex-row items-center gap-8 fade-in" style="background: linear-gradient(90deg, #fff 60%, #FCF6E8 100%);">
            <div class="flex-shrink-0 flex flex-col items-center md:items-start">
                <img src="{{ asset('storage/logo/momokologo.png') }}" alt="Ama Ko Momo Logo" class="h-20 sm:h-28 md:h-32 w-auto object-contain mb-2">
                <div class="text-2xl sm:text-3xl md:text-4xl font-bold brand-navy mb-2">Ama Ko Momo — Nepal's momo. A taste of love, a bond of home</div>
                <div class="text-base sm:text-lg text-gray-700 mb-4 max-w-md">
                    Co-own profitable momo branches.<br>
                    Earn from real sales. ROI based on actual business performance.
                </div>
                <div class="flex gap-4 mb-2">
                    <button class="btn-gold text-lg px-8 py-3 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 animate-bounce" id="heroInvestNowBtn">🚀 Invest Now</button>
                    <a href="#roi_calculator" class="border-2 border-[#6E0D25] text-[#6E0D25] bg-white font-semibold rounded-lg px-6 py-3 hover:bg-[#6E0D25] hover:text-white transition-all duration-300">See ROI</a>
                </div>
                <!-- Investor Deadlines Card (Hero) -->
                <div class="mb-3 relative">
                    <div class="absolute -top-5 left-0 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg z-10 animate-pulse">🔥 Limited Time — Only <span id="daysLeftBadge">...</span> Days Left</div>
                    <div class="bg-white border-2 border-yellow-200 rounded-xl shadow-md p-4 pt-8">
                        <div class="flex items-center gap-2 text-lg font-bold text-yellow-700 mb-2">
                            <span class="text-2xl">🕓</span> Investor Deadlines
                        </div>
                        <div class="flex items-center gap-2 text-sm font-semibold text-yellow-700 mb-1">
                            <span class="text-lg">📆</span> Lock-In Deadline:
                            <span class="font-bold text-[#D22B2B]">July 31, 2025</span>
                        </div>
                        <div class="text-xs text-gray-700 mb-1">Secure your equity slot</div>
                        <div class="flex items-center gap-2 text-sm font-semibold text-green-700 mb-1 mt-2">
                            <span class="text-lg">💳</span> Final Payment Deadline:
                            <span class="font-bold text-[#1E88E5]">September 15, 2024</span> — No exceptions
                        </div>
                        <div class="text-xs text-gray-700 mb-1">Transfer funds to activate your investment</div>
                        <!-- Progress bar -->
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-3 mb-1 overflow-hidden">
                            <div id="lockinProgressBar" class="bg-gradient-to-r from-[#D22B2B] to-[#FFD600] h-3 rounded-full transition-all duration-1000" style="width: 65%"></div>
                        </div>
                        <div class="text-xs text-gray-600 mb-2">🔁 Time Left to Lock Equity</div>
                    </div>
                </div>
                <div class="font-bold text-green-700 text-sm mb-4">✅ Fully Notarized. No grey zones. This is your chance to co-own a profitable, tech-backed momo business.</div>
                <div class="flex gap-2 mt-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#FFF8E1] text-[#DAA520] border border-[#DAA520]/30">Legally Notarized</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#F3F4F6] text-[#6E0D25] border border-[#6E0D25]/20">{{ $stats['total_investors'] ?? 0 }} Investors Joined</span>
                </div>
            </div>
            <div class="flex-1 flex flex-col items-center md:items-end w-full">
                <a href="https://sabinsecurityhub.xyz/" target="_blank" class="block w-full mb-2 px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 text-white font-bold rounded-xl hover:from-green-600 hover:to-teal-600 transition-all duration-300 shadow-lg text-center text-base sm:text-lg flex items-center justify-center gap-2 sm:gap-3">
                    <span class="text-2xl">📊</span> <span class="truncate">Visit Web Application</span>
                </a>
                <img src="{{ asset('storage/products/image.png') }}" alt="Ama Ko Shop Web Application Screenshot" class="w-full max-w-xs sm:max-w-sm md:max-w-xs lg:max-w-xs rounded-lg shadow-md border border-gray-200 mb-2 object-contain">
                <div class="text-xs text-gray-500 text-center break-words">Preview: Our digital platform for investors</div>
            </div>
        </div>

        <!-- ROI Calculator & Key Metrics -->
        <div class="dashboard-card fade-in" id="roi_calculator">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <div class="text-xl font-bold brand-navy mb-2 md:mb-0">ROI Calculator</div>
            </div>
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-2" id="customAmountInput" style="display:none;">
                                    <label for="customAmount" class="text-sm text-gray-700 mb-1 sm:mb-0">Enter amount ({{ getCurrencySymbol() }})</label>
                    <input type="text" id="customAmount" class="border rounded-lg p-2 w-40" placeholder="Enter amount ({{ getCurrencySymbol() }})" min="1000" step="1000">
            </div>
            <div class="mb-4 flex gap-2">
                <button class="btn-gold" id="btn1L">1L Investment</button>
                <button class="btn-navy" id="btnCustom">Custom Amount</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="bg-[#FCF6E8] rounded-lg p-4 text-center shadow-sm min-w-0">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="text-xl">📊</span>
                        <div class="text-lg sm:text-xl font-bold brand-gold truncate" id="monthly_roi">{{ formatPrice(3997) }}</div>
                    </div>
                    <div class="text-xs text-gray-700">Monthly ROI</div>
                </div>
                <div class="bg-[#FCF6E8] rounded-lg p-4 text-center shadow-sm min-w-0">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="text-xl">📈</span>
                        <div class="text-lg sm:text-xl font-bold brand-navy truncate" id="annual_roi">{{ formatPrice(47964) }}</div>
                    </div>
                    <div class="text-xs text-gray-700">Annual ROI</div>
                </div>
                <div class="bg-[#FCF6E8] rounded-lg p-4 text-center shadow-sm min-w-0">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="text-xl">📉</span>
                        <div class="text-lg sm:text-xl font-bold brand-gold truncate" id="five_year_roi">{{ formatPrice(239850) }}</div>
                    </div>
                    <div class="text-xs text-gray-700">5-Year Return</div>
                </div>
            </div>
            <!-- Risk bar below ROI -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-2 overflow-hidden">
                <div class="bg-gradient-to-r from-green-400 via-yellow-300 to-red-400 h-2 rounded-full" style="width: 40%"></div>
            </div>
            <div class="text-xs text-gray-600 mb-2">ROI is based on real sales, not projections. Returns may vary — track live via dashboard.</div>
            <div class="text-right">
                <button class="text-xs text-[#6E0D25] underline hover:text-[#DAA520] transition" id="compareScenariosBtn">Compare Scenarios</button>
            </div>
            <div id="compareScenariosSection" class="mt-4 hidden bg-[#FCF6E8] rounded-lg p-4">
                <div class="font-bold mb-2">Scenario Comparison</div>
                <ul class="text-sm text-gray-700 list-disc pl-5">
                    <li><span class="font-semibold text-green-700">Best Case:</span> 5-Year Return: Rs. 3,50,000+ (Rapid scale-up)</li>
                    <li><span class="font-semibold text-blue-700">Most Likely:</span> 5-Year Return: Rs. 2,39,850 (Steady growth)</li>
                    <li><span class="font-semibold text-yellow-700">Delayed Scale:</span> 5-Year Return: Rs. 1,80,000 (Slow expansion)</li>
                </ul>
            </div>
            <div class="text-xs text-gray-600 mb-2">ROI shown is based on current operating profits. Returns may vary with branch performance.</div>
            <div class="mb-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm font-semibold text-yellow-700 mb-1">
                    <span class="text-lg">⏰</span> <span class="truncate">Investor Lock-In Deadline</span>
                </div>
                <div class="text-xs text-gray-700 mb-1 break-words">Confirm your equity and reserve your slot by:</div>
                <div class="font-bold text-base text-red-700" id="lockinDeadlineROI">July 31, 2025</div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm font-semibold text-green-700 mt-2 mb-1">
                    <span class="text-lg">💰</span> <span class="truncate">Final Payment Deadline</span>
                </div>
                <div class="text-xs text-gray-700 mb-1 break-words">Transfer your investment funds no later than:</div>
                <div class="font-bold text-base text-blue-700" id="paymentDeadlineROI">September 15, 2024 — Hard cut-off for onboarding</div>
            </div>
        </div>

        <!-- Branch Snapshot -->
        <div class="dashboard-card fade-in">
            <div class="text-xl font-bold brand-navy mb-4 break-words">Where Your ROI Comes From</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                <div class="bg-[#F8F9FA] rounded-lg p-4 flex flex-col items-center shadow-sm w-full">
                    <div class="text-lg font-semibold brand-navy mb-1">Koteshwor Branch</div>
                    <div class="text-2xl font-bold brand-gold mb-2">Rs. 2,70,000/mo</div>
                    <div class="text-xs text-gray-600">Expected monthly profit share</div>
                </div>
                <div class="bg-[#F8F9FA] rounded-lg p-4 flex flex-col items-center shadow-sm">
                    <div class="text-lg font-semibold brand-navy mb-1">Central Operations</div>
                    <div class="text-2xl font-bold brand-gold mb-2">Rs. 1,89,700/mo</div>
                    <div class="text-xs text-gray-600">Expected monthly profit share</div>
                </div>
            </div>
            <div class="text-xs text-gray-600 mt-3">These are current branch profits. ROI depends on momo sales volume and operating performance. <span title="250 plates/day × Rs. 60 profit = Rs. 4.5L total">(e.g., 250 plates/day × Rs. 60 profit = Rs. 4.5L total)</span></div>
        </div>

        <!-- Expansion Forecast -->
        <div class="dashboard-card fade-in">
            <div class="text-xl font-bold brand-navy mb-4">Expansion Forecast</div>
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="flex-1">
                    <div class="text-base text-gray-700 mb-2">Projected ROI as we open more branches:</div>
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-[#DAA520] to-[#6E0D25] h-4 rounded-full transition-all duration-1000" style="width: 60%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>2 Branches</span>
                        <span>4 Branches</span>
                        <span>6 Branches</span>
                    </div>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-2xl font-bold brand-gold">Up to 2x ROI</div>
                    <div class="text-xs text-gray-600">with expansion</div>
                </div>
            </div>
        </div>

        <!-- Risk Profile (Tabbed) -->
        <div class="dashboard-card fade-in">
            <div class="text-xl font-bold brand-navy mb-4">Risk Profile</div>
            <div>
                <div class="flex gap-2 mb-4">
                    <button class="btn-navy" onclick="showRiskTab('best')" id="risk-tab-best">Best Case</button>
                    <button class="btn-gold" onclick="showRiskTab('likely')" id="risk-tab-likely">Most Likely</button>
                    <button class="btn-navy" onclick="showRiskTab('delayed')" id="risk-tab-delayed">Delayed Scale</button>
                </div>
                <div id="risk-content-best" class="risk-content">
                    <div class="text-lg font-semibold brand-gold mb-2">Best Case</div>
                    <div class="text-gray-700 mb-1">All branches scale as planned, ROI grows rapidly.</div>
                    <div class="text-green-600 font-bold">5-Year Return: Rs. 3,50,000+ <span class='text-xs text-gray-700'>(on Rs. 1,00,000 investment)</span></div>
                </div>
                <div id="risk-content-likely" class="risk-content hidden">
                    <div class="text-lg font-semibold brand-navy mb-2">Most Likely</div>
                    <div class="text-gray-700 mb-1">Steady growth, some delays, but strong monthly returns.</div>
                    <div class="text-blue-600 font-bold">5-Year Return: Rs. 2,39,850 <span class='text-xs text-gray-700'>(on Rs. 1,00,000 investment)</span></div>
                </div>
                <div id="risk-content-delayed" class="risk-content hidden">
                    <div class="text-lg font-semibold text-gray-600 mb-2">Delayed Scale</div>
                    <div class="text-gray-700 mb-1">Expansion is slow, but Koteshwor branch remains profitable.</div>
                    <div class="text-yellow-600 font-bold">5-Year Return: Rs. 1,80,000 <span class='text-xs text-gray-700'>(on Rs. 1,00,000 investment)</span></div>
                </div>
            </div>
            <div class="text-xs text-gray-600 mt-4">Returns are not fixed or guaranteed. This is equity-based profit sharing.</div>
        </div>

        <!-- Funding Progress -->
        <div class="dashboard-card fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <div class="text-xl font-bold brand-navy mb-2 md:mb-0">Funding Progress</div>
                <button class="btn-navy" id="toggleLeaderboardBtn">View Leaderboard</button>
            </div>
            @php
                $goalAmount = 3000000; // Rs. 30L goal
                $raisedAmount = $stats['total_invested'] ?? 0;
                $progressPercentage = min(100, ($raisedAmount / $goalAmount) * 100);
                $raisedInLakhs = number_format($raisedAmount / 100000, 1);
            @endphp
            <div class="w-full bg-gray-200 rounded-full h-5 mb-2 overflow-hidden">
                <div class="bg-gradient-to-r from-[#DAA520] to-[#6E0D25] h-5 rounded-full transition-all duration-1000" style="width: {{ $progressPercentage }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>Rs. {{ $raisedInLakhs }}L raised</span>
                <span>Goal: Rs. 30L+</span>
            </div>
        </div>

        <!-- Leaderboard Section (hidden by default) -->
        <div id="leaderboardSection" class="dashboard-card fade-in hidden overflow-x-auto w-full">
            <div class="text-xl font-bold brand-navy mb-4 break-words">Investment Leaderboard</div>
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-2 break-words">Top Investors</h2>
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount (Rs.)</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topInvestors ?? [] as $i => $investor)
                            <tr>
                                <td class="px-4 py-2">{{ $i+1 }}</td>
                                <td class="px-4 py-2 font-semibold text-gray-900">{{ $investor->name }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $investor->address }}</td>
                                <td class="px-4 py-2 text-blue-700 font-bold">Rs. {{ number_format($investor->total_invested, 2) }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $investor->investment_date ? $investor->investment_date->format('Y-m-d') : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- More sections (Branch Snapshot, Expansion Forecast, Risk Profile, Funding Progress, Registration) will follow in next steps -->

        <!-- Registration Modal Trigger (Sticky Button for Mobile) -->
        <button id="openRegistrationModal" class="fixed bottom-6 right-6 z-50 btn-gold shadow-lg md:hidden animate-bounce">Invest Now</button>

        <!-- Registration Modal (Step Wizard) -->
        <div id="registrationModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative">
                <button id="closeRegistrationModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                <div id="wizardStep1">
                    <div class="text-lg font-bold brand-navy mb-2">Step 1: Your Details</div>
                    <input type="text" class="w-full border rounded-lg p-2 mb-3" placeholder="Full Name" id="regName">
                    <input type="email" class="w-full border rounded-lg p-2 mb-3" placeholder="Email" id="regEmail">
                    <input type="text" class="w-full border rounded-lg p-2 mb-3" placeholder="Address" id="regAddress">
                    <button class="btn-gold w-full" onclick="showWizardStep(2)">Next</button>
                </div>
                <div id="wizardStep2" class="hidden">
                    <div class="text-lg font-bold brand-navy mb-2">Step 2: Investment Amount</div>
                    <input type="text" class="w-full border rounded-lg p-2 mb-3" placeholder="Amount (Rs.)" id="regAmount">
                    <button class="btn-navy w-full mb-2" onclick="showWizardStep(1)">Back</button>
                    <button class="btn-gold w-full" onclick="showWizardStep(3)">Next</button>
                </div>
                <div id="wizardStep3" class="hidden">
                    <div class="text-lg font-bold brand-navy mb-2">Step 3: Confirm & Submit</div>
                    <div class="text-xs text-gray-600 mb-3">Review your details and submit your investment application.</div>
                    <!-- Deadlines in registration modal -->
                    <div class="mb-3">
                        <div class="flex items-center gap-2 text-sm font-semibold text-yellow-700 mb-1">
                            <span class="text-lg">⏰</span> Investor Lock-In Deadline
                        </div>
                        <div class="text-xs text-gray-700 mb-1">Confirm your equity and reserve your slot by:</div>
                        <div class="font-bold text-base text-red-700" id="lockinDeadlineReg">July 31, 2025</div>
                        <div class="flex items-center gap-2 text-sm font-semibold text-green-700 mt-2 mb-1">
                            <span class="text-lg">💰</span> Final Payment Deadline
                        </div>
                        <div class="text-xs text-gray-700 mb-1">Transfer your investment funds no later than:</div>
                        <div class="font-bold text-base text-blue-700" id="paymentDeadlineReg">September 15, 2024 — Hard cut-off for onboarding</div>
                        <div class="text-xs text-gray-600 mt-1">📝 All signed investors must complete fund transfer before September 15 to secure equity in this batch.</div>
                    </div>
                    <button class="btn-navy w-full mb-2" onclick="showWizardStep(2)">Back</button>
                    <button class="btn-gold w-full" id="submitApplicationBtn">Submit Application</button>
                    <div id="regResultMsg" class="mt-3 text-center text-sm"></div>
                </div>
            </div>
        </div>

        <!-- Trust & Conversion Add-Ons -->
        <div class="dashboard-card fade-in flex flex-col md:flex-row items-center gap-6 mt-8">
            <div class="flex-1 flex flex-col items-center md:items-start">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-[#FFF8E1] text-[#DAA520] border border-[#DAA520]/30 font-semibold mb-3">
                    <span class="text-2xl mr-2">📄</span> AmaKo Momo Investor Pitch Deck
                </div>
                <div class="text-sm text-gray-700 mb-3 max-w-lg">
                    <span class="block mb-1 font-semibold">✅ What's inside:</span>
                    <ul class="list-disc pl-5 mb-2">
                        <li class="mb-1">📈 ROI breakdown and 5-year projections</li>
                        <li class="mb-1">🏪 Business model & branch performance</li>
                        <li class="mb-1">🧾 Investment structure (Rs. 1L = 1% equity)</li>
                        <li class="mb-1">🚀 Expansion roadmap</li>
                        <li class="mb-1">💡 Brand story and mission</li>
                    </ul>
                    ROI shown in the pitch is based on current business performance and is subject to change with operational scale, cost, or delays.<br>
                    👉 Preview or download the pitch deck below to understand why this is your opportunity to co-own Nepal's next iconic food brand.
                </div>
                <div class="flex gap-3 mt-2">
                    <a href="/storage/documents/investment-guide.pdf" download class="btn-navy">Download Pitch Deck PDF</a>
                    <button class="btn-gold" id="previewPdfBtn">Preview PDF</button>
                </div>
            </div>
            <div class="flex-1 flex flex-col items-center">
                <img src="{{ asset('storage/founder_image.png') }}" alt="Ama Ko Momo Founder" class="w-40 h-24 object-cover rounded-lg shadow-md mb-2 cursor-pointer hover:shadow-lg transition-shadow" id="founderImage" onclick="openFounderPreview()">
                <div class="text-xs text-gray-600">Hear from our founder: Why invest, how ROI works, and our vision.</div>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div id="pdfPreviewModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden px-2">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-2 sm:p-4 relative">
                <button id="closePdfPreview" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                <div class="text-lg font-bold brand-navy mb-2 break-words">Investment Guide PDF Preview</div>
                <iframe src="/storage/documents/investment-guide.pdf" class="w-full h-[60vh] sm:h-[80vh] min-h-[300px] sm:min-h-[500px] rounded-lg border" frameborder="0"></iframe>
            </div>
        </div>

        <!-- Founder Image Preview Modal -->
        <div id="founderPreviewModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 hidden px-2">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-4 relative">
                <button id="closeFounderPreview" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">&times;</button>
                <div class="text-lg font-bold brand-navy mb-4 text-center">Ama Ko Momo Founder</div>
                <img src="{{ asset('storage/founder_image.png') }}" alt="Ama Ko Momo Founder" class="w-full h-auto rounded-lg shadow-lg">
                <div class="text-sm text-gray-600 mt-4 text-center">Hear from our founder: Why invest, how ROI works, and our vision.</div>
            </div>
        </div>
    </div>

    <!-- Footer Disclaimer -->
    <div class="text-xs text-gray-500 text-center mt-8 mb-20 md:mb-4 px-4">
        📉 All returns shown are examples based on current performance. Actual investor ROI may vary based on business operations. This is an equity-based investment, not a fixed return instrument.
    </div>

    <script>
    function showRiskTab(tab) {
        document.querySelectorAll('.risk-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('risk-content-' + tab).classList.remove('hidden');
        document.getElementById('risk-tab-best').classList.remove('btn-gold');
        document.getElementById('risk-tab-best').classList.add('btn-navy');
        document.getElementById('risk-tab-likely').classList.remove('btn-gold');
        document.getElementById('risk-tab-likely').classList.add('btn-navy');
        document.getElementById('risk-tab-delayed').classList.remove('btn-gold');
        document.getElementById('risk-tab-delayed').classList.add('btn-navy');
        document.getElementById('risk-tab-' + tab).classList.remove('btn-navy');
        document.getElementById('risk-tab-' + tab).classList.add('btn-gold');
    }
    // Default to Most Likely
    showRiskTab('likely');

    // Registration Modal Logic
    const openBtn = document.getElementById('openRegistrationModal');
    const modal = document.getElementById('registrationModal');
    const closeBtn = document.getElementById('closeRegistrationModal');
    function showWizardStep(step) {
        [1,2,3].forEach(i => document.getElementById('wizardStep'+i).classList.add('hidden'));
        document.getElementById('wizardStep'+step).classList.remove('hidden');
    }
    if(openBtn && modal && closeBtn) {
        openBtn.onclick = () => { modal.classList.remove('hidden'); showWizardStep(1); };
        closeBtn.onclick = () => { modal.classList.add('hidden'); };
        window.onclick = (e) => { if(e.target === modal) modal.classList.add('hidden'); };
    }
    // Hero Invest Now button
    const heroBtn = document.getElementById('heroInvestNowBtn');
    if(heroBtn && modal) {
        heroBtn.onclick = () => { modal.classList.remove('hidden'); showWizardStep(1); };
    }

    // ROI Calculator Logic
    const btn1L = document.getElementById('btn1L');
    const btnCustom = document.getElementById('btnCustom');
    const customAmountInput = document.getElementById('customAmountInput');
    const customAmount = document.getElementById('customAmount');
    const monthlyROI = document.getElementById('monthly_roi');
    const annualROI = document.getElementById('annual_roi');
    const fiveYearROI = document.getElementById('five_year_roi');
    let currentAmount = 100000;
    function updateROI(amount) {
        // Example calculation logic (replace with your real logic)
        const monthly = Math.round(amount * 0.03997);
        const annual = Math.round(monthly * 12);
        const fiveYear = Math.round(annual * 5);
                    monthlyROI.textContent = `{{ getCurrencySymbol() }} ${monthly.toLocaleString()}`;
            annualROI.textContent = `{{ getCurrencySymbol() }} ${annual.toLocaleString()}`;
            fiveYearROI.textContent = `{{ getCurrencySymbol() }} ${fiveYear.toLocaleString()}`;
    }
    if(btn1L && btnCustom && customAmountInput && customAmount) {
        btn1L.onclick = () => {
            btn1L.classList.add('btn-gold');
            btn1L.classList.remove('btn-navy');
            btnCustom.classList.remove('btn-gold');
            btnCustom.classList.add('btn-navy');
            customAmountInput.style.display = 'none';
            currentAmount = 100000;
            updateROI(currentAmount);
        };
        btnCustom.onclick = () => {
            btnCustom.classList.add('btn-gold');
            btnCustom.classList.remove('btn-navy');
            btn1L.classList.remove('btn-gold');
            btn1L.classList.add('btn-navy');
            customAmountInput.style.display = 'block';
            customAmount.focus();
        };
        // Allow free editing while typing
        customAmount.addEventListener('input', (e) => {
            let val = e.target.value.replace(/[^\d]/g, '');
            currentAmount = parseInt(val) || 0;
            updateROI(currentAmount);
        });
        // Format and apply minimum on blur
        customAmount.addEventListener('blur', (e) => {
            let val = e.target.value.replace(/[^\d]/g, '');
            if(val === '') {
                currentAmount = 0;
                updateROI(currentAmount);
                e.target.value = '';
                return;
            }
            let num = parseInt(val) || 0;
            if(num < 1000) num = 1000;
            currentAmount = num;
            updateROI(currentAmount);
            e.target.value = formatNepaliNumber(num);
        });
    }
    // Compare Scenarios Toggle
    const compareBtn = document.getElementById('compareScenariosBtn');
    const compareSection = document.getElementById('compareScenariosSection');
    if(compareBtn && compareSection) {
        compareBtn.onclick = () => {
            compareSection.classList.toggle('hidden');
        };
    }
    // Initialize ROI
    updateROI(currentAmount);

    // Helper for Nepalese comma formatting
    function formatNepaliNumber(x) {
        let num = x.toString();
        let lastThree = num.substring(num.length-3);
        let otherNumbers = num.substring(0,num.length-3);
        if(otherNumbers !== '')
            lastThree = ',' + lastThree;
        return otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
    }

    // Leaderboard toggle
    const leaderboardBtn = document.getElementById('toggleLeaderboardBtn');
    const leaderboardSection = document.getElementById('leaderboardSection');
    if(leaderboardBtn && leaderboardSection) {
        leaderboardBtn.onclick = () => {
            leaderboardSection.classList.toggle('hidden');
            leaderboardBtn.textContent = leaderboardSection.classList.contains('hidden') ? 'View Leaderboard' : 'Hide Leaderboard';
        };
    }

    // Registration form AJAX submit
    const submitBtn = document.getElementById('submitApplicationBtn');
    if(submitBtn) {
        submitBtn.onclick = function(e) {
            e.preventDefault();
            const name = document.getElementById('regName').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const address = document.getElementById('regAddress').value.trim();
            let amount = document.getElementById('regAmount').value.replace(/,/g, '').trim();
            amount = parseInt(amount) || 0;
            // For demo, set likelihood to 5 (very likely)
            const likelihood = 5;
            const resultMsg = document.getElementById('regResultMsg');
            resultMsg.textContent = '';
            if(!name || !address || amount < 1000) {
                resultMsg.textContent = 'Please fill all required fields (minimum {{ formatPrice(1000) }}).';
                resultMsg.className = 'mt-3 text-center text-sm text-red-600';
                return;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            fetch('/invest/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    address: address,
                    investment_amount: amount,
                    likelihood: likelihood
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    resultMsg.textContent = data.message || 'Application submitted!';
                    resultMsg.className = 'mt-3 text-center text-sm text-green-600';
                    setTimeout(() => {
                        document.getElementById('registrationModal').classList.add('hidden');
                        // Reset fields
                        document.getElementById('regName').value = '';
                        document.getElementById('regEmail').value = '';
                        document.getElementById('regAddress').value = '';
                        document.getElementById('regAmount').value = '';
                        showWizardStep(1);
                        resultMsg.textContent = '';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Application';
                    }, 2000);
                } else {
                    resultMsg.textContent = data.message || 'Submission failed.';
                    resultMsg.className = 'mt-3 text-center text-sm text-red-600';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Application';
                }
            })
            .catch(() => {
                resultMsg.textContent = 'Submission failed. Please try again.';
                resultMsg.className = 'mt-3 text-center text-sm text-red-600';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Application';
            });
        };
    }

    // PDF Preview Modal logic
    const previewPdfBtn = document.getElementById('previewPdfBtn');
    const pdfPreviewModal = document.getElementById('pdfPreviewModal');
    const closePdfPreview = document.getElementById('closePdfPreview');
    if(previewPdfBtn && pdfPreviewModal && closePdfPreview) {
        previewPdfBtn.onclick = () => pdfPreviewModal.classList.remove('hidden');
        closePdfPreview.onclick = () => pdfPreviewModal.classList.add('hidden');
        window.addEventListener('click', function(e) {
            if(e.target === pdfPreviewModal) pdfPreviewModal.classList.add('hidden');
        });
    }

    // Founder Image Preview Modal logic
    const founderPreviewModal = document.getElementById('founderPreviewModal');
    const closeFounderPreview = document.getElementById('closeFounderPreview');
    
    function openFounderPreview() {
        if(founderPreviewModal) {
            founderPreviewModal.classList.remove('hidden');
        }
    }
    
    if(founderPreviewModal && closeFounderPreview) {
        closeFounderPreview.onclick = () => founderPreviewModal.classList.add('hidden');
        window.addEventListener('click', function(e) {
            if(e.target === founderPreviewModal) founderPreviewModal.classList.add('hidden');
        });
    }

    // Live countdown for lock-in deadline
    function updateLockinCountdowns() {
        const lockinDate = new Date('2024-07-31T23:59:59');
        const now = new Date();
        const diff = lockinDate - now;
        let days = Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
        let text = days === 1 ? 'Only 1 day left' : `Only ${days} days left`;
        [
            document.getElementById('lockinCountdownHero'),
            document.getElementById('lockinCountdownROI'),
            document.getElementById('lockinCountdownReg')
        ].forEach(el => { if(el) el.textContent = text; });
    }
    updateLockinCountdowns();
    setInterval(updateLockinCountdowns, 60 * 60 * 1000); // update every hour

    // Update days left badge
    function updateDaysLeftBadge() {
        // Set deadline to July 31, 2025 at end of day
        const lockinDate = new Date(2025, 6, 31, 23, 59, 59); // Month is 0-indexed, so 6 = July
        const now = new Date();
        
        // Calculate difference in milliseconds
        const diff = lockinDate.getTime() - now.getTime();
        
        // Convert to days and round up
        let days = Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
        
        const badgeElement = document.getElementById('daysLeftBadge');
        if(badgeElement) {
            badgeElement.textContent = days;
            console.log('Days left calculation:', {
                lockinDate: lockinDate.toISOString(),
                now: now.toISOString(),
                diff: diff,
                days: days
            });
        }
    }
    updateDaysLeftBadge();
    setInterval(updateDaysLeftBadge, 60 * 60 * 1000); // update every hour

    // Update progress bar for lock-in deadline
    function updateLockinProgressBar() {
        const lockinDate = new Date('2024-07-31T23:59:59');
        const now = new Date();
        const total = lockinDate - new Date('2024-06-01T00:00:00');
        const left = Math.max(0, lockinDate - now);
        let percent = 100 - Math.round((left / total) * 100);
        percent = Math.max(0, Math.min(100, percent));
        document.getElementById('lockinProgressBar').style.width = percent + '%';
    }
    updateLockinProgressBar();
    setInterval(updateLockinProgressBar, 60 * 60 * 1000);
    </script>
</body>
</html>