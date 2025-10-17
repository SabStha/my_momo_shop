@php /* Payment Panel Partial */ @endphp
<!-- Payment Panel -->
<div id="paymentPanel" class="w-2/3 bg-white shadow-lg border-l border-gray-200 flex flex-col h-[90vh]">
    <div class="h-full flex flex-col">
        <!-- Panel Header -->
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Payment Details</h3>
                <div class="flex items-center space-x-2">
                    <button id="openPaymentViewerBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i> Open Viewer
                    </button>
                    <button id="closePaymentPanel" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Payment Form -->
        <div class="flex-1 overflow-auto p-6">
            <form id="paymentPanelForm" class="max-w-4xl mx-auto space-y-6">
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
                        <button type="button" class="payment-method-btn bg-blue-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors" data-method="cash">
                            <i class="fas fa-money-bill-wave text-lg mb-1"></i><br>
                            Cash
                        </button>
                        <button type="button" class="payment-method-btn bg-green-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors" data-method="card">
                            <i class="fas fa-credit-card text-lg mb-1"></i><br>
                            Card
                        </button>
                        <button type="button" class="payment-method-btn bg-purple-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors" data-method="wallet">
                            <i class="fas fa-wallet text-lg mb-1"></i><br>
                            Wallet
                        </button>
                        <button type="button" class="payment-method-btn bg-orange-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-orange-700 transition-colors" data-method="khalti">
                            <i class="fas fa-qrcode text-lg mb-1"></i><br>
                            Khalti
                        </button>
                        <button type="button" class="payment-method-btn bg-indigo-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors" data-method="mobile">
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
                    <!-- Direct Cash Amount Input -->
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <label class="block text-sm font-medium text-blue-900 mb-2">
                            <i class="fas fa-money-bill-wave mr-1"></i>Total Cash Received from Customer
                        </label>
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <input type="number" id="totalCashReceived" class="w-full border border-blue-300 rounded-lg px-4 py-3 text-lg font-bold text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="0.00" step="0.01" min="0">
                            </div>
                            <div class="text-blue-700 font-medium">Rs</div>
                        </div>
                        <div class="mt-2 text-xs text-blue-600">
                            <i class="fas fa-info-circle mr-1"></i>Enter the total amount of cash received from customer
                        </div>
                    </div>
                </div>
                <!-- Reference Number (for card/mobile) -->
                <div id="cardMobileFields" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                    <input type="text" id="paymentPanelReferenceNumber" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                           placeholder="Transaction reference">
                </div>
                <!-- Card Payment Fields -->
                <div id="cardFields" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Card Transaction Reference</label>
                    <input type="text" id="cardReferenceNumber" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                           placeholder="Card transaction reference">
                </div>
                <!-- Wallet Payment Fields -->
                <div id="walletFields" class="hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Wallet Number</label>
                            <div class="flex space-x-2">
                                <input type="text" id="walletNumber" class="flex-1 border border-gray-300 rounded-lg px-4 py-3" 
                                       placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19">
                                <button type="button" id="scanWalletBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            </div>
                        </div>
                        <div id="walletBalanceDisplay" class="hidden">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="text-sm text-green-800">Wallet Balance: <span id="walletBalance" class="font-bold">Rs 0.00</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Khalti Payment Fields -->
                <div id="khaltiFields" class="hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Khalti Transaction ID</label>
                            <input type="text" id="khaltiTransactionId" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                                   placeholder="Khalti transaction ID">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                            <div id="khaltiQrCode" class="w-full border border-gray-300 rounded-lg p-4 flex items-center justify-center bg-gray-50">
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-qrcode text-4xl mb-2"></i>
                                    <p>QR code will be generated here</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Mobile Payment Fields -->
                <div id="mobileFields" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Transaction Reference</label>
                    <input type="text" id="mobileReferenceNumber" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                           placeholder="Mobile transaction reference">
                </div>
                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="paymentNotes" class="w-full border border-gray-300 rounded-lg px-4 py-3" 
                              rows="3" placeholder="Optional payment notes"></textarea>
                </div>
                <!-- Action Buttons -->
                <div class="flex space-x-3 pt-4">
                    <button type="button" id="processPaymentBtn" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium">
                        <i class="fas fa-check mr-2"></i> Process Payment
                    </button>
                    <button type="button" id="cancelPaymentBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg text-sm font-medium">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 