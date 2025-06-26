@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto py-8 max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Edit Offer</h1>
    <form action="{{ route('admin.offers.update', $offer) }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
        @csrf
        @method('PUT')
        @include('admin.offers._form', ['offer' => $offer])
        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.offers.index') }}" class="px-4 py-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="bg-[#6E0D25] text-white px-6 py-2 rounded hover:bg-[#8B0D2F]">Update Offer</button>
        </div>
    </form>
</div>
@endsection 