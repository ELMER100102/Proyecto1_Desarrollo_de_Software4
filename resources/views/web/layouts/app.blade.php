<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title> @yield('titulo')</title>

  @include('web/parts/css')

</head>
<body class="d-flex flex-column min-vh-100">

  <!-- HEADER -->
  @include('web/parts/header')

  <main class="flex-grow-1">

    @yield('contenido')

  </main>

  <!-- FOOTER -->
  @include('web/parts/footer')

  @include('web/parts/js')
  
  @yield('scripts')
</body>
</html>