@extends('layouts.app')

@section('title', 'Credits Top-up Scanner')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Credits Top-up Scanner</h1>
            <p class="text-gray-600 mt-2">Scan customer QR codes to process credits top-ups</p>
        </div>

        <!-- Scanner Interface -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">QR Code Scanner</h2>
                <button type="button" onclick="startScanner()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Start Scanner
                </button>
            </div>
            
            <div class="text-center">
                <div id="scannerContainer" class="mb-4">
                    <video id="scanner" class="w-full max-w-md mx-auto border-2 border-gray-300 rounded-lg" autoplay></video>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Point camera at customer's QR code</p>
                </div>
                
                <!-- Manual Entry -->
                <div class="border-t pt-4">
                    <h3 class="text-md font-medium text-gray-900 mb-2">Manual Entry</h3>
                    <div class="flex space-x-2 max-w-md mx-auto">
                        <input type="text" id="barcodeInput" placeholder="Enter credits barcode manually" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="lookupBarcode()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Lookup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credits Account Information (Hidden initially) -->
        <div id="creditsInfo" class="bg-white rounded-lg shadow-lg p-6 mb-6 hidden">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Credits Account Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">Customer Name</p>
                    <p class="text-lg font-semibold text-gray-900" id="customerName">-</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Account Number</p>
                    <p class="text-lg font-mono font-semibold text-gray-900" id="accountNumber">-</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Credits Barcode</p>
                    <p class="text-lg font-mono font-semibold text-gray-900" id="creditsBarcode">-</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Current Credits</p>
                    <p class="text-2xl font-bold text-green-600" id="currentCredits">0 Credits</p>
                </div>
            </div>

            <!-- Top-up Form -->
            <div class="border-t pt-4">
                <h3 class="text-md font-semibold text-gray-900 mb-4">Add Credits</h3>
                <form id="topUpForm" class="space-y-4">
                    <input type="hidden" id="creditsAccountId" name="credits_account_id">
                    
                    <div>
                        <label for="creditsAmount" class="block text-sm font-medium text-gray-700 mb-2">Credits Amount</label>
                        <input type="number" id="creditsAmount" name="credits_amount" min="1" max="1000" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter credits amount">
                        <p class="text-sm text-gray-500 mt-1">Maximum 1000 credits per transaction</p>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <input type="text" id="description" name="description" maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., Cash top-up, Promotion credits">
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Process Top-up
                        </button>
                        <button type="button" onclick="resetScanner()" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success Message -->
        <div id="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 hidden">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 001.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-green-800 font-medium" id="successText">Credits top-up successful!</span>
            </div>
        </div>

        <!-- Important Information -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>AmaKo Credits are non-refundable loyalty rewards</li>
                            <li>Credits cannot be transferred between accounts</li>
                            <li>Credits can only be used within AmaKo stores</li>
                            <li>1 Credit = 1 point (not a currency)</li>
                            <li>Credits cannot be used with online payment services</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include QuaggaJS for barcode scanning -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

<script>
let scanner = null;
let currentCreditsAccount = null;

function startScanner() {
    const video = document.getElementById('scanner');
    
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: video,
            constraints: {
                width: 640,
                height: 480,
                facingMode: "environment"
            },
        },
        decoder: {
            readers: ["qr_code_reader", "code_128_reader", "ean_reader"]
        }
    }, function(err) {
        if (err) {
            console.error('Scanner initialization failed:', err);
            alert('Failed to initialize scanner. Please check camera permissions.');
            return;
        }
        
        Quagga.start();
        console.log('Scanner started');
    });

    Quagga.onDetected(function(result) {
        console.log('QR Code detected:', result.codeResult.code);
        processQRCode(result.codeResult.code);
    });
}

function processQRCode(qrData) {
    // Stop scanner
    Quagga.stop();
    
    fetch('{{ route("employee.credits.process-qr") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            qr_data: qrData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCreditsInfo(data.wallet);
        } else {
            alert('Error: ' + data.message);
            startScanner(); // Restart scanner
        }
    })
    .catch(error => {
        console.error('Error processing QR code:', error);
        alert('Error processing QR code');
        startScanner(); // Restart scanner
    });
}

function lookupBarcode() {
    const barcode = document.getElementById('barcodeInput').value.trim();
    
    if (!barcode) {
        alert('Please enter a credits barcode');
        return;
    }
    
    fetch('{{ route("employee.credits.balance-by-barcode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            barcode: barcode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCreditsInfo(data.wallet);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error looking up barcode:', error);
        alert('Error looking up barcode');
    });
}

function displayCreditsInfo(creditsAccount) {
    currentCreditsAccount = creditsAccount;
    
    document.getElementById('customerName').textContent = creditsAccount.user_name;
    document.getElementById('accountNumber').textContent = creditsAccount.account_number;
    document.getElementById('creditsBarcode').textContent = creditsAccount.credits_barcode;
    document.getElementById('currentCredits').textContent = creditsAccount.current_balance + ' Credits';
    document.getElementById('creditsAccountId').value = creditsAccount.id;
    
    document.getElementById('creditsInfo').classList.remove('hidden');
    document.getElementById('scannerContainer').classList.add('hidden');
}

function resetScanner() {
    currentCreditsAccount = null;
    document.getElementById('creditsInfo').classList.add('hidden');
    document.getElementById('successMessage').classList.add('hidden');
    document.getElementById('scannerContainer').classList.remove('hidden');
    document.getElementById('topUpForm').reset();
    document.getElementById('barcodeInput').value = '';
    
    if (Quagga) {
        Quagga.stop();
    }
}

// Handle form submission
document.getElementById('topUpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        credits_account_id: formData.get('credits_account_id'),
        credits_amount: formData.get('credits_amount'),
        description: formData.get('description')
    };
    
    fetch('{{ route("employee.credits.topup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Credits top-up successful! New balance: ' + data.transaction.new_balance + ' Credits');
            document.getElementById('currentCredits').textContent = data.transaction.new_balance + ' Credits';
            this.reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error processing credits top-up:', error);
        alert('Error processing credits top-up');
    });
});

function showSuccessMessage(message) {
    document.getElementById('successText').textContent = message;
    document.getElementById('successMessage').classList.remove('hidden');
    
    setTimeout(() => {
        document.getElementById('successMessage').classList.add('hidden');
    }, 5000);
}

// Initialize scanner on page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto-start scanner
    startScanner();
});
</script>
@endsection 