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
        // Add your logic to process the code
        // For example, make an AJAX call to your backend
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
                alert('Funds added successfully!');
                window.location.href = '/wallet';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the code.');
        });
    }
</script>
@endpush
@endsection 