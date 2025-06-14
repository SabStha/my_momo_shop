@extends('layouts.admin')

@section('title', 'Creator Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.creators.index') }}" class="text-blue-500 hover:text-blue-600">
            <i class="fas fa-arrow-left mr-2"></i>Back to Creators
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Creator Profile Header -->
        <div class="p-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if($creator->user->avatar)
                        <img src="{{ Storage::url($creator->user->avatar) }}" alt="{{ $creator->user->name }}" class="w-20 h-20 rounded-full border-4 border-white">
                    @else
                        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center border-4 border-white">
                            <span class="text-blue-500 text-2xl">{{ substr($creator->user->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">{{ $creator->user->name }}</h1>
                    <p class="text-blue-100">{{ $creator->user->email }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.creators.edit', $creator) }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <form action="{{ route('admin.creators.destroy', $creator) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600" onclick="return confirm('Are you sure you want to delete this creator?')">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Total Referrals</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $creator->referrals->count() }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Points</h3>
                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($creator->points, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Rank</h3>
                <p class="text-2xl font-bold text-gray-900">#{{ $creator->rank }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                <p class="text-2xl font-bold text-green-600">Active</p>
            </div>
        </div>

        <!-- Bio Section -->
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold mb-2">Bio</h2>
            <p class="text-gray-600">{{ $creator->bio ?? 'No bio provided.' }}</p>
        </div>

        <!-- Referrals Section -->
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Recent Referrals</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referred User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($creator->referrals as $referral)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($referral->referredUser->avatar)
                                            <img src="{{ Storage::url($referral->referredUser->avatar) }}" alt="{{ $referral->referredUser->name }}" class="h-10 w-10 rounded-full">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-500">{{ substr($referral->referredUser->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $referral->referredUser->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $referral->referredUser->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $referral->status === 'ordered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($referral->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $referral->points }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $referral->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No referrals yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rewards Section -->
        <div class="p-6 bg-gray-50">
            <h2 class="text-lg font-semibold mb-4">Rewards</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($creator->rewards as $reward)
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $reward->type }}</h3>
                            <p class="text-sm text-gray-500">{{ $reward->month->format('F Y') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $reward->claimed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $reward->claimed ? 'Claimed' : 'Pending' }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <p class="text-lg font-bold text-gray-900">₦{{ number_format($reward->amount, 2) }}</p>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center text-gray-500">
                    No rewards yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection 