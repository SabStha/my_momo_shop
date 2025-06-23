@extends('layouts.admin')

@section('title', 'Audit Sessions')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Audit Sessions</h2>
            <p class="text-sm text-gray-500 mt-1">{{ ucfirst($type) }} audit sessions and their results</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <a href="{{ route('admin.inventory.audit-reports.index', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-chart-bar"></i> Audit Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $user = auth()->user();
                $isMainBranch = $user && $user->branch && $user->branch->is_main;
            @endphp
            
            @if($isMainBranch)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select name="branch" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branchOption)
                            <option value="{{ $branchOption->id }}" {{ request('branch') == $branchOption->id ? 'selected' : '' }}>
                                {{ $branchOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <input type="text" value="{{ $branch ? $branch->name : 'Your Branch' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600" readonly>
                    <input type="hidden" name="branch" value="{{ $branch ? $branch->id : '' }}">
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Audit Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="weekly" {{ $type === 'weekly' ? 'selected' : '' }}>Weekly</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-search mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Sessions List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Audit Sessions ({{ $sessions->count() }})</h3>
        </div>
        
        @if($sessions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auditor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Checked</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancies</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($sessions as $session)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            Session: {{ Str::limit($session->audit_session_id, 8) }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $session->checked_at ? $session->checked_at->format('M j, Y g:i A') : 'N/A' }}
                                        </p>
                                        @if($session->branch)
                                            <p class="text-xs text-gray-400">{{ $session->branch->name }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $session->user ? $session->user->name : 'Unknown' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $session->user ? $session->user->email : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $session->items_checked }}</div>
                                    <div class="text-sm text-gray-500">items audited</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $session->discrepancies }}</span>
                                        <span class="text-sm text-gray-500">discrepancies</span>
                                    </div>
                                    <div class="text-sm {{ $session->total_discrepancy_value > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($session->total_discrepancy_value, 2) }} Rs.
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.inventory.audit-reports.detailed', ['type' => $type, 'session' => $session->audit_session_id, 'branch' => request('branch')]) }}" 
                                       class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 transition-all duration-200">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Audit Sessions Found</h3>
                <p class="text-gray-600 mb-4">
                    No {{ $type }} audit sessions found for the selected criteria.
                </p>
                <a href="{{ route('admin.inventory.' . $type . '-checks.index', ['branch' => request('branch')]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-plus"></i> Start New {{ ucfirst($type) }} Check
                </a>
            </div>
        @endif
    </div>
</div>
@endsection 