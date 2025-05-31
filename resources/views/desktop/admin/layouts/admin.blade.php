<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.2);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            margin-bottom: 20px;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center mb-4">
                    <h4>Momo Shop</h4>
                    <p class="text-muted">Admin Panel</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                        <i class="fas fa-box"></i> Products
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                    @role('admin|cashier')
                    <a class="nav-link" href="/payment-manager">
                        <i class="fas fa-credit-card"></i> Payment Management
                    </a>
                    <a class="nav-link" href="/pos">
                        <i class="fas fa-cash-register"></i> POS
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.pos-access-logs') ? 'active' : '' }}" href="{{ route('admin.pos-access-logs') }}">
                        <i class="fas fa-history"></i> POS Access Logs
                    </a>
                    @endrole
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="fas fa-store"></i> View Shop
                    </a>
                    <a class="nav-link {{ request()->is('schedules*') ? 'active' : '' }}" href="{{ route('schedules.index') }}">
                        <i class="fas fa-calendar-alt"></i> Employee Schedule
                    </a>
                    <a class="nav-link {{ request()->is('admin/inventory*') ? 'active' : '' }}" href="{{ route('admin.inventory.dashboard') }}">
                        <i class="fas fa-warehouse"></i> Inventory
                    </a>
                    <a class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                        <i class="fas fa-user-shield"></i> Role & Permission Management
                    </a>
                    <a class="nav-link {{ request()->routeIs('creators.index') ? 'active' : '' }}" href="{{ route('creators.index') }}">
                        <i class="fas fa-user-edit"></i> Manage Creators
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand">@yield('title', 'Dashboard')</span>
                        <div class="d-flex align-items-center">
                            <span class="me-3">Welcome, {{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html> 