<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AmaKo MOMO') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="text-2xl font-bold text-[#6E0D25]">
                                AmaKo MOMO
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('home') }}" class="border-transparent text-gray-500 hover:border-[#6E0D25] hover:text-[#6E0D25] inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Home
                            </a>
                            <a href="{{ route('menu') }}" class="border-transparent text-gray-500 hover:border-[#6E0D25] hover:text-[#6E0D25] inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Menu
                            </a>
                            <a href="{{ route('bulk') }}" class="border-transparent text-gray-500 hover:border-[#6E0D25] hover:text-[#6E0D25] inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Bulk Orders
                            </a>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        @auth
                            <a href="{{ route('cart') }}" class="text-gray-500 hover:text-[#6E0D25] px-3 py-2 rounded-md text-sm font-medium">
                                Cart
                            </a>
                            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-[#6E0D25] px-3 py-2 rounded-md text-sm font-medium">
                                Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-500 hover:text-[#6E0D25] px-3 py-2 rounded-md text-sm font-medium">
                                    Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-500 hover:text-[#6E0D25] px-3 py-2 rounded-md text-sm font-medium">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="text-gray-500 hover:text-[#6E0D25] px-3 py-2 rounded-md text-sm font-medium">
                                Register
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">About Us</h3>
                        <p class="mt-4 text-base text-gray-500">
                            AmaKo MOMO - Nepal's favorite momo shop, serving delicious dumplings with love since 2010.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Quick Links</h3>
                        <ul class="mt-4 space-y-4">
                            <li>
                                <a href="{{ route('menu') }}" class="text-base text-gray-500 hover:text-[#6E0D25]">
                                    Menu
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('bulk') }}" class="text-base text-gray-500 hover:text-[#6E0D25]">
                                    Bulk Orders
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Contact</h3>
                        <ul class="mt-4 space-y-4">
                            <li class="text-base text-gray-500">
                                Phone: +977 1234567890
                            </li>
                            <li class="text-base text-gray-500">
                                Email: info@amakomomo.com
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 pt-8">
                    <p class="text-base text-gray-400 text-center">
                        &copy; {{ date('Y') }} AmaKo MOMO. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html> 