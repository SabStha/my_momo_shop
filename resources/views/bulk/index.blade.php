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

.party-pack-card .text-[#6E0D25] {
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.party-pack-card .text-[#800000] {
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
    background: linear-gradient(135deg, #6E0D25, #8B1A3A);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(110, 13, 37, 0.3);
}

.party-pack-card .item-icon {
    background: linear-gradient(135deg, #6E0D25, #8B1A3A);
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
    background: linear-gradient(135deg, #6E0D25 0%, #8B1A3A 100%);
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
    background: linear-gradient(90deg, #ef4444, #f97316);
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
    background: linear-gradient(135deg, #6E0D25 0%, #8B1A3A 100%);
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
    background: linear-gradient(135deg, #6E0D25, #8B1A3A);
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
    background: linear-gradient(135deg, #6E0D25 0%, #8B1A3A 100%);
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
    border: 2px solid #6E0D25;
    border-radius: 12px;
    padding: 14px 22px;
    font-weight: 600;
    font-size: 14px;
    color: #6E0D25;
    transition: all 0.3s ease;
}

.secondary-cta:hover {
    background: #6E0D25;
    color: white;
    transform: translateY(-1px);
}
</style>

<div x-data="bulkOrder()" class="bg-white min-h-screen pb-2 mobile-container">

    <!-- COMPACT HERO SECTION (500-550px total height) -->
        <div class="relative z-10 pt-2 sm:pt-4 pb-1 px-1">
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
                                <button @click="scrollToPackages()" class="bg-[#6E0D25] text-white py-2 px-4 rounded-xl font-bold text-sm shadow-lg hover:bg-[#8B1A3A] transition-all duration-300 transform hover:scale-105">
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
                                        <span class="font-semibold text-white">4.9/5 (120+ orders)</span>
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
                            :class="{ 'bg-[#6E0D25] text-white shadow-lg': orderType === 'cooked', 'text-[#6E0D25] hover:bg-gray-50': orderType !== 'cooked' }"
                            class="flex-1 px-3 py-2 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center gap-2 text-sm min-h-[40px]">
                        <span class="text-red-600">🔥</span>
                        <span class="font-semibold">Hot</span>
                    </button>
                    <button @click="orderType = 'frozen'" 
                            :class="{ 'bg-[#6E0D25] text-white shadow-lg': orderType === 'frozen', 'text-[#6E0D25] hover:bg-gray-50': orderType !== 'frozen' }"
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
        
        <!-- COOKED PACKAGES -->
        <div x-show="orderType === 'cooked'" x-transition>
            <div class="text-center mb-1">
                <h2 class="text-lg sm:text-xl font-bold text-[#6E0D25] mb-1">🔥 Hot & Ready Packages</h2>
                <p class="text-sm text-gray-600">Perfect for immediate consumption</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4 mobile-grid">
                @foreach($packages['cooked'] as $index => $package)
                <div class="party-pack-card rounded-2xl shadow-xl p-3 sm:p-5 hover:shadow-2xl transition-all duration-300" 
                     style="--bg-image: url('/storage/products/foods/{{ $index === 0 ? 'steamed-chicken-momos.jpg' : ($index === 1 ? 'spicy-chicken-momos.jpg' : 'veg-momos.jpg') }}');">
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
                        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-[#6E0D25] mb-1">{{ $package->name }}</h3>
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
                            <div class="text-xs font-bold text-[#6E0D25]">Rs. {{ $item['price'] }}</div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Offer Card - Polished Conversion Focus -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-6 shadow-xl">
                        <div class="relative">
                            <!-- Best Seller Badge -->
                            <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded-bl-lg font-bold">
                                🔥 Best Seller
                            </div>
                            
                            <!-- Deal Title -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg sm:text-xl font-bold text-[#6E0D25]">🎉 Party Pack Deal</h3>
                            </div>
                            
                            <!-- Value Proposition -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-green-600">✅</span>
                                    <span>Feeds 8–10 people</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-blue-600">💰</span>
                                    <span>Save Rs. 250+ vs buying individually</span>
                                </div>
                            </div>
                            
                            <!-- Price Comparison -->
                            <div class="space-y-2 mb-4">
                                <div class="text-xs text-gray-500">
                                    <span class="line-through">Original Price: Rs. {{ $package->total_price + 250 }}</span>
                                </div>
                                <div class="text-2xl sm:text-3xl font-bold text-[#800000] tracking-wide">
                                    Rs. {{ $package->total_price }}.00
                                </div>
                            </div>
                            
                            <!-- Urgency Hook -->
                            <div class="text-xs text-gray-600 mb-4">
                                🕒 Order before 2PM for same-day delivery
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <button @click="selectPackage('{{ $package->package_key }}')" class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-bold text-lg shadow-md hover:bg-[#8B1A3A] transition-all duration-300">
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
            <div class="text-center mb-1">
                <h2 class="text-lg sm:text-xl font-bold text-[#6E0D25] mb-1">❄️ Frozen & Ready Packages</h2>
                <p class="text-sm text-gray-600">Perfect for stocking up your freezer</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4 mobile-grid">
                @foreach($packages['frozen'] as $index => $package)
                <div class="party-pack-card rounded-2xl shadow-xl p-3 sm:p-5 hover:shadow-2xl transition-all duration-300" 
                     style="--bg-image: url('/storage/products/foods/{{ $index === 0 ? 'fried-chicken-momos.jpg' : ($index === 1 ? 'classic-pork-momos.jpg' : 'cheese-corn-momos.jpg') }}');">
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
                        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-[#6E0D25] mb-1">{{ $package->name }}</h3>
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
                            <div class="text-xs font-bold text-[#6E0D25] {{ $item['price'] < 0 ? 'text-green-600' : '' }}">Rs. {{ $item['price'] }}</div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Offer Card - Polished Conversion Focus -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-6 shadow-xl">
                        <div class="relative">
                            <!-- Best Seller Badge -->
                            <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded-bl-lg font-bold">
                                🔥 Best Seller
                            </div>
                            
                            <!-- Deal Title -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg sm:text-xl font-bold text-[#6E0D25]">🎉 Party Pack Deal</h3>
                            </div>
                            
                            <!-- Value Proposition -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-green-600">✅</span>
                                    <span>Feeds 8–10 people</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-blue-600">💰</span>
                                    <span>Save Rs. 250+ vs buying individually</span>
                                </div>
                            </div>
                            
                            <!-- Price Comparison -->
                            <div class="space-y-2 mb-4">
                                <div class="text-xs text-gray-500">
                                    <span class="line-through">Original Price: Rs. {{ $package->total_price + 250 }}</span>
                                </div>
                                <div class="text-2xl sm:text-3xl font-bold text-[#800000] tracking-wide">
                                    Rs. {{ $package->total_price }}.00
                                </div>
                            </div>
                            
                            <!-- Urgency Hook -->
                            <div class="text-xs text-gray-600 mb-4">
                                🕒 Order before 2PM for same-day delivery
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <button @click="selectPackage('{{ $package->package_key }}')" class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-bold text-lg shadow-md hover:bg-[#8B1A3A] transition-all duration-300">
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

        <!-- PARTY EXTRAS SECTION -->
        <div class="bg-gradient-to-br from-orange-50 via-yellow-50 to-red-50 rounded-2xl shadow-xl p-6 border border-orange-200 mb-6">
            <div class="text-center mb-6">
                <div class="flex items-center justify-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-red-500 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-[#6E0D25]">🎉 Party Extras</h2>
                </div>
                <p class="text-gray-600 font-medium">Make your celebration unforgettable with these premium add-ons</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <!-- Soft Drinks Card -->
                <div class="group relative bg-[#F4F8FF] rounded-lg p-3 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border border-[#E8F0FF]">
                    <div class="absolute top-1 right-1 bg-[#325D99] text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                        POPULAR
                    </div>
                    <div class="w-10 h-10 mx-auto mb-2 bg-gradient-to-br from-[#325D99] to-[#4A7BC7] rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-[#2B2B2B] mb-1 text-sm">Refreshing Drinks</h3>
                    <p class="text-xs text-gray-600 mb-2">6-pack variety</p>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-[#2B2B2B]">Rs. 299</span>
                        <button class="bg-[#8B2E3E] text-white px-2 py-0.5 rounded text-xs hover:bg-[#6E0D25] transition-colors font-medium">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Dessert Card -->
                <div class="group relative bg-[#FFF9F0] rounded-lg p-3 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border border-[#F5E6D3]">
                    <div class="absolute top-1 right-1 bg-[#C75D7B] text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                        SWEET
                    </div>
                    <div class="w-10 h-10 mx-auto mb-2 bg-gradient-to-br from-[#C75D7B] to-[#D47A94] rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-[#2B2B2B] mb-1 text-sm">Dessert Delights</h3>
                    <p class="text-xs text-gray-600 mb-2">Assorted sweets</p>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-[#2B2B2B]">Rs. 199</span>
                        <button class="bg-[#8B2E3E] text-white px-2 py-0.5 rounded text-xs hover:bg-[#6E0D25] transition-colors font-medium">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Extra Sides Card -->
                <div class="group relative bg-[#F8FBF8] rounded-lg p-3 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border border-[#E8F5E8]">
                    <div class="absolute top-1 right-1 bg-[#568259] text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                        FRESH
                    </div>
                    <div class="w-10 h-10 mx-auto mb-2 bg-gradient-to-br from-[#568259] to-[#6B9A6B] rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 3v8h8V3H3zm6 6H5V5h4v4zm-6 4v8h8v-8H3zm6 6H5v-4h4v4zm4-16v8h8V3h-8zm6 6h-4V5h4v4zm-6 4v8h8v-8h-8zm6 6h-4v-4h4v4z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-[#2B2B2B] mb-1 text-sm">Extra Sides</h3>
                    <p class="text-xs text-gray-600 mb-2">2 additional sides</p>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-[#2B2B2B]">Rs. 150</span>
                        <button class="bg-[#8B2E3E] text-white px-2 py-0.5 rounded text-xs hover:bg-[#6E0D25] transition-colors font-medium">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Party Decor Card -->
                <div class="group relative bg-[#F9F8FF] rounded-lg p-3 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105 border border-[#F0EEFF]">
                    <div class="absolute top-1 right-1 bg-[#7F6CA9] text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                        FUN
                    </div>
                    <div class="w-10 h-10 mx-auto mb-2 bg-gradient-to-br from-[#7F6CA9] to-[#8F7CB9] rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-[#2B2B2B] mb-1 text-sm">Party Decor</h3>
                    <p class="text-xs text-gray-600 mb-2">Balloons & banners</p>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-[#2B2B2B]">Rs. 99</span>
                        <button class="bg-[#8B2E3E] text-white px-2 py-0.5 rounded text-xs hover:bg-[#6E0D25] transition-colors font-medium">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUSTOM BUILDER SECTION -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-[#6E0D25] mb-2">✍️ Build Your Own Custom Order</h2>
                <p class="text-gray-600">Have something specific in mind? Create your perfect order!</p>
            </div>
            
            @include('bulk.custom-builder')
        </div>

        <!-- WHY CHOOSE AMAKO BULK - COMPACT VERSION -->
        <div class="bg-gradient-to-br from-[#FDF7F2] to-white rounded-xl shadow-lg p-4 sm:p-6 border border-[#F5E6D3]">
            <div class="text-center mb-4">
                <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#6E0D25] mb-2 tracking-wide">✨ Why Choose AmaKo Bulk?</h2>
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
                        <h3 class="text-sm font-bold text-[#6E0D25] mb-1">✅ Uncompromising Hygiene & Freshness</h3>
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
                        <h3 class="text-sm font-bold text-[#6E0D25] mb-1">🐶 Purpose-Driven: We Feed Dogs Too</h3>
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
                        <h3 class="text-sm font-bold text-[#6E0D25] mb-1">⏱️ On-Time or It's Free</h3>
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
                        <h3 class="text-sm font-bold text-[#6E0D25] mb-1">📦 Bulk Without the B.S.</h3>
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
                        <h3 class="text-sm font-bold text-[#6E0D25] mb-1">👨‍🍳 Chef-Crafted, Event-Ready</h3>
                        <p class="text-xs text-gray-600">AmaKo Bulk is cooked by trained pros, not your average kitchen team. Consistency. Quantity. Quality — scaled.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MOBILE FLOATING ACTION BUTTON -->
    <div class="fixed bottom-20 right-4 z-40 sm:hidden">
        <button @click="scrollToCustomBuilder()" class="bg-[#6E0D25] text-white p-4 rounded-full shadow-lg hover:bg-[#8B1A3A] transition-all duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </button>
    </div>
</div>

<!-- Alpine.js Script -->
<script>
function bulkOrder() {
    return {
        orderType: 'cooked',
        partySize: '8-10',
        selectedPackage: null,
        
        selectPackage(packageType) {
            this.selectedPackage = packageType;
            // Redirect to checkout or show order form
            console.log('Selected package:', packageType);
        },
        
        customizePackage(packageType) {
            this.selectedPackage = packageType;
            // Load custom builder with pre-filled values
            console.log('Customize package:', packageType);
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