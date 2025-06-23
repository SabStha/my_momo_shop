@php /* Physical Drawer Denominations Modal */ @endphp
<div id="physicalDrawerDenominationsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[98vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-600 to-yellow-400">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-cash-register mr-2"></i>
                    Cash Drawer Denominations
                </h3>
                <button id="closePhysicalDrawerDenominationsModalBtn" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <!-- Password Section -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="font-medium text-yellow-900 mb-2">🔐 Security Required</h4>
                <p class="text-yellow-800 text-sm mb-4">
                    To modify denominations, please enter the security password.
                </p>
                <div class="flex items-center space-x-4">
                    <input type="password" id="denominationPassword" class="flex-1 px-3 py-2 border border-yellow-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" placeholder="Enter security password">
                    <button id="validatePasswordBtn" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors">
                        Validate
                    </button>
                </div>
                <div id="passwordError" class="mt-2 text-red-600 text-sm hidden">Invalid password. Please try again.</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Starting Denominations -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-4">Starting Denominations</h4>
                    <div id="startingDenomsList" class="space-y-2"></div>
                    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
                        <span class="font-medium text-gray-700">Total Starting:</span>
                        <span id="totalStartingDenoms" class="font-bold text-green-600">Rs 0</span>
                    </div>
                </div>
                <!-- Current Denominations (Editable) -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-4">Current Denominations</h4>
                    <div id="currentDenomsList" class="space-y-2"></div>
                    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
                        <span class="font-medium text-gray-700">Total Current:</span>
                        <span id="totalCurrentDenoms" class="font-bold text-blue-600">Rs 0</span>
                    </div>
                </div>
            </div>
            <!-- Alerts Section -->
            <div id="denominationAlertsSection" class="mt-8">
                <h4 class="font-medium text-red-700 mb-4 flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i>Denomination Alerts</h4>
                <div id="denominationAlertsList" class="space-y-3"></div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-4">
            <button id="saveDenominationsBtn" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Save Changes
            </button>
            <button id="closePhysicalDrawerDenominationsModalBtn2" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                Close
            </button>
        </div>
    </div>
</div> 