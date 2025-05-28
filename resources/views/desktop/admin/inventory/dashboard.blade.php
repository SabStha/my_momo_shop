@extends('desktop.admin.layouts.admin')
@section('title', 'Inventory Dashboard')
@section('content')
<div class="container py-3">
    <h2>Inventory Dashboard</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12 mb-3">
            <button class="btn btn-outline-primary w-100 py-4 inventory-tab" data-tab="count">
                <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                Daily Inventory Count
            </button>
        </div>
        <div class="col-md-4 col-12 mb-3">
            <button class="btn btn-outline-success w-100 py-4 inventory-tab" data-tab="forecast">
                <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                2-Day Forecast
            </button>
        </div>
        <div class="col-md-4 col-12 mb-3">
            <button class="btn btn-outline-warning w-100 py-4 inventory-tab" data-tab="orders">
                <i class="fas fa-truck fa-2x mb-2"></i><br>
                Orders
            </button>
        </div>
    </div>
    <div id="inventory-content">
        <div id="tab-count" class="inventory-section" style="display:none;">
            @include('desktop.admin.inventory.count-partial')
        </div>
        <div id="tab-forecast" class="inventory-section" style="display:none;">
            @include('desktop.admin.inventory.forecast-partial')
        </div>
        <div id="tab-orders" class="inventory-section" style="display:none;">
            @include('desktop.admin.inventory.orders-partial')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('.inventory-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.inventory-section').hide();
        $('#tab-' + tab).show();
    });
    // Show the first tab by default
    $('#tab-count').show();
});
</script>
@endpush 