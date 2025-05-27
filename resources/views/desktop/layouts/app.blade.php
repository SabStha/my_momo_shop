<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .desktop-header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .desktop-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .desktop-nav-item {
            color: #4a5568;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .desktop-nav-item:hover {
            background-color: #f3f4f6;
            color: #1a202c;
        }
        .desktop-nav-item.active {
            background-color: #e2e8f0;
            color: #1a202c;
        }
        .desktop-nav-item i {
            margin-right: 0.5rem;
        }
        .main-content {
            margin-top: 80px;
            padding: 2rem;
        }
        .user-menu {
            margin-left: auto;
        }
        .desktop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .desktop-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Desktop Header -->
        <header class="desktop-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="desktop-nav">
                    <a class="desktop-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="desktop-nav-item {{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}">
                        <i class="fas fa-cash-register"></i> POS
                    </a>
                    <a class="desktop-nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                        <i class="fas fa-box"></i> Products
                    </a>
                    <a class="desktop-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                    <a class="desktop-nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                        <i class="fas fa-users"></i> Employees
                    </a>

                    <a class="desktop-nav-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}" href="{{ route('schedules.index') }}">
                        <i class="fas fa-calendar"></i> Schedules
                    </a>
                    <a class="desktop-nav-item {{ request()->routeIs('payment-manager') ? 'active' : '' }}" href="{{ route('payment-manager') }}">
                        <i class="fas fa-money-bill-wave"></i> Payments
                    </a>
                </div>

                <div class="user-menu">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
