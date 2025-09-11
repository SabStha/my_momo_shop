@extends('layouts.app')

@section('content')
<div class="container mx-auto py-4">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Scan QR Code</h2>
        <p class="text-gray-600 mb-4">Scan a QR code to add funds to your wallet.</p>
        
        <div class="mb-4">
            <div id="reader" class="w-full"></div>
        </div>

        <div class="text-center">
            <p class="text-sm text-gray-500">Or enter the code manually:</p>
            <input type="text" id="manual-code" class="mt-2 w-full border rounded p-2" placeholder="Enter code manually">
            <button onclick="processManualCode()" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Submit
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Handle the scanned code
        console.log(`Code scanned = ${decodedText}`);
        // You can add your logic here to process the scanned code
        processCode(decodedText);
    }

    function onScanFailure(error) {
        // Handle scan failure
        console.warn(`QR code scan error: ${error}`);
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: {width: 250, height: 250} },
        false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);

    function processManualCode() {
        const code = document.getElementById('manual-code').value;
        if (code) {
            processCode(code);
        }
    }

    function processCode(code) {
        // Show loading state
        const resultDiv = document.getElementById('result');
        if (resultDiv) {
            resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Processing...</span></div><p class="mt-2">Processing QR code...</p></div>';
        }

        // Make AJAX call to process the QR code
        fetch('/wallet/process-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message with amount
                const amount = data.amount_added || 'funds';
                const newBalance = data.new_balance || 'updated';
                
                if (resultDiv) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success text-center">
                            <h4 class="alert-heading">✅ Success!</h4>
                            <p><strong>${amount}</strong> credits added to your wallet!</p>
                            <p>New balance: <strong>${newBalance}</strong> credits</p>
                            <hr>
                            <p class="mb-0">Redirecting to wallet...</p>
                        </div>
                    `;
                } else {
                    alert(`Success! ${amount} credits added. New balance: ${newBalance}`);
                }
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '/wallet';
                }, 2000);
            } else {
                // Show error message
                if (resultDiv) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <h4 class="alert-heading">❌ Error</h4>
                            <p>${data.message || 'Failed to process QR code'}</p>
                            <hr>
                            <button class="btn btn-primary" onclick="location.reload()">Try Again</button>
                        </div>
                    `;
                } else {
                    alert('Error: ' + (data.message || 'Failed to process QR code'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (resultDiv) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <h4 class="alert-heading">❌ Error</h4>
                        <p>An error occurred while processing the QR code.</p>
                        <hr>
                        <button class="btn btn-primary" onclick="location.reload()">Try Again</button>
                    </div>
                `;
            } else {
                alert('An error occurred while processing the QR code.');
            }
        });
    }
</script>
@endpush
@endsection 