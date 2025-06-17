@extends('layouts.admin')

@section('title', 'Campaigns')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Campaigns</h1>
        <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Create Campaign
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($campaigns as $campaign)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $campaign->name }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $campaign->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($campaign->status) }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="font-medium text-indigo-600 hover:text-indigo-500 mr-4">
                                    Edit
                                </a>
                                <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-500" onclick="return confirm('Are you sure you want to delete this campaign?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ $campaign->description }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    {{ $campaign->segment->name }} • {{ $campaign->offer_type }}: {{ $campaign->offer_value }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($campaign->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($campaign->end_date)->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-4 sm:px-6">
                    <p class="text-sm text-gray-500">No campaigns found.</p>
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $campaigns->links() }}
    </div>
</div>
@endsection 