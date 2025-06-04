<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AmaKo MOMO') }}</title>

    <!-- PWA Meta -->
    <meta name="theme-color" content="#6E0D25">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ url('/images/icons/icon-192x192.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">

    <style>
        :root {
            --top-nav-height: 70px;
            --bottom-nav-height: 65px;
            --brand-color: #6E0D25;
            --highlight-color: #FFFFB3;
        }

        body {
            padding-top: var(--top-nav-height);
            padding-bottom: 0; /* Moved padding to main for better control */
            background-color: #fffaf3;
            color: #6e3d1b;
            font-family: 'Figtree', sans-serif;
        }

        .navbar {
            background-color: var(--brand-color);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--top-nav-height);
            z-index: 1050;
        }

        .navbar-brand {
            color: #fff !important;
            font-size: 1.6rem;
        }

        .navbar-brand img {
            height: 45px;
            margin-right: 6px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: var(--bottom-nav-height);
            background-color: var(--brand-color);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1050;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .bottom-nav .nav-item {
            color: #fff;
            text-align: center;
            font-size: 12px;
            flex-grow: 1;
        }

        .bottom-nav .nav-item i {
            font-size: 18px;
        }

        .bottom-nav .nav-item.active {
            color: var(--highlight-color);
            font-weight: bold;
        }

        main {
            min-height: 100vh;
            padding-bottom: var(--bottom-nav-height); /* Prevent overlap */
        }
    </style>
</head>
<body>
    <div class="position-relative">
        @if (!isset($hideTopNav))
        <nav class="navbar navbar-expand-lg navbar-dark px-3">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ url('storage/logo/momo_icon.png') }}" alt="Momo Icon">
                    AmaKo MOMO
                </a>

                <!-- Icons -->
                <div class="d-flex gap-3">
                    <!-- Notifications -->
                    <a href="{{ route('notifications') }}" class="text-white position-relative">
                        <i class="fas fa-bell fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </a>

                    <!-- Cart -->
                    <a href="{{ route('cart') }}" class="text-white position-relative">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">2</span>
                    </a>
                </div>
            </div>
        </nav>
        @endif

        <!-- Page Content -->
        <main class="container-fluid px-0">
            @yield('content')
        </main>

        <!-- Bottom Nav (conditionally hidden) -->
        @if (!isset($hideBottomNav))
        <div class="bottom-nav">
            
            <a href="{{ route('menu') }}" class="nav-item {{ request()->is('menu') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i>
                <div>Menu</div>
            
            </a>
            <a href="{{ route('bulk') }}" class="nav-item {{ request()->is('bulk') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i>
                <div>Bulk</div>
            </a>

            <a href="{{ route('finds') }}" class="nav-item {{ request()->is('finds') ? 'active' : '' }}">
                <i class="fas fa-dumpster"></i>
                <div>AmaKo Finds</div>
            </a>
            <a href="{{ route('search') }}" class="nav-item {{ request()->is('finds') ? 'active' : '' }}">
                <i class="fas fa-search"></i>
                <div>Search</div>
            </a>

            <a href="{{ route('account') }}" class="nav-item {{ request()->is('account') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <div>Account</div>
            </a>
        </div>
        @endif
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/vue@3.4.15/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered'))
                    .catch(err => console.warn('SW failed', err));
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
