@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'combo' }" class="bg-[url('/images/bd2.png')] bg-repeat bg-center min-h-screen">

    <!-- SECONDARY NAV BAR -->
    <div class="relative z-10 pt-[20px] pb-6">
        <div class="w-max mx-auto bg-white rounded-xl shadow px-6 py-2">
            <div class="flex gap-6 font-bold text-sm sm:text-base text-[#000] whitespace-nowrap">
                <button @click="activeTab = 'combo'" :class="{ 'text-red-600': activeTab === 'combo' }" class="hover:text-red-600 transition">COMBO</button>
                <button @click="activeTab = 'featured'" :class="{ 'text-red-600': activeTab === 'featured' }" class="hover:text-red-600 transition">FEATURED</button>
                <button @click="activeTab = 'momo'" :class="{ 'text-red-600': activeTab === 'momo' }" class="hover:text-red-600 transition">MOMO</button>
                <button @click="activeTab = 'drinks'" :class="{ 'text-red-600': activeTab === 'drinks' }" class="hover:text-red-600 transition">DRINKS</button>
                <button @click="activeTab = 'desserts'" :class="{ 'text-red-600': activeTab === 'desserts' }" class="hover:text-red-600 transition">DESSERTS</button>
            </div>
        </div>
    </div>

    <!-- TAB CONTENT AREA -->
    <div class="px-4 pb-20 space-y-16">
        <div x-show="activeTab === 'combo'" x-transition>
            @include('menu.combo')
        </div>
        <div x-show="activeTab === 'featured'" x-transition>
            @include('menu.featured')
        </div>
        <div x-show="activeTab === 'momo'" x-transition>
            @include('menu.momo')
        </div>
        <div x-show="activeTab === 'drinks'" x-transition>
            @include('menu.drinks')
        </div>
        <div x-show="activeTab === 'desserts'" x-transition>
            @include('menu.desserts')
        </div>
    </div>
</div>

<!-- AOS (optional animations) -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
@endsection
