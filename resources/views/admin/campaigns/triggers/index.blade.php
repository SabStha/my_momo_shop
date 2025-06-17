@extends('layouts.admin')

@section('title', 'Campaign Triggers')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Campaign Triggers</h1>
                <p class="mt-1 text-sm text-gray-600">Manage automated marketing campaign triggers</p>
            </div>
            <a href="{{ route('admin.campaigns.triggers.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Trigger
            </a>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <!-- Active Triggers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Triggers</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $triggers->where('is_active', true)->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduled Triggers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Scheduled Triggers</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $triggers->where('trigger_type', 'scheduled')->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Triggers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Triggers</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $triggers->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Integrations Section -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Platform Integrations</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Sync your segments with email and SMS platforms</p>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Mailchimp Integration -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Mailchimp</h4>
                                <p class="text-sm text-gray-500">Sync segments to your email campaigns</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button onclick="toggleModal('mailchimpSyncModal')" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Sync Segments
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Twilio Integration -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Twilio</h4>
                                <p class="text-sm text-gray-500">Sync segments to your SMS campaigns</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button onclick="toggleModal('twilioSyncModal')" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Sync Segments
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Triggers List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($triggers as $trigger)
            <li>
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($trigger->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $trigger->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $trigger->description }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <button onclick="toggleModal('testTriggerModal')" 
                                    class="test-trigger inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    data-trigger-id="{{ $trigger->id }}">
                                Test
                            </button>
                            <a href="{{ route('admin.campaigns.triggers.edit', $trigger) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Edit
                            </a>
                            <form action="{{ route('admin.campaigns.triggers.destroy', $trigger) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        onclick="return confirm('Are you sure you want to delete this trigger?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-2 sm:flex sm:justify-between">
                        <div class="sm:flex">
                            <p class="flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ $trigger->segment->name ?? 'No Segment' }}
                            </p>
                            <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Last triggered: {{ $trigger->last_triggered_at ? $trigger->last_triggered_at->diffForHumans() : 'Never' }}
                            </p>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li class="px-4 py-5 sm:px-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No triggers</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new campaign trigger.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.campaigns.triggers.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Trigger
                        </a>
                    </div>
                </div>
            </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $triggers->links() }}
    </div>
</div>

<!-- Test Trigger Modal -->
<div id="testTriggerModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Test Trigger
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" id="testTriggerMessage">
                            Loading...
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6">
                <button type="button" onclick="toggleModal('testTriggerModal')" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mailchimp Sync Modal -->
<div id="mailchimpSyncModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Sync with Mailchimp
                    </h3>
                    <div class="mt-4">
                        <select id="mailchimpSegment" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select a segment</option>
                            @foreach($segments as $segment)
                                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <select id="mailchimpList" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select a Mailchimp list</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="button" onclick="syncWithMailchimp()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                    Sync Now
                </button>
                <button type="button" onclick="toggleModal('mailchimpSyncModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Twilio Sync Modal -->
<div id="twilioSyncModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Sync with Twilio
                    </h3>
                    <div class="mt-4">
                        <select id="twilioSegment" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="">Select a segment</option>
                            @foreach($segments as $segment)
                                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <select id="twilioGroup" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="">Select a Twilio group</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="button" onclick="syncWithTwilio()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
                    Sync Now
                </button>
                <button type="button" onclick="toggleModal('twilioSyncModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.test-trigger').forEach(button => {
        button.addEventListener('click', async function() {
            const triggerId = this.dataset.triggerId;
            const modal = document.getElementById('testTriggerModal');
            const messageElement = document.getElementById('testTriggerMessage');
            
            messageElement.textContent = 'Testing trigger...';
            toggleModal('testTriggerModal');
            
            try {
                const response = await fetch(`/admin/campaigns/triggers/${triggerId}/test`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                messageElement.textContent = data.message;
            } catch (error) {
                messageElement.textContent = 'Error testing trigger. Please try again.';
            }
        });
    });
});

function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.toggle('hidden');
}

// Mailchimp Integration
async function loadMailchimpLists() {
    try {
        const response = await fetch('/admin/integrations/mailchimp/lists');
        const data = await response.json();
        const select = document.getElementById('mailchimpList');
        select.innerHTML = '<option value="">Select a Mailchimp list</option>';
        data.lists.forEach(list => {
            select.innerHTML += `<option value="${list.id}">${list.name}</option>`;
        });
    } catch (error) {
        console.error('Error loading Mailchimp lists:', error);
    }
}

async function syncWithMailchimp() {
    const segmentId = document.getElementById('mailchimpSegment').value;
    const listId = document.getElementById('mailchimpList').value;
    
    if (!segmentId || !listId) {
        alert('Please select both a segment and a Mailchimp list');
        return;
    }
    
    try {
        const response = await fetch('/admin/integrations/mailchimp/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ segment_id: segmentId, list_id: listId })
        });
        
        const data = await response.json();
        alert(data.message);
        toggleModal('mailchimpSyncModal');
    } catch (error) {
        console.error('Error syncing with Mailchimp:', error);
        alert('Error syncing with Mailchimp. Please try again.');
    }
}

// Twilio Integration
async function loadTwilioGroups() {
    try {
        const response = await fetch('/admin/integrations/twilio/groups');
        const data = await response.json();
        const select = document.getElementById('twilioGroup');
        select.innerHTML = '<option value="">Select a Twilio group</option>';
        data.groups.forEach(group => {
            select.innerHTML += `<option value="${group.id}">${group.name}</option>`;
        });
    } catch (error) {
        console.error('Error loading Twilio groups:', error);
    }
}

async function syncWithTwilio() {
    const segmentId = document.getElementById('twilioSegment').value;
    const groupId = document.getElementById('twilioGroup').value;
    
    if (!segmentId || !groupId) {
        alert('Please select both a segment and a Twilio group');
        return;
    }
    
    try {
        const response = await fetch('/admin/integrations/twilio/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ segment_id: segmentId, group_id: groupId })
        });
        
        const data = await response.json();
        alert(data.message);
        toggleModal('twilioSyncModal');
    } catch (error) {
        console.error('Error syncing with Twilio:', error);
        alert('Error syncing with Twilio. Please try again.');
    }
}

// Load integration data when modals are opened
document.getElementById('mailchimpSyncModal').addEventListener('show', loadMailchimpLists);
document.getElementById('twilioSyncModal').addEventListener('show', loadTwilioGroups);
</script>
@endpush 