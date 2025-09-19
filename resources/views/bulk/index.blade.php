@extends('layouts.app')

@section('content')
<style>
/* Mobile-specific improvements */
@media (max-width: 640px) {
    .mobile-container {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .mobile-text {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .mobile-flex {
        flex-direction: column;
    }
    
    .mobile-grid {
        grid-template-columns: 1fr;
    }
}

/* Enhanced visual effects */
.party-pack-card {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border: 2px solid transparent;
    background-clip: padding-box;
    position: relative;
    overflow: hidden;
}

.party-pack-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.4)), var(--bg-image);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 1;
    z-index: 0;
    transition: opacity 0.3s ease;
    filter: blur(1px);
}

.party-pack-card:hover::after {
    opacity: 0.4;
}

.party-pack-card > * {
    position: relative;
    z-index: 1;
}

/* Text styling for better readability over background images */
.party-pack-card h3 {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    font-weight: 700;
    font-size: 18px;
}

.party-pack-card p {
    color: #ffffff !important;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
}

.party-pack-card .text-gray-600 {
    color: #f3f4f6 !important;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
}

.party-pack-card .text-gray-700 {
    color: #ffffff !important;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
}

.party-pack-card .text-amk-brown-1 {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.party-pack-card .text-amk-brown-1 {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

/* Deal card styling */
.party-pack-card .bg-white {
    background-color: rgba(64, 64, 64, 0.5) !important;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 12px !important;
    margin-bottom: 16px !important;
}

.party-pack-card .bg-gradient-to-br {
    background: rgba(80, 80, 80, 0.5) !important;
    backdrop-filter: blur(8px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Deal card typography */
.party-pack-card .bg-white h3 {
    color: #ffffff !important;
    font-size: 20px;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    letter-spacing: 0.5px;
}

.party-pack-card .bg-white .text-2xl {
    color: #ffffff !important;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 1px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.party-pack-card .bg-white .text-xs {
    color: #e5e5e5 !important;
    font-size: 14px;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
}

.party-pack-card .bg-white .text-sm {
    color: #ffffff !important;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
}

/* Button improvements */
.party-pack-card .bg-white button {
    transition: all 0.3s ease;
}

.party-pack-card .bg-white button:hover {
    transform: scale(1.02);
}

/* Add testimonial quote */
.party-pack-card .bg-white .text-xs.text-gray-600:last-of-type::after {
    content: '"Our team loved it — perfect for events!" - Sarah M.';
    display: block;
    font-style: italic;
    margin-top: 8px;
    color: #ffffff;
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
}

/* Better, more relevant icons */
.party-pack-card .package-icon {
    background: linear-gradient(135deg, var(--amako-brown-1), var(--amako-brown-2));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(110, 13, 37, 0.3);
}

.party-pack-card .item-icon {
    background: linear-gradient(135deg, var(--amako-brown-1), var(--amako-brown-2));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(110, 13, 37, 0.2);
}

/* Horizontal scroll for item breakdown on mobile */
@media (max-width: 640px) {
    .item-breakdown {
        display: flex;
        overflow-x: auto;
        gap: 8px;
        padding: 8px 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .item-breakdown::-webkit-scrollbar {
        display: none;
    }
    
    .item-breakdown > div {
        flex-shrink: 0;
        min-width: 120px;
    }
}

/* Hero background animation */
.hero-background {
    animation: subtle-zoom 20s ease-in-out infinite;
}

@keyframes subtle-zoom {
    0%, 100% { transform: scale(1.05); }
    50% { transform: scale(1.1); }
}

.party-pack-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--amako-brown-1) 0%, var(--amako-brown-2) 100%);
    border-radius: inherit;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.party-pack-card:hover::before {
    opacity: 0.05;
}

.value-badge {
    background: linear-gradient(45deg, #fbbf24, #f59e0b, #d97706);
    animation: value-pulse 2s ease-in-out infinite;
}

@keyframes value-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.trust-badge {
    background: linear-gradient(45deg, #10b981, #059669, #047857);
    animation: trust-glow 3s ease-in-out infinite;
}

@keyframes trust-glow {
    0%, 100% { box-shadow: 0 0 5px rgba(16, 185, 129, 0.3); }
    50% { box-shadow: 0 0 15px rgba(16, 185, 129, 0.6); }
}

.urgency-banner {
    background: linear-gradient(90deg, var(--amako-brown-1), var(--amako-amber));
    animation: urgency-slide 2s ease-in-out infinite;
}

@keyframes urgency-slide {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(5px); }
}

.lifestyle-image {
    background: linear-gradient(135deg, rgba(110, 13, 37, 0.1) 0%, rgba(139, 26, 58, 0.1) 100%);
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.lifestyle-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%236E0D25" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%236E0D25" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%236E0D25" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.pricing-card {
    background: linear-gradient(135deg, var(--amako-brown-1) 0%, var(--amako-brown-2) 100%);
    color: white;
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.pricing-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(180deg); }
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--amako-brown-1), var(--amako-brown-2));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
    color: white;
    box-shadow: 0 4px 12px rgba(110, 13, 37, 0.3);
}

.primary-cta {
    background: linear-gradient(135deg, var(--amako-brown-1) 0%, var(--amako-brown-2) 100%);
    border: none;
    border-radius: 12px;
    padding: 16px 24px;
    font-weight: bold;
    font-size: 16px;
    color: white;
    box-shadow: 0 4px 12px rgba(110, 13, 37, 0.3);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.primary-cta::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.primary-cta:hover::before {
    left: 100%;
}

.primary-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 13, 37, 0.4);
}

/* Warm, professional color palette */
.hero-gradient {
    background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 50%, #fbbf24 100%);
}

