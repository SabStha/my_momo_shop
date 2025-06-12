<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ama Ko Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-gray-800">

    {{-- TOP NAVBAR --}}
    @include('partials.topnav')

    {{-- MAIN PAGE CONTENT --}}
    <main class="pt-16 pb-16 px-4">
        @yield('content')
    </main>

    {{-- BOTTOM NAVBAR --}}
    @include('partials.bottomnav')

</body>
</html>
