@extends('desktop.admin.layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card-body">
                    <div id="payment-manager"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Initialize any payment manager specific scripts here
    document.addEventListener('DOMContentLoaded', function() {
        // The Vue component will be mounted automatically by app.js
    });
</script>
@endpush 