@extends('layouts.admin')

@section('title', 'QR Code Generator')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold">QR Code Generator</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Current Branch: {{ optional($currentBranch)->name ?? 'N/A' }}
                    </p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('wallet.index') }}" 
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                        Back to Wallet
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- QR Code Generation Form -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Generate QR Code</h3>
                    <form action="{{ route('wallet.generate-qr') }}" method="POST" id="qrForm">
                        @csrf
                        <div class="mb-4">
                            <label for="qr-amount" class="block text-sm font-medium text-gray-700">Amount</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    Rs
                                </span>
                                <input type="number" 
                                       name="amount" 
                                       id="qr-amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       required
                                       class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-r-md"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires In</label>
                            <select name="expires_at" id="expires_at" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="5">5 minutes</option>
                                <option value="15">15 minutes</option>
                                <option value="30">30 minutes</option>
                                <option value="60">1 hour</option>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Generate QR Code
                            </button>
                        </div>
                    </form>
                </div>

                <!-- QR Code Display -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">QR Code</h3>
                    <div id="qrCodeContainer" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                        <div id="qrCode" class="w-64 h-64 flex items-center justify-center">
                            <p class="text-gray-500 text-center">Generate a QR code to display here</p>
                        </div>
                        <div id="qrCodeInfo" class="mt-4 text-center">
                            <p class="text-sm text-gray-500">Amount: <span id="qrAmount">-</span></p>
                            <p class="text-sm text-gray-500">Expires: <span id="qrExpires">-</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('qrForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('QR Code data:', data);
            console.log('Expires in seconds:', data.expires_in_seconds);
            
            // Update QR code
            document.getElementById('qrCode').innerHTML = `<img src="${data.qr_code}" alt="QR Code" class="w-full h-full">`;
            
            // Update info
            document.getElementById('qrAmount').textContent = `Rs ${data.amount}`;
            document.getElementById('qrExpires').textContent = 'Calculating...';
            
            // Start countdown using seconds instead of timestamps
            // Fallback: calculate seconds from expires_at if expires_in_seconds is not available
            const totalSeconds = data.expires_in_seconds || calculateSecondsFromExpiry(data.expires_at);
            startCountdownFromSeconds(totalSeconds);
        } else {
            alert('Error generating QR code: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating QR code. Please try again.');
    });
});

function calculateSecondsFromExpiry(expiresAt) {
    // Fallback: assume 24 hours (86400 seconds) if we can't calculate from expires_at
    console.log('Using fallback: 24 hours (86400 seconds)');
    return 86400; // 24 hours in seconds
}

function startCountdownFromSeconds(totalSeconds) {
    console.log('Starting countdown with total seconds:', totalSeconds);
    const countdownElement = document.getElementById('qrExpires');
    
    // Check if totalSeconds is valid
    if (!totalSeconds || totalSeconds <= 0) {
        console.error('Invalid total seconds:', totalSeconds);
        countdownElement.textContent = 'Invalid expiry time';
        return;
    }
    
    let remainingSeconds = totalSeconds;
    
    const countdown = setInterval(() => {
        if (remainingSeconds <= 0) {
            clearInterval(countdown);
            countdownElement.textContent = 'Expired';
            return;
        }
        
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const seconds = remainingSeconds % 60;
        
        if (hours > 0) {
            countdownElement.textContent = `${hours}h ${minutes}m ${seconds}s remaining`;
        } else if (minutes > 0) {
            countdownElement.textContent = `${minutes}m ${seconds}s remaining`;
        } else {
            countdownElement.textContent = `${seconds}s remaining`;
        }
        
        remainingSeconds--;
    }, 1000);
}
</script>
@endpush
@endsection