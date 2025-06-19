<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Payment Management - {{ config('app.name', 'Momo Admin') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    
    {{-- Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <!-- Content Wrapper (Full Width) -->
        <div class="flex flex-col flex-1 overflow-y-auto">

            <!-- Top Navbar -->
            <header class="bg-white shadow px-6 py-3">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.dashboard', ['branch' => $branch->id ?? 1]) }}" class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                        <h2 class="text-lg font-semibold">Payment Management</h2>
                        @if(isset($branch))
                            <span class="text-sm text-gray-500">• {{ $branch->name }}</span>
                        @endif
        </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </button>
                        </form>
                    </div>
                </div>
                
                <!-- Compact Summary & Controls -->
                <div class="flex items-center justify-between">
                    <!-- Summary Stats -->
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-xs font-medium text-gray-700">Sales:</span>
                            <span class="text-sm font-bold text-blue-600">Rs {{ number_format($todaySummary['total_sales'], 0) }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-xs font-medium text-gray-700">Orders:</span>
                            <span class="text-sm font-bold text-green-600">{{ $todaySummary['total_orders'] }}</span>
            </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                            <span class="text-xs font-medium text-gray-700">Payments:</span>
                            <span class="text-sm font-bold text-indigo-600">Rs {{ number_format($todaySummary['total_payments'], 0) }}</span>
        </div>
    </div>

                    <!-- Cash Drawer Controls -->
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-600">Drawer:</span>
                            <div class="flex space-x-2">
                                <button id="openDrawerBtn" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors">
                                    <i class="fas fa-door-open mr-1"></i> Open
                                </button>
                                <button id="closeDrawerBtn" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors">
                                    <i class="fas fa-door-closed mr-1"></i> Close
                    </button>
                </div>
                    </div>
                        <div id="drawerStatus" class="bg-blue-100 border border-blue-400 text-blue-700 px-2 py-1 rounded text-xs">
                            Loading...
                </div>
                        <div id="drawerWarning" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-700 px-2 py-1 rounded text-xs">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Closed
            </div>
                        <!-- Cash Drawer Alerts -->
                        <div id="drawerAlerts" class="hidden">
                            <div id="alertList" class="space-y-1">
                                <!-- Individual alerts will be populated here -->
        </div>
                            <div class="mt-2">
                                <button onclick="showCashAdjustmentModal()" class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Quick Adjust
                                </button>
    </div>
                        </div>
        </div>
                </div>
            </header>

            <!-- Cash Drawer Modal -->
            <div id="cashDrawerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-purple-600">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-cash-register mr-2"></i>
                                <span id="modalTitle">Cash Drawer Management</span>
                            </h3>
                            <button id="closeModalBtn" class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
        </div>
                </div>

                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <!-- Status Section -->
                        <div id="statusSection" class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Current Status</h4>
                            <div id="modalDrawerStatus" class="text-sm text-gray-600">
                                Loading...
                </div>
                </div>

                        <!-- Denominations Section -->
                        <div id="denominationsSection" class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-4">Cash Denominations</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 1,000</label>
                                    <input type="number" id="denom_1000" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_1000">0</span></div>
            </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 500</label>
                                    <input type="number" id="denom_500" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="5">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_500">2,500</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 100</label>
                                    <input type="number" id="denom_100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="30">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_100">3,000</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 50</label>
                                    <input type="number" id="denom_50" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="30">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_50">1,500</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 20</label>
                                    <input type="number" id="denom_20" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="30">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_20">600</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 10</label>
                                    <input type="number" id="denom_10" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="30">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_10">300</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 5</label>
                                    <input type="number" id="denom_5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="30">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_5">150</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 2</label>
                                    <input type="number" id="denom_2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="200">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_2">400</span></div>
                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 1</label>
                                    <input type="number" id="denom_1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" value="150">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="total_1">150</span></div>
                </div>
        </div>
    </div>

                        <!-- Total Section -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total Cash:</span>
                                <span id="totalCashAmount" class="text-2xl font-bold text-blue-600">Rs 8,620</span>
        </div>
    </div>

                        <!-- Notes Section -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea id="drawerNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Add any notes about the cash drawer..."></textarea>
                        </div>
        </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                        <button id="cancelModalBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button id="confirmModalBtn" class="px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center">
                            <span id="confirmBtnText">Confirm</span>
                            <span id="loadingSpinner" class="hidden ml-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Settlement Modal -->
            <div id="settlementModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-600 to-red-600">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Pending Cash Payments - Settlement Required
                            </h3>
                            <button id="closeSettlementModalBtn" class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
            </div>
            
                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                            <h4 class="font-medium text-orange-900 mb-2">⚠️ Important Notice</h4>
                            <p class="text-orange-800 text-sm">
                                You cannot close the cash drawer while there are pending cash payments. 
                                Please process all pending payments before closing the drawer.
                            </p>
            </div>

                        <!-- Pending Orders List -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-4">Pending Orders Requiring Payment</h4>
                            <div id="pendingOrdersList" class="space-y-3">
                                <!-- Orders will be populated here -->
        </div>
    </div>

                        <!-- Summary -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total Pending Amount:</span>
                                <span id="totalPendingAmount" class="text-2xl font-bold text-blue-600">Rs 0</span>
        </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                        <button id="cancelSettlementBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button id="processAllPaymentsBtn" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium">
                            Process All Payments
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Alert Settings Modal -->
            <div id="alertSettingsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-indigo-600">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-bell mr-2"></i>
                                Cash Drawer Alert Settings
                            </h3>
                            <button id="closeAlertSettingsModalBtn" class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <h4 class="font-medium text-purple-900 mb-2">📊 Individual Alert Configuration</h4>
                            <p class="text-purple-800 text-sm">
                                Configure individual low and high thresholds for each denomination. Low alerts warn when change is running out, 
                                high alerts notify when too much cash accumulates. <strong>Rs 1000 notes don't need low alerts</strong> as they're the highest denomination.
                            </p>
                        </div>

                        <!-- Alert Settings Table -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-4">Denomination Alert Thresholds</h4>
            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denomination</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low Threshold</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">High Threshold</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                                    <tbody id="alertSettingsTable" class="divide-y divide-gray-200">
                                        <!-- Alert settings will be populated here -->
                    </tbody>
                </table>
            </div>
            </div>
        </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                        <button id="cancelAlertSettingsBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                        <button id="saveAlertSettingsBtn" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors font-medium">
                            Save All Changes
                        </button>
                    </div>
    </div>
</div>

            <!-- Cash Adjustment Modal -->
            <div id="cashAdjustmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-600 to-blue-600">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Secure Cash Drawer Adjustment
                            </h3>
                            <button id="closeCashAdjustmentModalBtn" class="text-white hover:text-gray-200 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="font-medium text-green-900 mb-2">🔐 Secure Adjustment</h4>
                            <p class="text-green-800 text-sm">
                                Add or subtract cash from the drawer. This requires the security password (333122) and will be logged for audit purposes.
                            </p>
                        </div>

                        <!-- Password Section -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Security Password</label>
                            <input type="password" id="adjustmentPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Enter password: 333122">
                        </div>

                        <!-- Reason Section -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Adjustment</label>
                            <input type="text" id="adjustmentReason" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="e.g., Restock change, Remove excess cash, etc.">
                        </div>

                        <!-- Current Denominations Display -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-4">Current Cash in Drawer</h4>
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 1000</div>
                                    <div id="current_1000" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 500</div>
                                    <div id="current_500" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 100</div>
                                    <div id="current_100" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 50</div>
                                    <div id="current_50" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 20</div>
                                    <div id="current_20" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 10</div>
                                    <div id="current_10" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 5</div>
                                    <div id="current_5" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 2</div>
                                    <div id="current_2" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-700">Rs 1</div>
                                    <div id="current_1" class="text-lg font-bold text-gray-900">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Adjustment Inputs -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-4">Adjustment Amounts (Use + for add, - for subtract)</h4>
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 1000</label>
                                    <input type="number" id="adjust_1000" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_1000">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Rs 500</label>
                                    <input type="number" id="adjust_500" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_500">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 100</label>
                                    <input type="number" id="adjust_100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_100">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 50</label>
                                    <input type="number" id="adjust_50" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_50">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 20</label>
                                    <input type="number" id="adjust_20" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_20">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 10</label>
                                    <input type="number" id="adjust_10" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_10">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 5</label>
                                    <input type="number" id="adjust_5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_5">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 2</label>
                                    <input type="number" id="adjust_2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_2">0</span></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Rs 1</label>
                                    <input type="number" id="adjust_1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                                    <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_1">0</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total Adjustment:</span>
                                <span id="totalAdjustmentAmount" class="text-2xl font-bold text-blue-600">Rs 0</span>
                            </div>
                            <div class="mt-2 text-sm text-gray-600">
                                <span id="adjustmentType">No adjustment</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                        <button id="cancelCashAdjustmentBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button id="confirmCashAdjustmentBtn" onclick="applyCashAdjustment()" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium flex items-center">
                            <span id="confirmAdjustmentBtnText">Apply Adjustment</span>
                            <span id="adjustmentLoadingSpinner" class="hidden ml-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="flex-1 p-6 relative">
                <!-- Blur Overlay for Drawer Status -->
                <div id="drawerBlurOverlay" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-40 flex items-center justify-center transition-all duration-300">
                    <div class="text-center p-8 bg-white rounded-lg shadow-lg border">
                        <div class="text-6xl mb-4">🔒</div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Cash Drawer Required</h3>
                        <p class="text-gray-600 mb-4">Please open the cash drawer to process payments</p>
                        <button onclick="document.getElementById('openDrawerBtn').click()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            Open Cash Drawer
                        </button>
                    </div>
                </div>

                <!-- Cash Drawer Status Bar -->
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
                                <button onclick="document.getElementById('openDrawerBtn').click()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium">
                                    Open Drawer
                                </button>
                                <button onclick="checkPendingCashPayments().then(hasPendingPayments => { if (hasPendingPayments) { showSettlementModal(); } else { showCashDrawerModal('close'); } })" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm font-medium">
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

                <!-- Orders and Payment Container -->
                <div class="flex h-full relative">
                    <!-- Orders Grid - 30% width -->
                    <div class="w-1/3 flex flex-col overflow-hidden border-r border-gray-200">
                        <div class="flex-1 overflow-auto p-4 space-y-6">
                            <!-- Takeaway Orders Section -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-utensils mr-2 text-orange-600"></i>
                                        Takeaway Orders
                                        <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full">
                                            {{ $posOrders->where('order_type', 'takeaway')->count() }}
                                        </span>
                                    </h3>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 gap-3" id="takeawayOrdersGrid">
                                        @foreach($posOrders->where('order_type', 'takeaway') as $order)
                                            <div class="order-item bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors shadow-sm" 
                                                 data-order-id="{{ $order->id }}" 
                                                 data-status="{{ $order->status }}"
                                                 data-total="{{ $order->total }}"
                                                 data-type="{{ $order->order_type }}">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="text-sm font-semibold text-gray-900 truncate">#{{ $order->id }}</h3>
                                                        <p class="text-xs text-gray-600 truncate">{{ $order->user ? $order->user->name : 'Guest' }}</p>
                                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                                            Takeaway
                                                        </span>
                                                    </div>
                                                    <div class="text-right ml-2">
                                                        <p class="text-lg font-bold text-gray-900">Rs{{ number_format($order->total, 0) }}</p>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium 
                                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                               ($order->status === 'processing' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                                                            {{ ucfirst($order->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                                                    <span>{{ $order->items->count() }} items</span>
                                                    <span>{{ $order->created_at->diffForHumans() }}</span>
                                                </div>

                                                <div class="flex justify-center">
                                                    <button class="process-order-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium w-full">
                                                        <i class="fas fa-credit-card mr-1"></i> Process
                                                    </button>
                                                </div>
                                            </div>
                    @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Dine-in Orders Section -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-chair mr-2 text-purple-600"></i>
                                        Dine-in Orders
                                        <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded-full">
                                            {{ $posOrders->where('order_type', 'dine_in')->count() }}
                                        </span>
                                    </h3>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 gap-3" id="dineInOrdersGrid">
                                        @foreach($posOrders->where('order_type', 'dine_in') as $order)
                                            <div class="order-item bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors shadow-sm" 
                                                 data-order-id="{{ $order->id }}" 
                                                 data-status="{{ $order->status }}"
                                                 data-total="{{ $order->total }}"
                                                 data-type="{{ $order->order_type }}">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="text-sm font-semibold text-gray-900 truncate">#{{ $order->id }}</h3>
                                                        <p class="text-xs text-gray-600 truncate">{{ $order->user ? $order->user->name : 'Guest' }}</p>
                                                        @if($order->table)
                                                            <p class="text-xs text-gray-500">Table: {{ $order->table->name }}</p>
                                                        @endif
                                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                            Dine In
                                                        </span>
                                                    </div>
                                                    <div class="text-right ml-2">
                                                        <p class="text-lg font-bold text-gray-900">Rs{{ number_format($order->total, 0) }}</p>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium 
                                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                               ($order->status === 'processing' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                                                            {{ ucfirst($order->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                                                    <span>{{ $order->items->count() }} items</span>
                                                    <span>{{ $order->created_at->diffForHumans() }}</span>
                                            </div>
                                            
                                            <div class="flex justify-center">
                                                <button class="process-order-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium w-full">
                                                    <i class="fas fa-credit-card mr-1"></i> Process
                                                </button>
                                            </div>
                                        </div>
                    @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Online Orders Section -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-globe mr-2 text-green-600"></i>
                                        Online Orders
                                        <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                                            {{ $onlineOrders->count() ?? 0 }}
                                        </span>
                                    </h3>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 gap-3" id="onlineOrdersGrid">
                                        @foreach($onlineOrders ?? [] as $order)
                                            <div class="order-item bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors shadow-sm" 
                                                 data-order-id="{{ $order->id }}" 
                                                 data-status="{{ $order->status }}"
                                                 data-total="{{ $order->total }}"
                                                 data-type="online">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="text-sm font-semibold text-gray-900 truncate">#{{ $order->id }}</h3>
                                                        <p class="text-xs text-gray-600 truncate">{{ $order->user ? $order->user->name : 'Guest' }}</p>
                                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            Online
                                                        </span>
                                                    </div>
                                                    <div class="text-right ml-2">
                                                        <p class="text-lg font-bold text-gray-900">Rs{{ number_format($order->total, 0) }}</p>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium 
                                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                               ($order->status === 'processing' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                                                            {{ ucfirst($order->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                                                    <span>{{ $order->items->count() }} items</span>
                                                    <span>{{ $order->created_at->diffForHumans() }}</span>
                                                </div>

                                                <div class="flex justify-center">
                                                    <button class="process-order-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium w-full">
                                                        <i class="fas fa-credit-card mr-1"></i> Process
                                                    </button>
                                                </div>
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Panel - 70% width -->
                    <div id="paymentPanel" class="w-2/3 bg-white shadow-lg border-l border-gray-200 flex flex-col">
                        <div class="h-full flex flex-col">
                            <!-- Panel Header -->
                            <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">Payment Details</h3>
                                    <button id="closePaymentPanel" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <div class="flex-1 overflow-auto p-6">
                                <div id="paymentForm" class="max-w-4xl mx-auto space-y-6">
                                    <!-- Order Summary -->
                                    <div id="orderSummary" class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-2">Order Summary</h4>
                                        <div id="orderDetails" class="text-sm text-gray-600">
                                            <p class="text-gray-500">Select an order to process payment</p>
                                        </div>
                                    </div>

                                    <!-- Payment Method Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                                        <div class="grid grid-cols-3 gap-3">
                                            <button class="payment-method-btn bg-blue-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors" data-method="cash">
                                                <i class="fas fa-money-bill-wave text-lg mb-1"></i><br>
                                                Cash
                                            </button>
                                            <button class="payment-method-btn bg-green-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors" data-method="card">
                                                <i class="fas fa-credit-card text-lg mb-1"></i><br>
                                                Card
                                            </button>
                                            <button class="payment-method-btn bg-purple-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors" data-method="mobile">
                                                <i class="fas fa-mobile-alt text-lg mb-1"></i><br>
                                                Mobile
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Cash Payment Section: Single List -->
                                    <div id="cashFields" class="hidden">
                                        <h4 class="font-semibold mb-2">Cash Denominations</h4>
                                        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                                            <div class="grid grid-cols-3 gap-2 font-semibold mb-2">
                                                <div>Denomination</div>
                                                <div>Received</div>
                                                <div>Change Given</div>
                                            </div>
                                            <div id="denominationRows" class="space-y-2">
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>1000</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="1000" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="1000" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>500</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="500" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="500" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>100</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="100" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="100" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>50</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="50" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="50" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>20</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="20" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="20" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>10</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="10" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="10" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>5</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="5" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="5" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>2</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="2" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="2" min="0" value="0" readonly disabled>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2 items-center">
                                                    <span>1</span>
                                                    <input type="number" class="denomination-input border rounded px-2 py-1 w-20 text-right" data-value="1" min="0" value="0">
                                                    <input type="number" class="change-given-input border rounded px-2 py-1 w-20 text-right bg-gray-100" data-value="1" min="0" value="0" readonly disabled>
                                                </div>
                                            </div>
                                            <div class="mt-4 font-medium">Total Received: <span id="denominationTotal" class="font-bold">0</span></div>
                                            <div class="mt-2 font-medium">Total Change: <span id="changeAmount" class="font-bold">0</span></div>
                                        </div>
                                    </div>

                                    <!-- Reference Number (for card/mobile) -->
                                    <div id="cardMobileFields" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                                        <input type="text" id="referenceNumber" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                                               placeholder="Transaction reference">
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                        <textarea id="paymentNotes" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                                                  rows="3" placeholder="Optional payment notes"></textarea>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex space-x-3 pt-4">
                                        <button id="processPaymentBtn" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium">
                                            <i class="fas fa-check mr-2"></i> Process Payment
                                        </button>
                                        <button id="cancelPaymentBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg text-sm font-medium">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // Cash Drawer Modal Variables
        let currentDenominations = {
            '1000': 0,
            '500': 5,
            '100': 30,
            '50': 30,
            '20': 30,
            '10': 30,
            '5': 30,
            '2': 200,
            '1': 150
        };

    // Function to update cash drawer status
    function updateDrawerStatus() {
            const branchId = {{ $branch->id ?? 1 }};
            
            fetch(`/admin/cash-drawer/status?branch_id=${branchId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const isDrawerOpen = data.session && data.session.opened_at && !data.session.closed_at;
                
                const statusIndicator = document.getElementById('drawerStatusIndicator');
                const statusText = document.getElementById('drawerStatusText');
                const balanceText = document.getElementById('drawerBalance');
                const startingAmountText = document.getElementById('drawerStartingAmount');
                const sessionDurationText = document.getElementById('drawerSessionDuration');
                const blurOverlay = document.getElementById('drawerBlurOverlay');
                
                // Handle alerts
                updateDrawerAlerts(data.alerts);
                
                if (isDrawerOpen) {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                    statusText.textContent = 'Cash Drawer: Open';
                    balanceText.textContent = `Balance: Rs ${data.total_balance ? data.total_balance.toLocaleString() : '0'}`;
                    startingAmountText.textContent = `Starting: Rs ${data.session.opening_balance ? data.session.opening_balance.toLocaleString() : '0'}`;
                    
                    // Calculate session duration
                    const startTime = new Date(data.session.opened_at);
                    const now = new Date();
                    const duration = Math.floor((now - startTime) / 1000 / 60); // minutes
                    sessionDurationText.textContent = `Duration: ${duration}m`;
                    
                    // Hide blur overlay when drawer is open
                    blurOverlay.classList.add('hidden');
                    
                    // Update detailed information
                    updateDrawerDetails(data);
                } else {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                    statusText.textContent = 'Cash Drawer: Closed';
                    balanceText.textContent = 'Balance: Rs 0';
                    startingAmountText.textContent = 'Starting: Rs 0';
                    sessionDurationText.textContent = 'Duration: --';
                    
                    // Show blur overlay when drawer is closed
                    blurOverlay.classList.remove('hidden');
                    
                    // Clear detailed information
                    clearDrawerDetails();
                }
            })
            .catch(error => {
                console.error('Error fetching drawer status:', error);
                // Show blur overlay on error
                document.getElementById('drawerBlurOverlay').classList.remove('hidden');
            });
        }

        // Function to update drawer alerts
        function updateDrawerAlerts(alerts) {
            const alertsContainer = document.getElementById('drawerAlerts');
            const alertList = document.getElementById('alertList');
            
            if (!alerts || !alerts.has_alerts) {
                alertsContainer.classList.add('hidden');
                return;
            }
            
            // Show alerts container
            alertsContainer.classList.remove('hidden');
            
            // Clear existing alerts
            alertList.innerHTML = '';
            
            // Create individual alert elements
            alerts.alerts.forEach(alert => {
                const alertDiv = document.createElement('div');
                alertDiv.className = `px-2 py-1 rounded text-xs border ${
                    alert.status === 'low' 
                        ? 'bg-red-100 border-red-400 text-red-700' 
                        : 'bg-orange-100 border-orange-400 text-orange-700'
                }`;
                
                const icon = alert.status === 'low' ? 'fa-exclamation-circle' : 'fa-info-circle';
                const statusText = alert.status === 'low' ? 'Low' : 'High';
                
                alertDiv.innerHTML = `
                    <i class="fas ${icon} mr-1"></i>
                    <span class="font-medium">Rs ${alert.denomination.toLocaleString()}</span>: 
                    <span>${statusText} (${alert.current_count}/${alert.threshold})</span>
                `;
                
                alertList.appendChild(alertDiv);
            });
        }

        function updateDrawerDetails(data) {
            // Update starting denominations
            const startingDenominations = document.getElementById('startingDenominations');
            const totalStartingAmount = document.getElementById('totalStartingAmount');
            
            if (data.session.opening_denominations) {
                let startingHTML = '';
                let startingTotal = 0;
                
                Object.entries(data.session.opening_denominations).forEach(([denomination, count]) => {
                    const amount = parseInt(denomination) * count;
                    startingTotal += amount;
                    startingHTML += `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Rs ${parseInt(denomination).toLocaleString()}:</span>
                            <span class="font-medium">${count} × Rs ${parseInt(denomination).toLocaleString()} = Rs ${amount.toLocaleString()}</span>
                        </div>
                    `;
                });
                
                startingDenominations.innerHTML = startingHTML;
                totalStartingAmount.textContent = `Rs ${startingTotal.toLocaleString()}`;
            }
            
            // Update current denominations
            const currentDenominations = document.getElementById('currentDenominations');
            const totalCurrentAmount = document.getElementById('totalCurrentAmount');
            
            if (data.denominations) {
                let currentHTML = '';
                let currentTotal = 0;
                
                Object.entries(data.denominations).forEach(([denomination, count]) => {
                    const amount = parseInt(denomination) * count;
                    currentTotal += amount;
                    currentHTML += `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Rs ${parseInt(denomination).toLocaleString()}:</span>
                            <span class="font-medium">${count} × Rs ${parseInt(denomination).toLocaleString()} = Rs ${amount.toLocaleString()}</span>
                        </div>
                    `;
                });
                
                currentDenominations.innerHTML = currentHTML;
                totalCurrentAmount.textContent = `Rs ${currentTotal.toLocaleString()}`;
            }
            
            // Update session summary
            const sessionStartTime = document.getElementById('sessionStartTime');
            const sessionDuration = document.getElementById('sessionDuration');
            const cashSales = document.getElementById('cashSales');
            const expectedBalance = document.getElementById('expectedBalance');
            const discrepancy = document.getElementById('discrepancy');
            
            // Session start time
            const startTime = new Date(data.session.opened_at);
            sessionStartTime.textContent = startTime.toLocaleTimeString();
            
            // Session duration
            const now = new Date();
            const durationMinutes = Math.floor((now - startTime) / 1000 / 60);
            const hours = Math.floor(durationMinutes / 60);
            const minutes = durationMinutes % 60;
            sessionDuration.textContent = `${hours}h ${minutes}m`;
            
            // Fetch cash sales for this session
            fetchCashSalesForSession(data.session.id).then(cashSalesAmount => {
                cashSales.textContent = `Rs ${cashSalesAmount.toLocaleString()}`;
                
                // Expected balance = starting balance + cash sales
                const expected = (data.session.opening_balance || 0) + cashSalesAmount;
                expectedBalance.textContent = `Rs ${expected.toLocaleString()}`;
                
                // Discrepancy = actual - expected
                const actual = data.total_balance || 0;
                const discrepancyAmount = actual - expected;
                discrepancy.textContent = `Rs ${discrepancyAmount.toLocaleString()}`;
                discrepancy.className = `font-medium ${discrepancyAmount >= 0 ? 'text-green-600' : 'text-red-600'}`;
            });
        }

        function fetchCashSalesForSession(sessionId) {
            return fetch(`/admin/cash-drawer/session-sales?session_id=${sessionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                return data.total_cash_sales || 0;
            })
            .catch(error => {
                console.error('Error fetching cash sales:', error);
                return 0;
            });
        }

        function clearDrawerDetails() {
            // Clear all detailed information
            document.getElementById('startingDenominations').innerHTML = '<div class="text-gray-500">No session data</div>';
            document.getElementById('currentDenominations').innerHTML = '<div class="text-gray-500">No session data</div>';
            document.getElementById('totalStartingAmount').textContent = 'Rs 0';
            document.getElementById('totalCurrentAmount').textContent = 'Rs 0';
            document.getElementById('sessionStartTime').textContent = '--';
            document.getElementById('sessionDuration').textContent = '--';
            document.getElementById('cashSales').textContent = 'Rs 0';
            document.getElementById('expectedBalance').textContent = 'Rs 0';
            document.getElementById('discrepancy').textContent = 'Rs 0';
        }

        // Toggle drawer details
        document.getElementById('toggleDrawerDetails').addEventListener('click', function() {
            const details = document.getElementById('drawerDetails');
            const icon = this.querySelector('i');
            
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                icon.className = 'fas fa-chevron-up';
                // Refresh drawer details when shown
                refreshDrawerDetails();
            } else {
                details.classList.add('hidden');
                icon.className = 'fas fa-chevron-down';
            }
        });

        function refreshDrawerDetails() {
            const branchId = {{ $branch->id ?? 1 }};
            fetch(`/admin/cash-drawer/status?branch_id=${branchId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.session) {
                    updateDrawerDetails(data);
                }
            })
            .catch(error => {
                console.error('Error refreshing drawer details:', error);
            });
        }

        // Auto-refresh drawer details every 30 seconds when visible
        setInterval(() => {
            const details = document.getElementById('drawerDetails');
            if (!details.classList.contains('hidden')) {
                refreshDrawerDetails();
            }
        }, 30000);

        // Handle open drawer button
    document.getElementById('openDrawerBtn').addEventListener('click', function() {
            showCashDrawerModal('open');
        });

        // Handle close drawer button
        document.getElementById('closeDrawerBtn').addEventListener('click', function() {
            // Check for pending cash payments before closing
            checkPendingCashPayments().then(hasPendingPayments => {
                if (hasPendingPayments) {
                    showSettlementModal();
                } else {
                    showCashDrawerModal('close');
                }
            });
        });

        // Handle modal close button
        document.getElementById('closeModalBtn').addEventListener('click', hideCashDrawerModal);
        document.getElementById('cancelModalBtn').addEventListener('click', hideCashDrawerModal);

        // Handle denomination input changes
        document.querySelectorAll('[id^="denom_"]').forEach(input => {
            input.addEventListener('input', function() {
                const denomination = this.id.replace('denom_', '');
                const count = parseInt(this.value) || 0;
                updateDenominationTotal(denomination, count);
                updateDenominationTotals();
            });
        });

        // Handle modal confirmation
        document.getElementById('confirmModalBtn').addEventListener('click', async function() {
            const confirmBtn = this;
            const confirmBtnText = document.getElementById('confirmBtnText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const cancelBtn = document.getElementById('cancelModalBtn');
            
            // Show loading state
            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            loadingSpinner.classList.remove('hidden');
            cancelBtn.disabled = true;
            cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            const branchId = {{ $branch->id ?? 1 }};
            const denominations = getModalDenominations();
            const totalBalance = calculateTotal();
            const notes = document.getElementById('drawerNotes').value;

            try {
                if (currentDrawerAction === 'open') {
                    const response = await fetch('/admin/cash-drawer/open', {
            method: 'POST',
            headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            branch_id: branchId,
                            opening_balance: totalBalance,
                            opening_denominations: denominations,
                            notes: notes
                        })
                    });

                    const data = await response.json();
                    if (data.success || data.message) {
                        hideCashDrawerModal();
                        updateDrawerStatus();
                        showNotification('Cash drawer opened successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to open cash drawer');
                    }
                } else {
                    const response = await fetch('/admin/cash-drawer/close', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            branch_id: branchId,
                            closing_denominations: denominations,
                            notes: notes
                        })
                    });

                    const data = await response.json();
                    if (data.success || data.message) {
                        hideCashDrawerModal();
                        updateDrawerStatus();
                        showNotification('Cash drawer closed successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to close cash drawer');
                    }
                }
            } catch (error) {
                console.error('Cash drawer error:', error);
                showNotification('Error: ' + error.message, 'error');
            } finally {
                // Hide loading state
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                loadingSpinner.classList.add('hidden');
                cancelBtn.disabled = false;
                cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });

        // Simple notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Centralized payment functionality
        window.selectedPaymentMethod = null;
        window.currentOrderData = null;
        let currentDrawerAction = null;

        // Initialize denomination inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to denomination inputs
            document.querySelectorAll('[id^="denom_"]').forEach(input => {
                input.addEventListener('input', function() {
                    const denomination = this.id.replace('denom_', '');
                    const count = parseInt(this.value) || 0;
                    updateDenominationTotal(denomination, count);
                    updateDenominationTotals();
                });
            });
            
            // Initialize totals
            updateDenominationTotals();
            
            // Initialize drawer status
            updateDrawerStatus();
        });

        // Also call updateDrawerStatus immediately in case DOMContentLoaded already fired
        updateDrawerStatus();

        // Test if blur overlay exists
        const blurOverlay = document.getElementById('drawerBlurOverlay');
        console.log('Blur overlay found:', blurOverlay);
        
        // If we can see the drawer is open from the UI, manually hide the blur
        const statusText = document.getElementById('drawerStatusText');
        if (statusText && statusText.textContent.includes('Open')) {
            console.log('Drawer appears to be open, manually hiding blur');
            if (blurOverlay) {
                blurOverlay.classList.add('hidden');
            }
        }
        
        updateDrawerStatus();

        // Process order button click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('process-order-btn')) {
                const orderItem = e.target.closest('.order-item');
                const orderId = orderItem.dataset.orderId;
                const status = orderItem.dataset.status;
                const total = parseFloat(orderItem.dataset.total);
                const type = orderItem.dataset.type;
                
                // Store current order data (make global)
                window.currentOrderData = {
                    orderId: orderId,
                    status: status,
                    total: total,
                    type: type
                };
                
                // Update order details in payment panel
                const orderDetails = document.getElementById('orderDetails');
                const timeAgo = orderItem.querySelector('span:last-child').textContent;
                const customerName = orderItem.querySelector('p').textContent;
                const itemCount = orderItem.querySelector('span:first-child').textContent;
                
                orderDetails.innerHTML = `
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Order ID:</span>
                            <span class="font-medium">#${orderId}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Customer:</span>
                            <span class="font-medium">${customerName}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Type:</span>
                            <span class="font-medium capitalize">${type}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Items:</span>
                            <span class="font-medium">${itemCount}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Time:</span>
                            <span class="font-medium">${timeAgo}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-semibold">Total Amount:</span>
                            <span class="font-bold text-lg">Rs ${total.toFixed(2)}</span>
                        </div>
                    </div>
                `;
                
                // Reset form
                resetPaymentForm();
                
                // Show payment panel (it's now always visible, just highlight the selected order)
                document.querySelectorAll('.order-item').forEach(item => {
                    item.classList.remove('ring-2', 'ring-blue-500');
                });
                orderItem.classList.add('ring-2', 'ring-blue-500');
            }
        });

        // Close payment panel
        document.getElementById('closePaymentPanel').addEventListener('click', function() {
            // Clear selection
            document.querySelectorAll('.order-item').forEach(item => {
                item.classList.remove('ring-2', 'ring-blue-500');
            });
            resetPaymentForm();
            currentOrderData = null;
            
            // Clear order details
            document.getElementById('orderDetails').innerHTML = '<p class="text-gray-500">Select an order to process payment</p>';
        });

        // Cancel payment button
        document.getElementById('cancelPaymentBtn').addEventListener('click', function() {
            // Clear selection
            document.querySelectorAll('.order-item').forEach(item => {
                item.classList.remove('ring-2', 'ring-blue-500');
            });
            resetPaymentForm();
            currentOrderData = null;
            
            // Clear order details
            document.getElementById('orderDetails').innerHTML = '<p class="text-gray-500">Select an order to process payment</p>';
        });

        function resetPaymentForm() {
            // Reset payment method selection
            window.selectedPaymentMethod = null;
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('bg-gray-400');
                if (btn.dataset.method === 'cash') btn.classList.add('bg-blue-600');
                if (btn.dataset.method === 'card') btn.classList.add('bg-green-600');
                if (btn.dataset.method === 'mobile') btn.classList.add('bg-purple-600');
            });
            
            // Hide all field types
            document.getElementById('cashFields').classList.add('hidden');
            document.getElementById('cardMobileFields').classList.add('hidden');
            
            // Clear form fields
            document.querySelectorAll('.denomination-input').forEach(input => input.value = 0);
            document.querySelectorAll('.change-given-input').forEach(input => input.value = 0);
            const refInput = document.getElementById('referenceNumber');
            if (refInput) refInput.value = '';
            const notesInput = document.getElementById('paymentNotes');
            if (notesInput) notesInput.value = '';
            const changeAmountElem = document.getElementById('changeAmount');
            if (changeAmountElem) changeAmountElem.textContent = 'Rs 0.00';
            const denomTotalElem = document.getElementById('denominationTotal');
            if (denomTotalElem) denomTotalElem.textContent = '0';
        }

        // Payment method selection
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('payment-method-btn')) {
                const method = e.target.dataset.method;
                
                // Update button styles
                document.querySelectorAll('.payment-method-btn').forEach(btn => {
                    btn.classList.remove('bg-gray-400');
                    if (btn.dataset.method === 'cash') btn.classList.add('bg-blue-600');
                    if (btn.dataset.method === 'card') btn.classList.add('bg-green-600');
                    if (btn.dataset.method === 'mobile') btn.classList.add('bg-purple-600');
                });
                
                e.target.classList.remove('bg-blue-600', 'bg-green-600', 'bg-purple-600');
                e.target.classList.add('bg-gray-400');
                
                window.selectedPaymentMethod = method;
                
                // Show/hide relevant fields
                const cashFields = document.getElementById('cashFields');
                const cardMobileFields = document.getElementById('cardMobileFields');
                
                if (method === 'cash') {
                    cashFields.classList.remove('hidden');
                    cardMobileFields.classList.add('hidden');
                    
                    // Check if drawer is open by looking at the blur overlay
                    const blurOverlay = document.getElementById('drawerBlurOverlay');
                    if (blurOverlay && !blurOverlay.classList.contains('hidden')) {
                        alert('Please open the cash drawer before processing cash payments.');
                        return;
                    }
                } else {
                    cashFields.classList.add('hidden');
                    cardMobileFields.classList.remove('hidden');
                }
            }
        });

        // Amount received calculation (for cash payments)
        var amountReceivedInput = document.getElementById('amountReceived');
        if (amountReceivedInput) {
            amountReceivedInput.addEventListener('input', function(e) {
                if (!window.currentOrderData) return;
                const total = window.currentOrderData.total;
                const received = parseFloat(e.target.value) || 0;
                const change = received - total;
                if (change >= 0) {
                    e.target.style.borderColor = '#10b981'; // Green for valid amount
                    document.getElementById('changeAmount').textContent = `Change: Rs ${change.toFixed(2)}`;
                    document.getElementById('changeAmount').style.color = '#059669';
                } else {
                    e.target.style.borderColor = '#ef4444'; // Red for insufficient amount
                    document.getElementById('changeAmount').textContent = `Insufficient amount (need Rs ${Math.abs(change).toFixed(2)} more)`;
                    document.getElementById('changeAmount').style.color = '#dc2626';
                }
            });
        }

        // Process payment
        document.getElementById('processPaymentBtn').addEventListener('click', function() {
            if (!window.currentOrderData) return;
            
            const orderId = window.currentOrderData.orderId;
            const total = window.currentOrderData.total;
            
            if (!window.selectedPaymentMethod) {
                alert('Please select a payment method first.');
                return;
            }
            
            // Validate required fields
            if (window.selectedPaymentMethod === 'cash') {
                // Sum denomination inputs for amount received
                let amountReceived = 0;
                document.querySelectorAll('.denomination-input').forEach(input => {
                    const count = parseInt(input.value) || 0;
                    const value = parseInt(input.getAttribute('data-value'));
                    amountReceived += count * value;
                });
                if (amountReceived < total) {
                    alert('Amount received must be equal to or greater than the order total.');
                    return;
                }
                
                // Check if drawer is open
                const blurOverlay = document.getElementById('drawerBlurOverlay');
                if (blurOverlay && !blurOverlay.classList.contains('hidden')) {
                    alert('Please open the cash drawer before processing cash payments.');
                    return;
                }
            }
            
            // Collect payment data
            let amountReceived = total;
            let changeAmount = 0;
            if (window.selectedPaymentMethod === 'cash') {
                amountReceived = 0;
                document.querySelectorAll('.denomination-input').forEach(input => {
                    const count = parseInt(input.value) || 0;
                    const value = parseInt(input.getAttribute('data-value'));
                    amountReceived += count * value;
                });
                changeAmount = amountReceived - total;
            }
            const paymentData = {
                order_id: orderId,
                amount: total,
                payment_method: window.selectedPaymentMethod,
                amount_received: amountReceived,
                change_amount: changeAmount,
                reference_number: String(document.getElementById('referenceNumber').value || ''),
                notes: document.getElementById('paymentNotes').value || '',
                branch_id: {{ $branch->id ?? 1 }}
            };
            
            // Process payment
            processCentralizedPayment(paymentData);
        });

        function processCentralizedPayment(paymentData) {
            const processBtn = document.getElementById('processPaymentBtn');
            const originalText = processBtn.innerHTML;
            
            // Show processing state
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            processBtn.disabled = true;
            
            fetch('/admin/payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Branch-ID': String({{ $branch->id ?? 1 }})
                },
                body: JSON.stringify(paymentData)
        })
        .then(response => response.json())
        .then(data => {
                if (data.success || data.message) {
                    // Show success
                    processBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Success!';
                    processBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    processBtn.classList.add('bg-green-500');
                    
                    // Update order status in the grid
                    const orderItem = document.querySelector(`[data-order-id="${paymentData.order_id}"]`);
                    if (orderItem) {
                        orderItem.dataset.status = 'completed';
                        const statusBadge = orderItem.querySelector('span');
                        statusBadge.textContent = 'Completed';
                        statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    }
                    
                    // Hide payment panel after delay
                    setTimeout(() => {
                        // Clear selection
                        document.querySelectorAll('.order-item').forEach(item => {
                            item.classList.remove('ring-2', 'ring-blue-500');
                        });
                        
                        processBtn.innerHTML = originalText;
                        processBtn.disabled = false;
                        processBtn.classList.remove('bg-green-500');
                        processBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                        
                        // Update counters
                        updatePaymentCounters();
                        
                        // Reset form
                        resetPaymentForm();
                        window.currentOrderData = null;
                        
                        // Clear order details
                        document.getElementById('orderDetails').innerHTML = '<p class="text-gray-500">Select an order to process payment</p>';
                    }, 2000);
                    
            } else {
                    throw new Error(data.message || 'Payment failed');
                }
            })
            .catch(error => {
                console.error('Payment error:', error);
                alert('Payment failed: ' + error.message);
                
                // Reset button
                processBtn.innerHTML = originalText;
                processBtn.disabled = false;
            });
        }

        function updatePaymentCounters() {
            // Update the counters in the tabs
            const pendingCount = document.querySelectorAll('.order-item[data-status="pending"]').length;
            const processingCount = document.querySelectorAll('.order-item[data-status="processing"]').length;
            const completedCount = document.querySelectorAll('.order-item[data-status="completed"]').length;
            
            document.querySelector('#posCount').textContent = pendingCount + processingCount + completedCount;
            document.querySelector('#onlineCount').textContent = {{ $onlineOrders->count() ?? 0 }};
            document.querySelector('#historyCount').textContent = {{ $orderHistory->count() ?? 0 }};
        }

        function updateModalStatus() {
            const branchId = {{ $branch->id ?? 1 }};
            const confirmBtn = document.getElementById('confirmModalBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            
            fetch(`/admin/cash-drawer/status?branch_id=${branchId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const isDrawerOpen = data.session && data.session.opened_at && !data.session.closed_at;
                
                if (currentDrawerAction === 'open') {
                    if (isDrawerOpen) {
                        document.getElementById('modalDrawerStatus').innerHTML = 
                            `<span class="text-green-600">Drawer is already open with Rs ${data.total_balance ? data.total_balance.toLocaleString() : '0'}</span>`;
                        confirmBtn.disabled = true;
                        confirmBtn.className = 'px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center bg-gray-400 cursor-not-allowed';
                        confirmBtnText.textContent = 'Already Open';
                    } else {
                        document.getElementById('modalDrawerStatus').innerHTML = '<span class="text-yellow-600">Drawer is currently closed</span>';
                        confirmBtn.disabled = false;
                        confirmBtn.className = 'px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center bg-green-600 hover:bg-green-700';
                        confirmBtnText.textContent = 'Open Drawer';
                        // Set default denominations for opening
                        populateModalDenominations(currentDenominations);
                    }
                } else {
                    if (isDrawerOpen && data.denominations) {
                        populateModalDenominations(data.denominations);
                        document.getElementById('modalDrawerStatus').innerHTML = 
                            `<span class="text-green-600">Drawer is open with Rs ${data.total_balance ? data.total_balance.toLocaleString() : '0'}</span>`;
                        confirmBtn.disabled = false;
                        confirmBtn.className = 'px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center bg-red-600 hover:bg-red-700';
                        confirmBtnText.textContent = 'Close Drawer';
                    } else {
                        document.getElementById('modalDrawerStatus').innerHTML = 
                            '<span class="text-red-600">No open drawer session found</span>';
                        confirmBtn.disabled = true;
                        confirmBtn.className = 'px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center bg-gray-400 cursor-not-allowed';
                        confirmBtnText.textContent = 'Cannot Close';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching drawer status:', error);
                document.getElementById('modalDrawerStatus').innerHTML = 
                    '<span class="text-red-600">Error loading drawer status</span>';
            });
        }

        function checkPendingCashPayments() {
            return new Promise((resolve) => {
                // Get all pending orders that might have cash payments
                const pendingOrders = document.querySelectorAll('.order-item[data-status="pending"]');
                let cashPaymentOrders = [];
                
                pendingOrders.forEach(order => {
                    const orderId = order.dataset.orderId;
                    const total = parseFloat(order.dataset.total);
                    const type = order.dataset.type;
                    
                    // For now, we'll assume all pending orders might be cash payments
                    // In a real implementation, you'd check the payment method from the database
                    cashPaymentOrders.push({
                        id: orderId,
                        total: total,
                        type: type,
                        customer: order.querySelector('p').textContent,
                        time: order.querySelector('span:last-child').textContent
                    });
                });
                
                resolve(cashPaymentOrders.length > 0);
            });
        }

        function showSettlementModal() {
            const modal = document.getElementById('settlementModal');
            const pendingOrdersList = document.getElementById('pendingOrdersList');
            const totalPendingAmount = document.getElementById('totalPendingAmount');
            
            // Get all pending orders
            const pendingOrders = document.querySelectorAll('.order-item[data-status="pending"]');
            let totalAmount = 0;
            let ordersHTML = '';
            
            pendingOrders.forEach(order => {
                const orderId = order.dataset.orderId;
                const total = parseFloat(order.dataset.total);
                const type = order.dataset.type;
                const customer = order.querySelector('p').textContent;
                const time = order.querySelector('span:last-child').textContent;
                const itemCount = order.querySelector('span:first-child').textContent;
                
                totalAmount += total;
                
                ordersHTML += `
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h5 class="font-semibold text-gray-900">#${orderId}</h5>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        ${type}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">${customer}</p>
                                <p class="text-xs text-gray-500">${itemCount} • ${time}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">Rs ${total.toFixed(2)}</p>
                                <button onclick="processOrderPayment(${orderId}, ${total})" 
                                        class="mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                    Process Payment
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            pendingOrdersList.innerHTML = ordersHTML;
            totalPendingAmount.textContent = `Rs ${totalAmount.toFixed(2)}`;
            
            modal.classList.remove('hidden');
        }

        // Settlement modal event handlers
        document.getElementById('closeSettlementModalBtn').addEventListener('click', hideSettlementModal);
        document.getElementById('cancelSettlementBtn').addEventListener('click', hideSettlementModal);
        
        document.getElementById('processAllPaymentsBtn').addEventListener('click', function() {
            const pendingOrders = document.querySelectorAll('.order-item[data-status="pending"]');
            if (pendingOrders.length === 0) {
                hideSettlementModal();
                showCashDrawerModal('close');
                return;
            }
            
            // Process all pending payments
            processAllPendingPayments(pendingOrders);
        });

        function hideSettlementModal() {
            document.getElementById('settlementModal').classList.add('hidden');
        }

        function processOrderPayment(orderId, total) {
            // Set current order data and show payment panel
            window.currentOrderData = {
                orderId: orderId,
                status: 'pending',
                total: total,
                type: 'takeaway' // Default type
            };
            
            // Find and highlight the order
            const orderItem = document.querySelector(`[data-order-id="${orderId}"]`);
            if (orderItem) {
                document.querySelectorAll('.order-item').forEach(item => {
                    item.classList.remove('ring-2', 'ring-blue-500');
                });
                orderItem.classList.add('ring-2', 'ring-blue-500');
                
                // Update order details
                const orderDetails = document.getElementById('orderDetails');
                const customerName = orderItem.querySelector('p').textContent;
                const itemCount = orderItem.querySelector('span:first-child').textContent;
                const timeAgo = orderItem.querySelector('span:last-child').textContent;
                const type = orderItem.dataset.type;
                
                orderDetails.innerHTML = `
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Order ID:</span>
                            <span class="font-medium">#${orderId}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Customer:</span>
                            <span class="font-medium">${customerName}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Type:</span>
                            <span class="font-medium capitalize">${type}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Items:</span>
                            <span class="font-medium">${itemCount}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Time:</span>
                            <span class="font-medium">${timeAgo}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-semibold">Total Amount:</span>
                            <span class="font-bold text-lg">Rs ${total.toFixed(2)}</span>
                        </div>
                    </div>
                `;
            }
            
            // Hide settlement modal and show payment panel
            hideSettlementModal();
            
            // Auto-select cash payment method
            setTimeout(() => {
                const cashRadio = document.querySelector('input[value="cash"]');
                if (cashRadio) {
                    cashRadio.checked = true;
                    cashRadio.dispatchEvent(new Event('change'));
                }
            }, 100);
        }

        function processAllPendingPayments(pendingOrders) {
            const processBtn = document.getElementById('processAllPaymentsBtn');
            const originalText = processBtn.innerHTML;
            
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            processBtn.disabled = true;
            
            let processedCount = 0;
            const totalOrders = pendingOrders.length;
            
            // Process each order sequentially
            const processNextOrder = (index) => {
                if (index >= totalOrders) {
                    // All orders processed
                    processBtn.innerHTML = '<i class="fas fa-check mr-2"></i> All Processed!';
                    setTimeout(() => {
                        hideSettlementModal();
                        showCashDrawerModal('close');
                        processBtn.innerHTML = originalText;
                        processBtn.disabled = false;
                        updatePaymentCounters();
                    }, 2000);
                    return;
                }
                
                const order = pendingOrders[index];
                const orderId = order.dataset.orderId;
                const total = parseFloat(order.dataset.total);
                
                // Process this order
                const paymentData = {
                    order_id: orderId,
                    amount: total,
                    payment_method: 'cash',
                    amount_received: total,
                    change_amount: 0,
                    reference_number: '',
                    notes: 'Auto-processed during drawer closing',
                    branch_id: {{ $branch->id ?? 1 }}
                };
                
                fetch('/admin/payments', {
            method: 'POST',
            headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Branch-ID': String({{ $branch->id ?? 1 }})
                    },
                    body: JSON.stringify(paymentData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.message) {
                        processedCount++;
                        // Update order status
                        order.dataset.status = 'completed';
                        const statusBadge = order.querySelector('span');
                        if (statusBadge) {
                            statusBadge.textContent = 'Completed';
                            statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                        }
                        
                        // Process next order
                        setTimeout(() => processNextOrder(index + 1), 500);
                    } else {
                        throw new Error(data.message || 'Payment failed');
                    }
                })
                .catch(error => {
                    console.error('Payment error:', error);
                    alert(`Failed to process order #${orderId}: ${error.message}`);
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;
                });
            };
            
            // Start processing
            processNextOrder(0);
        }

        // Cash drawer modal functions
        function showCashDrawerModal(action) {
            currentDrawerAction = action;
            const modal = document.getElementById('cashDrawerModal');
            const modalTitle = document.getElementById('modalTitle');
            const confirmBtn = document.getElementById('confirmModalBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            
            if (action === 'open') {
                modalTitle.textContent = 'Open Cash Drawer';
                confirmBtnText.textContent = 'Open Drawer';
                confirmBtn.className = 'px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center bg-green-600 hover:bg-green-700';
            } else {
                modalTitle.textContent = 'Close Cash Drawer';
                confirmBtnText.textContent = 'Close Drawer';
                confirmBtn.className = 'px-6 py-2 text-white font-medium rounded-md transition-colors flex items-center bg-red-600 hover:bg-red-700';
            }
            
            modal.classList.remove('hidden');
            updateModalStatus();
        }

        function hideCashDrawerModal() {
            const modal = document.getElementById('cashDrawerModal');
            modal.classList.add('hidden');
            
            // Reset form
            document.getElementById('drawerNotes').value = '';
            
            // Reset denominations to defaults for next open
            if (currentDrawerAction === 'open') {
                populateModalDenominations(currentDenominations);
            }
        }

        function populateModalDenominations(denominations) {
            Object.entries(denominations).forEach(([denomination, count]) => {
                const input = document.getElementById(`denom_${denomination}`);
                if (input) {
                    input.value = count;
                    updateDenominationTotal(denomination, count);
                }
            });
        }

        function updateDenominationTotal(denomination, count) {
            const totalElement = document.getElementById(`total_${denomination}`);
            if (totalElement) {
                const total = parseInt(denomination) * count;
                totalElement.textContent = total.toLocaleString();
            }
        }

        function getModalDenominations() {
            const denominations = {};
            document.querySelectorAll('[id^="denom_"]').forEach(input => {
                const denomination = input.id.replace('denom_', '');
                denominations[denomination] = parseInt(input.value) || 0;
            });
            return denominations;
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('[id^="denom_"]').forEach(input => {
                const denomination = parseInt(input.id.replace('denom_', ''));
                const count = parseInt(input.value) || 0;
                total += denomination * count;
            });
            return total;
        }

        function updateDenominationTotals() {
            document.querySelectorAll('[id^="denom_"]').forEach(input => {
                const denomination = input.id.replace('denom_', '');
                const count = parseInt(input.value) || 0;
                updateDenominationTotal(denomination, count);
            });
            
            // Update total cash amount
            const total = calculateTotal();
            document.getElementById('totalCashAmount').textContent = `Rs ${total.toLocaleString()}`;
        }

        

        function hideAlertSettingsModal() {
            const modal = document.getElementById('alertSettingsModal');
            modal.classList.add('hidden');
        }

        function loadAlertSettings() {
            const branchId = {{ $branch->id ?? 1 }};
            
            fetch(`/admin/cash-drawer/alerts?branch_id=${branchId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                populateAlertSettingsTable(data.alerts);
            })
            .catch(error => {
                console.error('Error loading alert settings:', error);
                alert('Failed to load alert settings');
            });
        }

        function populateAlertSettingsTable(alerts) {
            const tableBody = document.getElementById('alertSettingsTable');
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            
            let html = '';
            
            denominations.forEach(denomination => {
                const alert = alerts.find(a => a.denomination === denomination) || {
                    denomination: denomination,
                    low_threshold: denomination === 1000 ? 0 : (denomination <= 10 ? 30 : 10),
                    high_threshold: denomination <= 10 ? 200 : 100,
                    is_active: true
                };
                
                const isHighestDenomination = denomination === 1000;
                const lowThresholdDisabled = isHighestDenomination ? 'disabled' : '';
                const lowThresholdValue = isHighestDenomination ? 0 : alert.low_threshold;
                const lowThresholdClass = isHighestDenomination ? 'w-20 px-2 py-1 border border-gray-300 rounded text-sm bg-gray-100' : 'w-20 px-2 py-1 border border-gray-300 rounded text-sm';
                
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            Rs ${denomination.toLocaleString()}
                            ${isHighestDenomination ? '<span class="text-xs text-gray-500 block">(Highest denomination)</span>' : ''}
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   class="low-threshold ${lowThresholdClass}" 
                                   value="${lowThresholdValue}" 
                                   min="0" 
                                   ${lowThresholdDisabled}
                                   data-denomination="${denomination}">
                            ${isHighestDenomination ? '<div class="text-xs text-gray-500 mt-1">No low alerts needed</div>' : ''}
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   class="high-threshold w-20 px-2 py-1 border border-gray-300 rounded text-sm" 
                                   value="${alert.high_threshold}" 
                                   min="0" 
                                   data-denomination="${denomination}">
                        </td>
                        <td class="px-4 py-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       class="alert-active mr-2" 
                                       ${alert.is_active ? 'checked' : ''} 
                                       data-denomination="${denomination}">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </td>
                        <td class="px-4 py-3">
                            <button onclick="saveAlertSetting(${denomination})" 
                                    class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition-colors">
                                Save
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = html;
        }

        function saveAlertSetting(denomination) {
            const branchId = {{ $branch->id ?? 1 }};
            const lowThresholdInput = document.querySelector(`input[data-denomination="${denomination}"].low-threshold`);
            const highThreshold = document.querySelector(`input[data-denomination="${denomination}"].high-threshold`).value;
            const isActive = document.querySelector(`input[data-denomination="${denomination}"].alert-active`).checked;
            
            // For Rs 1000, always set low threshold to 0
            const lowThreshold = denomination === 1000 ? 0 : lowThresholdInput.value;
            
            if (parseInt(lowThreshold) >= parseInt(highThreshold)) {
                alert('Low threshold must be less than high threshold');
                return;
            }
            
            const data = {
                branch_id: branchId,
                denomination: denomination,
                low_threshold: parseInt(lowThreshold),
                high_threshold: parseInt(highThreshold)
            };
            
            fetch('/admin/cash-drawer/alerts/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Show success message
                    const button = event.target;
                    const originalText = button.textContent;
                    button.textContent = 'Saved!';
                    button.className = 'text-xs bg-green-600 text-white px-2 py-1 rounded transition-colors';
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.className = 'text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition-colors';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error saving alert setting:', error);
                alert('Failed to save alert setting');
            });
        }

        function saveAllAlertSettings() {
            const branchId = {{ $branch->id ?? 1 }};
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            let savedCount = 0;
            
            denominations.forEach(denomination => {
                const lowThresholdInput = document.querySelector(`input[data-denomination="${denomination}"].low-threshold`);
                const highThreshold = document.querySelector(`input[data-denomination="${denomination}"].high-threshold`).value;
                
                // For Rs 1000, always set low threshold to 0
                const lowThreshold = denomination === 1000 ? 0 : lowThresholdInput.value;
                
                if (parseInt(lowThreshold) >= parseInt(highThreshold)) {
                    alert(`Invalid thresholds for Rs ${denomination}: Low must be less than High`);
                    return;
                }
                
                const data = {
                    branch_id: branchId,
                    denomination: denomination,
                    low_threshold: parseInt(lowThreshold),
                    high_threshold: parseInt(highThreshold)
                };
                
                fetch('/admin/cash-drawer/alerts/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    savedCount++;
                    if (savedCount === denominations.length) {
                        alert('All alert settings saved successfully!');
                        hideAlertSettingsModal();
                        updateDrawerStatus(); // Refresh alerts
                    }
                })
                .catch(error => {
                    console.error('Error saving alert setting:', error);
                    alert(`Failed to save alert setting for Rs ${denomination}`);
                });
            });
        }

        // Event listeners for alert settings modal
        document.addEventListener('DOMContentLoaded', function() {
            // Alert Settings Modal Event Listeners
            document.getElementById('closeAlertSettingsModalBtn').addEventListener('click', hideAlertSettingsModal);
            document.getElementById('cancelAlertSettingsBtn').addEventListener('click', hideAlertSettingsModal);
            document.getElementById('saveAlertSettingsBtn').addEventListener('click', saveAllAlertSettings);
            
            // Close modal when clicking outside
            document.getElementById('alertSettingsModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideAlertSettingsModal();
                }
            });

            // Cash Adjustment Modal Event Listeners
            document.getElementById('closeCashAdjustmentModalBtn').addEventListener('click', hideCashAdjustmentModal);
            document.getElementById('cancelCashAdjustmentBtn').addEventListener('click', hideCashAdjustmentModal);
            document.getElementById('confirmCashAdjustmentBtn').addEventListener('click', applyCashAdjustment);
            
            // Close modal when clicking outside
            document.getElementById('cashAdjustmentModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideCashAdjustmentModal();
                }
            });

            // Add event listeners for adjustment inputs
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            denominations.forEach(denomination => {
                const input = document.getElementById(`adjust_${denomination}`);
                if (input) {
                    input.addEventListener('input', updateAdjustmentTotals);
                }
            });
        });

        // Cash Adjustment Functions
        window.showCashAdjustmentModal = function() {
            const modal = document.getElementById('cashAdjustmentModal');
            modal.classList.remove('hidden');
            loadCurrentDenominations();
            resetAdjustmentForm();

            // Always re-attach event listeners for adjustment inputs
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            denominations.forEach(denomination => {
                const input = document.getElementById(`adjust_${denomination}`);
                if (input) {
                    input.removeEventListener('input', window.updateAdjustmentTotals); // Remove old
                    input.addEventListener('input', window.updateAdjustmentTotals);    // Add new
                }
            });

            // Enable the button by default
            const confirmBtn = document.getElementById('confirmCashAdjustmentBtn');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.className = 'px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium flex items-center';
            }
        };

        function hideCashAdjustmentModal() {
            const modal = document.getElementById('cashAdjustmentModal');
            modal.classList.add('hidden');
            resetAdjustmentForm();
        }

        function loadCurrentDenominations() {
            const branchId = {{ $branch->id ?? 1 }};
            
            fetch(`/admin/cash-drawer/status?branch_id=${branchId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.denominations) {
                    Object.entries(data.denominations).forEach(([denomination, count]) => {
                        const element = document.getElementById(`current_${denomination}`);
                        if (element) {
                            element.textContent = count;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading current denominations:', error);
            });
        }

        function resetAdjustmentForm() {
            document.getElementById('adjustmentPassword').value = '';
            document.getElementById('adjustmentReason').value = '';
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            denominations.forEach(denomination => {
                const input = document.getElementById(`adjust_${denomination}`);
                if (input) {
                    input.value = '0';
                }
            });
            updateAdjustmentTotals();
        }

        function updateAdjustmentTotals() {
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            let totalAdjustment = 0;
            let hasAdjustments = false;
            denominations.forEach(denomination => {
                const input = document.getElementById(`adjust_${denomination}`);
                const totalElement = document.getElementById(`adjust_total_${denomination}`);
                if (input && totalElement) {
                    const adjustment = parseInt(input.value) || 0;
                    const total = denomination * adjustment;
                    totalElement.textContent = total.toLocaleString();
                    totalAdjustment += total;
                    if (adjustment !== 0) {
                        hasAdjustments = true;
                    }
                }
            });
            const totalElement = document.getElementById('totalAdjustmentAmount');
            const typeElement = document.getElementById('adjustmentType');
            if (totalElement) {
                totalElement.textContent = `Rs ${totalAdjustment.toLocaleString()}`;
            }
            if (typeElement) {
                if (totalAdjustment > 0) {
                    typeElement.textContent = `Adding Rs ${totalAdjustment.toLocaleString()} to drawer`;
                    typeElement.className = 'text-sm text-green-600';
                } else if (totalAdjustment < 0) {
                    typeElement.textContent = `Removing Rs ${Math.abs(totalAdjustment).toLocaleString()} from drawer`;
                    typeElement.className = 'text-sm text-red-600';
                } else {
                    typeElement.textContent = 'No adjustment';
                    typeElement.className = 'text-sm text-gray-600';
                }
            }
            const confirmBtn = document.getElementById('confirmCashAdjustmentBtn');
            if (confirmBtn) {
                confirmBtn.disabled = !hasAdjustments;
                confirmBtn.className = hasAdjustments 
                    ? 'px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium flex items-center'
                    : 'px-6 py-2 bg-gray-400 text-white rounded-md transition-colors font-medium flex items-center cursor-not-allowed';
            }
        }

        function applyCashAdjustment() {
            const password = document.getElementById('adjustmentPassword').value;
            const reason = document.getElementById('adjustmentReason').value;
            if (!password) {
                alert('Please enter the security password');
                return;
            }
            if (!reason) {
                alert('Please provide a reason for the adjustment');
                return;
            }
            const adjustments = {};
            const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            let hasAdjustments = false;
            denominations.forEach(denomination => {
                const input = document.getElementById(`adjust_${denomination}`);
                if (input) {
                    const adjustment = parseInt(input.value) || 0;
                    if (adjustment !== 0) {
                        adjustments[denomination] = adjustment;
                        hasAdjustments = true;
                    }
                }
            });
            if (!hasAdjustments) {
                alert('Please enter at least one adjustment amount');
                return;
            }
            const confirmBtn = document.getElementById('confirmCashAdjustmentBtn');
            const btnText = document.getElementById('confirmAdjustmentBtnText');
            const spinner = document.getElementById('adjustmentLoadingSpinner');
            btnText.textContent = 'Processing...';
            spinner.classList.remove('hidden');
            confirmBtn.disabled = true;
            const branchId = {{ $branch->id ?? 1 }};
            const data = {
                branch_id: branchId,
                password: password,
                adjustments: adjustments,
                reason: reason
            };
            fetch('/admin/cash-drawer/adjust', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                    alert(`Cash drawer adjusted successfully!\nTotal adjustment: Rs ${data.total_adjustment.toLocaleString()}\nNew balance: Rs ${data.new_balance.toLocaleString()}`);
                    hideCashAdjustmentModal();
                updateDrawerStatus();
            } else {
                    alert(`Failed to adjust cash drawer: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error applying cash adjustment:', error);
                alert('Failed to apply cash adjustment. Please try again.');
            })
            .finally(() => {
                btnText.textContent = 'Apply Adjustment';
                spinner.classList.add('hidden');
                confirmBtn.disabled = false;
            });
        }

        // ... existing code ...
        window.showCashAdjustmentModal = showCashAdjustmentModal;
        window.hideCashAdjustmentModal = hideCashAdjustmentModal;
        window.applyCashAdjustment = applyCashAdjustment;
        window.loadCurrentDenominations = loadCurrentDenominations;
        window.resetAdjustmentForm = resetAdjustmentForm;
        window.updateAdjustmentTotals = updateAdjustmentTotals;
        // ... existing code ...
    });

    // Add toast notification JS at the end of the script
    window.showToast = function(message, type = 'success', duration = 3500) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `flex items-center px-4 py-3 rounded shadow-lg text-white text-sm font-medium transition-all duration-300 opacity-0 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-times-circle'} mr-2 text-lg"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('opacity-0'); toast.classList.add('opacity-100'); }, 10);
        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // Update applyCashAdjustment to use showToast
    window.applyCashAdjustment = function() {
        const password = document.getElementById('adjustmentPassword').value;
        const reason = document.getElementById('adjustmentReason').value;
        if (!password) {
            showToast('Please enter the security password', 'error');
            return;
        }
        if (!reason) {
            showToast('Please provide a reason for the adjustment', 'error');
            return;
        }
        const adjustments = {};
        const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
        let hasAdjustments = false;
        denominations.forEach(denomination => {
            const input = document.getElementById(`adjust_${denomination}`);
            if (input) {
                const adjustment = parseInt(input.value) || 0;
                if (adjustment !== 0) {
                    adjustments[denomination] = adjustment;
                    hasAdjustments = true;
                }
            }
        });
        if (!hasAdjustments) {
            showToast('Please enter at least one adjustment amount', 'error');
            return;
        }
        const confirmBtn = document.getElementById('confirmCashAdjustmentBtn');
        const btnText = document.getElementById('confirmAdjustmentBtnText');
        const spinner = document.getElementById('adjustmentLoadingSpinner');
        btnText.textContent = 'Processing...';
        spinner.classList.remove('hidden');
        confirmBtn.disabled = true;
        const branchId = {{ $branch->id ?? 1 }};
        const data = {
            branch_id: branchId,
            password: password,
            adjustments: adjustments,
            reason: reason
        };
        fetch('/admin/cash-drawer/adjust', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Cash drawer adjusted successfully!<br>Total adjustment: Rs ${data.total_adjustment.toLocaleString()}<br>New balance: Rs ${data.new_balance.toLocaleString()}`,'success', 5000);
                hideCashAdjustmentModal();
                updateDrawerStatus();
            } else {
                showToast(`Failed to adjust cash drawer: ${data.message}`, 'error', 5000);
            }
        })
        .catch(error => {
            console.error('Error applying cash adjustment:', error);
            showToast('Failed to apply cash adjustment. Please try again.', 'error', 5000);
        })
        .finally(() => {
            btnText.textContent = 'Apply Adjustment';
            spinner.classList.add('hidden');
            confirmBtn.disabled = false;
        });
    }

    // ... existing code

    // ... existing code ...
    
    // ... existing code ...

    function getChangeDenominations(change) {
        const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
        let remaining = Math.floor(change);
        let result = [];
        denominations.forEach(denom => {
            const count = Math.floor(remaining / denom);
            if (count > 0) {
                result.push({ denom, count });
                remaining -= count * denom;
            }
        });
        return result;
    }

    // Attach input event to all denomination inputs for live update
    document.querySelectorAll('.denomination-input').forEach(input => {
        input.addEventListener('input', updateCashUI);
    });

    function updateCashUI() {
        if (!window.currentOrderData || !window.currentOrderData.total) {
            // Optionally, clear the fields or show a warning
            const denomTotalElem = document.getElementById('denominationTotal');
            if (denomTotalElem) denomTotalElem.textContent = '0';
            const changeElem = document.getElementById('changeAmount');
            if (changeElem) {
                changeElem.textContent = 'Select an order first';
                changeElem.style.color = '#dc2626';
            }
            document.querySelectorAll('.change-given-input').forEach(input => input.value = 0);
            return;
        }

        const totalAmount = parseFloat(window.currentOrderData.total);
        let received = 0;

        document.querySelectorAll('.denomination-input').forEach(input => {
            const denom = parseFloat(input.dataset.value);
            const count = parseInt(input.value) || 0;
            received += denom * count;
        });

        // Update Total Received
        const denomTotalElem = document.getElementById('denominationTotal');
        if (denomTotalElem) {
            denomTotalElem.textContent = received.toLocaleString();
        }

        const change = received - totalAmount;
        const changeElem = document.getElementById('changeAmount');
        if (changeElem) {
            if (change >= 0) {
                changeElem.textContent = `Rs ${change.toFixed(2)}`;
                changeElem.style.color = '#059669';
            } else {
                changeElem.textContent = `Short Rs ${Math.abs(change).toFixed(2)}`;
                changeElem.style.color = '#dc2626';
            }
        }

        const changeDenoms = getChangeDenominations(change > 0 ? change : 0);
        const denomMap = {};
        changeDenoms.forEach(cd => {
            denomMap[cd.denom] = cd.count;
        });

        document.querySelectorAll('.change-given-input').forEach(input => {
            const denom = parseInt(input.getAttribute('data-value'));
            input.value = denomMap[denom] || 0;
        });
    }

    function resetPaymentForm() {
        document.querySelectorAll('.denomination-input').forEach(input => input.value = 0);
        document.querySelectorAll('.change-given-input').forEach(input => input.value = 0);
        const refInput = document.getElementById('referenceNumber');
        if (refInput) refInput.value = '';
        const notesInput = document.getElementById('paymentNotes');
        if (notesInput) notesInput.value = '';
        const changeAmountElem = document.getElementById('changeAmount');
        if (changeAmountElem) changeAmountElem.textContent = 'Rs 0.00';
        const denomTotalElem = document.getElementById('denominationTotal');
        if (denomTotalElem) denomTotalElem.textContent = '0';
    }

    function processCentralizedPayment(paymentData) {
        const processBtn = document.getElementById('processPaymentBtn');
        const originalText = processBtn.innerHTML;

        processBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        processBtn.disabled = true;

        // Ensure cash calculation BEFORE sending
        updateCashUI();

        fetch('/admin/payments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Branch-ID': String({{ $branch->id ?? 1 }})
            },
            body: JSON.stringify(paymentData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                processBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Success!';
                processBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                processBtn.classList.add('bg-green-500');

                const orderItem = document.querySelector(`[data-order-id="${paymentData.order_id}"]`);
                if (orderItem) {
                    orderItem.dataset.status = 'completed';
                    const statusBadge = orderItem.querySelector('span');
                    statusBadge.textContent = 'Completed';
                    statusBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                }

                setTimeout(() => {
                    document.querySelectorAll('.order-item').forEach(item => {
                        item.classList.remove('ring-2', 'ring-blue-500');
                    });

                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;
                    processBtn.classList.remove('bg-green-500');
                    processBtn.classList.add('bg-green-600', 'hover:bg-green-700');

                    updatePaymentCounters();
                    resetPaymentForm();
                    window.currentOrderData = null;

                    document.getElementById('orderDetails').innerHTML = '<p class="text-gray-500">Select an order to process payment</p>';
                }, 2000);
            } else {
                throw new Error(data.message || 'Payment failed');
            }
        })
        .catch(error => {
            console.error('Payment Error:', error);
            processBtn.innerHTML = originalText;
            processBtn.disabled = false;
            alert('Payment failed. Please try again.');
        });
    }

    function getChangeDenominations(change) {
        const denominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
        const result = [];
        denominations.forEach(denom => {
            const count = Math.floor(change / denom);
            if (count > 0) {
                result.push({ denom, count });
                change -= count * denom;
            }
        });
        return result;
    }

    // Add input event listener to update cash UI
    setTimeout(() => {
        document.querySelectorAll('.denomination-input').forEach(input => {
            input.addEventListener('input', updateCashUI);
        });
    }, 500);

    // Helper to attach input listeners to denomination fields
    function attachDenominationInputListeners() {
        document.querySelectorAll('.denomination-input').forEach(input => {
            // Remove previous listener if any (by cloning)
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            newInput.addEventListener('input', updateCashUI);
        });
        attachProcessPaymentBtnListener();
    }

    function attachProcessPaymentBtnListener() {
        const btn = document.getElementById('processPaymentBtn');
        if (btn) {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', function() {
                if (!window.currentOrderData) return;
                const orderId = window.currentOrderData.orderId;
                const total = window.currentOrderData.total;
                if (!window.selectedPaymentMethod) {
                    alert('Please select a payment method first.');
                    return;
                }
                // Validate required fields
                if (window.selectedPaymentMethod === 'cash') {
                    let amountReceived = 0;
                    document.querySelectorAll('.denomination-input').forEach(input => {
                        const count = parseInt(input.value) || 0;
                        const value = parseInt(input.getAttribute('data-value'));
                        amountReceived += count * value;
                    });
                    if (amountReceived < total) {
                        alert('Amount received must be equal to or greater than the order total.');
                        return;
                    }
                    const blurOverlay = document.getElementById('drawerBlurOverlay');
                    if (blurOverlay && !blurOverlay.classList.contains('hidden')) {
                        alert('Please open the cash drawer before processing cash payments.');
                        return;
                    }
                }
                // Collect payment data
                let amountReceived = total;
                let changeAmount = 0;
                if (window.selectedPaymentMethod === 'cash') {
                    amountReceived = 0;
                    document.querySelectorAll('.denomination-input').forEach(input => {
                        const count = parseInt(input.value) || 0;
                        const value = parseInt(input.getAttribute('data-value'));
                        amountReceived += count * value;
                    });
                    changeAmount = amountReceived - total;
                }
                const paymentData = {
                    order_id: orderId,
                    amount: total,
                    payment_method: window.selectedPaymentMethod,
                    amount_received: amountReceived,
                    change_amount: changeAmount,
                    reference_number: String(document.getElementById('referenceNumber').value || ''),
                    notes: document.getElementById('paymentNotes').value || '',
                    branch_id: {{ $branch->id ?? 1 }}
                };
                processCentralizedPayment(paymentData);
            });
        }
    }

    // On DOMContentLoaded, also attach listeners once
    attachDenominationInputListeners();

    // In payment method selection handler, re-attach listeners when cash is selected
    const paymentMethodHandler = function(e) {
        if (e.target.classList.contains('payment-method-btn')) {
            const method = e.target.dataset.method;
            const cashFields = document.getElementById('cashFields');
            const cardMobileFields = document.getElementById('cardMobileFields');
            if (method === 'cash') {
                cashFields.classList.remove('hidden');
                cardMobileFields.classList.add('hidden');
                attachDenominationInputListeners();
            } else {
                cashFields.classList.add('hidden');
                cardMobileFields.classList.remove('hidden');
            }
        }
    };
    document.addEventListener('click', paymentMethodHandler);

    // ... existing code ...
    if (typeof updatePaymentCounters !== 'function') {
        function updatePaymentCounters() {}
    }
    // ... existing code ...


// ... existing code ...
if (typeof updatePaymentCounters !== 'function') {
    function updatePaymentCounters() {}
}
// ... existing code ...
</script>

<!-- Toast Notification -->
<div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>

<script src="/js/payment-manager.js"></script>
</body>
</html> 