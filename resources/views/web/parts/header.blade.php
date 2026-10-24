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
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contacto') ? 'active' : '' }}"
                        href="{{ route('contacto') }}">Contacto</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
