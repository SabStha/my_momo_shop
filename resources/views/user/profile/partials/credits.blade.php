<!-- AmaKo Credits Profile Section -->
<div class="bg-gradient-to-br from-yellow-50 to-orange-100 rounded-xl shadow-lg border border-yellow-200 mb-4 lg:mb-6 overflow-hidden">
    <!-- Header Section -->
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-yellow-600/10 to-orange-600/10"></div>
        <div class="relative px-6 py-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="bg-white/60 backdrop-blur-sm border-t border-white/30 px-6 py-6">
        <!-- Desktop Layout -->
        <div class="hidden md:flex items-center justify-between">
            <!-- Left Side: User Info -->
            <div class="flex items-center space-x-6">
                <!-- Profile Picture -->
                <div class="relative group">
                    <div class="w-20 h-20 rounded-full border-4 border-white shadow-xl overflow-hidden bg-white ring-4 ring-yellow-100">
                        @if($user->profile_picture)
                            <img class="w-full h-full object-cover" 
                                 src="{{ Storage::url($user->profile_picture) }}" 
                                 alt="{{ $user->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-100 to-orange-100">
                                <svg class="h-10 w-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- User Name and Info -->
                <div class="space-y-2">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                            <p class="text-gray-600 font-medium">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Member since {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Current Badge -->
            <div class="text-right">
                @php
                    $highestBadge = $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->orderBy('badge_rank_id', 'desc')->orderBy('badge_tier_id', 'desc')->first();
                @endphp
                
                @if($highestBadge)
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-white/50">
                        <div class="text-sm font-medium text-gray-600 mb-3">Current Achievement</div>
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-400 to-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-600 rounded-full flex items-center justify-center shadow-lg ring-2 ring-white">
                                <span class="text-lg text-white">{{ $highestBadge->badgeClass->icon }}</span>
                            </div>
                            <div class="text-left">
                                <div class="font-bold text-gray-900 text-lg">{{ $highestBadge->badgeClass->name }}</div>
                                <div class="text-sm text-gray-600">{{ $highestBadge->badgeRank->name }} • {{ $highestBadge->badgeTier->name }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-white/50">
                        <div class="text-sm font-medium text-gray-600 mb-3">Current Achievement</div>
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center shadow-lg ring-2 ring-white">
                                <span class="text-lg text-gray-500">🏆</span>
                            </div>
                            <div class="text-left">
                                <div class="font-bold text-gray-900 text-lg">No Badge Yet</div>
                                <div class="text-sm text-gray-600">Start earning badges!</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Mobile Layout -->
        <div class="md:hidden space-y-4">
            <!-- User Info Section -->
            <div class="flex items-center space-x-4">
                <!-- Profile Picture -->
                <div class="relative group">
                    <div class="w-16 h-16 rounded-full border-4 border-white shadow-xl overflow-hidden bg-white ring-4 ring-yellow-100">
                        @if($user->profile_picture)
                            <img class="w-full h-full object-cover" 
                                 src="{{ Storage::url($user->profile_picture) }}" 
                                 alt="{{ $user->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-100 to-orange-100">
                                <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- User Name and Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                    <div class="flex items-center space-x-1 mb-1">
                        <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                        <p class="text-sm text-gray-600 truncate">{{ $user->email }}</p>
                    </div>
                    <div class="flex items-center space-x-1 text-xs text-gray-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Member since {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Current Badge Section -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-white/50">
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-600 mb-3">Current Achievement</div>
                    @php
                        $highestBadge = $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->orderBy('badge_rank_id', 'desc')->orderBy('badge_tier_id', 'desc')->first();
                    @endphp
                    
                    @if($highestBadge)
                        <div class="flex flex-col items-center space-y-3">
                            <div class="w-16 h-16 bg-gradient-to-br from-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-400 to-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-600 rounded-full flex items-center justify-center shadow-lg ring-4 ring-white">
                                <span class="text-2xl text-white">{{ $highestBadge->badgeClass->icon }}</span>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-gray-900 text-lg mb-1">{{ $highestBadge->badgeClass->name }}</div>
                                <div class="text-sm text-gray-600 bg-gray-100 rounded-full px-3 py-1 inline-block">{{ $highestBadge->badgeRank->name }} • {{ $highestBadge->badgeTier->name }}</div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center space-y-3">
                            <div class="w-16 h-16 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center shadow-lg ring-4 ring-white">
                                <span class="text-2xl text-gray-500">🏆</span>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-gray-900 text-lg mb-1">No Badge Yet</div>
                                <div class="text-sm text-gray-600 bg-gray-100 rounded-full px-3 py-1 inline-block">Start earning badges!</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Credit Top-up Section -->
<div class="bg-white/90 backdrop-blur-sm border border-gray-200 rounded-xl p-6 mb-6 shadow-lg">
    <div class="flex items-center space-x-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900">Add Credits</h2>
    </div>
    
    <div class="space-y-6">
        <!-- Tab Navigation -->
        <div class="flex space-x-1 bg-gradient-to-r from-gray-100 to-gray-200 p-1 rounded-lg shadow-inner">
            <button type="button" 
                    id="showQrTab"
                    onclick="switchTopUpTab('show')"
                    class="flex-1 px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 bg-white text-green-600 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                </svg>
                Show QR Code
            </button>
            <button type="button" 
                    id="scanQrTab"
                    onclick="switchTopUpTab('scan')"
                    class="flex-1 px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 text-gray-600 hover:text-green-600 hover:bg-white/50">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                </svg>
                Scan QR Code
            </button>
        </div>

        <!-- Show QR Code Tab Content -->
        <div id="showQrContent" class="space-y-6">
            <div class="text-center">
                <button type="button" 
                        onclick="showTopUpQR()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                    </svg>
                    Show Top-up QR Code
                </button>
            </div>
            
        </div>

        <!-- Scan QR Code Tab Content -->
        <div id="scanQrContent" class="hidden space-y-6">
            <div class="text-center bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-700 font-medium">Scan a QR code or enter barcode to top up your account</p>
            </div>
            
            <!-- Barcode Scanner Section -->
            <div class="space-y-4">
                <div class="text-center">
                    <button type="button" 
                            onclick="startBarcodeScanner()"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                        Scan Barcode
                    </button>
                </div>

                <!-- Manual Entry Option -->
                <div class="text-center">
                    <button type="button" 
                            onclick="showManualEntry()"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium underline">
                        Or enter code manually
                    </button>
                </div>

                <!-- Manual Entry Form (Hidden by default) -->
                <div id="manualEntryForm" class="hidden space-y-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div>
                        <label for="barcode_input" class="block text-sm font-medium text-gray-700 mb-2">
                            QR Code or Barcode
                        </label>
                        <input type="text" 
                               id="barcode_input" 
                               name="barcode_input" 
                               placeholder="Enter QR code data or 12-digit barcode"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm"
                               oninput="formatBarcode(this)">
                        <p class="text-xs text-gray-500 mt-1">Enter QR code data (JSON) or 12-digit barcode</p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" 
                                onclick="processBarcode()"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Process Card
                        </button>
                        
                        <button type="button" 
                                onclick="hideManualEntry()"
                                class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

            <!-- Scanner Modal -->
            <div id="scannerModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-900">Scan Credit Card Barcode</h3>
                            <button onclick="closeScanner()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="mb-6">
                            <div id="scannerContainer" class="relative overflow-hidden rounded-lg bg-gray-100 h-80 flex items-center justify-center border-2 border-dashed border-gray-300">
                                <div class="text-center">
                                    <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                                    </svg>
                                    <p class="text-gray-600 font-medium mb-2">Position the barcode within the frame</p>
                                    <p class="text-sm text-gray-500">Camera access required for scanning</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-blue-800 font-medium mb-2">How to scan:</p>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Hold your device steady</li>
                                <li>• Position the barcode within the frame</li>
                                <li>• Ensure good lighting</li>
                                <li>• Wait for automatic detection</li>
                            </ul>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeScanner()" 
                                    class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                Cancel
                            </button>
                            <button type="button" onclick="toggleScanner()" 
                                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                <span id="scannerButtonText">Start Scanner</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top-up Status -->
            <div id="topUpStatus" class="hidden mt-4 p-4 rounded-lg">
                <div id="topUpSuccess" class="hidden bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 001.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        <span class="font-medium">Top-up successful!</span>
                    </div>
                    <p class="text-sm mt-1" id="topUpMessage"></p>
                </div>
                
                <div id="topUpError" class="hidden bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        <span class="font-medium">Top-up failed!</span>
                    </div>
                    <p class="text-sm mt-1" id="topUpErrorMessage"></p>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Top-up Tab Switching
function switchTopUpTab(tab) {
    const showTab = document.getElementById('showQrTab');
    const scanTab = document.getElementById('scanQrTab');
    const showContent = document.getElementById('showQrContent');
    const scanContent = document.getElementById('scanQrContent');
    
    if (tab === 'show') {
        // Show QR Code tab
        showTab.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
        showTab.classList.remove('text-gray-600', 'hover:text-blue-600');
        scanTab.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
        scanTab.classList.add('text-gray-600', 'hover:text-blue-600');
        
        showContent.classList.remove('hidden');
        scanContent.classList.add('hidden');
    } else {
        // Scan QR Code tab
        scanTab.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
        scanTab.classList.remove('text-gray-600', 'hover:text-blue-600');
        showTab.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
        showTab.classList.add('text-gray-600', 'hover:text-blue-600');
        
        scanContent.classList.remove('hidden');
        showContent.classList.add('hidden');
    }
}

// Barcode Scanner Functions
let scanner = null;
let isScanning = false;

// Barcode formatting
function formatBarcode(input) {
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    input.value = value.substring(0, 12); // Limit to 12 digits
}

// Manual entry functions
function showManualEntry() {
    document.getElementById('manualEntryForm').classList.remove('hidden');
}

function hideManualEntry() {
    document.getElementById('manualEntryForm').classList.add('hidden');
    document.getElementById('barcode_input').value = '';
}

// Scanner functions
function startBarcodeScanner() {
    document.getElementById('scannerModal').classList.remove('hidden');
}

function closeScanner() {
    document.getElementById('scannerModal').classList.add('hidden');
    if (scanner) {
        scanner.stop();
        scanner = null;
    }
    isScanning = false;
    document.getElementById('scannerButtonText').textContent = 'Start Scanner';
}

function toggleScanner() {
    if (isScanning) {
        stopScanner();
    } else {
        startScanner();
    }
}

function startScanner() {
    const scannerContainer = document.getElementById('scannerContainer');
    const buttonText = document.getElementById('scannerButtonText');
    
    // Check if browser supports camera access
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showErrorToast('Camera access not supported in this browser');
        return;
    }
    
    // Set scanning state
    isScanning = true;
    buttonText.textContent = 'Stop Scanner';
    
    // Load HTML5 QR Code scanner library
    if (typeof Html5Qrcode === 'undefined') {
        // Load the library if not already loaded
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode';
        script.onload = function() {
            initializeQRScanner();
        };
        script.onerror = function() {
            showErrorToast('Failed to load QR scanner library');
            isScanning = false;
            buttonText.textContent = 'Start Scanner';
        };
        document.head.appendChild(script);
    } else {
        initializeQRScanner();
    }
            
    function initializeQRScanner() {
        // Clear container
        scannerContainer.innerHTML = '';
        
        // Use Html5Qrcode directly for better camera control
        scanner = new Html5Qrcode("scannerContainer");
        
        // Get available cameras and prefer back camera
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                // Find back camera (environment facing)
                let selectedCamera = cameras.find(camera => 
                    camera.label.toLowerCase().includes('back') || 
                    camera.label.toLowerCase().includes('rear') ||
                    camera.label.toLowerCase().includes('environment')
                );
                
                // If no back camera found, use the last camera (usually back camera)
                if (!selectedCamera) {
                    selectedCamera = cameras[cameras.length - 1];
                }
                
                console.log('Selected camera:', selectedCamera);
                
                // Start scanning with selected camera
                scanner.start(
                    selectedCamera.id,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    },
                    function(decodedText, decodedResult) {
                        // QR code detected
                        console.log('QR Code detected:', decodedText);
                        console.log('QR Code type:', typeof decodedText);
                        console.log('QR Code length:', decodedText.length);
                        
                        // Try to parse as JSON to see the structure
                        try {
                            const qrData = JSON.parse(decodedText);
                            console.log('Parsed QR Data:', qrData);
                            console.log('QR Data keys:', Object.keys(qrData));
                        } catch (e) {
                            console.log('QR Code is not JSON:', e.message);
                        }
                        
                        // Stop the scanner
                        scanner.stop().then(() => {
                            scanner.clear();
                            scanner = null;
                            
                            // Close scanner modal
                            closeScanner();
                            
                            // Show processing message
                            showProcessingMessage();
                            
                            // Process the result
                            processBarcodeResult(decodedText);
                        }).catch(err => {
                            console.error('Error stopping scanner:', err);
                        });
                    },
                    function(error) {
                        // Scan error (ignore common errors)
                        if (error && !error.includes('No QR code found')) {
                            console.warn('QR scan error:', error);
                        }
                    }
                ).catch(err => {
                    console.error('Error starting scanner:', err);
                    alert('Failed to start camera. Please check permissions.');
                });
            } else {
                console.error('No cameras found');
                alert('No cameras found on this device.');
            }
        }).catch(err => {
            console.error('Error getting cameras:', err);
            alert('Failed to access cameras. Please check permissions.');
        });
    }
}

