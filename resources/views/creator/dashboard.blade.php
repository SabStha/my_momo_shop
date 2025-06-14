@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Profile Section -->
        <div class="bg-white shadow rounded-lg p-6 text-center">
            @if(auth()->user()->creator && auth()->user()->creator->avatar)
                <img src="{{ Storage::url(auth()->user()->creator->avatar) }}" alt="Profile Picture" class="w-36 h-36 mx-auto rounded-full object-cover border-4 border-gray-200">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=150" alt="Profile Picture" class="w-36 h-36 mx-auto rounded-full object-cover border-4 border-gray-200">
            @endif
            <h4 class="text-xl font-semibold mt-4">{{ auth()->user()->name }}</h4>
            <p class="text-gray-500">Creator</p>

            @if(isset($wallet))
                <div class="text-green-600 font-semibold mt-2">Wallet: Rs. {{ number_format($wallet->balance, 2) }}</div>
            @endif

            <form action="{{ route('creator-dashboard.update-profile-photo') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                @csrf
                <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <button type="submit" class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Update Photo</button>
            </form>
        </div>

        <!-- Stats and Wallet -->
        <div class="md:col-span-2 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-500 text-white p-4 rounded shadow">
                    <h5 class="text-sm font-medium">Total Referrals</h5>
                    <h2 class="text-2xl font-bold">{{ $stats['total_referrals'] }}</h2>
                </div>
                <div class="bg-green-500 text-white p-4 rounded shadow">
                    <h5 class="text-sm font-medium">Completed Orders</h5>
                    <h2 class="text-2xl font-bold">{{ $stats['ordered_referrals'] }}</h2>
                </div>
                <div class="bg-teal-500 text-white p-4 rounded shadow">
                    <h5 class="text-sm font-medium">Referral Points</h5>
                    <h2 class="text-2xl font-bold">{{ $stats['referral_points'] }}</h2>
                </div>
                <div class="bg-yellow-500 text-white p-4 rounded shadow">
                    <h5 class="text-sm font-medium">Wallet Balance</h5>
                    <h2 class="text-2xl font-bold">Rs. {{ isset($wallet) ? number_format($wallet->balance, 2) : '0.00' }}</h2>
                </div>
            </div>

            <!-- QR Wallet Top-up -->
            <div class="bg-white p-6 shadow rounded">
                <h5 class="text-lg font-semibold mb-2">Top Up Wallet</h5>
                <p class="text-gray-600 mb-4">Scan a QR code to add funds to your wallet.</p>
                <a href="{{ route('wallet.scan') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fas fa-qrcode mr-2"></i> Scan QR Code
                </a>
            </div>

            <!-- Referral Code -->
            <div class="bg-white p-6 shadow rounded">
                <h5 class="text-lg font-semibold mb-3">Share this link with your friends:</h5>
                <div class="input-group">
                    <input type="text" class="form-control" id="referralLink" 
                        value="{{ url('/register?ref=' . auth()->user()->creator->code) }}" readonly>
                    <button class="btn btn-primary" type="button" onclick="copyReferralLink()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h6 class="font-semibold">You Earn:</h6>
                        <ul class="list-disc list-inside text-sm text-gray-700">
                            <li>{{ config('settings.referral.creator_referral_bonus', 10) }} points when they sign up</li>
                            <li>{{ config('settings.referral.creator_first_order_bonus', 5) }} points on their first order</li>
                            <li>{{ config('settings.referral.creator_subsequent_order_bonus', 5) }} points for each of their next {{ config('settings.referral.max_referral_orders', 9) }} orders</li>
                        </ul>
                    </div>
                    <div>
                        <h6 class="font-semibold">They Earn:</h6>
                        <ul class="list-disc list-inside text-sm text-gray-700">
                            <li>Rs {{ config('settings.referral.referral_welcome_bonus', 50) }} discount for signing up</li>
                            <li>Rs {{ config('settings.referral.referral_first_order_bonus', 30) }} discount on their first order</li>
                            <li>Rs {{ config('settings.referral.referral_subsequent_order_bonus', 10) }} discount for each of their next {{ config('settings.referral.max_referral_orders', 9) }} orders</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard -->
    <div class="mt-10 bg-white shadow rounded">
        <div class="p-4 border-b">
            <h5 class="text-lg font-semibold">Top Creators Leaderboard</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Rank</th>
                        <th class="px-4 py-2 text-left">Creator</th>
                        <th class="px-4 py-2 text-left">Points</th>
                        <th class="px-4 py-2 text-left">Referrals</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCreators as $index => $user)
                        <tr class="{{ $user->creator->id === auth()->user()->creator->id ? 'bg-blue-50' : '' }}">
                            <td class="px-4 py-2">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 flex items-center space-x-2">
                                @if($user->creator->avatar)
                                    <img src="{{ Storage::url($user->creator->avatar) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=40" alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                                @endif
                                <span>{{ $user->name }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $user->creator->points }}</td>
                            <td class="px-4 py-2">{{ $user->creator->referrals_count }}</td>
                            <td class="px-4 py-2">
                                @if($user->creator->isTrending())
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Trending</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-700 rounded">Stable</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Your Referrals -->
    <div class="mt-10 bg-white shadow rounded">
        <div class="p-4 border-b">
            <h5 class="text-lg font-semibold">Your Referrals</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Orders</th>
                        <th class="px-4 py-2 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $referral)
                        <tr>
                            <td class="px-4 py-2">
                                {{ $referral->referredUser ? (count(explode(' ', $referral->referredUser->name)) > 1 ? explode(' ', $referral->referredUser->name)[1] : $referral->referredUser->name) : 'N/A' }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 text-xs rounded font-medium {{ $referral->status === 'ordered' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($referral->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $referral->order_count ?? 0 }}</td>
                            <td class="px-4 py-2">{{ $referral->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">No referrals yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    input.select();
    
    // Use the modern Clipboard API
    navigator.clipboard.writeText(input.value)
        .then(() => {
            // Show success message
            const button = input.nextElementSibling;
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.add('bg-green-500', 'text-white');
            button.classList.remove('btn-primary');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('bg-green-500', 'text-white');
                button.classList.add('btn-primary');
            }, 2000);
        })
        .catch(err => {
            console.error('Failed to copy text: ', err);
            alert('Failed to copy referral link. Please try again.');
        });
}
</script>
@endsection
