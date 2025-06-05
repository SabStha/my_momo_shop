<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>AMAKO MOMO</title>
    
    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
<body class="bg-[#fffaf3] text-[#6e3d1b] font-sans">
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

    @stack('scripts')
</body>
</html>
