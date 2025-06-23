@php /* Cash Adjustment Modal Partial */ @endphp
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
                    @foreach([1000,500,100,50,20,10,5,2,1] as $denom)
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-700">Rs {{ $denom }}</div>
                        <div id="current_{{ $denom }}" class="text-lg font-bold text-gray-900">0</div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Adjustment Inputs -->
            <div class="mb-6">
                <h4 class="font-medium text-gray-900 mb-4">Adjustment Amounts (Use + for add, - for subtract)</h4>
                <div class="grid grid-cols-3 md:grid-cols-5 gap-4">
                    @foreach([1000,500,100,50,20,10,5,2,1] as $denom)
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Rs {{ $denom }}</label>
                        <input type="number" id="adjust_{{ $denom }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="0" value="0">
                        <div class="text-xs text-gray-500">Total: Rs <span id="adjust_total_{{ $denom }}">0</span></div>
                    </div>
                    @endforeach
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
            <button id="cancelCashAdjustmentBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
            <button id="confirmCashAdjustmentBtn" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium flex items-center">
                <span id="confirmAdjustmentBtnText">Apply Adjustment</span>
                <span id="adjustmentLoadingSpinner" class="hidden ml-2">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </button>
        </div>
    </div>
</div> 