function stopScanner() {
    if (scanner) {
        // Stop the QR scanner properly
        scanner.stop().then(() => {
            scanner.clear();
            scanner = null;
            isScanning = false;
            resetScannerUI();
        }).catch(err => {
            console.error('Error stopping scanner:', err);
            scanner.clear();
            scanner = null;
            isScanning = false;
            resetScannerUI();
        });
    } else {
        isScanning = false;
        resetScannerUI();
    }
}

function resetScannerUI() {
    document.getElementById('scannerButtonText').textContent = 'Start Scanner';
    
    // Reset scanner container
    const scannerContainer = document.getElementById('scannerContainer');
    scannerContainer.innerHTML = `
        <div class="text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                </svg>
            <p class="text-gray-600">Position the QR code within the frame</p>
            <p class="text-sm text-gray-500 mt-2">Camera access required</p>
            </div>
    `;
}

// Process barcode (manual entry)
function processBarcode() {
    const barcode = document.getElementById('barcode_input').value.trim();
    
    if (!barcode) {
        showErrorToast('Please enter a QR code or barcode');
        return;
    }
    
    // Check if it's a 12-digit barcode
    if (barcode.length === 12 && /^\d{12}$/.test(barcode)) {
        processBarcodeResult(barcode);
        return;
    }
    
    // Check if it's JSON (QR code data)
    try {
        const qrData = JSON.parse(barcode);
        if (typeof qrData === 'object' && qrData !== null) {
            processBarcodeResult(barcode);
            return;
        }
    } catch (e) {
        // Not JSON, continue with validation
    }
    
    showErrorToast('Please enter a valid QR code (JSON) or 12-digit barcode');
}

