<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Invest in Ama Ko Shop</title>
    <link rel="icon" type="image/png" href="/storage/logo/momo_icon.png">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-2 sm:py-4 md:py-8 lg:py-12">
    <!-- 30 Days Countdown Timer -->
    

    <div class="max-w-6xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-4 sm:mb-6 md:mb-8">
            <!-- Main Brand Section -->
            <div class="relative mb-4 sm:mb-6 md:mb-8">
                <!-- Background decorative elements -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 lg:w-80 lg:h-80 bg-gradient-to-r from-purple-200 to-blue-300 rounded-full opacity-20 blur-xl sm:blur-2xl md:blur-3xl"></div>
                </div>
                
                <!-- Brand Name with enhanced styling -->
                <div id="brand-name" class="relative inline-block text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-red tracking-wide sm:tracking-wider bg-gradient-to-r from-red-600 via-red-500 to-red-400 bg-clip-text text-transparent drop-shadow-xl sm:drop-shadow-2xl px-4 sm:px-6 md:px-8 py-2 sm:py-3 md:py-4 rounded-xl sm:rounded-2xl select-none animate-bounce-in transform hover:scale-105 transition-transform duration-300 shadow-red-500/50 red-glow">
                    <div class="relative z-10">आमाको म:म:</div>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent rounded-xl sm:rounded-2xl backdrop-blur-sm"></div>
                    <span class="absolute shimmer left-[-100%] top-0 h-full w-1/3"></span>
                </div>
                
            </div>
            
            <!-- 30 Days Countdown Timer -->
            <div class="mb-4 sm:mb-6 md:mb-8 relative z-20">
                <div class="inline-block bg-gradient-to-r from-red-500 to-red-600 text-yellow-800 px-4 py-3 rounded-lg shadow-lg border border-red-400 relative z-20 isolate">
                    <div class="text-center">
                        <div class="text-sm font-medium mb-1">⏰ Investment Deadline</div>
                        <div class="text-lg font-bold" id="countdown-30-days">30 days</div>
                    </div>
                </div>
            </div>
            
            <!-- Investment Opportunity Section -->
            <div class="relative mb-4 sm:mb-6 md:mb-8">
                <!-- Main opportunity card -->
                <div class="bg-gradient-to-br from-purple-600 via-blue-600 to-indigo-700 text-white px-4 sm:px-6 md:px-8 lg:px-12 py-4 sm:py-5 md:py-6 lg:py-8 rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl inline-block transform hover:scale-105 transition-all duration-300 border-2 border-purple-400/30" style="box-shadow: 0 15px 30px -8px rgba(147, 51, 234, 0.4), 0 0 0 1px rgba(147, 51, 234, 0.1);">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent rounded-2xl sm:rounded-3xl"></div>
                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row items-center justify-center mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 bg-white/20 rounded-full flex items-center justify-center mb-2 sm:mb-0 sm:mr-4">
                                <span class="text-xl sm:text-2xl md:text-3xl">🚀</span>
                            </div>
                            <h3 class="text-lg sm:text-xl md:text-2xl lg:text-4xl font-bold text-center sm:text-left">Investment Opportunity</h3>
                        </div>
                        <p class="text-sm sm:text-base md:text-lg lg:text-xl font-medium mb-2 sm:mb-3 text-center">
                            Raising <span class="font-bold text-yellow-300 text-lg sm:text-xl md:text-2xl lg:text-3xl">₹30 Lakh+</span> for 
                            <span class="font-bold text-yellow-300 text-lg sm:text-xl md:text-2xl lg:text-3xl">30% Equity</span>
                        </p>
                        <div class="text-xs sm:text-sm md:text-base opacity-90 mb-2 text-center">
                            📍 Ama Ko Momo Central & Koteshwor Branch
                        </div>
                        <div class="text-xs sm:text-sm opacity-75 text-center">
                            💡 Accepting investments beyond ₹30L - More investors welcome!
                        </div>
                    </div>
                </div>
                
                <!-- Investment Details -->
                <div class="mt-3 sm:mt-4 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 md:gap-4 max-w-4xl mx-auto">
                    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg p-2 sm:p-3 md:p-4 text-center shadow-md">
                        <div class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600 mb-1">₹30L+</div>
                        <div class="text-xs sm:text-sm text-gray-700 font-medium">Target Funding Goal</div>
                        <div class="text-xs text-gray-500 mt-1">Flexible - More welcome!</div>
                    </div>
                    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg p-2 sm:p-3 md:p-4 text-center shadow-md">
                        <div class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-600 mb-1">30%</div>
                        <div class="text-xs sm:text-sm text-gray-700 font-medium">Equity Offered</div>
                        <div class="text-xs text-gray-500 mt-1">For ₹30L base amount</div>
                    </div>
                    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg p-2 sm:p-3 md:p-4 text-center shadow-md">
                        <div class="text-xl sm:text-2xl md:text-3xl font-bold text-purple-600 mb-1">∞</div>
                        <div class="text-xs sm:text-sm text-gray-700 font-medium">Investor Limit</div>
                        <div class="text-xs text-gray-500 mt-1">No maximum limit</div>
                    </div>
                </div>
                
                <!-- Funding Progress -->
                <div class="mt-4 sm:mt-6 max-w-4xl mx-auto">
                    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl p-3 sm:p-4 md:p-6 shadow-lg">
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-3 gap-2">
                            <h4 class="text-base sm:text-lg md:text-xl font-bold text-gray-800">Funding Progress</h4>
                            <div class="text-center sm:text-right">
                                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600">₹<?php echo e(number_format($stats['total_invested'] ?? 0)); ?></div>
                                <div class="text-xs sm:text-sm text-gray-600">Raised so far</div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="w-full bg-gray-200 rounded-full h-3 sm:h-4 md:h-6">
                                <?php
                                    $target = 3000000; // ₹30 lakhs
                                    $raised = $stats['total_invested'] ?? 0;
                                    $percentage = min(($raised / $target) * 100, 100);
                                    $excessPercentage = max(0, ($raised / $target) * 100 - 100);
                                ?>
                                <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 sm:h-4 md:h-6 rounded-full transition-all duration-500" style="width: <?php echo e($percentage); ?>%"></div>
                                <?php if($excessPercentage > 0): ?>
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 sm:h-4 md:h-6 rounded-full transition-all duration-500" style="width: <?php echo e($excessPercentage); ?>%; margin-left: 100%;"></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between items-center mt-2 gap-1">
                                <div class="text-xs sm:text-sm text-gray-600">
                                    Target: ₹30L (100%)
                                </div>
                                <div class="text-xs sm:text-sm font-medium">
                                    <?php if($raised >= $target): ?>
                                        <span class="text-green-600">Target Achieved! +<?php echo e(number_format($excessPercentage, 1)); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-blue-600"><?php echo e(number_format($percentage, 1)); ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-2 text-center">
                            💡 We accept investments beyond ₹30L - No maximum limit!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        @keyframes shimmer-move {
        0% { transform: translateX(-100%); opacity: 0; }
        50% { opacity: 0.8; }
        100% { transform: translateX(250%); opacity: 0; }
        }

        @keyframes bounce-in {
        0% { transform: scale(0.7); opacity: 0; }
        50% { transform: scale(1.08); opacity: 1; }
        100% { transform: scale(1); }
        }

        @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        }

        @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.3); }
        50% { box-shadow: 0 0 40px rgba(34, 197, 94, 0.6); }
        }

        .shimmer {
            pointer-events: none;
        background: linear-gradient(130deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 50%, rgba(255,255,255,0) 100%);
            filter: blur(2px);
        animation: shimmer-move 3s ease-in-out 1;
        }

        .animate-bounce-in {
        animation: bounce-in 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) 1;
        }

        .float-animation {
        animation: float 3s ease-in-out infinite;
        }

        .pulse-glow {
        animation: pulse-glow 2s ease-in-out infinite;
        }

        /* Glass morphism effect */
        .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Smooth transitions */
        .smooth-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Red glow effect for brand name */
        .red-glow {
        box-shadow: 0 0 30px rgba(220, 38, 38, 0.3), 0 0 60px rgba(220, 38, 38, 0.1);
        }

        .red-glow:hover {
        box-shadow: 0 0 40px rgba(220, 38, 38, 0.4), 0 0 80px rgba(220, 38, 38, 0.2);
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
        const brand = document.getElementById('brand-name');
            if (brand) {
            const shimmer = brand.querySelector('.shimmer');
            if (shimmer) {
            shimmer.style.animation = 'shimmer-move 2.2s ease-in-out';
            }
            }
        });
        </script>

        <!-- Call to Action Section -->
        <div class="text-center mb-4 sm:mb-6 md:mb-8">
            <div class="relative">
                <!-- Background decorative elements -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 lg:w-80 lg:h-80 bg-gradient-to-r from-yellow-200 to-orange-200 rounded-full opacity-20 blur-xl sm:blur-2xl md:blur-3xl"></div>
                </div>
                
                <!-- Main CTA card -->
                <div class="relative bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500 text-white px-4 sm:px-6 md:px-8 lg:px-12 py-4 sm:py-5 md:py-6 lg:py-8 rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl inline-block transform hover:scale-105 transition-all duration-300 pulse-glow">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent rounded-2xl sm:rounded-3xl"></div>
                    <div class="relative z-10">
                        <h3 class="text-base sm:text-lg md:text-2xl font-bold mb-2">🎯 Don't Miss This Opportunity!</h3>
                        <p class="text-xs sm:text-sm md:text-lg mb-2 sm:mb-3">
                            Join us in expanding Ama Ko Momo to new heights. 
                            <span class="font-bold">30% equity for ₹30 lakh base</span> - but we welcome more!
                        </p>
                        <div class="text-xs sm:text-sm opacity-90">
                            ⏰ Limited time opportunity • 📈 High growth potential • 💰 Flexible investment amounts
                        </div>
                        <div class="text-xs sm:text-sm opacity-75 mt-2">
                            🚀 Accepting investments beyond ₹30L - No maximum limit!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard Section -->
        <div class="bg-white rounded-xl md:rounded-2xl shadow-xl p-3 sm:p-4 md:p-8 mb-4 sm:mb-6 md:mb-12">
            <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-center text-gray-900 mb-3 sm:mb-4 md:mb-8">Investment Leaderboard</h2>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 md:gap-6 mb-3 sm:mb-4 md:mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg md:rounded-xl shadow-lg p-2 sm:p-3 md:p-6 text-center text-white">
                    <div class="text-lg sm:text-xl md:text-3xl font-bold"><?php echo e($stats['total_investors'] ?? 0); ?></div>
                    <div class="text-xs sm:text-sm md:text-base text-blue-100">Total Investors</div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg md:rounded-xl shadow-lg p-2 sm:p-3 md:p-6 text-center text-white">
                    <div class="text-sm sm:text-lg md:text-3xl font-bold">₹<?php echo e(number_format($stats['total_invested'] ?? 0)); ?></div>
                    <div class="text-xs sm:text-sm md:text-base text-green-100">Raised Amount</div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg md:rounded-xl shadow-lg p-2 sm:p-3 md:p-6 text-center text-white">
                    <div class="text-sm sm:text-lg md:text-3xl font-bold">₹30L</div>
                    <div class="text-xs sm:text-sm md:text-base text-purple-100">Funding Goal</div>
                </div>
                </div>

            <!-- Top Investors -->
            <div class="mb-3 sm:mb-4 md:mb-8">
                <h3 class="text-base sm:text-lg md:text-2xl font-bold text-gray-900 mb-2 sm:mb-3 md:mb-6">All Interested Investors</h3>
                <div class="text-xs text-gray-600 mb-2 text-center">* Percentage based on ₹1,00,000 = 1% of total investment pool • 💡 Accepting investments beyond ₹30L target</div>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.No</th>
                                    <th class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                                    <th class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Likelihood</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__empty_1 = true; $__currentLoopData = $topInvestors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $investor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
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
                                ?>
                                <tr class="<?php echo e($rowColor); ?> transition-colors">
                                    <td class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-4 whitespace-nowrap">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900"><?php echo e($serialNumber); ?></div>
                                    </td>
                                    <td class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-4 whitespace-nowrap">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900"><?php echo e($investor->name ?? $investor->full_name ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-4 whitespace-nowrap">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">₹<?php echo e(number_format($investor->investment_amount ?? $investor->amount ?? $investor->total_invested ?? 0)); ?></div>
                                    </td>
                                    <td class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-4 whitespace-nowrap">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">
                                            <?php
                                                $investmentAmount = $investor->investment_amount ?? $investor->amount ?? $investor->total_invested ?? 0;
                                                $percentage = ($investmentAmount / 100000) * 1; // 100,000 = 1% (same as registration form)
                                            ?>
                                            <?php echo e(number_format($percentage, 2)); ?>%
                                        </div>
                                    </td>
                                    <td class="px-1 sm:px-2 md:px-6 py-1 sm:py-2 md:py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-1 sm:px-1.5 md:px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($likelihoodColors[$likelihood]); ?>">
                                            <?php echo e($likelihood); ?>/5
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-1 sm:px-2 md:px-6 py-3 sm:py-4 md:py-8 text-center text-gray-600">
                                        <div class="text-xl sm:text-2xl md:text-4xl mb-1 sm:mb-2 md:mb-4">📈</div>
                                        <div class="text-xs sm:text-sm">Be the first to invest and top the leaderboard!</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investment Guide Countdown Section -->
        <div id="pdf-countdown-section" class="max-w-sm mx-auto bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg md:rounded-xl shadow-lg p-2 sm:p-3 md:p-4 mb-3 sm:mb-4 md:mb-6">
            <div class="text-center">
                <div class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 mb-1">Investment Guide Available Soon</h3>
                <p class="text-xs text-gray-600 mb-1 sm:mb-2">You can download our comprehensive investment guide in:</p>
                <div class="text-base sm:text-lg md:text-xl font-bold text-blue-600 mb-1 sm:mb-2">
                    <span id="countdown-timer">10</span> seconds
                </div>
                <button id="download-btn" class="bg-blue-600 text-white px-2 sm:px-3 md:px-4 py-1 sm:py-2 rounded-lg hover:bg-blue-700 transition flex items-center mx-auto opacity-50 cursor-not-allowed text-xs sm:text-sm" disabled>
                    <svg class="w-2 h-2 sm:w-3 sm:h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Download Guide
                </button>
            </div>
            </div>

        <!-- Registration Form and Web App Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 md:gap-8 mb-4 sm:mb-6 md:mb-12">
            <!-- Registration Form -->
            <div class="bg-white rounded-xl md:rounded-2xl shadow-2xl p-3 sm:p-4 md:p-8">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-center text-gray-900 mb-3 sm:mb-4 md:mb-6">Investment Registration</h2>
                <form id="investment-form" class="space-y-3 sm:space-y-4 md:space-y-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-2 sm:px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Ram Shrestha">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" class="w-full px-2 sm:px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Address *</label>
                        <textarea name="address" rows="2" required class="w-full px-2 sm:px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Kathmandu, Nepal"></textarea>
                            </div>
                            <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Amount to Invest (₹) *</label>
                        <input type="number" name="investment_amount" id="investment_amount" min="1000" step="1000" required class="w-full px-2 sm:px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Minimum ₹1,00,000">
                        <div class="mt-2 md:mt-3 p-2 md:p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-xs sm:text-sm font-medium text-blue-800">Investment Percentage:</span>
                                <span id="percentage_display" class="text-xs sm:text-sm md:text-lg font-bold text-blue-600 bg-white px-2 md:px-3 py-1 rounded-md border border-blue-300">0.00%</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 sm:mb-4 md:mb-6">
                        <label class="block text-xs sm:text-sm font-medium text-gray-800 mb-2">
                            Likelihood to Invest <span class="text-red-500">*</span>
                        </label>

                        <div class="flex items-center gap-1 md:gap-2">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="likelihood" value="<?php echo e($i); ?>" id="likelihood-<?php echo e($i); ?>" class="sr-only" required>
                                <label for="likelihood-<?php echo e($i); ?>" class="text-xl sm:text-2xl md:text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-all duration-300 hover:scale-125 star-label" data-value="<?php echo e($i); ?>">
                                    ★
                                </label>
                            <?php endfor; ?>
                        </div>

                        <div id="likelihood-feedback" class="text-xs text-gray-500 mt-1 text-right">
                            <!-- Dynamic label filled by JS -->
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2 md:py-3 rounded-lg font-semibold text-base md:text-lg hover:from-blue-700 hover:to-indigo-700 transition">Submit Investment Application</button>
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
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl sm:rounded-2xl shadow-xl p-3 sm:p-4 md:p-6">
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 101.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                        </div>
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Our Web Application</h4>
                    <p class="text-sm sm:text-base text-gray-600 mb-3 sm:mb-4">Check out our comprehensive web application for more features and tools.</p>
                    
                    <!-- Screenshot Image -->
                    <div class="mb-3 sm:mb-4">
                        <img src="<?php echo e(asset('storage/products/image.png')); ?>" 
                             alt="Ama Ko Shop Web Application Screenshot" 
                             class="w-full max-w-xs mx-auto rounded-lg shadow-md border border-gray-200"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="hidden text-center py-6 sm:py-8 text-gray-500 bg-gray-100 rounded-lg">
                            <svg class="w-8 h-8 sm:w-12 sm:h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm sm:text-base">Web Application Screenshot</p>
                            <p class="text-xs sm:text-sm">Click the button below to visit our web application</p>
                        </div>
                    </div>
                    
                    <a href="https://sabinsecurityhub.xyz/" target="_blank" class="inline-flex items-center bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 text-white px-4 sm:px-6 md:px-8 py-2 sm:py-3 md:py-4 rounded-lg sm:rounded-xl hover:from-green-600 hover:via-emerald-600 hover:to-teal-600 transition-all duration-300 font-bold text-sm sm:text-base md:text-lg shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 mr-2 sm:mr-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 101.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="relative z-10">🚀 Visit sabinsecurityhub.xyz</span>
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 ml-1 sm:ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 md:p-8 max-w-sm sm:max-w-md mx-4 text-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Application Submitted!</h3>
            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Thank you for your investment interest. We will review your application and contact you soon.</p>
            
            <!-- PDF Download Section -->
            <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <h4 class="text-base sm:text-lg font-semibold text-blue-900 mb-2">📄 Investment Guide</h4>
                <p class="text-xs sm:text-sm text-blue-700 mb-2 sm:mb-3">Download our comprehensive investment guide to learn more about our opportunities.</p>
                
                <button onclick="downloadPDFFromModal()" class="inline-flex items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition font-semibold mb-2 sm:mb-3 text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Download Investment Guide
                </button>
                
                <div class="text-xs text-blue-500">
                    📖 Contains detailed information about investment opportunities
                </div>
            </div>
            
            <button onclick="closeSuccessModal()" class="bg-blue-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-blue-700 transition mt-3 sm:mt-4 text-sm sm:text-base">Close</button>
        </div>
    </div>

    <!-- PDF Download Modal -->
    <div id="pdf-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 md:p-8 max-w-sm sm:max-w-md mx-4 text-center">
            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Download Investment Guide</h3>
            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Would you like to download our comprehensive investment guide PDF? It contains detailed information about our investment opportunities and processes.</p>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 justify-center">
                <button onclick="downloadPDF()" class="bg-blue-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center text-sm sm:text-base">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Yes, Download
                </button>
                <button onclick="closePDFModal()" class="bg-gray-300 text-gray-700 px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-400 transition text-sm sm:text-base">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Welcome Popup -->
    <div id="welcome-popup" class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 md:p-8 max-w-sm sm:max-w-lg mx-4 shadow-2xl">
            <!-- Welcome Image -->
            <div class="mb-4 sm:mb-6">
                <img src="/storage/welcome/welcome-image.png" alt="Welcome to Ama Ko Shop" class="w-full h-auto max-h-48 sm:max-h-64 object-contain rounded-lg" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="hidden w-full h-48 sm:h-64 items-center justify-center text-gray-500 bg-gray-100 rounded-lg">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            
            <!-- Okay Button -->
            <div class="text-center">
                <button onclick="closeWelcomePopup()" class="bg-blue-600 text-white px-6 sm:px-10 py-2 sm:py-3 rounded-lg hover:bg-blue-700 transition font-medium text-sm sm:text-base">
                    Continue
                </button>
            </div>
        </div>
    </div>

    <script>
    // Check if popup has been shown before
    const popupShown = localStorage.getItem('pdfPopupShown');
    
    // Show PDF download popup after 15 seconds only if not shown before
    if (!popupShown) {
        setTimeout(function() {
            document.getElementById('pdf-modal').classList.remove('hidden');
            // Mark popup as shown
            localStorage.setItem('pdfPopupShown', 'true');
        }, 15000);
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
        start30DayCountdown();
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
        
        fetch('<?php echo e(route('public.investment.register')); ?>', {
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
        try {
            // Create a temporary link element to trigger download
            const link = document.createElement('a');
            link.href = '<?php echo e(asset("storage/documents/investment-guide.pdf")); ?>';
            link.download = 'Ama-Ko-Investment-Guide.pdf';
            link.style.display = 'none';
            
            // Append to body, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Close the modal after download
            closePDFModal();
            
            // Show a success message
            alert('Investment guide PDF is being downloaded!');
        } catch (error) {
            console.error('Download failed:', error);
            alert('Download failed. Please try again or contact support.');
        }
    }

    function downloadPDFFromModal() {
        try {
            // Create a temporary link element to trigger download
            const link = document.createElement('a');
            link.href = '<?php echo e(asset("storage/documents/investment-guide.pdf")); ?>';
            link.download = 'Ama-Ko-Investment-Guide.pdf';
            link.style.display = 'none';
            
            // Append to body, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show a success message
            alert('Investment guide PDF is being downloaded!');
        } catch (error) {
            console.error('Download failed:', error);
            alert('Download failed. Please try again or contact support.');
        }
    }

    function closePDFModal() {
        const pdfModal = document.getElementById('pdf-modal');
        if (pdfModal) {
            pdfModal.classList.add('hidden');
        }
        
        // Mark popup as cancelled (for tracking purposes)
        popupCancelled = true;
    }

    function startCountdown() {
        // Reset countdown
        countdownTime = 10;
        const countdownTimer = document.getElementById('countdown-timer');
        if (countdownTimer) {
            countdownTimer.textContent = countdownTime;
        }
        
        // Disable download button
        const downloadBtn = document.getElementById('download-btn');
        if (downloadBtn) {
            downloadBtn.disabled = true;
            downloadBtn.classList.add('opacity-50', 'cursor-not-allowed');
            downloadBtn.classList.remove('opacity-100', 'cursor-pointer');
        }
        
        // Start countdown interval
        countdownInterval = setInterval(function() {
            countdownTime--;
            const countdownTimer = document.getElementById('countdown-timer');
            if (countdownTimer) {
                countdownTimer.textContent = countdownTime;
            }
            
            if (countdownTime <= 0) {
                clearInterval(countdownInterval);
                enableDownloadButton();
            }
        }, 1000);
    }

    function enableDownloadButton() {
        const downloadBtn = document.getElementById('download-btn');
        if (downloadBtn) {
            downloadBtn.disabled = false;
            downloadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            downloadBtn.classList.add('opacity-100', 'cursor-pointer');
        }
        
        // Update text
        const countdownTimer = document.getElementById('countdown-timer');
        if (countdownTimer) {
            countdownTimer.textContent = '0';
        }
        
        // Add click event for download
        if (downloadBtn) {
            downloadBtn.onclick = function() {
                downloadPDFFromCountdown();
            };
        }
    }

    function downloadPDFFromCountdown() {
        try {
            // Create a temporary link element to trigger download
            const link = document.createElement('a');
            link.href = '<?php echo e(asset("storage/documents/investment-guide.pdf")); ?>';
            link.download = 'Ama-Ko-Investment-Guide.pdf';
            link.style.display = 'none';
            
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
        } catch (error) {
            console.error('Download failed:', error);
            alert('Download failed. Please try again or contact support.');
        }
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
        if (welcomePopup) {
            // Show popup immediately
            welcomePopup.classList.remove('hidden');
            
            // Hide popup after 10 seconds
            setTimeout(function() {
                if (welcomePopup) {
                    welcomePopup.classList.add('hidden');
                }
            }, 10000);
        }
    }

    function closeWelcomePopup() {
        const welcomePopup = document.getElementById('welcome-popup');
        if (welcomePopup) {
            welcomePopup.classList.add('hidden');
        }
    }

    // 30 Day Countdown functionality
    function start30DayCountdown() {
        // Set the target date (30 days from now)
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 30);
        
        // Store the target date in localStorage to persist across sessions
        if (!localStorage.getItem('investmentDeadline')) {
            localStorage.setItem('investmentDeadline', targetDate.getTime());
        }
        
        const deadline = parseInt(localStorage.getItem('investmentDeadline'));
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = deadline - now;
            
            const countdownElement = document.getElementById('countdown-30-days');
            if (!countdownElement) {
                return; // Element not found, exit function
            }
            
            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                
                if (days > 0) {
                    countdownElement.textContent = `${days} days`;
                } else if (hours > 0) {
                    countdownElement.textContent = `${hours}h ${minutes}m`;
                } else {
                    countdownElement.textContent = `${minutes}m`;
                }
            } else {
                // Deadline has passed
                countdownElement.textContent = 'Expired';
                
                // Change the styling to indicate expired
                const countdownContainer = countdownElement.closest('.bg-gradient-to-r');
                if (countdownContainer) {
                    countdownContainer.classList.remove('from-red-500', 'to-red-600', 'border-red-400');
                    countdownContainer.classList.add('from-gray-500', 'to-gray-600', 'border-gray-400');
                }
            }
        }
        
        // Update countdown immediately
        updateCountdown();
        
        // Update countdown every minute
        setInterval(updateCountdown, 60000);
    }
    </script>
</body>
</html> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/public/investment/index.blade.php ENDPATH**/ ?>