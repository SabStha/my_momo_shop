<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AMAKAKO POS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        
        /* Navigation styles */
        .navbar {
            position: relative;
            z-index: 50;
            background-color: #6E0D25;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Main content styles */
        .main-content {
            padding-top: 0.5rem;
        }

        /* Ensure cart and other fixed elements stay above content */
        .fixed-element {
            position: fixed;
            z-index: 40;
            background-color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="navbar shadow-sm">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex justify-between h-10">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-base font-bold text-white">POS</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-xs text-white">
                        <i class="fas fa-store mr-1"></i>
                        <span id="branchName" class="font-medium"></span>
                    </div>
                    <div class="text-xs text-white">
                        <i class="fas fa-user mr-1"></i>
                        <span id="userName" class="font-medium"></span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-white hover:text-gray-900">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8 main-content">
        @yield('content')
    </main>

    <script>
        // Set branch and user information
        document.addEventListener('DOMContentLoaded', function() {
            const branchData = JSON.parse(localStorage.getItem('pos_branch') || '{}');
            const userData = JSON.parse(localStorage.getItem('pos_user') || '{}');
            
            document.getElementById('branchName').textContent = branchData.name || 'No Branch Selected';
            document.getElementById('userName').textContent = userData.name || 'Guest';
        });
    </script>

    @stack('scripts')
</body>
</html> 