<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>AMAKO MOMO</title>
    
    {{-- Single Vite directive for all assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --top-nav-height: 70px;
            --bottom-nav-height: 65px;
            --brand-color: #6E0D25;
            --highlight-color: #FFFFB3;
        }
    </style>
</head>
<body class="bg-[#fffaf3] text-[#6e3d1b] font-sans">

    {{-- Top Navigation --}}
    @unless (isset($hideTopNav))
    <nav class="fixed top-0 left-0 w-full h-[var(--top-nav-height)] bg-[#6E0D25] text-white z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 text-xl font-bold">
                <img src="{{ url('storage/logo/momo_icon.png') }}" alt="Logo" class="h-10 w-10 object-contain" />
                <span>AmaKo MOMO</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="{{ route('notifications') }}" class="relative">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1">3</span>
                </a>
                <a href="{{ route('cart') }}" class="relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="absolute -top-1 -right-2 bg-yellow-400 text-black text-xs rounded-full px-1">2</span>
                </a>
            </div>
        </div>
    </nav>
    @endunless

    {{-- Page Content --}}
    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    @unless (isset($hideBottomNav))
    <nav class="fixed bottom-0 left-0 w-full h-[var(--bottom-nav-height)] bg-[#6E0D25] text-white z-50 shadow-inner">
        <div class="max-w-7xl mx-auto px-4 h-full flex justify-around items-center">
            @php
                $navItems = [
                    ['route' => 'menu', 'icon' => 'fa-utensils', 'label' => 'Menu'],
                    ['route' => 'bulk', 'icon' => 'fa-box-open', 'label' => 'Bulk'],
                    ['route' => 'finds', 'icon' => 'fa-dumpster', 'label' => 'Finds'],
                    ['route' => 'search', 'icon' => 'fa-search', 'label' => 'Search'],
                    ['route' => 'account', 'icon' => 'fa-user', 'label' => 'Account'],
                ];
            @endphp

            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex flex-col items-center text-xs transition-all duration-200 hover:text-yellow-300 {{ request()->is($item['route']) ? 'text-yellow-200 font-semibold' : '' }}">
                    <i class="fas {{ $item['icon'] }} text-lg"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
    @endunless

    @stack('scripts')
</body>
</html>
