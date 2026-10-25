<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel administrativo')</title>

    <link rel="manifest" href="{{ route('pwa.manifest.admin') }}">
    <meta name="theme-color" content="#4a2c1d">
    <link rel="apple-touch-icon" href="{{ route('pwa.icono', 180) }}">

    <script>
        (function() {
            let tema = null;
            try {
                tema = localStorage.getItem('tema-admin');
            } catch (e) {}
            if (!tema) {
                tema = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', tema);
        })();
    </script>

    @include('admin.parts.css')
</head>

<body>

    <div class="d-flex min-vh-100">

        <!-- SIDEBAR -->
        @include('admin.parts.sidebar')

        <!-- ÁREA PRINCIPAL -->
        <div class="flex-grow-1 bg-body-tertiary" style="min-width: 0;">

            <!-- BARRA SUPERIOR DE MOVIL-->
            <header class="d-md-none bg-body border-bottom p-2">
                <button class="btn btn-dark btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                    <i class="bi bi-list"></i> Menú
                </button>
            </header>

            <main class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 m-0">@yield('encabezado', 'Gestión de Noticias')</h2>
                    <div class="d-flex align-items-center gap-2">
                        @yield('boton')
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTema"
                            title="Cambiar entre modo claro y oscuro" aria-label="Cambiar tema">
                            <i class="bi bi-moon-stars" id="iconoTema"></i>
                        </button>
                    </div>
                </div>

                @yield('contenido')
            </main>
        </div>

    </div>

    @include('admin.parts.js')

    @yield('scripts')
</body>

</html>
