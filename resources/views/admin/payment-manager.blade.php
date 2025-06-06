@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div id="payment-manager"></div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/app.js'])
<script>
    // The Vue component will be mounted automatically by app.js
</script>
@endpush 