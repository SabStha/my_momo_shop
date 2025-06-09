<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Momo Admin') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="h-full text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

            <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden lg:block">
            <div class="p-6 text-center border-b border-gray-700">
                <h1 class="text-2xl font-bold">🍲 Momo Admin</h1>
                <p class="text-sm text-gray-400">Control Center</p>
                </div>

            <nav class="p-4 space-y-1">
                @php
                    $nav = [
                        ['route' => 'admin.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
                        ['route' => 'admin.products.index', 'icon' => 'fas fa-box', 'label' => 'Products'],
                        ['route' => 'admin.orders.index', 'icon' => 'fas fa-shopping-cart', 'label' => 'Orders'],
                        ['route' => 'admin.inventory.index', 'icon' => 'fas fa-warehouse', 'label' => 'Inventory'],
                        ['route' => 'admin.wallet.index', 'icon' => 'fas fa-wallet', 'label' => 'Wallet'],
                        ['route' => 'admin.roles.index', 'icon' => 'fas fa-user-shield', 'label' => 'Roles & Permissions'],
                        ['route' => 'home', 'icon' => 'fas fa-store', 'label' => 'View Shop'],
                        ['route' => 'admin.branches.index', 'icon' => 'fas fa-store', 'label' => 'Branches']
                    ];
                @endphp

                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center px-4 py-2 rounded text-sm transition {{ request()->routeIs($item['route'].'*') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                        <i class="{{ $item['icon'] }} mr-3 w-5"></i> {{ $item['label'] }}
                    </a>
                @endforeach

                <form method="POST" action="{{ route('logout') }}" class="pt-4 border-t border-gray-700">
                        @csrf
                    <button type="submit" class="flex w-full items-center px-4 py-2 rounded hover:bg-gray-800 text-sm">
                        <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                        </button>
                    </form>
                </nav>
        </aside>

        <!-- Content Wrapper -->
        <div class="flex flex-col flex-1 overflow-y-auto">

            <!-- Top Navbar -->
            <header class="bg-white shadow px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">@yield('title', 'Dashboard')</h2>
                    <div class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</div>
                </div>
                
                <!-- Branch Switcher -->
                @if(auth()->user()->hasRole('admin'))
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Branch:</span>
                        <x-branch-switcher />
                    </div>
                @endif
            </header>

            <!-- Toast -->
            <div id="alert-container" class="fixed top-4 right-4 z-50 max-w-xs w-full"></div>

            <!-- Main Content -->
                <main class="p-6">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
                </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html> 
