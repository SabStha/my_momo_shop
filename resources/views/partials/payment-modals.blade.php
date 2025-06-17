{{-- resources/views/partials/payment-modals.blade.php --}}

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 id="successModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2"></h3>
            <div class="mt-2 px-7 py-3">
                <p id="successModalMessage" class="text-sm text-gray-500"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="successModalClose" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 id="errorModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2"></h3>
            <div class="mt-2 px-7 py-3">
                <p id="errorModalMessage" class="text-sm text-gray-500"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="errorModalClose" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 id="loadingModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2">Processing...</h3>
            <div class="mt-2 px-7 py-3">
                <p id="loadingModalMessage" class="text-sm text-gray-500">Please wait while we process your request.</p>
            </div>
        </div>
    </div>
</div>

<!-- Session Modal -->
<div id="sessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="sessionModalTitle" class="text-xl font-semibold text-gray-900"></h3>
                <button id="closeSessionModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <!-- Denominations -->
                <div>
                    <h4 class="font-medium text-base text-gray-600 mb-3">Denominations</h4>
                    <div class="space-y-3" id="sessionDenominations">
                        @php
                            $denominations = [
                                '1000' => 'Rs. 1000',
                                '500' => 'Rs. 500',
                                '100' => 'Rs. 100',
                                '50' => 'Rs. 50',
                                '20' => 'Rs. 20',
                                '10' => 'Rs. 10',
                                '5' => 'Rs. 5',
                                '1' => 'Rs. 1'
                            ];
                        @endphp
                        @foreach($denominations as $value => $label)
                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                <div class="flex items-center space-x-2">
                                    <button type="button" 
                                            data-action="decrement"
                                            data-denomination="{{ $value }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <input type="number" 
                                           id="session_denomination_{{ $value }}"
                                           data-denomination="{{ $value }}"
                                           min="0" 
                                           class="w-16 text-center rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="0">
                                    <button type="button"
                                            data-action="increment"
                                            data-denomination="{{ $value }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-600 hover:text-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between items-center bg-indigo-50 p-3 rounded-lg">
                                <span class="text-sm font-medium text-indigo-700">Total Amount</span>
                                <span class="text-lg font-semibold text-indigo-900" id="sessionTotalAmount">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <h4 class="font-medium text-base text-gray-600 mb-3">Notes</h4>
                    <textarea id="sessionNotes" rows="10" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add any notes here..."></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button id="cancelSessionBtn" class="px-4 py-2 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                    Cancel
                </button>
                <button id="confirmSessionBtn" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>