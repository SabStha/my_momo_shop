@extends('layouts.app')

@section('content')
<div class="text-3xl text-red-500 font-bold">TAILWIND IS WORKING</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-center mb-6">🔥 Featured Items</h1>
    <div class="text-3xl text-red-500 font-bold">TAILWIND IS WORKING</div>

    @if($featuredProducts->count())
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
            @foreach($featuredProducts as $product)
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden">
                    <img src="{{ asset('storage/' . $product->image) }}"
                         onerror="this.src='/storage/products/default.png'"
                         alt="{{ $product->name }}"
                         class="w-full h-48 object-cover">
                    <div class="p-4 flex flex-col h-full">
                        <h2 class="text-lg font-semibold mb-1">{{ $product->name }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ $product->description }}</p>
                        <div class="mt-auto flex items-center justify-between">
                            <span class="text-base font-bold text-gray-800">
                                Rs. {{ number_format($product->price) }}
                            </span>
                            <button class="inline-flex items-center bg-teal-600 text-white text-sm font-medium px-3 py-1.5 rounded hover:bg-teal-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.45M7.5 14.25a3 3 0 00-3 3h13.5m-10.5 0a3 3 0 006 0m-6 0h6m1.5 0a3 3 0 00-3-3m0 0L16.5 6.75M7.5 14.25L5.25 6.75M5.25 6.75h15l-1.5 5.25H6.75L5.25 6.75z" />
                                </svg>
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-yellow-100 text-yellow-800 text-center py-4 px-6 rounded mt-10">
            No featured items found.
        </div>
    @endif
</div>
@endsection
