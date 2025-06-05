@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <h2 class="mb-4">Install Momo Shop App</h2>
                    <p class="lead mb-4">Get the best experience by installing our app on your device!</p>
                    
                    <!-- Install Button -->
                    <button id="installButton" class="btn btn-primary btn-lg mb-4" style="display: none;">
                        <i class="fas fa-download me-2"></i>Add to Home Screen
                    </button>

                    <!-- Continue Link -->
                    <div class="mt-3">
                        <a href="{{ route('register') }}" class="text-decoration-none">
                            Continue to Sign Up <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <!-- Benefits List -->
                    <div class="mt-5">
                        <h4 class="mb-3">Benefits of Installing:</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Faster access to your favorite momos</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Offline access to menu and orders</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Push notifications for special offers</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Better user experience</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deferredPrompt;
const installButton = document.getElementById('installButton');

// Listen for the beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    // Show the install button
    installButton.style.display = 'inline-block';
});

// Handle the install button click
installButton.addEventListener('click', async () => {
    if (!deferredPrompt) {
        return;
    }
    
    // Show the install prompt
    deferredPrompt.prompt();
    
    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;
    
    // We no longer need the prompt. Clear it up
    deferredPrompt = null;
    
    // Hide the install button
    installButton.style.display = 'none';
    
    // If the user accepted the install, notify the server
    if (outcome === 'accepted') {
        fetch('{{ route("referral.installed") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    }
});

// Listen for successful installation
window.addEventListener('appinstalled', (evt) => {
    // Log install to analytics
    console.log('App was installed');
});
</script>
@endpush
@endsection 