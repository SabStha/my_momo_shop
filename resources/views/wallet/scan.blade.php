@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Scan QR Code</h4>
                        <a href="{{ route('wallet') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Wallet
                        </a>
                    </div>

                    <div class="text-center mb-4">
                        <p class="text-muted">Scan a QR code to top up your wallet</p>
                    </div>

                    <div id="reader" class="w-100 mb-4" style="max-width: 500px; margin: 0 auto;"></div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Make sure the QR code is clear and well-lit for better scanning.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        );

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanning after successful scan
            html5QrCode.stop();

            // Send the scanned data to the server
            fetch('{{ route("wallet.top-up") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    qr_data: decodedText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("wallet") }}';
                } else {
                    alert(data.message || 'Failed to process QR code');
                    // Restart scanning
                    html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        onScanSuccess,
                        onScanFailure
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the QR code');
                // Restart scanning
                html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess,
                    onScanFailure
                );
            });
        }

        function onScanFailure(error) {
            // Handle scan failure silently
            console.warn(`QR scan failure: ${error}`);
        }
    });
</script>
@endpush 