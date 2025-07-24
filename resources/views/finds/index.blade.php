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
    
    .mobile-nav {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .mobile-nav::-webkit-scrollbar {
        display: none;
    }
}
</style>

<div x-data="findsData()" class="bg-[#F4E9E1] min-h-screen mobile-container">

    <!-- HEADER WITH CHARITY MESSAGE -->
    <div class="relative z-20 pt-4 sm:pt-[20px] pb-3 sm:pb-4 px-4">
        <div class="w-full sm:w-max mx-auto bg-white rounded-xl shadow px-4 sm:px-6 py-3 text-center mobile-text">
            <h1 class="text-base sm:text-lg md:text-xl font-bold text-[#6E0D25] mb-1">Ama's Finds</h1>
            <p class="text-xs sm:text-sm text-green-600 font-medium">🐕 All profits go to dog shelters! 🐕</p>
        </div>
    </div>

    <!-- SEPARATE MODEL SELECTION SECTION -->
    <div class="bg-white/95 backdrop-blur-sm border-b border-[#6E0D25]/20 py-2 sm:py-3">
        <div class="container mx-auto px-2 sm:px-4">
            <div class="text-center mb-1 sm:mb-2">
                <h3 class="text-xs sm:text-sm font-semibold text-[#6E0D25]">Select Model: <span class="text-[#6E0D25] font-bold" x-text="selectedModel.toUpperCase()"></span></h3>
            </div>
            <div class="flex justify-center gap-1 sm:gap-2 md:gap-4 overflow-x-auto">
                <button @click="changeModel('all')" 
                        :class="{ 'bg-[#6E0D25] text-white shadow-md scale-105': selectedModel === 'all', 'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedModel !== 'all' }" 
                        class="px-2 sm:px-3 md:px-4 py-1 sm:py-2 md:py-3 rounded-md font-medium text-xs sm:text-sm transition-all duration-200 flex-shrink-0 min-h-[32px] sm:min-h-[36px] md:min-h-[40px]">
                    ALL
                </button>
                <button @click="changeModel('classic')" 
                        :class="{ 'bg-[#6E0D25] text-white shadow-md scale-105': selectedModel === 'classic', 'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedModel !== 'classic' }" 
                        class="px-2 sm:px-3 md:px-4 py-1 sm:py-2 md:py-3 rounded-md font-medium text-xs sm:text-sm transition-all duration-200 flex-shrink-0 min-h-[32px] sm:min-h-[36px] md:min-h-[40px]">
                    CLASSIC
                </button>
                <button @click="changeModel('premium')" 
                        :class="{ 'bg-[#6E0D25] text-white shadow-md scale-105': selectedModel === 'premium', 'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedModel !== 'premium' }" 
                        class="px-2 sm:px-3 md:px-4 py-1 sm:py-2 md:py-3 rounded-md font-medium text-xs sm:text-sm transition-all duration-200 flex-shrink-0 min-h-[32px] sm:min-h-[36px] md:min-h-[40px]">
                    PREMIUM
                </button>
                <button @click="changeModel('limited')" 
                        :class="{ 'bg-[#6E0D25] text-white shadow-md scale-105': selectedModel === 'limited', 'bg-gray-100 text-gray-700 hover:bg-gray-200': selectedModel !== 'limited' }" 
                        class="px-2 sm:px-3 md:px-4 py-1 sm:py-2 md:py-3 rounded-md font-medium text-xs sm:text-sm transition-all duration-200 flex-shrink-0 min-h-[32px] sm:min-h-[36px] md:min-h-[40px]">
                    LIMITED
                </button>
            </div>
        </div>
    </div>

    <!-- MODEL SELECTION FEEDBACK -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-2 mx-4 mb-2 rounded">
        <p class="text-xs sm:text-sm text-blue-800">
            <strong>Selected Model:</strong> <span x-text="selectedModel.toUpperCase()"></span>
            <span x-show="loading" class="text-blue-600">(Loading...)</span>
            <span x-show="!loading" class="text-blue-600">(Showing filtered results)</span>
        </p>
    </div>

    <!-- LOADING INDICATOR -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#6E0D25] mx-auto"></div>
            <p class="text-sm text-gray-600 mt-2">Loading merchandise...</p>
        </div>
    </div>

    <!-- SECONDARY NAV BAR -->
    <div class="relative z-10 pb-4 sm:pb-6 px-4 pt-4">
        <div class="w-full sm:w-max mx-auto bg-white rounded-xl shadow px-3 sm:px-6 py-2 mobile-nav">
            <div class="flex gap-2 sm:gap-4 font-bold text-xs sm:text-sm md:text-base text-[#000] whitespace-nowrap min-w-max">
                <button @click="activeTab = 'tshirts'" :class="{ 'text-red-600': activeTab === 'tshirts' }" class="hover:text-red-600 transition px-2 sm:px-3 py-1 sm:py-2 min-h-[44px] flex items-center justify-center">
                    T-SHIRTS
                </button>
                <button @click="activeTab = 'accessories'" :class="{ 'text-red-600': activeTab === 'accessories' }" class="hover:text-red-600 transition px-2 sm:px-3 py-1 sm:py-2 min-h-[44px] flex items-center justify-center">
                    ACCESSORIES
                </button>
                <button @click="activeTab = 'toys'" :class="{ 'text-red-600': activeTab === 'toys' }" class="hover:text-red-600 transition px-2 sm:px-3 py-1 sm:py-2 min-h-[44px] flex items-center justify-center">
                    KIDS TOYS
                </button>
                <button @click="activeTab = 'limited'" :class="{ 'text-red-600': activeTab === 'limited' }" class="hover:text-red-600 transition px-2 sm:px-3 py-1 sm:py-2 min-h-[44px] flex items-center justify-center">
                    LIMITED OFFERS
                </button>
                <button @click="activeTab = 'bulk'" :class="{ 'text-red-600': activeTab === 'bulk' }" class="hover:text-red-600 transition px-2 sm:px-3 py-1 sm:py-2 min-h-[44px] flex items-center justify-center">
                    BULK PACKAGES
                </button>
            </div>
        </div>
    </div>

    <!-- TAB CONTENT AREA -->
    <div class="px-4 pb-20 space-y-8 sm:space-y-16">
        <!-- T-SHIRTS SECTION -->
        <div x-show="activeTab === 'tshirts'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in merchandise.tshirts" :key="item.id">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <div class="relative">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-48 object-cover">
                            <template x-if="item.badge">
                                <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold text-white" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2" x-text="item.name"></h3>
                            <p class="text-gray-600 text-sm mb-3" x-text="item.description"></p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-[#6E0D25]" x-text="item.formatted_price"></span>
                                <template x-if="item.purchasable">
                                    <button class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#5A0A1F] transition-colors duration-200">
                                        Add to Cart
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                    <span class="text-gray-500 text-sm">Display Only</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ACCESSORIES SECTION -->
        <div x-show="activeTab === 'accessories'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in merchandise.accessories" :key="item.id">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <div class="relative">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-48 object-cover">
                            <template x-if="item.badge">
                                <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold text-white" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2" x-text="item.name"></h3>
                            <p class="text-gray-600 text-sm mb-3" x-text="item.description"></p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-[#6E0D25]" x-text="item.formatted_price"></span>
                                <template x-if="item.purchasable">
                                    <button class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#5A0A1F] transition-colors duration-200">
                                        Add to Cart
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                    <span class="text-gray-500 text-sm">Display Only</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- TOYS SECTION -->
        <div x-show="activeTab === 'toys'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in merchandise.toys" :key="item.id">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <div class="relative">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-48 object-cover">
                            <template x-if="item.badge">
                                <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold text-white" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2" x-text="item.name"></h3>
                            <p class="text-gray-600 text-sm mb-3" x-text="item.description"></p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-[#6E0D25]" x-text="item.formatted_price"></span>
                                <template x-if="item.purchasable">
                                    <button class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#5A0A1F] transition-colors duration-200">
                                        Add to Cart
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                    <span class="text-gray-500 text-sm">Display Only</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- LIMITED EDITION SECTION -->
        <div x-show="activeTab === 'limited'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in merchandise.limited" :key="item.id">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <div class="relative">
                            <img :src="item.image_url" :alt="item.name" class="w-full h-48 object-cover">
                            <template x-if="item.badge">
                                <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold text-white" :style="'background-color: ' + item.badge_color">
                                    <span x-text="item.badge"></span>
                                </div>
                            </template>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2" x-text="item.name"></h3>
                            <p class="text-gray-600 text-sm mb-3" x-text="item.description"></p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-[#6E0D25]" x-text="item.formatted_price"></span>
                                <template x-if="item.purchasable">
                                    <button class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#5A0A1F] transition-colors duration-200">
                                        Add to Cart
                                    </button>
                                </template>
                                <template x-if="!item.purchasable">
                                    <span class="text-gray-500 text-sm">Display Only</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- BULK PACKAGES SECTION -->
        <div x-show="activeTab === 'bulk'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bulkPackages as $package)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                    <div class="relative">
                        <div class="w-full h-48 bg-gradient-to-br from-[#6E0D25] to-[#8B1A3A] flex items-center justify-center">
                            <div class="text-6xl">{{ $package->emoji }}</div>
                        </div>
                        @if($package->badge)
                            <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold text-white" style="background-color: {{ $package->badge_color }}">
                                {{ $package->badge }}
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ $package->description }}</p>
                        <div class="space-y-2 mb-3">
                            @foreach($package->items as $item)
                            <div class="flex justify-between text-xs">
                                <span class="truncate flex-1 mr-2">{{ $item['name'] }}</span>
                                <span class="font-semibold flex-shrink-0 {{ $item['price'] < 0 ? 'text-green-600' : '' }}">Rs. {{ $item['price'] }}</span>
                            </div>
                            @endforeach
                            <hr class="my-2">
                            <div class="flex justify-between font-bold text-base">
                                <span>Total</span>
                                <span class="text-[#6E0D25]">Rs. {{ $package->total_price }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <button class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#5A0A1F] transition-colors duration-200">
                                Order Now
                            </button>
                            <button class="border border-[#6E0D25] text-[#6E0D25] px-4 py-2 rounded-lg hover:bg-[#6E0D25] hover:text-white transition-colors duration-200">
                                Customize
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- AOS (optional animations) -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

<script>
function findsData() {
    return {
        selectedModel: '{{ $selectedModel ?? 'all' }}',
        merchandise: @json($merchandise),
        loading: false,
        activeTab: 'tshirts',
        
        async changeModel(model) {
            this.loading = true;
            this.selectedModel = model;
            
            try {
                const response = await fetch(`/finds/data?model=${model}`);
                const data = await response.json();
                this.merchandise = data;
            } catch (error) {
                console.error('Error fetching merchandise:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection 