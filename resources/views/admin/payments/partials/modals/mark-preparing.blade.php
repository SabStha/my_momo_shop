@php /* Mark as Preparing Confirmation Modal */ @endphp

<!-- Mark as Preparing Confirmation Modal -->
<div id="markPreparingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-all duration-200">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-blue-500 to-blue-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🍳</span>
                    </div>
                    <h3 class="text-xl font-bold text-white">Mark as Preparing</h3>
                </div>
                <button onclick="closeMarkPreparingModal()" class="text-white hover:text-blue-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-blue-900">Order Number:</span>
                    <span id="markPreparingOrderNumber" class="text-lg font-bold text-blue-700">#---</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-blue-900">Table:</span>
                    <span id="markPreparingTableName" class="text-lg font-bold text-blue-700">—</span>
                </div>
            </div>

            <p class="text-gray-700 text-sm">Are you sure you want to mark this order as <strong>preparing</strong>? Kitchen staff will start working on it.</p>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex space-x-3">
                <button onclick="closeMarkPreparingModal()"
                        class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors focus:outline-none">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button onclick="confirmMarkAsPreparing()"
                        class="flex-1 px-4 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-colors shadow focus:outline-none">
                    🍳 Confirm Preparing
                </button>
            </div>
        </div>
    </div>
</div>
