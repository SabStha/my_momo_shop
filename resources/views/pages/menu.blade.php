@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'combo' }" class="pt-[68px] pb-32 px-4 bg-white min-h-screen">

    <!-- TOP TAB NAVIGATION -->
    <div class="flex justify-around text-xl font-bold mb-6 text-[#000] border-b pb-2">
        <button @click="activeTab = 'combo'"
                :class="{ 'text-red-600': activeTab === 'combo' }"
                class="hover:text-red-600 transition">COMBO</button>

        <button @click="activeTab = 'featured'"
                :class="{ 'text-red-600': activeTab === 'featured' }"
                class="hover:text-red-600 transition">FEATURED</button>

        <button @click="activeTab = 'momo'"
                :class="{ 'text-red-600': activeTab === 'momo' }"
                class="hover:text-red-600 transition">MOMO</button>

        <button @click="activeTab = 'drinks'"
                :class="{ 'text-red-600': activeTab === 'drinks' }"
                class="hover:text-red-600 transition">DRINKS</button>

        <button @click="activeTab = 'desserts'"
                :class="{ 'text-red-600': activeTab === 'desserts' }"
                class="hover:text-red-600 transition">DESSERTS</button>
    </div>

    <!-- COMBO TAB CONTENT -->
    <div x-show="activeTab === 'combo'" x-transition>
        @include('menu.combo')
    </div>

    <!-- FEATURED TAB CONTENT -->
    <div x-show="activeTab === 'featured'" x-transition>
        @include('menu.featured')
    </div>

    <!-- MOMO TAB CONTENT -->
    <div x-show="activeTab === 'momo'" x-transition>
        @include('menu.momo')
    </div>

    <!-- DRINKS TAB CONTENT -->
    <div x-show="activeTab === 'drinks'" x-transition>
        @include('menu.drinks')
    </div>

    <!-- DESSERTS TAB CONTENT -->
    <div x-show="activeTab === 'desserts'" x-transition>
        @include('menu.desserts')
    </div>
</div>
@endsection
