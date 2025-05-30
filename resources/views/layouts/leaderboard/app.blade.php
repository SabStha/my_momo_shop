<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momo Shop</title>
    
    <!-- Font Awesome CDN -->
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-yH5xzKHO+Yj+qUr2GJb9X+/FfZRB6VAFV3dLJdFC5WyqB5UOa3k6iJ5Qp/v5c4ZMdM1UAE6cfQbW6RU09hNk5A=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
    />

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">Momo Shop</a>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    @yield('scripts')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 