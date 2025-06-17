@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Campaign Trigger</h1>
        <a href="{{ route('admin.triggers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            <i class="fas fa-arrow-left mr-2"></i> Back to Triggers
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.triggers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Trigger Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="segment_id" class="block text-sm font-medium text-gray-700">Target Segment</label>
                        <select name="segment_id" id="segment_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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

                <!-- Trigger Settings -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Trigger Settings</h2>

                    <div>
                        <label for="trigger_type" class="block text-sm font-medium text-gray-700">Trigger Type</label>
                        <select name="trigger_type" id="trigger_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="behavioral" {{ old('trigger_type') == 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                            <option value="scheduled" {{ old('trigger_type') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                        @error('trigger_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="behavioral-conditions" class="space-y-4">
                        <div>
                            <label for="trigger_condition" class="block text-sm font-medium text-gray-700">Trigger Condition</label>
                            <select name="trigger_condition[]" id="trigger_condition"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="purchase_frequency" {{ old('trigger_condition') == 'purchase_frequency' ? 'selected' : '' }}>Purchase Frequency</option>
                                <option value="spending_amount" {{ old('trigger_condition') == 'spending_amount' ? 'selected' : '' }}>Spending Amount</option>
                                <option value="inactivity" {{ old('trigger_condition') == 'inactivity' ? 'selected' : '' }}>Inactivity Period</option>
                                <option value="cart_abandonment" {{ old('trigger_condition') == 'cart_abandonment' ? 'selected' : '' }}>Cart Abandonment</option>
                            </select>
                        </div>

                        <div>
                            <label for="condition_value" class="block text-sm font-medium text-gray-700">Condition Value</label>
                            <input type="number" name="condition_value" id="condition_value" value="{{ old('condition_value') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div id="scheduled-settings" class="space-y-4 hidden">
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency</label>
                            <select name="frequency" id="frequency"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>

                        <div>
                            <label for="next_scheduled_at" class="block text-sm font-medium text-gray-700">Next Scheduled Date</label>
                            <input type="datetime-local" name="next_scheduled_at" id="next_scheduled_at" value="{{ old('next_scheduled_at') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign Settings -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Campaign Settings</h2>

                <div>
                    <label for="campaign_type" class="block text-sm font-medium text-gray-700">Campaign Type</label>
                    <select name="campaign_type" id="campaign_type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="email" {{ old('campaign_type') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms" {{ old('campaign_type') == 'sms' ? 'selected' : '' }}>SMS</option>
                    </select>
                </div>

                <div>
                    <label for="campaign_template" class="block text-sm font-medium text-gray-700">Campaign Template</label>
                    <textarea name="campaign_template" id="campaign_template" rows="4" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('campaign_template') }}</textarea>
                </div>

                <div>
                    <label for="cooldown_period" class="block text-sm font-medium text-gray-700">Cooldown Period (days)</label>
                    <input type="number" name="cooldown_period" id="cooldown_period" value="{{ old('cooldown_period', 7) }}" min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.triggers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Trigger
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggerType = document.getElementById('trigger_type');
    const behavioralConditions = document.getElementById('behavioral-conditions');
    const scheduledSettings = document.getElementById('scheduled-settings');

    function toggleSettings() {
        if (triggerType.value === 'behavioral') {
            behavioralConditions.classList.remove('hidden');
            scheduledSettings.classList.add('hidden');
        } else {
            behavioralConditions.classList.add('hidden');
            scheduledSettings.classList.remove('hidden');
        }
    }

    triggerType.addEventListener('change', toggleSettings);
    toggleSettings();
});
</script>
@endpush
@endsection 