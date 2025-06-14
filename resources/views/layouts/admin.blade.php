@php
    $branches = \App\Models\Branch::all();
    $currentBranch = session('selected_branch_id') ? \App\Models\Branch::find(session('selected_branch_id')) : null;
    
    // Base navigation items
    $nav = [
        ['route' => 'admin.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'needs_branch' => true, 'roles' => ['admin', 'cashier']],
        ['route' => 'admin.payment-manager.index', 'icon' => 'fas fa-cash-register', 'label' => 'Payment Manager', 'needs_branch' => true, 'roles' => ['admin', 'cashier']],
        ['route' => 'pos.login', 'icon' => 'fas fa-cash-register', 'label' => 'POS System', 'needs_branch' => true, 'roles' => ['admin', 'cashier', 'employee']],
    ];

    // Admin-only navigation items
    if (auth()->user()->hasRole('admin')) {
        $adminNav = [
            // Analytics Section
            ['route' => 'admin.customer-analytics.index', 'icon' => 'fas fa-chart-line', 'label' => 'Customer Analytics', 'needs_branch' => true],
            ['route' => 'admin.sales.overview', 'icon' => 'fas fa-chart-bar', 'label' => 'Sales Analytics', 'needs_branch' => true],
            ['route' => 'admin.analytics.weekly-digest', 'icon' => 'fas fa-newspaper', 'label' => 'Weekly Digest', 'needs_branch' => true],
            
            // Existing Admin Items
            ['route' => 'admin.products.index', 'icon' => 'fas fa-box', 'label' => 'Products', 'needs_branch' => true],
            ['route' => 'admin.orders.index', 'icon' => 'fas fa-shopping-cart', 'label' => 'Orders', 'needs_branch' => true],
            ['route' => 'admin.inventory.index', 'icon' => 'fas fa-warehouse', 'label' => 'Inventory', 'needs_branch' => true],
            ['route' => 'admin.wallet.index', 'icon' => 'fas fa-wallet', 'label' => 'Wallet', 'needs_branch' => true],
            ['route' => 'admin.employees.index', 'icon' => 'fas fa-users', 'label' => 'Employees', 'needs_branch' => true],
            ['route' => 'admin.clock.index', 'icon' => 'fas fa-clock', 'label' => 'Clock In/Out', 'needs_branch' => true],
            ['route' => 'admin.creators.index', 'icon' => 'fas fa-users', 'label' => 'Creators', 'needs_branch' => false],
            ['route' => 'admin.referral-settings.index', 'icon' => 'fas fa-gift', 'label' => 'Referral Settings', 'needs_branch' => false],
            ['route' => 'admin.roles.index', 'icon' => 'fas fa-user-shield', 'label' => 'Roles & Permissions', 'needs_branch' => true],
            ['route' => 'admin.branches.index', 'icon' => 'fas fa-building', 'label' => 'Branches', 'needs_branch' => false],
            ['route' => 'admin.activity-logs.index', 'icon' => 'fas fa-history', 'label' => 'Activity Logs', 'needs_branch' => true],
        ];
        $nav = array_merge($nav, $adminNav);
    }

    // Add View Shop link for all roles
    $nav[] = ['route' => 'home', 'icon' => 'fas fa-store', 'label' => 'View Shop', 'needs_branch' => false, 'roles' => ['admin', 'cashier', 'employee']];
@endphp

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
    
    {{-- Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        @if(!request()->routeIs('admin.branches.index'))
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden lg:block">
            <div class="p-6 text-center border-b border-gray-700">
                <h1 class="text-2xl font-bold">🍲 Momo Admin</h1>
                <p class="text-sm text-gray-400">Control Center</p>
            </div>

            <nav class="p-4 space-y-1">
                @foreach ($nav as $item)
                    @if(isset($item['roles']) && !in_array(auth()->user()->roles->pluck('name')->first(), $item['roles']))
                        @continue
                    @endif
                    
                    @if($item['needs_branch'] && !$currentBranch)
                        <a href="{{ route('admin.branches.index') }}" 
                           class="flex items-center px-4 py-2 rounded text-sm transition hover:bg-gray-800">
                            <i class="{{ $item['icon'] }} mr-3 w-5"></i> {{ $item['label'] }}
                        </a>
                    @else
                        <a href="{{ $item['needs_branch'] ? route($item['route'], ['branch' => $currentBranch->id]) : route($item['route']) }}"
                           class="flex items-center px-4 py-2 rounded text-sm transition {{ request()->routeIs($item['route'].'*') ? 'bg-gray-800' : 'hover:bg-gray-800' }}">
                            <i class="{{ $item['icon'] }} mr-3 w-5"></i> {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach

                <form method="POST" action="{{ route('logout') }}" class="pt-4 border-t border-gray-700">
                    @csrf
                    <button type="submit" class="flex w-full items-center px-4 py-2 rounded hover:bg-gray-800 text-sm">
                        <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                    </button>
                </form>
            </nav>
        </aside>
        @endif

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
                        <div class="relative">
                            @if($currentBranch)
                                <button type="button" 
                                        class="flex items-center space-x-2 text-gray-300 hover:text-white focus:outline-none"
                                        onclick="showBranchSwitchModal({{ $currentBranch->id }}, '{{ addslashes($currentBranch->name) }}', {{ $currentBranch->requires_password ? 'true' : 'false' }})">
                                    <i class="fas fa-building"></i>
                                    <span data-branch-name>{{ $currentBranch->name }}</span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                            @else
                                <a href="{{ route('admin.branches.index') }}" class="flex items-center space-x-2 text-gray-300 hover:text-white focus:outline-none">
                                    <i class="fas fa-building"></i>
                                    <span>Select Branch</span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </a>
                            @endif
                        </div>
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

    @include('admin.branches.switch-modal')

    @stack('scripts')
</body>
</html> 
