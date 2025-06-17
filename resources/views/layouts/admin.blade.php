@php
    $branches = \App\Models\Branch::all();
    $currentBranch = session('selected_branch_id') ? \App\Models\Branch::find(session('selected_branch_id')) : null;
    
    // Base navigation items
    $nav = [
        ['route' => 'admin.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'needs_branch' => true, 'roles' => ['admin', 'cashier']],
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
            ['route' => 'admin.payments.index', 'icon' => 'fas fa-credit-card', 'label' => 'Payment Management', 'needs_branch' => true],
            
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

                <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="pt-4 border-t border-gray-700">
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
                    <div class="flex items-center space-x-4">
                        <!-- Notifications Button -->
                        <div class="relative">
                            <button id="notificationsButton" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                                <i class="fas fa-bell text-xl"></i>
                                <span id="notificationCount" class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
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
                        <div class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</div>
                    </div>
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
    <script>
        // AI Assistant Functions
        function toggleAIAssistant() {
            const dropdown = document.getElementById('aiAssistantDropdown');
            dropdown.classList.toggle('hidden');
        }

        function askQuestion(question) {
            document.getElementById('userInput').value = question;
            handleSubmit(new Event('submit'));
        }

        async function handleSubmit(event) {
            event.preventDefault();
            const input = document.getElementById('userInput');
            const question = input.value.trim();
            
            if (!question) return;

            // Add user message to chat
            addMessage(question, 'user');
            input.value = '';

            try {
                const response = await fetch('/api/customer-analytics/ai-assistant', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question: question,
                        context: {
                            start_date: document.getElementById('start_date')?.value || '',
                            end_date: document.getElementById('end_date')?.value || '',
                            branch_id: window.branchId || 1
                        }
                    })
                });

                const data = await response.json();
                
                if (data.status === 'success') {
                    addMessage(data.response, 'assistant', data.suggestions);
                } else {
                    throw new Error(data.message || 'Failed to get response');
                }
            } catch (error) {
                addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
                console.error('Error:', error);
            }
        }

        function addMessage(content, type, suggestions = null) {
            const chatContent = document.getElementById('chatContent');
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${type}`;
            
            let messageHTML = `
                <div class="message-content text-gray-800">${content}</div>
                <div class="message-meta">${new Date().toLocaleTimeString()}</div>
            `;

            if (suggestions && type === 'assistant') {
                messageHTML += `
                    <div class="mt-2 space-y-2">
                        ${suggestions.map(suggestion => `
                            <div class="suggestion-chip" onclick="askQuestion('${suggestion}')">
                                ${suggestion}
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            messageDiv.innerHTML = messageHTML;
            chatContent.appendChild(messageDiv);
            chatContent.scrollTop = chatContent.scrollHeight;
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('aiAssistantDropdown');
            const button = document.getElementById('aiAssistantButton');
            
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Toggle dropdown when clicking the button
        document.getElementById('aiAssistantButton').addEventListener('click', function(event) {
            event.stopPropagation();
            toggleAIAssistant();
        });

        // Notifications Functions
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationsDropdown');
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                loadNotifications();
            }
        }

        function loadNotifications() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/admin/notifications/churn-risks', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Please log in to view notifications');
                    }
                    if (response.status === 403) {
                        throw new Error('You do not have permission to view notifications');
                    }
                    throw new Error(`Server error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const notificationsContainer = document.getElementById('notificationsContent');
                const badge = document.getElementById('notificationCount');
                
                if (!Array.isArray(data)) {
                    throw new Error('Invalid response format');
                }
                
                if (data.length > 0) {
                    badge.textContent = data.length;
                    badge.classList.remove('hidden');
                    
                    notificationsContainer.innerHTML = data.map(notification => `
                        <div class="notification-item p-3 border-b border-gray-200 last:border-b-0">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full ${getNotificationClass(notification.type)}">
                                        <i class="${getNotificationIcon(notification.type)}"></i>
                                    </span>
                                </div>
                                <div class="ml-3 w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                                    <p class="mt-1 text-sm text-gray-500">${notification.message}</p>
                                    <p class="mt-1 text-xs text-gray-400">${new Date(notification.timestamp).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    badge.classList.add('hidden');
                    notificationsContainer.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            No new notifications
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                const notificationsContainer = document.getElementById('notificationsContent');
                notificationsContainer.innerHTML = `
                    <div class="p-4 text-center text-red-500">
                        ${error.message || 'Error loading notifications'}
                    </div>
                `;
                const badge = document.getElementById('notificationCount');
                badge.classList.add('hidden');
            });
        }

        function getNotificationClass(type) {
            const classes = {
                'info': 'bg-blue-50 border-blue-200 text-blue-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'danger': 'bg-red-50 border-red-200 text-red-800',
                'success': 'bg-green-50 border-green-200 text-green-800'
            };
            return classes[type] || classes.info;
        }

        function getNotificationIcon(type) {
            const icons = {
                'info': 'fas fa-info-circle text-blue-500',
                'warning': 'fas fa-exclamation-triangle text-yellow-500',
                'danger': 'fas fa-exclamation-circle text-red-500',
                'success': 'fas fa-check-circle text-green-500'
            };
            return icons[type] || icons.info;
        }

        // Close notifications dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationsDropdown');
            const button = document.getElementById('notificationsButton');
            
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Toggle notifications dropdown when clicking the button
        document.getElementById('notificationsButton').addEventListener('click', function(event) {
            event.stopPropagation();
            toggleNotifications();
        });

        // Load notifications every 5 minutes
        setInterval(loadNotifications, 300000);

        // Initial load
        loadNotifications();

        document.getElementById('logoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                window.location.href = '/login';
            })
            .catch(error => {
                console.error('Logout failed:', error);
                window.location.href = '/login';
            });
        });
    </script>
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
</body>
</html>