.trust-card {
    background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
    border: 1px solid #fde68a;
}

.urgency-card {
    background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
    border: 1px solid #fbbf24;
}

.secondary-cta {
    background: transparent;
    border: 2px solid var(--amako-brown-1);
    border-radius: 12px;
    padding: 14px 22px;
    font-weight: 600;
    font-size: 14px;
    color: var(--amako-brown-1);
    transition: all 0.3s ease;
}

.secondary-cta:hover {
    background: var(--amako-brown-1);
    color: white;
    transform: translateY(-1px);
}
</style>

<div x-data="bulkOrder()" class="bg-white min-h-screen pb-2 mobile-container">

    <!-- COMPACT HERO SECTION (500-550px total height) -->
        <div class="relative z-10 pt-8 sm:pt-10 pb-1 px-1">
        <div class="w-full">
            
                            <!-- A. Enhanced Hero Image with Integrated Elements -->
            <div class="relative h-[400px] sm:h-[500px] rounded-2xl overflow-hidden mb-4 shadow-xl">
                <!-- Background with Party Pack Image -->
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat hero-background" style="background-image: url('/storage/products/foods/tandoori-momos.jpg');">
                    <!-- Overlay for better text readability -->
                    <div class="absolute inset-0 bg-gradient-to-br from-black/40 to-black/60"></div>
                    
                    <!-- Main Content Area -->
                    <div class="absolute inset-0 flex flex-col justify-between p-6">
                        
                        <!-- Top Section: Title Only -->
                        <div>
                            <!-- Main Title -->
                            <div class="text-left text-white bg-black/50 backdrop-blur-sm rounded-2xl p-3 max-w-2xl">
                                <h1 class="text-lg sm:text-xl font-bold mb-1 drop-shadow-lg text-white">
                                AmaKo Party Pack
                            </h1>
                                <p class="text-xs sm:text-sm mb-1 drop-shadow-lg font-medium text-white">
                                Perfectly portioned for 8–10 guests
                            </p>
                                <p class="text-xs opacity-95 drop-shadow-lg text-white">
                                Includes momos, sides, sauces & sealed delivery
                            </p>
                            </div>
                        </div>
                        
                        <!-- Middle Section: Empty for Hero Image -->
                        <div class="flex-1"></div>
                        
                        <!-- Bottom Section: CTA and Trust Indicators -->
                        <div class="space-y-3">
                            <!-- CTA Button -->
                            <div class="text-left max-w-xl">
                                <button @click="scrollToPackages()" class="bg-amk-brown-1 text-white py-2 px-4 rounded-xl font-bold text-sm shadow-lg hover:bg-amk-brown-2 transition-all duration-300 transform hover:scale-105">
                                    🎉 Customize & Order Now
                                </button>
                            </div>
                            
                            <!-- Trust Indicators -->
                            <div class="bg-black/60 backdrop-blur-md rounded-xl p-2 shadow-lg max-w-3xl">
                                <div class="flex items-center gap-2 sm:gap-3 text-xs">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="font-semibold text-white">Dynamic Rating</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-semibold text-white">100% Satisfaction</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 12a1 1 0 011 1c0 1.691-.2 3.352-.584 4.906l-.767 2.767a1 1 0 01-1.414.586l-2.767-.767A13.022 13.022 0 016 18c-1.691 0-3.352-.2-4.906-.584l-2.767-.767a1 1 0 01-.586-1.414l.767-2.767A13.022 13.022 0 012 12c0-1.691.2-3.352.584-4.906l.767-2.767a1 1 0 011.414-.586l2.767.767A13.022 13.022 0 0118 12a1 1 0 011 1z"/>
                                        </svg>
                                        <span class="font-semibold text-white">Cancel free up to 12 hrs</span>
                        </div>
                    </div>
                </div>
            </div>
            
                    </div>
                </div>
            </div>
            
            <!-- Toggle Switch (Compact) -->
            <div class="flex justify-center mb-0">
                <div class="bg-white/95 backdrop-blur-sm rounded-xl p-1 flex w-full max-w-md border border-gray-200 shadow-lg">
                    <button @click="orderType = 'cooked'" 
                            :class="{ 'bg-amk-brown-1 text-white shadow-lg': orderType === 'cooked', 'text-amk-brown-1 hover:bg-gray-50': orderType !== 'cooked' }"
                            class="flex-1 px-3 py-2 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center gap-2 text-sm min-h-[40px]">
                        <span class="text-amk-amber">🔥</span>
                        <span class="font-semibold">Hot</span>
                    </button>
                    <button @click="orderType = 'frozen'" 
                            :class="{ 'bg-amk-brown-1 text-white shadow-lg': orderType === 'frozen', 'text-amk-brown-1 hover:bg-gray-50': orderType !== 'frozen' }"
                            class="flex-1 px-3 py-2 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center gap-2 text-sm min-h-[40px]">
                        <span class="text-blue-600">❄️</span>
                        <span class="font-semibold">Frozen</span>
                    </button>
                </div>
            </div>
            

        </div>
    </div>

    <!-- PACKAGE PREVIEWS -->
    <div class="px-2 space-y-1 w-full">
        
        <!-- ALL PACKAGES -->
        <div class="space-y-6">
            <!-- COOKED PACKAGES -->
            <div x-show="orderType === 'cooked'" x-transition>
                <div class="text-center mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-amk-brown-1 mb-1">🔥 Hot & Ready Packages</h2>
                    <p class="text-sm text-gray-600">Perfect for immediate consumption</p>
                </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4 mobile-grid">
                @foreach($packages['cooked'] as $index => $package)
                <div class="party-pack-card rounded-2xl shadow-xl p-3 sm:p-5 hover:shadow-2xl transition-all duration-300" 
                     style="--bg-image: url('{{ $package->image ? '/storage/' . $package->image : '/storage/products/foods/default-momo.jpg' }}');">
                    <!-- Package Header -->
                    <div class="text-center mb-3 sm:mb-4">
                        <div class="w-16 h-16 mx-auto mb-3 package-icon shadow-lg">
                            @if(str_contains(strtolower($package->name), 'family'))
                            <!-- Family Feast - Users Group Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A1.5 1.5 0 0 0 18.54 8H17c-.8 0-1.54.37-2.01 1l-3.99 5.33V22h8zM12.5 11.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5S11 9.17 11 10s.67 1.5 1.5 1.5zM5.5 6c1.11 0 2-.89 2-2s-.89-2-2-2-2 .89-2 2 .89 2 2 2zm2 16v-7H9V9c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v6h1.5v7h4z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'office'))
                            <!-- Office Saver - Briefcase Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'party'))
                            <!-- Party Pack - Sparkles Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'couple'))
                            <!-- Couple Combo - Heart Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'kids'))
                            <!-- Kids Special - Ball Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'event') || str_contains(strtolower($package->name), 'bulk'))
                            <!-- Event Bulk Box - Calendar Clock Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                            </svg>
                            @else
                            <!-- Default - Food Bowl Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                            @endif
                        </div>
                        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-amk-brown-1 mb-1">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-xs">{{ $package->description }}</p>
                    </div>
                    
                    <!-- Visual Package Breakdown -->
                    <div class="item-breakdown grid grid-cols-2 gap-2 mb-4">
                        @foreach($package->items as $item)
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-2 text-center">
                            <div class="w-8 h-8 mx-auto mb-2 item-icon">
                                @if(str_contains(strtolower($item['name']), 'momo'))
                                <!-- Food Bowl Icon for Momos -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'side'))
                                <!-- Grid Icon for Sides -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 3v8h8V3H3zm6 6H5V5h4v4zm-6 4v8h8v-8H3zm6 6H5v-4h4v4zm4-16v8h8V3h-8zm6 6h-4V5h4v4zm-6 4v8h8v-8h-8zm6 6h-4v-4h4v4z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'drink') || str_contains(strtolower($item['name']), 'beverage'))
                                <!-- Cup Icon for Drinks -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'sauce'))
                                <!-- Pepper Icon for Sauces -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'delivery'))
                                <!-- Truck Icon for Delivery -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'custom') || str_contains(strtolower($item['name']), 'choice'))
                                <!-- Slider Icon for Customization -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'rating') || str_contains(strtolower($item['name']), 'star'))
                                <!-- Star Icon for Ratings -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'time') || str_contains(strtolower($item['name']), 'clock'))
                                <!-- Clock Icon for Time-sensitive -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'bulk') || str_contains(strtolower($item['name']), 'package'))
                                <!-- Package Icon for Bulk Orders -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'kids') || str_contains(strtolower($item['name']), 'child'))
                                <!-- Smile Icon for Kids Meals -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                @else
                                <!-- Default - Food Bowl Icon -->
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                                @endif
                            </div>
                            <div class="text-xs font-medium text-gray-700 mb-1">{{ $item['name'] }}</div>
                            <div class="text-xs font-bold text-amk-brown-1">{{ $item['quantity'] ?? 1 }} pcs</div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Offer Card - Polished Conversion Focus -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-6 shadow-xl">
                        <div class="relative">
                            <!-- Best Seller Badge -->
                            <div class="absolute top-0 right-0 bg-amk-brown-1 text-white text-xs px-2 py-1 rounded-bl-lg font-bold">
                                🔥 Best Seller
                            </div>
                            
                            <!-- Deal Title -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg sm:text-xl font-bold text-amk-brown-1">🎉 {{ $package->deal_title ?? 'Package Deal' }}</h3>
                            </div>
                            
                            <!-- Value Proposition -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-green-600">✅</span>
                                    <span>{{ $package->feeds_people ?? 'Feeds 8–10 people' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-blue-600">💰</span>
                                    <span>{{ $package->savings_description ?? 'Save Rs. 250+ vs buying individually' }}</span>
                                </div>
                            </div>
                            
                            <!-- Price Comparison -->
                            <div class="space-y-2 mb-4">
                                @if($package->bulk_price && $package->total_price > $package->bulk_price)
                                <div class="text-xs text-gray-500">
                                    <span class="line-through">Original Price: Rs. {{ number_format($package->total_price, 2) }}</span>
                                </div>
                                @endif
                                <div class="text-2xl sm:text-3xl font-bold text-amk-brown-1 tracking-wide">
                                    Rs. {{ number_format($package->bulk_price ?? $package->total_price, 2) }}
                                </div>
                            </div>
                            
                            <!-- Urgency Hook -->
                            <div class="text-xs text-gray-600 mb-4">
                                🕒 {{ $package->delivery_note ?? 'Order before 2PM for same-day delivery' }}
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <button @click="selectPackage('{{ $package->package_key }}')" class="w-full bg-amk-brown-1 text-white py-3 px-4 rounded-lg font-bold text-lg shadow-md hover:bg-amk-brown-2 transition-all duration-300">
                                    🛒 Order the Party Pack Now
                                </button>
                                <button @click="customizePackage('{{ $package->package_key }}')" class="w-full bg-white text-gray-600 border border-gray-300 py-2 px-4 rounded-lg text-sm hover:bg-gray-50 transition-all duration-300">
                                    + Customize this pack
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            </div>

            <!-- FROZEN PACKAGES -->
            <div x-show="orderType === 'frozen'" x-transition>
                <div class="text-center mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-amk-brown-1 mb-1">❄️ Frozen & Ready Packages</h2>
                    <p class="text-sm text-gray-600">Perfect for stocking up your freezer</p>
                </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4 mobile-grid">
                @foreach($packages['frozen'] as $index => $package)
                <div class="party-pack-card rounded-2xl shadow-xl p-3 sm:p-5 hover:shadow-2xl transition-all duration-300" 
                     style="--bg-image: url('{{ $package->image ? '/storage/' . $package->image : '/storage/products/foods/default-momo.jpg' }}');">
                    <!-- Package Header -->
                    <div class="text-center mb-3 sm:mb-4">
                        <div class="w-16 h-16 mx-auto mb-3 package-icon shadow-lg">
                            @if(str_contains(strtolower($package->name), 'family'))
                            <!-- Family Feast - Users Group Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A1.5 1.5 0 0 0 18.54 8H17c-.8 0-1.54.37-2.01 1l-3.99 5.33V22h8zM12.5 11.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5S11 9.17 11 10s.67 1.5 1.5 1.5zM5.5 6c1.11 0 2-.89 2-2s-.89-2-2-2-2 .89-2 2 .89 2 2 2zm2 16v-7H9V9c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v6h1.5v7h4z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'office'))
                            <!-- Office Saver - Briefcase Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'party'))
                            <!-- Party Pack - Sparkles Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'couple'))
                            <!-- Couple Combo - Heart Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'kids'))
                            <!-- Kids Special - Ball Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            @elseif(str_contains(strtolower($package->name), 'event') || str_contains(strtolower($package->name), 'bulk'))
                            <!-- Event Bulk Box - Calendar Clock Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                            </svg>
                            @else
                            <!-- Default - Food Bowl Icon -->
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                            </svg>
                            @endif
                        </div>
                        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-amk-brown-1 mb-1">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-xs">{{ $package->description }}</p>
                    </div>
                    
                    <!-- Visual Package Breakdown -->
                    <div class="item-breakdown grid grid-cols-2 gap-2 mb-4">
                        @foreach($package->items as $item)
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-2 text-center">
                            <div class="w-8 h-8 mx-auto mb-2 item-icon">
                                @if(str_contains(strtolower($item['name']), 'momo'))
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'side'))
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'sauce'))
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                @elseif(str_contains(strtolower($item['name']), 'delivery'))
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                @endif
                            </div>
                            <div class="text-xs font-medium text-gray-700 mb-1">{{ $item['name'] }}</div>
                            <div class="text-xs font-bold text-amk-brown-1">{{ $item['quantity'] ?? 1 }} pcs</div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Offer Card - Polished Conversion Focus -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-6 shadow-xl">
                        <div class="relative">
                            <!-- Best Seller Badge -->
                            <div class="absolute top-0 right-0 bg-amk-brown-1 text-white text-xs px-2 py-1 rounded-bl-lg font-bold">
                                🔥 Best Seller
                            </div>
                            
                            <!-- Deal Title -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg sm:text-xl font-bold text-amk-brown-1">🎉 {{ $package->deal_title ?? 'Package Deal' }}</h3>
                            </div>
                            
                            <!-- Value Proposition -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-green-600">✅</span>
                                    <span>{{ $package->feeds_people ?? 'Feeds 8–10 people' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-blue-600">💰</span>
                                    <span>{{ $package->savings_description ?? 'Save Rs. 250+ vs buying individually' }}</span>
                                </div>
                            </div>
                            
                            <!-- Price Comparison -->
                            <div class="space-y-2 mb-4">
                                @if($package->bulk_price && $package->total_price > $package->bulk_price)
                                <div class="text-xs text-gray-500">
                                    <span class="line-through">Original Price: Rs. {{ number_format($package->total_price, 2) }}</span>
                                </div>
                                @endif
                                <div class="text-2xl sm:text-3xl font-bold text-amk-brown-1 tracking-wide">
                                    Rs. {{ number_format($package->bulk_price ?? $package->total_price, 2) }}
                                </div>
                            </div>
                            
                            <!-- Urgency Hook -->
                            <div class="text-xs text-gray-600 mb-4">
                                🕒 {{ $package->delivery_note ?? 'Order before 2PM for same-day delivery' }}
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <button @click="selectPackage('{{ $package->package_key }}')" class="w-full bg-amk-brown-1 text-white py-3 px-4 rounded-lg font-bold text-lg shadow-md hover:bg-amk-brown-2 transition-all duration-300">
                                    🛒 Order the Party Pack Now
                                </button>
                                <button @click="customizePackage('{{ $package->package_key }}')" class="w-full bg-white text-gray-600 border border-gray-300 py-2 px-4 rounded-lg text-sm hover:bg-gray-50 transition-all duration-300">
                                    + Customize this pack
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            </div>
            </div>
        </div>


        <!-- CUSTOM BUILDER SECTION -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-amk-brown-1 mb-2">✍️ Build Your Own Custom Order</h2>
                <p class="text-gray-600">Have something specific in mind? Create your perfect order!</p>
            </div>
            
            <div x-data="customBulkBuilder()" class="space-y-6">
                <!-- Order Type Selection -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Default Order Type</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="orderType = 'cooked'" 
                                :class="{'border-blue-500 bg-blue-50': orderType === 'cooked', 'border-gray-300 bg-white': orderType !== 'cooked'}"
                                class="border-2 rounded-lg p-4 text-left hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🔥</span>
                                <div>
                                    <div class="font-semibold text-gray-900">Hot & Ready</div>
                                    <p class="text-sm text-gray-600">Cooked and ready to eat</p>
                                </div>
                            </div>
                        </button>
                        <button @click="orderType = 'frozen'" 
                                :class="{'border-blue-500 bg-blue-50': orderType === 'frozen', 'border-gray-300 bg-white': orderType !== 'frozen'}"
                                class="border-2 rounded-lg p-4 text-left hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">❄️</span>
                                <div>
                                    <div class="font-semibold text-gray-900">Frozen</div>
                                    <p class="text-sm text-gray-600">Ready for your freezer</p>
                                </div>
                            </div>
                        </button>
                    </div>
                    
                    <!-- Info about per-item selection -->
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-blue-800 font-medium">💡 Mix & Match Available!</p>
                                <p class="text-xs text-blue-700 mt-1">You can choose different preparation types (hot/frozen) for each individual item after adding them to your order.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Date/Time -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">When do you need it?</label>
                        <input type="datetime-local" x-model="deliveryDateTime" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <!-- Menu Selection -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Select Items from Menu</h3>
                        <button type="button" @click="showProductSelector = true" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Browse Menu
                        </button>
                    </div>
                    
                    <!-- Selected Items -->
                    <div class="space-y-4" x-show="items.length > 0">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <!-- Mobile Layout -->
                                <div class="block md:hidden space-y-3">
                                    <!-- Product Info Row -->
                                    <div class="flex gap-3 items-center">
                                        <!-- Product Image -->
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                            <template x-if="item.image">
                                                <img :src="'/storage/' + item.image" 
                                                     :alt="item.name"
                                                     class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.image">
                                                <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Product Info -->
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900" x-text="item.name"></div>
                                            <div class="text-sm text-gray-600" x-text="'Category: ' + item.category"></div>
                                        </div>
                                        
                                        <!-- Remove Button -->
                                        <button type="button" @click="removeItem(index)" 
                                                class="bg-red-600 text-white p-2 rounded-md hover:bg-red-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Preparation Type Row -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- Preparation Type -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Preparation</label>
                                            <select x-model="item.preparationType" 
                                                    class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="cooked">🔥 Hot & Ready</option>
                                                <option value="frozen">❄️ Frozen</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Quantity -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                                            <input type="number" x-model.number="item.quantity" min="1" required
                                                   class="w-full px-2 py-2 border border-gray-300 rounded text-center text-sm">
                                        </div>
                                    </div>
                                    
                                    <!-- Prices Row -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- Regular Price -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Regular (Rs.)</label>
                                            <input type="number" x-model.number="item.regularPrice" step="0.01" readonly
                                                   class="w-full px-2 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 text-sm">
                                        </div>
                                        
                                        <!-- Bulk Price -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Bulk (Rs.)</label>
                                            <input type="number" x-model.number="item.bulkPrice" step="0.01" readonly
                                                   class="w-full px-2 py-2 border border-green-300 rounded-md bg-green-50 text-green-700 font-semibold text-sm">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Desktop Layout -->
                                <div class="hidden md:flex gap-4 items-center">
                                    <!-- Product Image -->
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                        <template x-if="item.image">
                                            <img :src="'/storage/' + item.image" 
                                                 :alt="item.name"
                                                 class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Product Info -->
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900" x-text="item.name"></div>
                                        <div class="text-sm text-gray-600" x-text="'Category: ' + item.category"></div>
                                    </div>
                                    
                                    <!-- Preparation Type -->
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Preparation</label>
                                        <select x-model="item.preparationType" 
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="cooked">🔥 Hot & Ready</option>
                                            <option value="frozen">❄️ Frozen</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Quantity -->
                                    <div class="w-24">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input type="number" x-model.number="item.quantity" min="1" required
                                               class="w-full px-2 py-1 border border-gray-300 rounded text-center">
                                    </div>
                                    
                                    <!-- Regular Price (Read-only) -->
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Regular Price (Rs.)</label>
                                        <input type="number" x-model.number="item.regularPrice" step="0.01" readonly
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                                    </div>
                                    
                                    <!-- Bulk Price (Read-only) -->
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulk Price (Rs.)</label>
                                        <input type="number" x-model.number="item.bulkPrice" step="0.01" readonly
                                               class="w-full px-3 py-2 border border-green-300 rounded-md bg-green-50 text-green-700 font-semibold">
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <button type="button" @click="removeItem(index)" 
                                            class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Empty state -->
                    <div x-show="items.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No items added yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Select items from our menu to create your custom order.</p>
                        <div class="mt-6">
                            <button type="button" @click="showProductSelector = true" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Browse Menu
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Selector Modal -->
                <div x-show="showProductSelector" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 z-50" @click="showProductSelector = false">
                    <div class="flex items-center justify-center min-h-screen p-4" @click.stop>
                        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Select Items from Menu</h3>
                                <button @click="showProductSelector = false" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Category Tabs -->
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                                    <template x-for="category in categories" :key="category">
                                        <button @click="selectedCategory = category" 
                                                :class="selectedCategory === category ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                            <span x-text="category"></span>
                                        </button>
                                    </template>
                                </nav>
                            </div>
                            
                            <!-- Subcategory Tabs (for Food and Drinks) -->
                            <div x-show="selectedCategory === 'Food' || selectedCategory === 'Drinks'" class="border-b border-gray-200 bg-gray-50">
                                <nav class="flex space-x-6 px-6" aria-label="Subcategory Tabs">
                                    <template x-for="subcategory in getSubcategories(selectedCategory)" :key="subcategory">
                                        <button @click="selectedSubcategory = subcategory" 
                                                :class="selectedSubcategory === subcategory ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                                            <span x-text="subcategory"></span>
                                        </button>
                                    </template>
                                </nav>
                            </div>
                            
                            <!-- Products Grid -->
                            <div class="p-6 overflow-y-auto max-h-96">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template x-for="product in getProductsByCategory(selectedCategory)" :key="product.id">
                                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg cursor-pointer transition-all duration-200" 
                                             @click="addProductToOrder(product)">
                                            <!-- Product Image -->
                                            <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                                <template x-if="product.image">
                                                    <img :src="'/storage/' + product.image" 
                                                         :alt="product.name"
                                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                                </template>
                                                <template x-if="!product.image">
                                                    <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <!-- Product Info -->
                                            <div class="p-3">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <h4 class="font-medium text-gray-900 text-sm" x-text="product.name"></h4>
                                                        <p class="text-sm font-semibold text-blue-600 mt-1" x-text="'Rs. ' + product.price"></p>
                                                    </div>
                                                    <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors">
                                                        Add
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h4 class="text-lg font-medium text-blue-900 mb-3">Order Summary</h4>
                    <div class="space-y-4">
                        <!-- Total Items -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-blue-700">Total Items:</span>
                            <span class="text-lg font-bold text-blue-900" x-text="items.length + ' items'"></span>
                        </div>
                        
                        <!-- Preparation Type Breakdown -->
                        <div x-show="items.length > 0" class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-700">🔥 Hot & Ready:</span>
                                <span class="text-sm font-bold text-orange-600" x-text="getItemsByPreparationType('cooked').length + ' items'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-700">❄️ Frozen:</span>
                                <span class="text-sm font-bold text-blue-600" x-text="getItemsByPreparationType('frozen').length + ' items'"></span>
                            </div>
                        </div>
                        
                        <!-- Original Total -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Original Total:</span>
                            <span class="text-lg font-semibold text-gray-500 line-through" x-text="'Rs. ' + originalTotal.toFixed(2)"></span>
                        </div>
                        
                        <!-- Total Savings -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-green-600">Total Savings:</span>
                            <span class="text-lg font-bold text-green-600" x-text="'Rs. ' + totalSavings.toFixed(2)"></span>
                        </div>
                        
                        <!-- Final Price -->
                        <div class="border-t border-blue-200 pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-blue-900">Final Price:</span>
                                <span class="text-2xl font-bold text-blue-900" x-text="'Rs. ' + totalPrice.toFixed(2)"></span>
                            </div>
                        </div>
                        
                        <!-- Savings Percentage -->
                        <div class="bg-green-100 rounded-lg p-3 border border-green-200">
                            <div class="text-center">
                                <span class="text-sm font-medium text-green-700">You're saving </span>
                                <span class="text-lg font-bold text-green-600" x-text="savingsPercentage.toFixed(1) + '%'"></span>
                                <span class="text-sm font-medium text-green-700"> with bulk discount!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivery Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Area</label>
                            <input type="text" x-model="deliveryArea" placeholder="Enter your delivery area" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                            <textarea x-model="specialNotes" placeholder="Any special instructions or notes..." 
                                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 h-20 resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <button @click="clearOrder()" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                        Clear Order
                    </button>
                    <button @click="addToCart()" :disabled="totalPrice === 0 || items.length === 0" 
                            :class="{'opacity-50 cursor-not-allowed': totalPrice === 0 || items.length === 0, 'hover:bg-blue-700': totalPrice > 0 && items.length > 0}" 
                            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        Add to Cart (Rs. <span x-text="totalPrice"></span>)
                    </button>
                </div>

                <!-- Success/Error Popup -->
                <div x-show="showPopup" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="opacity-100 scale-100" 
                     x-transition:leave-end="opacity-0 scale-95"
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                     @click="closePopup()">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all"
                         @click.stop>
                        
                        <!-- Success Popup -->
                        <div x-show="popupType === 'success'" class="p-6 text-center">
                            <!-- Success Icon -->
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            
                            <!-- Success Message -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-2" x-text="popupMessage"></h3>
                            
                            <!-- Order Details -->
                            <div x-show="popupDetails" class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Items:</span>
                                        <span class="font-medium" x-text="popupDetails.items + ' items'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total:</span>
                                        <span class="font-semibold text-green-600" x-text="'Rs. ' + popupDetails.total"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Delivery:</span>
                                        <span class="font-medium" x-text="popupDetails.delivery"></span>
                                    </div>
                                    <!-- Preparation Types -->
                                    <template x-if="popupDetails.isMixed">
                                        <div class="space-y-1">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">🔥 Hot & Ready:</span>
                                                <span class="font-medium text-orange-600" x-text="popupDetails.hotItems + ' items'"></span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">❄️ Frozen:</span>
                                                <span class="font-medium text-blue-600" x-text="popupDetails.frozenItems + ' items'"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!popupDetails.isMixed">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Type:</span>
                                            <span class="font-medium" x-text="popupDetails.hotItems > 0 ? '🔥 Hot & Ready' : '❄️ Frozen'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button @click="closePopup()" 
                                        class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                                    Continue Shopping
                                </button>
                                <button @click="viewCart()" 
                                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                    View Cart
                                </button>
                            </div>
                        </div>
                        
                        <!-- Error Popup -->
                        <div x-show="popupType === 'error'" class="p-6 text-center">
                            <!-- Error Icon -->
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            
                            <!-- Error Message -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-4" x-text="popupMessage"></h3>
                            
                            <!-- Action Button -->
                            <button @click="closePopup()" 
                                    class="w-full bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 transition-colors">
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WHY CHOOSE AMAKO BULK - COMPACT VERSION -->
        <div class="bg-gradient-to-br from-[#FDF7F2] to-white rounded-xl shadow-lg p-4 sm:p-6 border border-[#F5E6D3]">
            <div class="text-center mb-4">
                <h2 class="text-xl sm:text-2xl font-serif font-bold text-amk-brown-1 mb-2 tracking-wide">✨ Why Choose AmaKo Bulk?</h2>
                <p class="text-sm text-gray-600">When you're feeding teams, events, or families — trust a momo brand that delivers more than just food.</p>
            </div>
            
            <div class="space-y-3">
                <!-- Pillar 1: Hygiene & Freshness -->
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amk-brown-1 mb-1">✅ Uncompromising Hygiene & Freshness</h3>
                        <p class="text-xs text-gray-600">🧼 Real ingredients. Centralized prep. Daily QC. Zero compromise.</p>
                    </div>
                </div>

                <!-- Pillar 2: Purpose-Driven -->
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amk-brown-1 mb-1">🐶 Purpose-Driven: We Feed Dogs Too</h3>
                        <p class="text-xs text-gray-600">Part of every bulk order funds our Do One Good (DOG) mission. Eat well. Do good.</p>
                    </div>
                </div>

                <!-- Pillar 3: On-Time Guarantee -->
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amk-brown-1 mb-1">⏱️ On-Time or It's Free</h3>
                        <p class="text-xs text-gray-600">We respect your time — so we guarantee it. Delay? You don't pay.</p>
                    </div>
                </div>

                <!-- Pillar 4: Bulk Without BS -->
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amk-brown-1 mb-1">📦 Bulk Without the B.S.</h3>
                        <p class="text-xs text-gray-600">Flat pricing. Centralized packaging. No hidden charges. Big orders, simplified.</p>
                    </div>
                </div>

                <!-- Pillar 5: Chef-Crafted (Optional) -->
                <div class="flex items-start gap-3 p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amk-brown-1 mb-1">👨‍🍳 Chef-Crafted, Event-Ready</h3>
                        <p class="text-xs text-gray-600">AmaKo Bulk is cooked by trained pros, not your average kitchen team. Consistency. Quantity. Quality — scaled.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MOBILE FLOATING ACTION BUTTON -->
    <div class="fixed bottom-20 right-4 z-40 sm:hidden">
        <button @click="scrollToCustomBuilder()" class="bg-amk-brown-1 text-white p-4 rounded-full shadow-lg hover:bg-amk-brown-2 transition-all duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </button>
    </div>
</div>

<!-- Include Cart Modal -->
@include('components.cart-modal')

<!-- Alpine.js Script -->
<script>
// Initialize cartManager if not already available
if (typeof cartManager === 'undefined') {
    window.cartManager = new CartManager();
}

// Make bulk packages data available to JavaScript
window.bulkPackages = @json($packages);
window.bulkPackagesList = [
    @foreach($packages['cooked'] as $package)
    @json($package),
    @endforeach
    @foreach($packages['frozen'] as $package)
    @json($package),
    @endforeach
];

// Debug: Log the package data
console.log('Bulk packages data loaded:', window.bulkPackages);
console.log('Bulk packages list loaded:', window.bulkPackagesList);

function customBulkBuilder() {
    return {
        orderType: 'cooked',
        deliveryDateTime: '',
        items: [],
        showProductSelector: false,
        selectedCategory: 'Food',
        selectedSubcategory: 'Buff',
        categories: ['Food', 'Drinks', 'Desserts', 'Sides'],
        products: @json($products ?? []),
        bulkDiscountPercentage: {{ $bulkDiscountPercentage ?? 15 }},
        deliveryArea: '',
        specialNotes: '',
        showPopup: false,
        popupType: 'success', // 'success' or 'error'
        popupMessage: '',
        popupDetails: null,
        
        get totalPrice() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.bulkPrice) * item.quantity || 0), 0);
        },
        
        get originalTotal() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.regularPrice) * item.quantity || 0), 0);
        },
        
        get totalSavings() {
            return this.originalTotal - this.totalPrice;
        },
        
        get savingsPercentage() {
            if (this.originalTotal === 0) return 0;
            return (this.totalSavings / this.originalTotal) * 100;
        },
        
        getItemsByPreparationType(type) {
            return this.items.filter(item => item.preparationType === type);
        },
        
        getSubcategories(category) {
            if (category === 'Food') {
                return ['Buff', 'Chicken', 'Veg', 'Others'];
            } else if (category === 'Drinks') {
                return ['Hot', 'Cold'];
            }
            return [];
        },
        
        getProductsByCategory(category) {
            if (category === 'Food') {
                if (this.selectedSubcategory === 'Buff') {
                    return this.products.filter(p => p.category === 'buff');
                } else if (this.selectedSubcategory === 'Chicken') {
                    return this.products.filter(p => p.category === 'chicken');
                } else if (this.selectedSubcategory === 'Veg') {
                    return this.products.filter(p => p.category === 'veg');
                } else if (this.selectedSubcategory === 'Others') {
                    return this.products.filter(p => ['main', 'Momo'].includes(p.category));
                }
                return [];
            } else if (category === 'Drinks') {
                if (this.selectedSubcategory === 'Hot') {
                    return this.products.filter(p => p.category === 'hot');
                } else if (this.selectedSubcategory === 'Cold') {
                    return this.products.filter(p => ['cold', 'boba'].includes(p.category));
                }
                return [];
            } else if (category === 'Desserts') {
                return this.products.filter(p => p.category === 'desserts');
            } else if (category === 'Sides') {
                return this.products.filter(p => p.category === 'side');
            }
            return [];
        },
        
        addProductToOrder(product) {
            // Check if product already exists with same preparation type
            const existingItem = this.items.find(item => item.name === product.name && item.preparationType === this.orderType);
            const regularPrice = parseFloat(product.price);
            const discountAmount = (regularPrice * this.bulkDiscountPercentage) / 100;
            const bulkPrice = regularPrice - discountAmount;
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                this.items.push({
                    name: product.name,
                    category: product.category,
                    quantity: 1,
                    regularPrice: regularPrice,
                    bulkPrice: bulkPrice,
                    image: product.image,
                    preparationType: this.orderType
                });
            }
            this.showProductSelector = false;
        },
        
        removeItem(index) {
            this.items.splice(index, 1);
        },
        
        clearOrder() {
            this.items = [];
            this.deliveryArea = '';
            this.specialNotes = '';
            this.deliveryDateTime = '';
        },
        
        addToCart() {
            if (this.totalPrice === 0 || this.items.length === 0) return;
            
            // Validate required fields
            if (!this.deliveryArea.trim()) {
                this.showError('Please enter delivery area');
                return;
            }
            
            if (this.orderType === 'cooked' && !this.deliveryDateTime) {
                this.showError('Please select delivery date and time');
                return;
            }
            
            const orderData = {
                orderType: this.orderType,
                deliveryDateTime: this.deliveryDateTime,
                items: this.items,
                deliveryArea: this.deliveryArea,
                specialNotes: this.specialNotes,
                totalPrice: this.totalPrice,
                itemCount: this.items.length
            };
            
            console.log('Adding to cart:', orderData);
            
            // Show success popup
            this.showSuccessPopup();
            
            // Clear the form after successful addition
            this.clearOrder();
        },
        
        showSuccessPopup() {
            this.showPopup = true;
            this.popupType = 'success';
            this.popupMessage = 'Custom Order Added to Cart!';
            
            const hotItems = this.getItemsByPreparationType('cooked').length;
            const frozenItems = this.getItemsByPreparationType('frozen').length;
            
            this.popupDetails = {
                items: this.items.length,
                total: this.totalPrice,
                delivery: this.deliveryArea,
                hotItems: hotItems,
                frozenItems: frozenItems,
                isMixed: hotItems > 0 && frozenItems > 0
            };
        },
        
        showError(message) {
            this.showPopup = true;
            this.popupType = 'error';
            this.popupMessage = message;
        },
        
        closePopup() {
            this.showPopup = false;
        },
        
        viewCart() {
            this.closePopup();
            window.location.href = '{{ route("cart") }}';
        },
        
    }
}

