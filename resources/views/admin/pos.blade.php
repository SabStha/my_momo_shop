@extends('layouts.pos')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div id="pos-app" class="bg-white rounded-lg shadow-lg p-6"></div>
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
