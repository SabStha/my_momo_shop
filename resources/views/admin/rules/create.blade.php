@extends('layouts.admin')

@section('title', 'Create Automation Rule')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Create Automation Rule</h1>
        <a href="{{ route('admin.rules.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Back to Rules
        </a>
    </div>

    <form action="{{ route('admin.rules.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white shadow sm:rounded-md">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Basic Information</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Rule Name</label>
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
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                <input type="number" name="priority" id="priority" value="{{ old('priority', 1) }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conditions -->
                    <div>
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Conditions</h3>
                            <button type="button" onclick="addCondition()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Condition
                            </button>
                        </div>
                        <div id="conditions-container" class="mt-4 space-y-4">
                            <!-- Conditions will be added here dynamically -->
                        </div>
                        @error('conditions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div>
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Actions</h3>
                            <button type="button" onclick="addAction()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Action
                            </button>
                        </div>
                        <div id="actions-container" class="mt-4 space-y-4">
                            <!-- Actions will be added here dynamically -->
                        </div>
                        @error('actions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Rule
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let conditionIndex = 0;
let actionIndex = 0;

function addCondition() {
    const container = document.getElementById('conditions-container');
    const template = `
        <div class="condition-item border rounded-md p-4">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Condition Type</label>
                    <select name="conditions[${conditionIndex}][type]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="updateConditionFields(${conditionIndex})">
                        <option value="risk_level">Risk Level</option>
                        <option value="vip_status">VIP Status</option>
                        <option value="purchase_frequency">Purchase Frequency</option>
                        <option value="spending_amount">Spending Amount</option>
                        <option value="last_purchase">Last Purchase</option>
                    </select>
                </div>
                <div class="condition-fields">
                    <!-- Fields will be updated dynamically -->
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-900">Remove</button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
    updateConditionFields(conditionIndex);
    conditionIndex++;
}

function addAction() {
    const container = document.getElementById('actions-container');
    const template = `
        <div class="action-item border rounded-md p-4">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Action Type</label>
                    <select name="actions[${actionIndex}][type]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="updateActionFields(${actionIndex})">
                        <option value="launch_campaign">Launch Campaign</option>
                        <option value="update_customer">Update Customer</option>
                        <option value="send_notification">Send Notification</option>
                    </select>
                </div>
                <div class="action-fields">
                    <!-- Fields will be updated dynamically -->
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-900">Remove</button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
    updateActionFields(actionIndex);
    actionIndex++;
}

function updateConditionFields(index) {
    const container = document.querySelector(`select[name="conditions[${index}][type]"]`).closest('.condition-item').querySelector('.condition-fields');
    const type = document.querySelector(`select[name="conditions[${index}][type]"]`).value;

    let fields = '';
    switch (type) {
        case 'risk_level':
            fields = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Risk Level</label>
                    <select name="conditions[${index}][value]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            `;
            break;
        case 'vip_status':
            fields = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">VIP Status</label>
                    <select name="conditions[${index}][value]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                </div>
            `;
            break;
        case 'purchase_frequency':
        case 'spending_amount':
            fields = `
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Operator</label>
                        <select name="conditions[${index}][operator]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="equals">Equals</option>
                            <option value="not_equals">Not Equals</option>
                            <option value="greater_than">Greater Than</option>
                            <option value="less_than">Less Than</option>
                            <option value="greater_than_or_equal">Greater Than or Equal</option>
                            <option value="less_than_or_equal">Less Than or Equal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Value</label>
                        <input type="number" name="conditions[${index}][value]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Period (days)</label>
                        <input type="number" name="conditions[${index}][period]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
            `;
            break;
        case 'last_purchase':
            fields = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Operator</label>
                        <select name="conditions[${index}][operator]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="equals">Equals</option>
                            <option value="not_equals">Not Equals</option>
                            <option value="greater_than">Greater Than</option>
                            <option value="less_than">Less Than</option>
                            <option value="greater_than_or_equal">Greater Than or Equal</option>
                            <option value="less_than_or_equal">Less Than or Equal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Days Ago</label>
                        <input type="number" name="conditions[${index}][value]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
            `;
            break;
    }
    container.innerHTML = fields;
}

function updateActionFields(index) {
    const container = document.querySelector(`select[name="actions[${index}][type]"]`).closest('.action-item').querySelector('.action-fields');
    const type = document.querySelector(`select[name="actions[${index}][type]"]`).value;

    let fields = '';
    switch (type) {
        case 'launch_campaign':
            fields = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Campaign</label>
                    <select name="actions[${index}][campaign_id]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>
            `;
            break;
        case 'update_customer':
            fields = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Updates</label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="actions[${index}][updates][]" value="vip_status" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2">Update VIP Status</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="actions[${index}][updates][]" value="risk_level" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2">Update Risk Level</span>
                        </label>
                    </div>
                </div>
            `;
            break;
        case 'send_notification':
            fields = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="actions[${index}][message]" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                </div>
            `;
            break;
    }
    container.innerHTML = fields;
}

// Add initial condition and action if none exist
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('conditions-container').children.length === 0) {
        addCondition();
    }
    if (document.getElementById('actions-container').children.length === 0) {
        addAction();
    }
});
</script>
@endpush
@endsection 