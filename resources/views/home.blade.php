@extends('layouts.app')

@section('content')
<main class="pb-4 min-h-screen">
    
    <!-- Hero Section -->
    @include('home.sections.hero')
    
    <!-- Featured Products -->
    @include('home.sections.featured-products')
    
    <!-- Why Choose Us & Success Story -->
    @include('home.sections.why-choose-us')
    
    <!-- Customer Reviews -->
    @include('home.sections.customer-reviews')
    
    <!-- Shop Info -->
    @include('home.sections.shop-info')

</main>

<!-- Quick Order Modal -->
@include('home.components.quick-order-modal')

<!-- Add to Cart Success Toast -->
@include('home.components.cart-toast')

@endsection

@push('meta')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="theme-color" content="#6E0D25">
@endpush
