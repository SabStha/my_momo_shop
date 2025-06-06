@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-red-100 to-yellow-50 pt-12 pb-20 px-4 sm:px-6 lg:px-8 min-h-[calc(100vh-8rem)]">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 items-center gap-10">
        {{-- Left Side: Text & CTA --}}
        <div class="text-center lg:text-left space-y-6">
            <h1 class="text-3xl sm:text-4xl lg:text-6xl font-extrabold text-[#6E0D25] mb-4 leading-tight">
                Nepal's Favorite MOMO!
            </h1>
            <p class="text-base sm:text-lg lg:text-2xl text-[#6e3d1b] mb-6">
                Freshly handmade with love at <strong>AmaKo MOMO</strong>.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="{{ route('menu') }}"
                   class="inline-block bg-[#6E0D25] hover:bg-[#891234] text-white px-8 py-4 rounded-xl font-semibold text-sm sm:text-base md:text-lg transition transform hover:scale-105">
                    View Our Menu
                </a>
                <a href="{{ route('bulk') }}"
                   class="inline-block bg-white hover:bg-gray-50 text-[#6E0D25] border-2 border-[#6E0D25] px-8 py-4 rounded-xl font-semibold text-sm sm:text-base md:text-lg transition transform hover:scale-105">
                    Bulk Orders
                </a>
            </div>
        </div>

        {{-- Right Side: Image --}}
        <div class="flex justify-center lg:justify-end">
            <div class="relative">
                <img src="{{ asset('storage/products/momo1.jpg') }}"
                     alt="Delicious momo"
                     class="rounded-3xl shadow-lg w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-xl xl:max-w-2xl object-cover transform hover:scale-105 transition duration-300" />
                <div class="absolute -bottom-4 -right-4 bg-yellow-400 text-[#6E0D25] px-6 py-3 rounded-xl font-bold text-lg shadow-lg">
                    Best Seller!
                </div>
            </div>
        </div>
    </div>

    {{-- Featured Products Section --}}
    @if(isset($featuredProducts) && $featuredProducts->count() > 0)
    <div class="mt-20">
        <h2 class="text-2xl sm:text-3xl font-bold text-[#6E0D25] text-center mb-8">Featured Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredProducts as $product)
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                <img src="{{ asset('storage/' . $product->image) }}" 
                     alt="{{ $product->name }}" 
                     class="w-full h-48 object-cover" />
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $product->name }}</h3>
                    <p class="text-gray-600 mt-2">{{ $product->description }}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($product->price, 2) }}</span>
                        <a href="{{ route('products.show', $product) }}" 
                           class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Menu Highlights Section --}}
    @if(isset($menuHighlights) && $menuHighlights->count() > 0)
    <div class="mt-20">
        <h2 class="text-2xl sm:text-3xl font-bold text-[#6E0D25] text-center mb-8">Menu Highlights</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($menuHighlights as $product)
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                <img src="{{ asset('storage/' . $product->image) }}" 
                     alt="{{ $product->name }}" 
                     class="w-full h-40 object-cover" />
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-[#6E0D25]">{{ $product->name }}</h3>
                    <p class="text-gray-600 mt-2 text-sm">{{ Str::limit($product->description, 60) }}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-lg font-bold text-[#6E0D25]">Rs. {{ number_format($product->price, 2) }}</span>
                        <a href="{{ route('products.show', $product) }}" 
                           class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#891234] transition">
                            Order Now
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
