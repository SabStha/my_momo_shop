<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- SEO Meta -->
    <meta name="description" content="Your Laravel PWA for modern, fast, and reliable web experiences.">

    <!-- Fonts: Preload Nunito -->
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=Nunito" onload="this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"></noscript>

    <!-- Critical CSS: Preload and load app.css -->
    <link rel="preload" as="style" href="{{ mix('css/app.css') }}" onload="this.rel='stylesheet'">
    <noscript><link href="{{ mix('css/app.css') }}" rel="stylesheet"></noscript>

    <!-- Manifest & PWA Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2d3748">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="My Laravel PWA">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512x512.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#2d3748">
    <meta name="msapplication-TileImage" content="/icons/icon-192x192.png">
</head>
<body>
    <div id="app">
        {{-- Removed default Laravel navbar completely --}}
        <main>
            @yield('content')
        </main>
    </div>
    <!-- Defer and move scripts to end of body for performance -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/service-worker.js');
        });
      }
    </script>
</body>
</html>
