@php /* Settlement Modal Partial */ @endphp
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
            <button id="cancelSettlementBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
            <button id="processAllPaymentsBtn" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium">Process All Payments</button>
        </div>
    </div>
</div> 