<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Momo Pasal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Optional FontAwesome for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  @include('mobile.partials.topnav')

  <main class="mb-5 mt-2">
    @yield('content')
  </main>

  @include('mobile.partials.bottomnav')

</body>
</html>
