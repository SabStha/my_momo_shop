@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Point of Sale</h5>
                </div>
                <div class="card-body">
                    <div id="pos-app"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Register the PosApp component
    Vue.component('pos-app', require('./components/PosApp.vue').default);
</script>
@endpush 