function bulkOrder() {
    return {
        orderType: 'cooked',
        partySize: '8-10',
        selectedPackage: null,
        
        selectPackage(packageType) {
            this.selectedPackage = packageType;
            
            // Find the package data
            const package = this.getPackageByKey(packageType);
            if (!package) {
                alert('Package not found!');
                return;
            }
            
            // Add to cart using the existing cart system
            if (typeof cartManager !== 'undefined') {
                cartManager.addToCart(
                    `bulk-${package.id}`,
                    package.name,
                    package.bulk_price || package.total_price,
                    null, // no image for bulk packages
                    1
                );
                
                // Show success message
                this.showSuccessMessage(`🎉 ${package.name} added to cart!`);
            } else {
                // Fallback: redirect to cart with package data
                this.redirectToCart(package);
            }
        },
        
        customizePackage(packageType) {
            this.selectedPackage = packageType;
            
            // Find the package data
            const package = this.getPackageByKey(packageType);
            if (!package) {
                alert('Package not found!');
                return;
            }
            
            // Redirect to custom builder with package data
            this.redirectToCustomBuilder(package);
        },
        
        getPackageByKey(packageKey) {
            // Get package data from the server-rendered data
            const cookedPackages = @json($packages['cooked'] ?? []);
            const frozenPackages = @json($packages['frozen'] ?? []);
            
            return cookedPackages[packageKey] || frozenPackages[packageKey] || null;
        },
        
        redirectToCart(package) {
            // Create a form and submit it to add to cart
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("cart.add-to-cart") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add package data
            const productId = document.createElement('input');
            productId.type = 'hidden';
            productId.name = 'product_id';
            productId.value = `bulk-${package.id}`;
            form.appendChild(productId);
            
            const productName = document.createElement('input');
            productName.type = 'hidden';
            productName.name = 'product_name';
            productName.value = package.name;
            form.appendChild(productName);
            
            const price = document.createElement('input');
            price.type = 'hidden';
            price.name = 'price';
            price.value = package.total_price;
            form.appendChild(price);
            
            const quantity = document.createElement('input');
            quantity.type = 'hidden';
            quantity.name = 'quantity';
            quantity.value = '1';
            form.appendChild(quantity);
            
            document.body.appendChild(form);
            form.submit();
        },
        
        redirectToCustomBuilder(package) {
            // Redirect to custom builder with package data
            const params = new URLSearchParams({
                'package_id': package.id,
                'package_key': package.package_key,
                'package_name': package.name,
                'package_type': package.type,
                'package_price': package.total_price
            });
            
            window.location.href = `{{ url('/bulk/custom-builder') }}?${params.toString()}`;
        },
        
        showSuccessMessage(message) {
            // Create and show a success toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full opacity-0';
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);
            
            // Animate out after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        },

        scrollToCustomBuilder() {
            const customBuilder = document.querySelector('.bg-white.rounded-2xl.shadow-xl');
            if (customBuilder) {
                customBuilder.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        },

        scrollToPackages() {
            const packages = document.querySelector('.px-1.space-y-6');
            if (packages) {
                packages.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    }
}
</script>

@endsection 