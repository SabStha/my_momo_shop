@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Account</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Profile Information</h5>
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="profile-picture-container">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" class="profile-picture">
                                @else
                                    <div class="profile-picture-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <form action="{{ route('profile.picture') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                @csrf
                                <div class="mb-2">
                                    <input type="file" name="profile_picture" id="profile_picture" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">Update Picture</button>
                            </form>
                        </div>
                        <div class="col-md-9">
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PWA Installation Card -->
            <div class="card mb-4" id="pwa-install-card" style="display: none;">
                <div class="card-body">
                    <h5 class="card-title">Install App</h5>
                    <p class="text-muted">Install our app for a better experience on your device.</p>
                    <button id="pwa-install-button" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Install App
                    </button>
                </div>
            </div>

            <!-- Referral Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Refer Friends</h5>
                    <p class="text-muted">Share your referral link with friends and earn rewards!</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="{{ url('/register?ref=' . $user->referral_code) }}" id="referralLink" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="shareReferral('whatsapp')">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn btn-outline-primary" onclick="shareReferral('telegram')">
                            <i class="fab fa-telegram"></i> Telegram
                        </button>
                        <button class="btn btn-outline-primary" onclick="shareReferral('email')">
                            <i class="fas fa-envelope"></i> Email
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Wallet Balance</h5>
                    <h3 class="text-success mb-3">Rs. {{ number_format($wallet ? $wallet->balance : 0, 2) }}</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('wallet') }}" class="btn btn-outline-primary w-100">View Wallet</a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('wallet.scan') }}" class="btn btn-outline-success w-100">Top Up via QR</a>
                        </div>
                    </div>
                </div>
            </div>

            @if($transactions->count() > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Recent Transactions</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'credit' ? '+' : '-' }}Rs. {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Orders</h5>
                    <p class="text-muted">View your recent orders and track their status.</p>
                    <a href="{{ route('orders') }}" class="btn btn-outline-primary">View Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-picture-container {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #f8f9fa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-picture {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-picture-placeholder {
    width: 100%;
    height: 100%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #6c757d;
}

.form-control-sm {
    font-size: 0.875rem;
}

.gap-2 {
    gap: 0.5rem;
}
</style>

<script>
// PWA Installation
let deferredPrompt;
const pwaInstallCard = document.getElementById('pwa-install-card');
const pwaInstallButton = document.getElementById('pwa-install-button');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    pwaInstallCard.style.display = 'block';
});

pwaInstallButton.addEventListener('click', async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
            pwaInstallCard.style.display = 'none';
        }
        deferredPrompt = null;
    }
});

// Referral Link Copy
function copyReferralLink() {
    const referralLink = document.getElementById('referralLink');
    referralLink.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}

// Share Referral
function shareReferral(platform) {
    const referralLink = document.getElementById('referralLink').value;
    const shareText = `Join me on Momo Shop! Use my referral link to get special rewards: ${referralLink}`;

    let shareLink;
    switch(platform) {
        case 'whatsapp':
            shareLink = `https://wa.me/?text=${encodeURIComponent(shareText)}`;
            break;
        case 'telegram':
            shareLink = `https://t.me/share/url?url=${encodeURIComponent(referralLink)}&text=${encodeURIComponent(shareText)}`;
            break;
        case 'email':
            shareLink = `mailto:?subject=Join me on Momo Shop&body=${encodeURIComponent(shareText)}`;
            break;
    }

    if (shareLink) {
        window.open(shareLink, '_blank');
    }
}
</script>
@endsection 