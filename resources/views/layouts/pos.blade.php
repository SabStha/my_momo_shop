<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravelttttt') }} — POSSS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .pos-layout {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .pos-header,
        .pos-footer {
            background-color: #fff;
            padding: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }

        .pos-header {
            border-bottom: 1px solid #e9ecef;
        }

        .pos-footer {
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .pos-content {
            flex: 1;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }

        .pos-brand {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .pos-clock {
            font-family: monospace;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="pos-layout">
        <header class="pos-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="pos-brand">{{ config('app.name', 'Laravel') }} POS</div>
                <div class="pos-clock" id="current-time"></div>
            </div>
        </header>

        <main class="pos-content">
            @yield('content')
        </main>

        <footer class="pos-footer">
            <div class="container-fluid">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </div>
        </footer>
    </div>

    <script>
        // Live Clock Display
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: false });
            document.getElementById('current-time').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>
