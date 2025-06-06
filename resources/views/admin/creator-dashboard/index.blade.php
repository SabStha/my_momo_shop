@extends('layouts.app')

@section('content')
<div class="container mx-auto py-4">
    <div class="flex flex-wrap">
        <!-- Profile Section -->
        <div class="w-full md:w-1/3 mb-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="mb-3">
                    @if(auth()->user()->creator && auth()->user()->creator->avatar)
                        <img src="{{ Storage::url('avatars/' . auth()->user()->creator->avatar) }}" 
                             alt="Profile Picture" 
                             class="rounded-full w-32 h-32 object-cover mx-auto">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=150" 
                             alt="Profile Picture" 
                             class="rounded-full w-32 h-32 object-cover mx-auto">
                    @endif
                </div>
                <h4 class="text-xl font-bold">{{ auth()->user()->name }}</h4>
                <p class="text-gray-500">Creator</p>
                @if(isset($wallet))
                    <div class="my-3">
                        <span class="font-bold">Wallet Balance:</span>
                        <span class="text-green-500">₦{{ number_format($wallet->balance, 2) }}</span>
                    </div>
                @endif
                <form action="{{ route('creator-dashboard.update-profile-photo') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="mt-3">
                    @csrf
                    <div class="mb-3">
                        <input type="file" 
                               name="avatar" 
                               class="w-full border rounded p-2" 
                               accept="image/*">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Photo</button>
                </form>
            </div>
        </div>

        <!-- Stats and Referral Section -->
        <div class="w-full md:w-2/3">
            <!-- Stats Cards -->
            <div class="flex flex-wrap mb-4">
                <div class="w-full md:w-1/4 p-2">
                    <div class="bg-blue-500 text-white rounded-lg p-4">
                        <h5 class="text-lg font-semibold">Total Referrals</h5>
                        <h2 class="text-2xl font-bold">{{ $stats['total_referrals'] }}</h2>
                    </div>
                </div>
                <div class="w-full md:w-1/4 p-2">
                    <div class="bg-green-500 text-white rounded-lg p-4">
                        <h5 class="text-lg font-semibold">Completed Orders</h5>
                        <h2 class="text-2xl font-bold">{{ $stats['ordered_referrals'] }}</h2>
                    </div>
                </div>
                <div class="w-full md:w-1/4 p-2">
                    <div class="bg-blue-400 text-white rounded-lg p-4">
                        <h5 class="text-lg font-semibold">Referral Points</h5>
                        <h2 class="text-2xl font-bold">{{ $stats['referral_points'] }}</h2>
                    </div>
                </div>
                <div class="w-full md:w-1/4 p-2">
                    <div class="bg-yellow-500 text-white rounded-lg p-4">
                        <h5 class="text-lg font-semibold">Wallet Balance</h5>
                        <h2 class="text-2xl font-bold">₦{{ isset($wallet) ? number_format($wallet->balance, 2) : '0.00' }}</h2>
                    </div>
                </div>
            </div>

            <!-- Wallet Actions -->
            <div class="flex flex-wrap mb-4">
                <div class="w-full md:w-1/2 p-2">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h5 class="text-lg font-semibold">Top Up Wallet</h5>
                        <p class="text-gray-600">Scan a QR code to add funds to your wallet.</p>
                        <a href="{{ route('wallet.scan') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <i class="fas fa-qrcode"></i> Scan QR Code
                        </a>
                    </div>
                </div>
            </div>

            <!-- Referral Code Section -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <h5 class="text-lg font-semibold">Share this link with your friends:</h5>
                @if(Auth::user()->creator)
                    <div class="flex mb-3">
                        <input type="text" class="flex-1 border rounded-l p-2" id="referral-link" value="{{ url('/register?ref=' . Auth::user()->creator->code) }}" readonly>
                        <button class="bg-gray-200 text-gray-700 px-4 py-2 rounded-r hover:bg-gray-300" onclick="copyReferralLink()">Copy</button>
                    </div>
                    <div class="flex flex-wrap">
                        <div class="w-full md:w-1/2">
                            <h6 class="font-semibold">You Earn:</h6>
                            <ul class="list-disc list-inside">
                                <li>₦500 for each referral</li>
                                <li>₦1,000 when they make their first order</li>
                            </ul>
                        </div>
                        <div class="w-full md:w-1/2">
                            <h6 class="font-semibold">Your Referral Code:</h6>
                            <p class="text-2xl font-bold text-blue-600">{{ Auth::user()->creator->code }}</p>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                        <p>You don't have a creator account yet. Please contact support to set up your creator account.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Leaderboard Section -->
    <div class="flex flex-wrap">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow p-4">
                <h5 class="text-lg font-semibold mb-4">Top Creators Leaderboard</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2">Rank</th>
                                <th class="py-2">Creator</th>
                                <th class="py-2">Points</th>
                                <th class="py-2">Referrals</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCreators as $index => $creator)
                                <tr class="{{ $creator->id === auth()->user()->creator->id ? 'bg-blue-100' : '' }}">
                                    <td class="py-2">{{ $index + 1 }}</td>
                                    <td class="py-2">
                                        <div class="flex items-center">
                                            @if($creator->avatar)
                                                <img src="{{ Storage::url('avatars/' . $creator->avatar) }}" 
                                                     alt="{{ $creator->user->name }}" 
                                                     class="rounded-full w-10 h-10 object-cover mr-2">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($creator->user->name) }}&size=40" 
                                                     alt="{{ $creator->user->name }}" 
                                                     class="rounded-full w-10 h-10 object-cover mr-2">
                                            @endif
                                            {{ $creator->user->name }}
                                        </div>
                                    </td>
                                    <td class="py-2">{{ $creator->points }}</td>
                                    <td class="py-2">{{ $creator->referral_count }}</td>
                                    <td class="py-2">
                                        @if($creator->isTrending())
                                            <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs">Trending</span>
                                        @else
                                            <span class="bg-gray-500 text-white px-2 py-1 rounded-full text-xs">Stable</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Referrals List -->
    <div class="flex flex-wrap mt-4">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow p-4">
                <h5 class="text-lg font-semibold mb-4">Your Referrals</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2">User</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Orders</th>
                                <th class="py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($referrals as $referral)
                                <tr>
                                    <td class="py-2">{{ $referral->referredUser ? (count(explode(' ', $referral->referredUser->name)) > 1 ? explode(' ', $referral->referredUser->name)[1] : $referral->referredUser->name) : 'N/A' }}</td>
                                    <td class="py-2">
                                        <span class="bg-{{ $referral->status === 'ordered' ? 'green' : 'yellow' }}-500 text-white px-2 py-1 rounded-full text-xs">
                                            {{ ucfirst($referral->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2">{{ $referral->order_count ?? 0 }}</td>
                                    <td class="py-2">{{ $referral->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-2">No referrals yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralLink() {
    const input = document.getElementById('referral-link');
    input.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>
@endpush
@endsection 