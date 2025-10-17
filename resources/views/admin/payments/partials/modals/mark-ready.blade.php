@php /* Mark as Ready Confirmation Modal */ @endphp
<style>
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes scaleOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
    
    .animate-scale-in {
        animation: scaleIn 0.2s ease-out forwards;
    }
    
    .animate-scale-out {
        animation: scaleOut 0.2s ease-in forwards;
    }
    
    #markReadyModal {
        backdrop-filter: blur(4px);
    }
</style>

<!-- Mark as Ready Confirmation Modal -->
<div id="markReadyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-all duration-200">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden transform transition-all duration-200">
        <!-- Modal Header with Gradient -->
        <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">Mark Order as Ready</h3>
                </div>
                <button onclick="closeMarkReadyModal()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Order Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-blue-900">Order Number:</span>
                    <span id="markReadyOrderNumber" class="text-lg font-bold text-blue-700">#---</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-blue-900">Total Amount:</span>
                    <span id="markReadyOrderAmount" class="text-lg font-bold text-blue-700">Rs 0.00</span>
                </div>
            </div>

            <!-- Warning Message -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded p-4 mb-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-0.5"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">Important Confirmation</h4>
                        <p class="text-sm text-yellow-700 mb-1">Before marking this order as ready, please ensure:</p>
                    </div>
                </div>
            </div>

            <!-- Checklist -->
            <div class="space-y-3 mb-5">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Order is completely prepared</p>
                        <p class="text-xs text-gray-500">All items are cooked and packaged</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Quality checked</p>
                        <p class="text-xs text-gray-500">Food meets quality standards</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-bell text-blue-600 text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Customer will be notified</p>
                        <p class="text-xs text-gray-500">Push notification sent to mobile app</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-5 h-5 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-lock text-purple-600 text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Cannot be undone</p>
                        <p class="text-xs text-gray-500">Status change is permanent</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer with Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex space-x-3">
                <button onclick="closeMarkReadyModal()" 
                        class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button onclick="confirmMarkAsReady()" 
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transform hover:scale-105">
                    <i class="fas fa-check-circle mr-2"></i>Mark as Ready
                </button>
            </div>
        </div>
    </div>
</div>

