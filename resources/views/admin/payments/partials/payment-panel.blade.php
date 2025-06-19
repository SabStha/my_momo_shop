@php /* Payment Panel Partial */ @endphp
<!-- Payment Panel -->
<div id="paymentPanel" class="w-3/3 h-screen bg-white shadow-lg border-l border-gray-200 flex flex-col">
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
                    <input type="text" id="paymentPanelReferenceNumber" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
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