@php
    $branches = \App\Models\Branch::all();
    $currentBranch = session('selected_branch_id') ? \App\Models\Branch::find(session('selected_branch_id')) : null;
    
    // Base navigation items
    $nav = [
        ['route' => 'admin.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'needs_branch' => false, 'roles' => ['admin', 'cashier']],
        ['route' => 'pos.login', 'icon' => 'fas fa-cash-register', 'label' => 'POS System', 'needs_branch' => true, 'roles' => ['admin', 'cashier', 'employee']],
    ];

    // Admin-only navigation items
    if (auth()->user()->hasRole('admin')) {
        $adminNav = [
            // Analytics Section
            ['route' => 'admin.analytics.index', 'icon' => 'fas fa-chart-line', 'label' => 'Customer Analytics', 'needs_branch' => true],
            ['route' => 'admin.sales.overview', 'icon' => 'fas fa-chart-bar', 'label' => 'Sales Analytics', 'needs_branch' => true],
            ['route' => 'admin.analytics.weekly-digest', 'icon' => 'fas fa-newspaper', 'label' => 'Weekly Digest', 'needs_branch' => true],
            ['route' => 'admin.churn.index', 'icon' => 'fas fa-exclamation-triangle', 'label' => 'Churn Predictions', 'needs_branch' => true],
            
            // Payment Management Section
            ['route' => 'payment.login', 'icon' => 'fas fa-credit-card', 'label' => 'Payment Management', 'needs_branch' => true],
            
            // Existing Admin Items
            ['route' => 'admin.products.index', 'icon' => 'fas fa-box', 'label' => 'Products', 'needs_branch' => true],
            ['route' => 'admin.bulk-packages.index', 'icon' => 'fas fa-layer-group', 'label' => 'Bulk Packages', 'needs_branch' => true],
            ['route' => 'admin.orders.index', 'icon' => 'fas fa-shopping-cart', 'label' => 'Orders', 'needs_branch' => true],
            ['route' => 'admin.inventory.index', 'icon' => 'fas fa-warehouse', 'label' => 'Inventory', 'needs_branch' => true],
            ['route' => 'wallet.index', 'icon' => 'fas fa-wallet', 'label' => 'Amako Credits', 'needs_branch' => true],
            ['route' => 'admin.employees.index', 'icon' => 'fas fa-users', 'label' => 'Employees', 'needs_branch' => true],
            ['route' => 'admin.clock.index', 'icon' => 'fas fa-clock', 'label' => 'Clock In/Out', 'needs_branch' => true],
            ['route' => 'admin.creators.index', 'icon' => 'fas fa-users', 'label' => 'Creators', 'needs_branch' => false],
            ['route' => 'admin.investors.index', 'icon' => 'fas fa-chart-line', 'label' => 'Investors', 'needs_branch' => false],
            ['route' => 'accounting.dashboard', 'icon' => 'fas fa-calculator', 'label' => 'Accounting', 'needs_branch' => false],
            ['route' => 'admin.referral-settings.index', 'icon' => 'fas fa-gift', 'label' => 'Referral Settings', 'needs_branch' => false],
            ['route' => 'admin.roles.index', 'icon' => 'fas fa-user-shield', 'label' => 'Roles & Permissions', 'needs_branch' => true],
            ['route' => 'admin.branches.index', 'icon' => 'fas fa-building', 'label' => 'Branches', 'needs_branch' => false],
            ['route' => 'admin.activity-logs.index', 'icon' => 'fas fa-history', 'label' => 'Activity Logs', 'needs_branch' => true],
            ['route' => 'admin.site-settings.index', 'icon' => 'fas fa-cog', 'label' => 'Site Settings', 'needs_branch' => false],
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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    
    {{-- Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar (always rendered except on branches index) -->
        @if(!request()->routeIs('admin.branches.index'))
            <aside class="md:w-56 w-16 bg-indigo-900 text-white flex-shrink-0 flex flex-col" style="height: 100vh;">
                <!-- Fixed header -->
                <div class="p-4 md:p-6 text-center border-b border-indigo-800 flex-shrink-0">
                    <h1 class="text-2xl font-bold hidden md:block">🍲 Momo Admin</h1>
                    <p class="text-sm text-indigo-200 hidden md:block">Control Center</p>
                </div>
                <!-- Scrollable navigation -->
                <nav class="p-2 md:p-4 space-y-1 flex-1 overflow-y-auto">
                    @foreach ($nav as $item)
                        @if(isset($item['roles']) && !in_array(auth()->user()->roles->pluck('name')->first(), $item['roles']))
                            @continue
                        @endif
                        @if($item['needs_branch'] && !$currentBranch)
                            <a href="{{ route('admin.branches.index') }}" 
                               class="flex items-center px-2 md:px-4 py-2 rounded text-sm transition hover:bg-indigo-800">
                                <i class="{{ $item['icon'] }} mr-0 md:mr-3 w-5"></i> <span class="hidden md:inline">{{ $item['label'] }}</span>
                            </a>
                        @else
                            @if($item['route'] === 'payment.login')
                                @if($currentBranch)
                                    <a href="{{ route($item['route']) }}?branch={{ $currentBranch->id }}"
                                       class="flex items-center px-2 md:px-4 py-2 rounded text-sm transition {{ request()->routeIs($item['route'].'*') ? 'bg-indigo-800' : 'hover:bg-indigo-800' }}">
                                        <i class="{{ $item['icon'] }} mr-0 md:mr-3 w-5"></i> <span class="hidden md:inline">{{ $item['label'] }}</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.branches.index') }}" 
                                       class="flex items-center px-2 md:px-4 py-2 rounded text-sm transition hover:bg-indigo-800">
                                        <i class="{{ $item['icon'] }} mr-0 md:mr-3 w-5"></i> <span class="hidden md:inline">{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            @else
                                <a href="{{ $item['needs_branch'] ? route($item['route'], ['branch' => $currentBranch->id]) : route($item['route']) }}"
                                   class="flex items-center px-2 md:px-4 py-2 rounded text-sm transition {{ request()->routeIs($item['route'].'*') ? 'bg-indigo-800' : 'hover:bg-indigo-800' }}">
                                    <i class="{{ $item['icon'] }} mr-0 md:mr-3 w-5"></i> <span class="hidden md:inline">{{ $item['label'] }}</span>
                                </a>
                            @endif
                        @endif
                    @endforeach
                    <!-- Logout button at bottom -->
                    <div class="pt-4 border-t border-indigo-800 mt-4">
                        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-2 md:px-4 py-2 rounded hover:bg-indigo-800 text-sm">
                                <i class="fas fa-sign-out-alt mr-0 md:mr-3 w-5"></i> <span class="hidden md:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>
        @endif
        <!-- Main Content Wrapper -->
        <div class="flex-1 overflow-y-auto bg-white">
            <!-- Top Navbar -->
            <header class="bg-white shadow px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">@yield('title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications Button -->
                        <div class="relative">
                            <button id="notificationsButton" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                                <i class="fas fa-bell text-xl"></i>
                                <span id="notificationCount" class="bg-amk-brown-1 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                            </button>
                            <!-- Notifications Dropdown -->
                            <div id="notificationsDropdown" class="hidden fixed right-4 top-20 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200">
                                <div class="p-3 border-b border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-base font-semibold text-gray-900">Notifications</h3>
                                        <button onclick="toggleNotifications()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="notificationsContent" class="max-h-[400px] overflow-y-auto p-3 space-y-3">
                                    <!-- Notifications will be loaded here -->
                                </div>
                            </div>
                        </div>
                        <!-- AI Assistant Button -->
                        <div class="relative">
                            <button id="aiAssistantButton" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                                <i class="fas fa-robot text-xl"></i>
                                <span>AI Assistant</span>
                            </button>
                            <!-- AI Assistant Dropdown -->
                            <div id="aiAssistantDropdown" class="hidden fixed right-4 top-20 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200">
                                <div class="p-3 border-b border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-base font-semibold text-gray-900">AI Assistant</h3>
                                        <button onclick="toggleAIAssistant()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="chatContent" class="h-[400px] overflow-y-auto p-3 space-y-3">
                                    <div class="chat-message assistant">
                                        <div class="message-content text-gray-800 text-sm">
                                            Hello! I'm your AI assistant. I can help you with:
                                        </div>
                                        <div class="message-meta text-xs">Just now</div>
                                        <div class="mt-2 space-y-1">
                                            <div class="suggestion-chip text-sm" onclick="askQuestion('Who should I retain this week?')">
                                                Who should I retain this week?
                                            </div>
                                            <div class="suggestion-chip text-sm" onclick="askQuestion('Give me campaign ideas')">
                                                Give me campaign ideas
                                            </div>
                                            <div class="suggestion-chip text-sm" onclick="askQuestion('Analyze customer segments')">
                                                Analyze customer segments
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-t border-gray-200">
                                    <form id="chatForm" onsubmit="handleSubmit(event)" class="flex gap-2">
                                        <input type="text" id="userInput" 
                                            class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                                            placeholder="Ask me anything...">
                                        <button type="submit" 
                                            class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            <i class="fas fa-paper-plane text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-user-circle text-indigo-500"></i>
                            Welcome, {{ Auth::user()->name }}
                        </div>
                    </div>
                </div>
                <!-- Branch Switcher -->
                @if(auth()->user()->hasRole('admin'))
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Branch:</span>
                    <div class="relative">
                        @if($currentBranch)
                            <button type="button" 
                                    class="flex items-center space-x-1 text-gray-700 hover:text-indigo-600 font-medium transition"
                                    onclick="showBranchSwitchModal({{ $currentBranch->id }}, '{{ addslashes($currentBranch->name) }}', {{ $currentBranch->requires_password ? 'true' : 'false' }})">
                                <i class="fas fa-building text-indigo-500"></i>
                                <span class="text-sm truncate max-w-[140px]" data-branch-name>{{ $currentBranch->name }}</span>
                                <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </button>
                        @else
                            <a href="{{ route('admin.branches.index') }}" 
                            class="flex items-center space-x-1 text-gray-700 hover:text-indigo-600 font-medium transition">
                                <i class="fas fa-building text-indigo-500"></i>
                                <span class="text-sm">Select Branch</span>
                                <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </header>
            
            <!-- Global Success/Error Messages -->
            @if(session('success'))
                <div id="successMessage" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-green-50 border border-green-200 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-green-800">Success!</p>
                                <p class="mt-1 text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button onclick="closeSuccessMessage()" class="bg-green-50 rounded-md inline-flex text-green-400 hover:text-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div id="errorMessage" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-red-50 border border-red-200 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-amk-brown-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-red-800">Error!</p>
                                <p class="mt-1 text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button onclick="closeErrorMessage()" class="bg-red-50 rounded-md inline-flex text-red-400 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div id="warningMessage" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-yellow-50 border border-yellow-200 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-yellow-800">Warning!</p>
                                <p class="mt-1 text-sm text-yellow-700">{{ session('warning') }}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button onclick="closeWarningMessage()" class="bg-yellow-50 rounded-md inline-flex text-yellow-400 hover:text-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div id="infoMessage" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-blue-50 border border-blue-200 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-blue-800">Info!</p>
                                <p class="mt-1 text-sm text-blue-700">{{ session('info') }}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button onclick="closeInfoMessage()" class="bg-blue-50 rounded-md inline-flex text-blue-400 hover:text-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Toast -->
            <div id="alert-container" class="fixed top-4 right-4 z-50 max-w-xs w-full"></div>
            <!-- Main Content -->
            <main class="flex-1 p-0">
                <div class="max-w-7xl mx-auto w-full">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @include('admin.branches.switch-modal')
    @include('admin.payments.partials.payment-modal')

    @stack('modals')
    <style>
        .chat-message {
            @apply mb-3 p-2 rounded-lg;
        }

        .chat-message.user {
            @apply bg-gray-100 ml-6;
        }

        .chat-message.assistant {
            @apply bg-indigo-50 mr-6;
        }

        .chat-message .message-content {
            @apply mb-1;
        }

        .chat-message .message-meta {
            @apply text-xs text-gray-500;
        }

        .suggestion-chip {
            @apply inline-block px-3 py-1.5 m-0.5 bg-gray-100 rounded-full cursor-pointer transition-colors duration-200 hover:bg-gray-200 text-xs;
        }
    </style>
    @stack('scripts')
</body>
</html>