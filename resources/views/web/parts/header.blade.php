<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('inicio') }}">
            @if ($institucion?->logo)
                <img src="{{ asset('storage/' . $institucion->logo) }}" alt="Logo" height="40">
            @endif
            <div class="col-md-7 col-sm-12 text-center ftco-animate">
                <span class="subheading">{{ $institucion?->nombre_empresa }}</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
            aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                {{-- Ojo: lleva un espacio después de "nav-link", si no la clase queda "nav-linkactive" --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('inicio') ? 'active' : '' }}"
                        href="{{ route('inicio') }}">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('noticias', 'show.noticia') ? 'active' : '' }}"
                        href="{{ route('noticias') }}">Noticias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('galeria') ? 'active' : '' }}"
                        href="{{ route('galeria') }}">Galería</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('menu') ? 'active' : '' }}"
                        href="{{ route('menu') }}">Menú</a>
                </li>

                {{-- En "Mi pedido" este enlace lo reemplaza el botón del carrito (abajo) --}}
                @if ($pedidosConfig?->disponible() && !request()->routeIs('pedido.create'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pedido.*') ? 'active' : '' }}"
                            href="{{ route('pedido.create') }}">
                            Mi pedido <span class="contador-carrito" style="display:none">0</span>
                        </a>
                    </li>
                @endif
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contacto') ? 'active' : '' }}"
                        href="{{ route('contacto') }}">Contacto</a>
                </li>
            </ul>
        </div>

        {{-- Botón del carrito: solo en "Mi pedido". Abre el panel lateral (panel-pedido.js).
             La cantidad la actualiza pedido.js en .contador-carrito. --}}
        @if ($pedidosConfig?->disponible() && request()->routeIs('pedido.create'))
            <button type="button" class="carrito-boton" id="btnCarrito" aria-label="Ver mi pedido"
                aria-haspopup="dialog" aria-controls="panelPedido">
                <svg class="carrito-boton__bolsa" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                    <path d="M5 8h14l-1 12H6L5 8z" />
                    <path d="M9 8V6a3 3 0 0 1 6 0v2" />
                </svg>
                <span class="contador-carrito" style="display:none" aria-live="polite">0</span>
            </button>
        @endif
    </div>
</nav>
