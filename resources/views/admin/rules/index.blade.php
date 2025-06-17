@extends('layouts.admin')

@section('title', 'Automation Rules')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Automation Rules</h1>
        <a href="{{ route('admin.rules.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Create New Rule
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($rules as $rule)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-gray-900">{{ $rule->name }}</h2>
                                    <p class="text-sm text-gray-500">{{ $rule->description }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <form action="{{ route('admin.rules.toggle', $rule) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                        {{ $rule->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.rules.edit', $rule) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this rule?')">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-500">
                                <strong>Conditions:</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach($rule->conditions as $condition)
                                        <li>
                                            @switch($condition['type'])
                                                @case('risk_level')
                                                    Risk Level is {{ $condition['value'] }}
                                                    @break
                                                @case('vip_status')
                                                    VIP Status is {{ $condition['value'] ? 'Yes' : 'No' }}
                                                    @break
                                                @case('purchase_frequency')
                                                    Purchase Frequency {{ $condition['operator'] }} {{ $condition['value'] }} in {{ $condition['period'] }} days
                                                    @break
                                                @case('spending_amount')
                                                    Spending Amount {{ $condition['operator'] }} ${{ $condition['value'] }} in {{ $condition['period'] }} days
                                                    @break
                                                @case('last_purchase')
                                                    Last Purchase {{ $condition['operator'] }} {{ $condition['value'] }} days ago
                                                    @break
                                            @endswitch
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="text-sm text-gray-500 mt-2">
                                <strong>Actions:</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach($rule->actions as $action)
                                        <li>
                                            @switch($action['type'])
                                                @case('launch_campaign')
                                                    Launch Campaign: {{ \App\Models\Campaign::find($action['campaign_id'])->name }}
                                                    @break
                                                @case('update_customer')
                                                    Update Customer: {{ implode(', ', array_map(fn($k, $v) => "$k = $v", array_keys($action['updates']), $action['updates'])) }}
                                                    @break
                                                @case('send_notification')
                                                    Send Notification
                                                    @break
                                            @endswitch
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-4 sm:px-6">
                    <div class="text-center text-gray-500 py-4">
                        No rules found. Create your first automation rule to get started.
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection 