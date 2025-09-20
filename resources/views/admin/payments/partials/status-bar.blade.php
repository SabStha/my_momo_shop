@php /* Cash Drawer Status Bar Partial */ @endphp
<div class="mb-6 bg-white rounded-lg shadow-sm border">
    <!-- Main Status Row -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div id="drawerStatusIndicator" class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span id="drawerStatusText" class="font-medium text-gray-700">Cash Drawer: Closed</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    <span id="drawerBalance" class="text-gray-500">Balance: Rs 0</span>
                    <span id="drawerStartingAmount" class="text-gray-500">Starting: Rs 0</span>
                    <span id="drawerSessionDuration" class="text-gray-500">Duration: --</span>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="if(typeof showCashDrawerModal === 'function') { showCashDrawerModal('open'); } else { console.error('showCashDrawerModal not available'); }" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium">
                    Open Drawer
                </button>
                <button onclick="if(typeof showCashDrawerModal === 'function') { showCashDrawerModal('close'); } else { console.error('showCashDrawerModal not available'); }" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm font-medium">
                    Close Drawer
                </button>
                <button onclick="showCashAdjustmentModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">
                    <i class="fas fa-money-bill-wave mr-1"></i> Adjust
                </button>
                <button id="toggleDrawerDetails" class="px-3 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- Detailed Drawer Information (Collapsible) -->
    <div id="drawerDetails" class="hidden p-4 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Starting Amount Section -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-play-circle mr-2 text-green-600"></i>
                    Starting Amount
                </h4>
                <div id="startingDenominations" class="space-y-2 text-sm">
                    <div class="text-gray-500">No session data</div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700">Total Starting:</span>
                        <span id="totalStartingAmount" class="font-bold text-green-600">Rs 0</span>
                    </div>
                </div>
            </div>
            <!-- Current Amount Section -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-calculator mr-2 text-blue-600"></i>
                    Current Amount
                </h4>
                <div id="currentDenominations" class="space-y-2 text-sm">
                    <div class="text-gray-500">No session data</div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700">Total Current:</span>
                        <span id="totalCurrentAmount" class="font-bold text-blue-600">Rs 0</span>
                    </div>
                </div>
            </div>
            <!-- Session Summary Section -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-chart-line mr-2 text-purple-600"></i>
                    Session Summary
                </h4>
                <div id="sessionSummary" class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Session Started:</span>
                        <span id="sessionStartTime" class="font-medium">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Duration:</span>
                        <span id="sessionDuration" class="font-medium">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cash Sales:</span>
                        <span id="cashSales" class="font-medium">Rs 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Expected Balance:</span>
                        <span id="expectedBalance" class="font-medium">Rs 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Discrepancy:</span>
                        <span id="discrepancy" class="font-medium">Rs 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 