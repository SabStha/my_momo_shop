@extends('layouts.app')

@section('title', 'AmaKo Credits')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">AmaKo Credits</h1>
            <p class="text-gray-600 mt-2">Your loyalty rewards and store credits</p>
        </div>

        <!-- Credits Overview -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Current Balance -->
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">
                        {{ $creditsAccount->credits_balance }}
                    </div>
                    <div class="text-sm text-gray-500">Current Credits</div>
                </div>

                <!-- Total Earned -->
                <div class="text-center">
                    <div class="text-2xl font-semibold text-blue-600 mb-2">
                        {{ $creditsAccount->total_credits_earned }}
                    </div>
                    <div class="text-sm text-gray-500">Total Earned</div>
                </div>

                <!-- Total Spent -->
                <div class="text-center">
                    <div class="text-2xl font-semibold text-orange-600 mb-2">
                        {{ $creditsAccount->total_credits_spent }}
                    </div>
                    <div class="text-sm text-gray-500">Total Spent</div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Account Number</p>
                        <p class="text-lg font-mono font-semibold text-gray-900">{{ $creditsAccount->account_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Credits Barcode</p>
                        <p class="text-lg font-mono font-semibold text-gray-900">{{ $creditsAccount->credits_barcode }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Top-up QR Code</h2>
                <button type="button" onclick="generateQR()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Generate QR Code
                </button>
            </div>
            
            <div class="text-center">
                <div id="qrCodeContainer" class="hidden">
                    <div class="mb-4">
                        <img id="qrCodeImage" class="mx-auto border-2 border-gray-300 rounded-lg" alt="QR Code">
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Show this QR code to an employee to add credits to your account
                    </p>
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm font-medium text-gray-700">Important Notes:</p>
                        <ul class="text-sm text-gray-600 mt-2 space-y-1">
                            <li>• Credits are non-refundable and cannot be transferred</li>
                            <li>• Credits can only be used within AmaKo stores</li>
                            <li>• 1 Credit = 1 point (not a currency)</li>
                            <li>• Credits cannot be used with online payment services</li>
                        </ul>
                    </div>
                </div>
                
                <div id="qrCodePlaceholder" class="text-gray-500">
                    <svg class="mx-auto h-32 w-32 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                    </svg>
                    <p class="mt-2">Click "Generate QR Code" to create your top-up QR code</p>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
                <a href="{{ route('user.credits.transactions') }}" 
                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View All →
                </a>
            </div>
            
            @if($transactions->count() > 0)
                <div class="space-y-3">
                    @foreach($transactions->take(5) as $transaction)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $transaction->description }}</p>
                                <p class="text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->display_amount }}
                                </p>
                                <p class="text-sm text-gray-500">Balance: {{ $transaction->credits_balance_after }} Credits</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-gray-500">No transactions yet</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function generateQR() {
    const button = event.target;
    const originalText = button.textContent;
    
    button.textContent = 'Generating...';
    button.disabled = true;
    
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
            const qrImage = document.getElementById('qrCodeImage');
            
            if (data.qr_type === 'url') {
                // Use URL directly
                qrImage.src = data.qr_code;
            } else {
                // Use base64 data
                qrImage.src = data.qr_code;
            }
            
            document.getElementById('qrCodeContainer').classList.remove('hidden');
            document.getElementById('qrCodePlaceholder').classList.add('hidden');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error generating QR code:', error);
        alert('Error generating QR code');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}
</script>
@endsection 