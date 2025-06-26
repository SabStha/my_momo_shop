<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ama Ko Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="min-h-screen bg-[url('/images/back.png')] bg-cover bg-center bg-fixed text-gray-800">

    {{-- TOP NAVBAR --}}
    @include('partials.topnav')

    {{-- MAIN PAGE CONTENT --}}
    <main class="pt-8 pb-8 px-1">
        @yield('content')
    </main>

    {{-- BOTTOM NAVBAR --}}
    @include('partials.bottomnav')

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/special-offers.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}"></script>

</body>
</html>
