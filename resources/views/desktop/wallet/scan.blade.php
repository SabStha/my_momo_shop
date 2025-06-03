@extends('desktop.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Scan QR Code</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div id="reader"></div>
                    </div>

                    <div class="alert alert-info">
                        <p class="mb-0">Scan a product QR code to view its details.</p>
                    </div>

                    <div id="result" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
    padding: 1rem;
}
#reader {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}
#reader video {
    border-radius: 10px;
}
.alert {
    border-radius: 10px;
}
.btn-warning {
    background-color: #f97316;
    border-color: #f97316;
    color: white;
}
.btn-warning:hover {
    background-color: #ea580c;
    border-color: #ea580c;
    color: white;
}
</style>

<!-- Include HTML5 QR Code library -->
<script src="https://unpkg.com/html5-qrcode"></script>
<!-- Include jsQR library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultDiv = document.getElementById('result');
    let html5QrcodeScanner = null;

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop();
        }

        // Check if the scanned content is a URL
        if (decodedText.startsWith('http')) {
            // If it's a product URL, fetch the product details
            fetch('{{ route("products.qr-scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    url: decodedText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Extract product ID from URL
                    const productId = decodedText.split('/').pop();
                    // Redirect to the product page using the specific IP
                    window.location.href = 'http://192.168.2.157:8000/products/' + productId;
                } else {
                    showError('Invalid QR code');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to process QR code');
            });
        } else {
            showError('Invalid QR code format');
        }
    }

    function showError(message) {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                ${message}
            </div>
            <button class="btn btn-primary" onclick="restartScanner()">Scan Again</button>
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