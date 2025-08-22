@extends('layouts.app')

@section('content')
<main class="pb-4 min-h-screen">
    
    <!-- Hero Section -->
    @include('home.sections.hero')
    

    
    <!-- Featured Products -->
    @include('home.sections.featured-products')
    
    <!-- App Onboarding Guide -->
    {{-- @include('home.sections.limited-offers') --}}
    
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

<!-- Floating Tour Button -->
<div class="fixed bottom-20 right-4 z-40">
    <button onclick="startAmaKoTour()" class="bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 animate-pulse">
        <div class="text-2xl">🥟</div>
    </button>
    <div class="absolute -top-2 -left-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-bounce">
        <span>!</span>
    </div>
</div>

@endsection

@push('meta')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="theme-color" content="#6E0D25">
@endpush
