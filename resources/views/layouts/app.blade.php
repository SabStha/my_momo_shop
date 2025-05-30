<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMAKO MOMO</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3.4.15/dist/vue.global.prod.js"></script>

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
    crossorigin="anonymous"
    />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

   
</head>
<body style="background-color: #fffaf3; color: #6e3d1b;">
    <div class="position-relative">
        @if (!isset($hideTopNav))
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container d-flex justify-content-between align-items-center">
                {{-- Brand --}}
                <a class="navbar-brand fw-bold d-flex align-items-center" href="#" style="font-size: 1.8rem; color: #fff;">
                    <img src="{{ asset('storage/logo/momo_icon.png') }}" alt="Momo Icon" style="height: 50px; margin-right: 2px;">
                    AmaKo MOMO
                </a>

                {{-- Right-side icons --}}
                <div class="d-flex justify-content-end align-items-center gap-3">
                    {{-- Notification Icon --}}
                    <a href="{{ route('notifications') }}" class="text-white position-relative">
                        <i class="fas fa-bell fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>

                    {{-- Cart Icon --}}
                    <a href="{{ route('cart') }}" class="text-white position-relative">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                            2
                        </span>
                    </a>
                </div>
            </div>
        </nav>
        @endif


        <main class="py-0">
            @yield('content')
        </main>

        <div class="bottom-nav">
            <a href="{{ route('home') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('offers') }}" class="nav-item {{ request()->is('offers') ? 'active' : '' }}">
                <i class="fas fa-gift"></i>
                <span>Offers</span>
            </a>
            <a href="{{ route('menu') }}" class="nav-item {{ request()->is('menu') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i>
                <span>Menu</span>
            </a>
            <a href="{{ route('cart') }}" class="nav-item {{ request()->is('cart') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
            </a>
            <a href="{{ route('account') }}" class="nav-item {{ request()->is('account') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
        </div>
    </div>

    @yield('scripts')
</body>
</html> 