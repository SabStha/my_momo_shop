<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invest in Ama Ko Shop</title>
    <link rel="icon" type="image/png" href="/storage/logo/momo_icon.png">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h4 class="text-4xl font-extrabold text-gray-900 mb-4">Invest in AmaKo Shop</h4>
           
        </div>

        <!-- Investment Guide Countdown Section -->
        <div id="pdf-countdown-section" class="max-w-sm mx-auto bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-lg p-4 mb-6">
            <div class="text-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Investment Guide Available Soon</h3>
                <p class="text-xs text-gray-600 mb-2">You can download our comprehensive investment guide in:</p>
                <div class="text-xl font-bold text-blue-600 mb-2">
                    <span id="countdown-timer">10</span> seconds
                </div>
                <button id="download-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center mx-auto opacity-50 cursor-not-allowed text-sm" disabled>
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Download Guide
                </button>
            </div>
        </div>

        <!-- Registration Form and Web App Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Investment Registration</h2>
                <form id="investment-form" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Ram Shrestha)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                        <textarea name="address" rows="2" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Kathmandu, Nepal"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount to Invest (₹) *</label>
                        <input type="number" name="investment_amount" id="investment_amount" min="1000" step="1000" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Minimum ₹1,00,000">
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-blue-800">Investment Percentage:</span>
                                <span id="percentage_display" class="text-lg font-bold text-blue-600 bg-white px-3 py-1 rounded-md border border-blue-300">0.00%</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-800 mb-2">
                            Likelihood to Invest <span class="text-red-500">*</span>
                        </label>

                        <div class="flex items-center gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="likelihood" value="{{ $i }}" id="likelihood-{{ $i }}" class="sr-only" required>
                                <label for="likelihood-{{ $i }}" class="text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-all duration-300 hover:scale-125 star-label" data-value="{{ $i }}">
                                    ★
                                </label>
                            @endfor
                        </div>

                        <div id="likelihood-feedback" class="text-xs text-gray-500 mt-1 text-right">
                            <!-- Dynamic label filled by JS -->
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-lg font-semibold text-lg hover:from-blue-700 hover:to-indigo-700 transition">Submit Investment Application</button>
                </form>
                
                <!-- Canva Countdown Section -->
                <div id="canva-countdown-section" class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border border-purple-200 hidden">
                    <h4 class="text-lg font-semibold text-purple-900 mb-2">🎨 Exclusive Canva Access</h4>
                    <p class="text-sm text-purple-700 mb-3">Get access to our exclusive Canva templates for 10 minutes only!</p>
                    
                    <div class="mb-4">
                        <div class="text-sm text-purple-600 mb-1">Time remaining:</div>
                        <div class="text-2xl font-bold text-purple-800" id="canva-countdown">10:00</div>
                    </div>
                    
                    <a href="https://www.canva.com/design/DAGpl_ZDdtk/eWjHhqi7-roi58uos8NMoA/view?utm_content=DAGpl_ZDdtk&utm_campaign=designshare&utm_medium=link2&utm_source=uniquelinks&utlId=hccbfed73f9" target="_blank" id="canva-link" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-700 transition font-semibold mb-3">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 101.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                        Access Canva Templates
                    </a>
                    
                    <div class="text-xs text-purple-500">
                        ⏰ This link will expire in <span id="canva-time-remaining">10 minutes</span>
                    </div>
                </div>
            </div>

            <!-- Web Application Section -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl shadow-xl p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 101.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Our Web Application</h4>
                    <p class="text-gray-600 mb-4">Check out our comprehensive web application for more features and tools.</p>
                    
                    <!-- Screenshot Image -->
                    <div class="mb-4">
                        <img src="{{ asset('storage/products/image.png') }}" 
                             alt="Ama Ko Shop Web Application Screenshot" 
                             class="w-full max-w-xs mx-auto rounded-lg shadow-md border border-gray-200"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="hidden text-center py-8 text-gray-500 bg-gray-100 rounded-lg">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                            <p>Web Application Screenshot</p>
                            <p class="text-sm">Click the button below to visit our web application</p>
                        </div>
                    </div>
                    
                    <a href="https://sabinsecurityhub.xyz/" target="_blank" class="inline-flex items-center bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 text-white px-8 py-4 rounded-xl hover:from-green-600 hover:via-emerald-600 hover:to-teal-600 transition-all duration-300 font-bold text-lg shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        <svg class="w-6 h-6 mr-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 101.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="relative z-10">🚀 Visit sabinsecurityhub.xyz</span>
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Leaderboard Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-8">Investment Leaderboard</h2>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-center text-white">
                    <div class="text-3xl font-bold">{{ $stats['total_investors'] ?? 0 }}</div>
                    <div class="text-blue-100">Total Investors</div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-center text-white">
                    <div class="text-3xl font-bold">₹{{ number_format($stats['total_invested'] ?? 0) }}</div>
                    <div class="text-green-100">All Investementers Total Amount  </div>
                </div>
                
            </div>

            <!-- Top Investors -->
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">All Interested Investors</h3>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Likelihood</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topInvestors ?? [] as $index => $investor)
                                @php
                                    $serialNumber = $loop->iteration;
                                    $likelihood = $investor->likelihood ?? $investor->likelihood_to_invest ?? 1;
                                    $rowColor = '';
                                    if ($likelihood >= 4) {
                                        $rowColor = 'bg-green-50 hover:bg-green-100';
                                    } elseif ($likelihood >= 3) {
                                        $rowColor = 'bg-yellow-50 hover:bg-yellow-100';
                                    } else {
                                        $rowColor = 'bg-red-50 hover:bg-red-100';
                                    }
                                    
                                    $likelihoodColors = [
                                        1 => 'text-red-600 bg-red-100',
                                        2 => 'text-orange-600 bg-orange-100',
                                        3 => 'text-yellow-600 bg-yellow-100',
                                        4 => 'text-blue-600 bg-blue-100',
                                        5 => 'text-green-600 bg-green-100'
                                    ];
                                @endphp
                                <tr class="{{ $rowColor }} transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $serialNumber }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $investor->name ?? $investor->full_name ?? 'N/A' }}</div>
                                    </td>
                                     <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $investor->address ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">₹{{ number_format($investor->investment_amount ?? $investor->amount ?? $investor->total_invested ?? 0) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            @php
                                                $percentage = ($investor->investment_amount ?? $investor->amount ?? $investor->total_invested ?? 0) / ($stats['total_invested'] ?? 1) * 100;
                                            @endphp
                                            {{ number_format($percentage, 2) }}%
                                        </div>
                                    </td>
                                   
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900" style="filter: blur(2px); opacity: 1;">{{ $investor->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $likelihoodColors[$likelihood] }}">
                                            {{ $likelihood }}/5
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                                        <div class="text-4xl mb-4">📈</div>
                                        <div>Be the first to invest and top the leaderboard!</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Investments -->
           
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 max-w-md mx-4 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Application Submitted!</h3>
            <p class="text-gray-600 mb-6">Thank you for your investment interest. We will review your application and contact you soon.</p>
            
            <!-- PDF Download Section -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <h4 class="text-lg font-semibold text-blue-900 mb-2">📄 Investment Guide</h4>
                <p class="text-sm text-blue-700 mb-3">Download our comprehensive investment guide to learn more about our opportunities.</p>
                
                <button onclick="downloadPDFFromModal()" class="inline-flex items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition font-semibold mb-3">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Download Investment Guide
                </button>
                
                <div class="text-xs text-blue-500">
                    📖 Contains detailed information about investment opportunities
                </div>
            </div>
            
            <button onclick="closeSuccessModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition mt-4">Close</button>
        </div>
    </div>

    <!-- PDF Download Modal -->
    <div id="pdf-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 max-w-md mx-4 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Download Investment Guide</h3>
            <p class="text-gray-600 mb-6">Would you like to download our comprehensive investment guide PDF? It contains detailed information about our investment opportunities and processes.</p>
            <div class="flex space-x-4 justify-center">
                <button onclick="downloadPDF()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Yes, Download
                </button>
                <button onclick="closePDFModal()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Welcome Popup -->
    <div id="welcome-popup" class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 max-w-lg mx-4 shadow-2xl">
            <!-- Welcome Image -->
            <div class="mb-6">
                <img src="/storage/welcome/welcome-image.png" alt="Welcome to Ama Ko Shop" class="w-full h-auto max-h-64 object-contain rounded-lg" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="hidden w-full h-64 items-center justify-center text-gray-500 bg-gray-100 rounded-lg">
                    <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            
            <!-- Okay Button -->
            <div class="text-center">
                <button onclick="closeWelcomePopup()" class="bg-blue-600 text-white px-10 py-3 rounded-lg hover:bg-blue-700 transition font-medium text-base">
                    Continue
                </button>
            </div>
        </div>
    </div>

    <script>
    // Check if popup has been shown before
    const popupShown = localStorage.getItem('pdfPopupShown');
    
    // Show PDF download popup after 10 seconds only if not shown before
    if (!popupShown) {
        setTimeout(function() {
            document.getElementById('pdf-modal').classList.remove('hidden');
            // Mark popup as shown
            localStorage.setItem('pdfPopupShown', 'true');
        }, 10000);
    }

    // Countdown variables
    let countdownInterval;
    let countdownTime = 10;
    let popupCancelled = false;
    let canvaCountdownInterval;
    let canvaTimeRemaining = 600; // 10 minutes in seconds

    // Start countdown immediately when page loads
    document.addEventListener('DOMContentLoaded', function() {
        startCountdown();
        setupStarRating();
        setupPercentageCalculation();
        startWelcomePopup();
    });

    // Star rating functionality for new system
    function setupStarRating() {
        const radioButtons = document.querySelectorAll('input[name="likelihood"]');
        const starLabels = document.querySelectorAll('.star-label');
        const feedbackElement = document.getElementById('likelihood-feedback');
        
        const labels = {
            1: 'Very Unlikely',
            2: 'Unlikely', 
            3: 'Maybe',
            4: 'Likely',
            5: 'Very Likely'
        };

        // Handle radio button changes
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedValue = parseInt(this.value);
                updateStarDisplay(selectedValue);
                feedbackElement.textContent = labels[selectedValue] || 'Select your likelihood';
            });
        });

        // Handle star label hover effects
        starLabels.forEach((label, index) => {
            const starValue = index + 1;
            
            // Hover effects
            label.addEventListener('mouseenter', function() {
                highlightStars(starValue);
            });
            
            label.addEventListener('mouseleave', function() {
                const selectedRadio = document.querySelector('input[name="likelihood"]:checked');
                const selectedValue = selectedRadio ? parseInt(selectedRadio.value) : 0;
                updateStarDisplay(selectedValue);
            });
            
            // Click effects
            label.addEventListener('click', function() {
                const radio = document.getElementById(`likelihood-${starValue}`);
                radio.checked = true;
                updateStarDisplay(starValue);
                feedbackElement.textContent = labels[starValue] || 'Select your likelihood';
            });
        });
    }

    function updateStarDisplay(selectedValue) {
        const starLabels = document.querySelectorAll('.star-label');
        
        starLabels.forEach((label, index) => {
            const starValue = index + 1;
            
            // Remove all previous classes
            label.classList.remove('text-yellow-500', 'text-yellow-400', 'scale-110', 'drop-shadow-lg');
            
            if (starValue <= selectedValue) {
                // Selected stars
                label.classList.add('text-yellow-500', 'scale-110', 'drop-shadow-lg');
            } else {
                // Unselected stars
                label.classList.add('text-gray-300');
            }
        });
    }

    function highlightStars(hoverValue) {
        const starLabels = document.querySelectorAll('.star-label');
        
        starLabels.forEach((label, index) => {
            const starValue = index + 1;
            
            // Remove all previous classes
            label.classList.remove('text-yellow-500', 'text-yellow-400', 'scale-110', 'drop-shadow-lg');
            
            if (starValue <= hoverValue) {
                // Hovered stars
                label.classList.add('text-yellow-400', 'scale-125');
            } else {
                // Non-hovered stars
                label.classList.add('text-gray-300');
            }
        });
    }

    // Percentage calculation functionality
    function setupPercentageCalculation() {
        const amountInput = document.getElementById('investment_amount');
        const percentageDisplay = document.getElementById('percentage_display');
        
        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const percentage = (amount / 100000) * 1; // 100,000 = 1%
            percentageDisplay.textContent = `${percentage.toFixed(2)}%`;
        });
    }

    // Form submission
    document.getElementById('investment-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Submitting...';
        submitBtn.disabled = true;
        
        fetch('{{ route('public.investment.register') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('success-modal').classList.remove('hidden');
                document.getElementById('investment-form').reset();
                // Reset likelihood feedback
                document.getElementById('likelihood-feedback').textContent = '';
                // Show Canva countdown section below form
                document.getElementById('canva-countdown-section').classList.remove('hidden');
                // Start Canva countdown
                startCanvaCountdown();
            } else {
                throw new Error(data.message || 'Failed to submit application');
            }
        })
        .catch(error => {
            alert('Failed to submit application. Please try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    function closeSuccessModal() {
        document.getElementById('success-modal').classList.add('hidden');
    }

    function downloadPDF() {
        // Create a temporary link element to trigger download
        const link = document.createElement('a');
        link.href = '/storage/documents/investment-guide.pdf'; // Update this path to your actual PDF file
        link.download = 'Ama-Ko-Investment-Guide.pdf';
        link.target = '_blank';
        
        // Append to body, click, and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Close the modal after download
        closePDFModal();
        
        // Show a success message
        alert('Investment guide PDF is being downloaded!');
    }

    function downloadPDFFromModal() {
        // Create a temporary link element to trigger download
        const link = document.createElement('a');
        link.href = '/storage/documents/investment-guide.pdf'; // Update this path to your actual PDF file
        link.download = 'Ama-Ko-Investment-Guide.pdf';
        link.target = '_blank';
        
        // Append to body, click, and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show a success message
        alert('Investment guide PDF is being downloaded!');
    }

    function closePDFModal() {
        document.getElementById('pdf-modal').classList.add('hidden');
        
        // Mark popup as cancelled (for tracking purposes)
        popupCancelled = true;
    }

    function startCountdown() {
        // Reset countdown
        countdownTime = 10;
        document.getElementById('countdown-timer').textContent = countdownTime;
        
        // Disable download button
        const downloadBtn = document.getElementById('download-btn');
        downloadBtn.disabled = true;
        downloadBtn.classList.add('opacity-50', 'cursor-not-allowed');
        downloadBtn.classList.remove('opacity-100', 'cursor-pointer');
        
        // Start countdown interval
        countdownInterval = setInterval(function() {
            countdownTime--;
            document.getElementById('countdown-timer').textContent = countdownTime;
            
            if (countdownTime <= 0) {
                clearInterval(countdownInterval);
                enableDownloadButton();
            }
        }, 1000);
    }

    function enableDownloadButton() {
        const downloadBtn = document.getElementById('download-btn');
        downloadBtn.disabled = false;
        downloadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        downloadBtn.classList.add('opacity-100', 'cursor-pointer');
        
        // Update text
        document.getElementById('countdown-timer').textContent = '0';
        
        // Add click event for download
        downloadBtn.onclick = function() {
            downloadPDFFromCountdown();
        };
    }

    function downloadPDFFromCountdown() {
        // Create a temporary link element to trigger download
        const link = document.createElement('a');
        link.href = '/storage/documents/investment-guide.pdf'; // Update this path to your actual PDF file
        link.download = 'Ama-Ko-Investment-Guide.pdf';
        link.target = '_blank';
        
        // Append to body, click, and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show a success message
        alert('Investment guide PDF is being downloaded!');
        
        // Restart countdown after download
        setTimeout(function() {
            startCountdown();
        }, 2000);
    }

    // Canva countdown functionality
    function startCanvaCountdown() {
        canvaTimeRemaining = 600; // Reset to 10 minutes
        updateCanvaDisplay();
        
        canvaCountdownInterval = setInterval(function() {
            canvaTimeRemaining--;
            updateCanvaDisplay();
            
            if (canvaTimeRemaining <= 0) {
                clearInterval(canvaCountdownInterval);
                disableCanvaLink();
            }
        }, 1000);
    }

    function updateCanvaDisplay() {
        const minutes = Math.floor(canvaTimeRemaining / 60);
        const seconds = canvaTimeRemaining % 60;
        const countdownElement = document.getElementById('canva-countdown');
        const timeRemainingElement = document.getElementById('canva-time-remaining');
        
        if (countdownElement) {
            countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        if (timeRemainingElement) {
            if (minutes > 0) {
                timeRemainingElement.textContent = `${minutes} minute${minutes > 1 ? 's' : ''}`;
            } else {
                timeRemainingElement.textContent = `${seconds} second${seconds > 1 ? 's' : ''}`;
            }
        }
    }

    function disableCanvaLink() {
        const canvaLink = document.getElementById('canva-link');
        const canvaSection = document.getElementById('canva-section');
        
        if (canvaLink) {
            canvaLink.href = '#';
            canvaLink.onclick = function(e) {
                e.preventDefault();
                alert('Sorry! The Canva access link has expired. Please submit another application to get a new link.');
            };
            canvaLink.classList.remove('bg-gradient-to-r', 'from-purple-600', 'to-pink-600', 'hover:from-purple-700', 'hover:to-pink-700');
            canvaLink.classList.add('bg-gray-400', 'cursor-not-allowed');
            canvaLink.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg>Link Expired';
        }
        
        if (canvaSection) {
            canvaSection.classList.remove('from-purple-50', 'to-pink-50', 'border-purple-200');
            canvaSection.classList.add('bg-gray-100', 'border-gray-300');
        }
    }

    // Welcome popup functionality
    function startWelcomePopup() {
        const welcomePopup = document.getElementById('welcome-popup');
        
        // Show popup immediately
        welcomePopup.classList.remove('hidden');
        
        // Hide popup after 10 seconds
        setTimeout(function() {
            welcomePopup.classList.add('hidden');
        }, 10000);
    }

    function closeWelcomePopup() {
        document.getElementById('welcome-popup').classList.add('hidden');
    }
    </script>
</body>
</html> 