@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index', ['branch' => $order->branch_id]) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @include('admin.orders.show-partial', ['order' => $order])
</div>
@endsection 