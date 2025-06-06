@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#b8d8ba] to-[#d9dbbc] flex items-center justify-center">
    <div class="max-w-2xl mx-auto text-center px-4 py-16">
        <div class="bg-white rounded-2xl shadow-xl p-8 transform hover:scale-105 transition duration-300">
            <h1 class="text-4xl font-bold text-[#6E0D25] mb-4">Bulk Orders Coming Soon!</h1>
            <p class="text-gray-600 text-lg mb-8">
                We're working hard to bring you our bulk ordering system. Stay tuned for special deals and discounts for large orders!
            </p>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('home') }}" class="bg-[#6E0D25] text-white px-6 py-3 rounded-lg hover:bg-[#891234] transition">
                    Back to Home
                </a>
                <a href="{{ route('menu') }}" class="bg-[#ef959d] text-white px-6 py-3 rounded-lg hover:bg-[#f8a5ac] transition">
                    View Menu
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
