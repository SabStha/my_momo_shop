@extends('layouts.app')

@section('content')
@if(session('referral_discount'))
    <div class="modal fade" id="discount-popup" tabindex="-1" aria-labelledby="discountPopupLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discountPopupLabel">Discount Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You get a discount of Rs {{ session('referral_discount') }} because of the referral link!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endif
<div class="cart">
    <a href="{{ route('cart') }}" class="btn btn-outline-light">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge bg-danger">[[ cartCount ]]</span>
    </a>
</div>
@endsection

@section('scripts')
@if(session('referral_discount'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var discountModal = new bootstrap.Modal(document.getElementById('discount-popup'));
        discountModal.show();
    });
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const { createApp } = Vue;
    createApp({
        delimiters: ['[[', ']]'],
        data() {
            return {
                cartCount: 0,
                // ... existing data ...
            }
        },
        methods: {
            updateCartCount(count) {
                this.cartCount = count;
            },
            // ... existing methods ...
        }
    }).mount('#app');
});
</script>
@endsection 