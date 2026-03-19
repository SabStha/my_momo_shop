@php /* Cash Drawer Modal Partial */ @endphp
<!-- Cash Drawer Modal -->
<div id="cashDrawerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[98vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-600 to-blue-600">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-cash-register mr-2"></i>
                    Open Cash Drawer
                </h3>
                <button id="closeModalBtn" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <!-- Inline error banner (shown when close is blocked) -->
            <div id="drawerErrorMsg"
                 style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;
                        padding:12px 16px;margin-bottom:16px;color:#dc2626;font-size:14px;font-weight:600;">
            </div>
            <!-- Status Section -->
            <div id="statusSection" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Current Status</h4>
                <div id="modalDrawerStatus" class="text-sm text-gray-600">Loading...</div>
            </div>
            <!-- Denominations Section -->
            <div id="denominationsSection" class="mb-6">
                <h4 class="font-medium text-gray-900 mb-4">Cash Denominations</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach([1000,500,100,50,20,10,5,2,1] as $denom)
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Rs {{ number_format($denom) }}</label>
                        <input type="number" id="denom_{{ $denom }}" class="denom-modal-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-denom="{{ $denom }}" min="0" value="0" oninput="updateDrawerDenomTotal()">
                        <div class="text-xs text-gray-500">Total: Rs <span id="total_{{ $denom }}">0</span></div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Total Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total Cash:</span>
                    <span id="totalCashAmount" class="text-2xl font-bold text-blue-600">Rs 0</span>
                </div>
            </div>
            <!-- Closing Summary (shown only when closing) -->
            <div id="drawerClosingSummary" style="display:none;margin-bottom:24px;">
                <div style="padding:16px;background:#f8faff;border:1px solid #dbeafe;border-radius:10px;">
                    <h4 style="font-size:14px;font-weight:600;color:#1e40af;margin:0 0 12px;">Closing Summary</h4>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;color:#6b7280;">
                        <span>System expects:</span>
                        <span id="expectedTotal" style="font-weight:600;color:#374151;">Rs 0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;color:#6b7280;">
                        <span>You counted:</span>
                        <span id="closingTotalCash" style="font-weight:700;color:#111827;">Rs 0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;
                                padding-top:8px;border-top:1px solid #e5e7eb;">
                        <span>Difference:</span>
                        <span id="closingDifference" style="color:#6b7280;">Rs 0</span>
                    </div>
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
            <button id="cancelModalBtn" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
            <button id="confirmModalBtn" style="display:inline-block;" class="px-6 py-2 bg-green-600 text-white font-medium rounded-md transition-colors flex items-center hover:bg-green-700 focus:ring-2 focus:ring-green-400">
                <span id="confirmBtnText">Confirm</span>
                <span id="loadingSpinner" class="hidden ml-2">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </button>
        </div>
    </div>
</div> 