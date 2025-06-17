<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-token" content="{{ auth()->user()?->tokens()->latest()->first()?->plainTextToken ?? session('api_token') ?? '' }}">
    <meta name="branch-id" content="{{ session('selected_branch_id') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script>
        // Debug log for auth token
        document.addEventListener('DOMContentLoaded', function() {
            const authToken = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
            console.log('Auth token on page load:', authToken ? 'Present' : 'Missing');
            
            if (!authToken) {
                console.error('No auth token found');
                // Try to refresh the token
                fetch('/api/refresh-token', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Token refresh failed');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.token) {
                        // Update the meta tag with the new token
                        const metaTag = document.querySelector('meta[name="auth-token"]');
                        if (metaTag) {
                            metaTag.setAttribute('content', data.token);
                            console.log('Token refreshed successfully');
                            // Reload the page to ensure all scripts have the new token
                            window.location.reload();
                        }
                    } else {
                        throw new Error('No token in response');
                    }
                })
                .catch(error => {
                    console.error('Token refresh failed:', error);
                    window.location.href = '{{ route("login") }}';
                });
            }
        });
    </script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @stack('head')
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- Top Bar -->
    <header class="bg-white shadow-md py-3 px-6 flex justify-between items-center">
        <div class="text-xl font-bold text-[#6E0D25]">
            AmaKo Payment Management
        </div>
        <div class="flex items-center space-x-6">
            <span class="text-sm">Branch: <strong>{{ session('selected_branch_name') ?? 'N/A' }}</strong></span>
            <span class="text-sm">User: <strong>{{ auth()->user()->name ?? 'Guest' }}</strong></span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Logout</button>
            </form>
        </div>
    </header>

    <!-- Content Section -->
    <main class="flex-grow container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Optional Footer -->
    <footer class="bg-white text-center text-xs py-4 text-gray-500 border-t">
        &copy; {{ now()->year }} AmaKo Foods. All rights reserved.
    </footer>

    @stack('scripts')
</body>
</html>
