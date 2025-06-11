@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Point of Sale</h1>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <p class="text-gray-600">POS system is currently under maintenance.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize POS app
    document.addEventListener('DOMContentLoaded', function() {
        // The Vue component will be mounted automatically by app.js
    });
</script>
@endpush