// Show processing message
function showProcessingMessage() {
    // Show a processing message in the main content area
    const topUpStatus = document.getElementById('topUpStatus');
    const topUpSuccess = document.getElementById('topUpSuccess');
    const topUpError = document.getElementById('topUpError');
    const topUpMessage = document.getElementById('topUpMessage');
    
    if (topUpStatus && topUpMessage) {
        topUpStatus.classList.remove('hidden');
        topUpSuccess.classList.add('hidden');
        topUpError.classList.add('hidden');
        topUpMessage.textContent = 'Processing QR code...';
    }
}

// Process barcode result (from scanner or manual entry)
function processBarcodeResult(barcode) {
    // Hide manual entry form
    hideManualEntry();
    
    // Show processing message
    showProcessingMessage();
    
    const formData = new FormData();
    formData.append('barcode', barcode);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("profile.topup") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Server returned non-JSON response');
        }
    })
    .then(data => {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        
        if (data.success) {
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('Top-up successful!');
            }
            document.getElementById('topUpStatus').classList.remove('hidden');
            document.getElementById('topUpSuccess').classList.remove('hidden');
            document.getElementById('topUpError').classList.add('hidden');
            document.getElementById('topUpMessage').textContent = data.message;
        } else {
            document.getElementById('topUpStatus').classList.remove('hidden');
            document.getElementById('topUpSuccess').classList.add('hidden');
            document.getElementById('topUpError').classList.remove('hidden');
            document.getElementById('topUpErrorMessage').textContent = data.message || 'Failed to process credit card';
            showErrorToast(data.message || 'Failed to process credit card');
        }
    })
    .catch(error => {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        showErrorToast('An error occurred while processing the credit card');
    });
}

// Error toast function
function showErrorToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100';
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>${message}</span>
    </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
</div>