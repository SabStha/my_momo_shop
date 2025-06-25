@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Special Offers</h1>
        <a href="{{ route('admin.offers.create') }}" class="bg-[#6E0D25] text-white px-4 py-2 rounded hover:bg-[#8B0D2F]">+ New Offer</a>
    </div>
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valid</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($offers as $offer)
                <tr>
                    <td class="px-4 py-2">{{ $offer->title }}</td>
                    <td class="px-4 py-2 font-mono">{{ $offer->code }}</td>
                    <td class="px-4 py-2">{{ $offer->discount }}%</td>
                    <td class="px-4 py-2 capitalize">{{ $offer->type ?? '-' }}</td>
                    <td class="px-4 py-2 text-xs">{{ $offer->valid_from ? $offer->valid_from->format('Y-m-d') : '-' }} to {{ $offer->valid_until ? $offer->valid_until->format('Y-m-d') : '-' }}</td>
                    <td class="px-4 py-2">
                        @if($offer->is_active)
                            <span class="text-green-600 font-bold">Yes</span>
                        @else
                            <span class="text-gray-400">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 flex gap-2">
                        <a href="{{ route('admin.offers.edit', $offer) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('Delete this offer?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No offers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection 