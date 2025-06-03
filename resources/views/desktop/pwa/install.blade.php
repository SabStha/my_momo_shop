@extends('desktop.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Install PWA</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <h4>Scan QR Code to Install</h4>
                        <p class="text-muted">Scan this QR code with your mobile device to install our Progressive Web App</p>
                    </div>

                    <div id="qrCodeContainer" class="mb-4">
                        <img id="qrCode" src="" alt="PWA Installation QR Code" class="img-fluid" style="max-width: 300px;">
                    </div>

                    <div class="alert alert-info">
                        <h5>Installation Instructions:</h5>
                        <ol class="text-start">
                            <li>Scan the QR code with your mobile device</li>
                            <li>Open the link in your browser</li>
                            <li>Tap "Add to Home Screen" or "Install" when prompted</li>
                            <li>Follow your device's instructions to complete the installation</li>
                        </ol>
                    </div>
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
.alert {
    border-radius: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch and display QR code
    fetch('{{ route("pwa.qr") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qrCode').src = data.qr_code;
            } else {
                console.error('Failed to load QR code:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});
</script>
@endsection 