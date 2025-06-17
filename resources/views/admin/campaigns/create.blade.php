@extends('layouts.admin')

@section('title', 'Create Campaign')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Create Campaign</h1>
        <a href="{{ route('admin.campaigns.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Back to Campaigns
        </a>
    </div>

    <form action="{{ route('admin.campaigns.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white shadow sm:rounded-md">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Basic Information</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Campaign Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="segment_id" class="block text-sm font-medium text-gray-700">Target Segment</label>
                                <select name="segment_id" id="segment_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">Select a segment</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}" {{ old('segment_id') == $segment->id ? 'selected' : '' }}>
                                            {{ $segment->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('segment_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Offer Details -->
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Offer Details</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <div>
                                <label for="offer_type" class="block text-sm font-medium text-gray-700">Offer Type</label>
                                <select name="offer_type" id="offer_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="discount" {{ old('offer_type') == 'discount' ? 'selected' : '' }}>Discount</option>
                                    <option value="free_shipping" {{ old('offer_type') == 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                                    <option value="gift" {{ old('offer_type') == 'gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="cashback" {{ old('offer_type') == 'cashback' ? 'selected' : '' }}>Cashback</option>
                                </select>
                                @error('offer_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="offer_value" class="block text-sm font-medium text-gray-700">Offer Value</label>
                                <input type="text" name="offer_value" id="offer_value" value="{{ old('offer_value') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                @error('offer_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Campaign Period -->
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Campaign Period</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Campaign
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const oneMonthLater = new Date();
    oneMonthLater.setMonth(oneMonthLater.getMonth() + 1);
    const oneMonthLaterStr = oneMonthLater.toISOString().split('T')[0];

    if (!document.getElementById('start_date').value) {
        document.getElementById('start_date').value = today;
    }
    if (!document.getElementById('end_date').value) {
        document.getElementById('end_date').value = oneMonthLaterStr;
    }
});
</script>
@endpush
@endsection 