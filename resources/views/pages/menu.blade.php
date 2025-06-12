@extends('layouts.app')

@section('content')
<!-- Custom background image -->
<div class="bg-cover bg-center min-h-screen" style="background-image: url('{{ asset('images/menu-background.png') }}');">

    {{-- Second Top Nav for Categories --}}
    <nav class="fixed top-14 left-0 right-0 z-40 bg-[#F8F5F0] border-b border-[#C7A96B] shadow-sm flex justify-between px-4 py-2 overflow-x-auto text-sm font-medium text-[#2E2E2E] whitespace-nowrap">
        @foreach (['Featured', 'Combos', 'Momoes', 'Drinks', 'Desserts'] as $category)
            <a href="#{{ $category }}" class="hover:text-[#6E0D25] transition-colors duration-300 px-2">
                {{ $category }}
            </a>
        @endforeach
    </nav>

    {{-- Content --}}
    <div class="relative z-10 px-4 max-w-5xl mx-auto pt-28 pb-20 space-y-10">
        {{-- Sections for each category --}}
        @foreach ([
            'Featured' => $featured,
            'Combos' => $combos,
            'Momoes' => $momoes,
            'Drinks' => $drinks,
            'Desserts' => $desserts
        ] as $category => $items)
            @if($items->count())
                <section id="{{ $category }}">
                    <h2 class="text-2xl font-bold text-[#6E0D25] mb-4">{{ ucfirst($category) }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach ($items as $item)
                            @include('partials.menu-card', ['item' => $item])
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</div>
@endsection
