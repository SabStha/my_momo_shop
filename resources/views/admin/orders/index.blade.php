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
                <x-admin.orders-table :orders="$posOrdersPaid" />
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-1">Unpaid</h4>
                <x-admin.orders-table :orders="$posOrdersUnpaid" />
            </div>
        </div>
    </div>

    {{-- Online Orders --}}
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-indigo-700 mb-2">Online Orders</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-green-700 mb-1">Paid</h4>
                <x-admin.orders-table :orders="$onlineOrdersPaid" />
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-1">Unpaid</h4>
                <x-admin.orders-table :orders="$onlineOrdersUnpaid" />
            </div>
        </div>
    </div>

    {{-- Order History --}}
    <div>
        <h3 class="text-xl font-semibold text-indigo-700 mb-2">Order History (All)</h3>
        <x-admin.orders-table :orders="$orderHistory" />
        <div class="mt-4">{{ $orderHistory->links() }}</div>
    </div>
</div>
@endsection 