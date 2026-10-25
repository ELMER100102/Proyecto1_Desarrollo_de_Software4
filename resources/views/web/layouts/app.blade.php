<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('titulo')</title>

    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#8b2a22">
    <link rel="apple-touch-icon" href="{{ route('pwa.icono', 180) }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

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
