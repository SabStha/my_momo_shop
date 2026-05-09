@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Order Management</h2>

    {{-- POS Orders --}}
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-indigo-700 mb-2">POS Orders (Dine-In & Takeaway)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-green-700 mb-1">Paid</h4>
                @include('admin.orders-table', ['orders' => $posOrdersPaid])
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-1">Unpaid</h4>
                @include('admin.orders-table', ['orders' => $posOrdersUnpaid])
            </div>
        </div>
    </div>

    {{-- Online Orders --}}
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-indigo-700 mb-2">Online Orders</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-green-700 mb-1">Paid</h4>
                @include('admin.orders-table', ['orders' => $onlineOrdersPaid])
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-1">Unpaid</h4>
                @include('admin.orders-table', ['orders' => $onlineOrdersUnpaid])
            </div>
        </div>
    </div>

    {{-- Order History --}}
    <div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
            <h3 class="text-xl font-semibold text-indigo-700">Order History (All)</h3>
            
            <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <!-- Preserve existing branch or other query params if needed, except the ones we're updating -->
                @if(request('branch'))
                    <input type="hidden" name="branch" value="{{ request('branch') }}">
                @endif
                
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID or Name" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                
                <select name="status" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparing</option>
                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                
                <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From Date" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To Date" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm transition transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.orders.index', ['branch' => request('branch')]) }}" class="text-gray-500 hover:text-gray-700 text-sm underline">Clear</a>
                @endif
            </form>
        </div>
        
        @include('admin.orders-table', ['orders' => $orderHistory])
        <div class="mt-4">{{ $orderHistory->links() }}</div>
    </div>
</div>
@endsection 