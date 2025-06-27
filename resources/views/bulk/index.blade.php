@extends('layouts.app')

@section('content')
<style>
/* Mobile-specific improvements */
@media (max-width: 640px) {
    .mobile-container {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
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
</style>

<div x-data="bulkOrder()" class="bg-[url('/images/bd2.png')] bg-repeat bg-center min-h-screen pb-20 mobile-container">

    <!-- HERO SECTION -->
    <div class="relative z-10 pt-8 sm:pt-5 pb-2 sm:pb-3 px-4">
        <div class="text-center max-w-4xl mx-auto mobile-text">
            <h2 class="text-xs font-bold text-[#6E0D25] mb-1 drop-shadow-lg bg-white/90 backdrop-blur-sm rounded-lg px-2 py-1 inline-block whitespace-nowrap">
                🥟 Order AmaKo Momos in Bulk
            </h2>
            <div class="mb-1">
                <h3 class="text-xs sm:text-xs text-[#6E0D25] font-semibold drop-shadow-md bg-white/80 backdrop-blur-sm rounded-lg px-1 py-1 inline-block">
                    Perfect for Parties, Offices, and Freezers!
                </h3>
            </div>
            <p class="text-xs sm:text-xs text-[#6E0D25] leading-relaxed mb-2 drop-shadow-md bg-white/70 backdrop-blur-sm rounded-lg px-2 py-1 max-w-2xl mx-auto">
                Choose a ready-made package or fully customize your own. Get them cooked hot or frozen for later.
            </p>
            
            <!-- ORDER TYPE TOGGLE -->
            <div class="flex justify-center mb-2">
                <div class="bg-white/90 backdrop-blur-sm rounded-lg p-1 flex w-full max-w-xs border-2 border-[#6E0D25]/20 shadow-lg">
                    <button @click="orderType = 'cooked'" 
                            :class="{ 'bg-[#6E0D25] text-white shadow-lg transform scale-105': orderType === 'cooked', 'text-[#6E0D25] hover:bg-[#6E0D25]/10': orderType !== 'cooked' }"
                            class="flex-1 px-1 py-1 rounded-md font-bold transition-all duration-300 flex items-center justify-center gap-1 text-xs min-h-[28px]">
                        <span class="text-sm">🔥</span>
                        <span class="font-semibold">Hot</span>
                    </button>
                    <button @click="orderType = 'frozen'" 
                            :class="{ 'bg-[#6E0D25] text-white shadow-lg transform scale-105': orderType === 'frozen', 'text-[#6E0D25] hover:bg-[#6E0D25]/10': orderType !== 'frozen' }"
                            class="flex-1 px-1 py-1 rounded-md font-bold transition-all duration-300 flex items-center justify-center gap-1 text-xs min-h-[28px]">
                        <span class="text-sm">❄️</span>
                        <span class="font-semibold">Cold</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PACKAGE PREVIEWS -->
    <div class="px-4 pb-20 space-y-8 sm:space-y-16 max-w-6xl mx-auto">
        
        <!-- COOKED PACKAGES -->
        <div x-show="orderType === 'cooked'" x-transition>
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-[#6E0D25] mb-2">🔥 Hot & Ready Packages</h2>
                <p class="text-sm sm:text-base text-gray-600">Perfect for immediate consumption</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mobile-grid">
                @foreach($packages['cooked'] as $package)
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 hover:shadow-xl transition-shadow">
                    <div class="text-center mb-4">
                        <div class="text-3xl sm:text-4xl mb-2">{{ $package->emoji }}</div>
                        <h3 class="text-lg sm:text-xl font-bold text-[#6E0D25]">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-xs sm:text-sm">{{ $package->description }}</p>
                    </div>
                    <div class="space-y-2 mb-4">
                        @foreach($package->items as $item)
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="truncate flex-1 mr-2">{{ $item['name'] }}</span>
                            <span class="font-semibold flex-shrink-0">Rs. {{ $item['price'] }}</span>
                        </div>
                        @endforeach
                        <hr class="my-2">
                        <div class="flex justify-between font-bold text-base sm:text-lg">
                            <span>Total</span>
                            <span class="text-[#6E0D25]">Rs. {{ $package->total_price }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button @click="selectPackage('{{ $package->package_key }}')" class="w-full bg-[#6E0D25] text-white py-3 rounded-lg hover:bg-[#8B1A3A] transition text-sm sm:text-base min-h-[44px]">
                            Order Now
                        </button>
                        <button @click="customizePackage('{{ $package->package_key }}')" class="w-full border border-[#6E0D25] text-[#6E0D25] py-3 rounded-lg hover:bg-[#6E0D25] hover:text-white transition text-sm sm:text-base min-h-[44px]">
                            Customize Package
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- FROZEN PACKAGES -->
        <div x-show="orderType === 'frozen'" x-transition>
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-[#6E0D25] mb-2">❄️ Frozen & Ready Packages</h2>
                <p class="text-sm sm:text-base text-gray-600">Perfect for stocking up your freezer</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mobile-grid">
                @foreach($packages['frozen'] as $package)
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 hover:shadow-xl transition-shadow">
                    <div class="text-center mb-4">
                        <div class="text-3xl sm:text-4xl mb-2">{{ $package->emoji }}</div>
                        <h3 class="text-lg sm:text-xl font-bold text-[#6E0D25]">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-xs sm:text-sm">{{ $package->description }}</p>
                    </div>
                    <div class="space-y-2 mb-4">
                        @foreach($package->items as $item)
                        <div class="flex justify-between text-xs sm:text-sm">
                            <span class="truncate flex-1 mr-2">{{ $item['name'] }}</span>
                            <span class="font-semibold flex-shrink-0 {{ $item['price'] < 0 ? 'text-green-600' : '' }}">Rs. {{ $item['price'] }}</span>
                        </div>
                        @endforeach
                        <hr class="my-2">
                        <div class="flex justify-between font-bold text-base sm:text-lg">
                            <span>Total</span>
                            <span class="text-[#6E0D25]">Rs. {{ $package->total_price }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button @click="selectPackage('{{ $package->package_key }}')" class="w-full bg-[#6E0D25] text-white py-3 rounded-lg hover:bg-[#8B1A3A] transition text-sm sm:text-base min-h-[44px]">
                            Order Now
                        </button>
                        <button @click="customizePackage('{{ $package->package_key }}')" class="w-full border border-[#6E0D25] text-[#6E0D25] py-3 rounded-lg hover:bg-[#6E0D25] hover:text-white transition text-sm sm:text-base min-h-[44px]">
                            Customize Package
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- FULL CUSTOM BUILDER -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-8">
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-[#6E0D25] mb-2">✍️ Build Your Own Custom Order</h2>
                <p class="text-sm sm:text-base text-gray-600">Have something specific in mind? Create your perfect order!</p>
            </div>
            
            @include('bulk.custom-builder')
        </div>

        <!-- WHY CHOOSE AMAKO BULK -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-8">
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-[#6E0D25] mb-2">🎯 Why Choose AmaKo Bulk?</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mobile-grid">
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl mb-3 sm:mb-4">🧼</div>
                    <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-2">Hygiene & Quality</h3>
                    <p class="text-xs sm:text-sm text-gray-600">Real ingredients, consistent taste, highest hygiene standards</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl mb-3 sm:mb-4">🐾</div>
                    <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-2">Supporting Dogs</h3>
                    <p class="text-xs sm:text-sm text-gray-600">"Saving dogs, one momo at a time." Part of profits go to local shelters</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl mb-3 sm:mb-4">⏰</div>
                    <h3 class="text-base sm:text-lg font-bold text-[#6E0D25] mb-2">On-Time Guarantee</h3>
                    <p class="text-xs sm:text-sm text-gray-600">Delivery on-time guarantee or it's free</p>
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
            const customBuilder = document.querySelector('.bg-white.rounded-xl.shadow-lg');
            if (customBuilder) {
                customBuilder.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    }
}
</script>

@endsection 