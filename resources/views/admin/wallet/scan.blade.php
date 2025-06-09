@extends('layouts.admin')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-xl font-semibold text-gray-800">Scan QR Code</h5>
                </div>
            <div class="p-6">
                <div class="text-center mb-6">
                    <div id="reader" class="max-w-md mx-auto"></div>
                    </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <p class="text-blue-700">Scan a QR code to top up your wallet or view product details.</p>
                </div>

                <div id="result" class="mt-6"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include HTML5 QR Code library -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultDiv = document.getElementById('result');
    let html5QrcodeScanner = null;

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop();
        }

        try {
            // Try to parse the QR code data as JSON
            const qrData = JSON.parse(decodedText);
            
            if (qrData.type === 'wallet_topup') {
                // Handle wallet top-up QR code
                fetch('{{ route("wallet.top-up") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        amount: qrData.amount,
                        description: 'Top up via QR code'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route("wallet") }}';
                    } else {
                        showError(data.message || 'Failed to process top-up');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to process QR code');
                });
            } else {
                showError('Invalid QR code type');
            }
        } catch (e) {
            // If not JSON, check if it's a product URL
            if (decodedText.startsWith('http')) {
                const productId = decodedText.split('/').pop();
                window.location.href = '{{ url("/products") }}/' + productId;
            } else {
                showError('Invalid QR code format');
            }
        }
    }

    function showError(message) {
        resultDiv.innerHTML = `
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <p class="text-red-700">${message}</p>
            </div>
            <button onclick="restartScanner()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                Scan Again
            </button>
        `;
    }

    function restartScanner() {
        resultDiv.innerHTML = '';
        html5QrcodeScanner.start();
    }

    // Initialize scanner
    html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { 
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        }
    );

    html5QrcodeScanner.render(onScanSuccess);
});
</script>
@endsection 