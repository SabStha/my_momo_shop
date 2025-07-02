<!-- Credits Information -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">AmaKo Credits</h2>
    </div>
    <div class="p-6">
        @php
            $creditsAccount = $user->creditsAccount;
            $recentTransactions = $creditsAccount ? $creditsAccount->transactions()->latest()->take(5)->get() : collect();
        @endphp
        
        @if($creditsAccount)
            <!-- Credits Balance -->
            <div class="mb-6">
                <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Current Credits</p>
                            <p class="text-3xl font-bold">{{ $creditsAccount->credits_balance }} Credits</p>
                        </div>
                        <div class="text-right">
                            <p class="text-green-100 text-sm">Account Number</p>
                            <p class="text-lg font-mono font-semibold">{{ $creditsAccount->account_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top-up Section -->
            <div class="mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-md font-semibold text-gray-900 mb-1">Need to Add Credits?</h3>
                            <p class="text-sm text-gray-600">Show this QR code to an employee to add credits to your account</p>
                        </div>
                        <button type="button" onclick="showTopUpQR()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Show Top-up QR Code
                        </button>
                    </div>
                </div>
            </div>

            <!-- Credits Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Total Earned</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $creditsAccount->total_credits_earned }} Credits</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Total Spent</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $creditsAccount->total_credits_spent }} Credits</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            @if($recentTransactions->count() > 0)
                <div>
                    <h3 class="text-md font-semibold text-gray-900 mb-4">Recent Transactions</h3>
                    <div class="space-y-3">
                        @foreach($recentTransactions as $transaction)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($transaction->type === 'credit')
                                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $transaction->description }}</p>
                                        <p class="text-xs text-gray-500">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->display_amount }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($transaction->type) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($creditsAccount->transactions()->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('user.credits.transactions') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View All Transactions →
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">No transactions yet</p>
                    <p class="text-gray-400 text-xs mt-1">Your transaction history will appear here</p>
                </div>
            @endif
        @else
            <!-- No Credits Account -->
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-gray-500 text-sm">No credits account found</p>
                <p class="text-gray-400 text-xs mt-1">Contact support to create your credits account</p>
            </div>
        @endif
    </div>
</div>

<!-- Top-up QR Code Modal -->
<div id="topUpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Credits Top-up QR Code</h3>
                <button type="button" onclick="closeTopUpModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="text-center">
                <div class="mb-4">
                    <div id="qrCodeContainer" class="flex justify-center">
                        <!-- QR Code will be loaded here -->
                    </div>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Show this QR code to an employee</p>
                    <p class="text-xs text-gray-500">Account: {{ $creditsAccount->account_number ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">Barcode: {{ $creditsAccount->credits_barcode ?? 'N/A' }}</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-xs text-blue-800 font-medium mb-1">Important Notes:</p>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Credits are non-refundable and cannot be transferred</li>
                        <li>• Credits can only be used within AmaKo stores</li>
                        <li>• 1 Credit = 1 point (not a currency)</li>
                    </ul>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="downloadQRCode()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Download QR
                    </button>
                    <button type="button" onclick="closeTopUpModal()" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTopUpQR() {
    const modal = document.getElementById('topUpModal');
    const qrContainer = document.getElementById('qrCodeContainer');
    
    // Show loading
    qrContainer.innerHTML = '<div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>';
    modal.classList.remove('hidden');
    
    // Generate QR code
    fetch('{{ route("user.credits.generate-qr") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qrContainer.innerHTML = `<img src="${data.qr_code}" alt="Credits QR Code" class="w-32 h-32">`;
        } else {
            qrContainer.innerHTML = '<p class="text-red-500">Error generating QR code</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        qrContainer.innerHTML = '<p class="text-red-500">Error generating QR code</p>';
    });
}

function closeTopUpModal() {
    document.getElementById('topUpModal').classList.add('hidden');
}

function downloadQRCode() {
    const qrImage = document.querySelector('#qrCodeContainer img');
    if (qrImage) {
        const link = document.createElement('a');
        link.download = 'credits-qr-code.png';
        link.href = qrImage.src;
        link.click();
    }
}
</script> 