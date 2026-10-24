{{-- Pie de página. Usa las clases de la plantilla (ftco-footer, ftco-heading-2) y datos de $institucion.
     Íconos: Bootstrap Icons (bi). Se quitó ftco-animate para que nada quede invisible. --}}
<footer class="ftco-footer ftco-section img">
    <div class="overlay"></div>
    <div class="container">

        <div class="row mb-5">

            {{-- Marca y redes --}}
            <div class="col-lg-4 col-md-6 mb-5 mb-lg-0">
                <div class="ftco-footer-widget">
                    <h2 class="ftco-heading-2">{{ $institucion?->nombre_empresa ?? 'Sobre nosotros' }}</h2>
                    <p>Horneamos con pasión cada día para llevar a tu mesa el auténtico sabor artesanal.
                        Seleccionamos los mejores ingredientes para ofrecerte panes frescos, repostería
                        crujiente y momentos dulces hechos con el corazón.</p>

                    @if ($institucion?->facebook || $institucion?->instagram)
                        <ul class="pie-redes list-unstyled d-flex gap-2 mt-4 mb-0">
                            @if ($institucion?->facebook)
                                <li>
                                    <a href="{{ $institucion->facebook }}" target="_blank" rel="noopener"
                                        aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                                </li>
                            @endif
                            @if ($institucion?->instagram)
                                <li>
                                    <a href="{{ $institucion->instagram }}" target="_blank" rel="noopener"
                                        aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Navegación --}}
            <div class="col-lg-2 col-md-6 col-6 mb-5 mb-lg-0">
                <div class="ftco-footer-widget">
                    <h2 class="ftco-heading-2">Explora</h2>
                    <ul class="pie-lista list-unstyled">
                        <li><a href="{{ route('inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('menu') }}">Menú</a></li>
                        <li><a href="{{ route('galeria') }}">Galería</a></li>
                        <li><a href="{{ route('noticias') }}">Noticias</a></li>
                        <li><a href="{{ route('contacto') }}">Contacto</a></li>
                    </ul>
                </div>
            </div>

            {{-- Servicios --}}
            <div class="col-lg-3 col-md-6 col-6 mb-5 mb-lg-0">
                <div class="ftco-footer-widget">
                    <h2 class="ftco-heading-2">Nuestra panadería</h2>
                    <ul class="pie-lista list-unstyled">
                        <li><a href="{{ route('menu') }}">Pan fresco</a></li>
                        <li><a href="{{ route('menu') }}">Repostería y pasteles</a></li>
                        <li><a href="{{ route('menu') }}">Bebidas</a></li>
                        <li><a href="{{ route('contacto') }}">Pedidos por encargo</a></li>
                    </ul>
                </div>
            </div>

            {{-- Contacto --}}
            <div class="col-lg-3 col-md-6 mb-5 mb-lg-0">
                <div class="ftco-footer-widget">
                    <h2 class="ftco-heading-2">Contáctanos</h2>
                    <ul class="pie-contacto list-unstyled">
                        @if ($institucion?->direccion)
                            <li>
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>{{ $institucion->direccion }}</span>
                            </li>
                        @endif
                        @if ($institucion?->telefono)
                            <li>
                                <i class="bi bi-telephone-fill"></i>
                                <a
                                    href="tel:{{ preg_replace('/[^0-9+]/', '', $institucion->telefono) }}">{{ $institucion->telefono }}</a>
                            </li>
                        @endif
                        @if ($institucion?->correo)
                            <li>
                                <i class="bi bi-envelope-fill"></i>
                                <a href="mailto:{{ $institucion->correo }}">{{ $institucion->correo }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        {{-- Franja inferior --}}
        <div class="pie-inferior row pt-4">
            <div class="col-12 text-center">
                <small>© {{ date('Y') }} {{ $institucion?->nombre_empresa }}. Todos los derechos
                    reservados.</small>
            </div>
        </div>

    </div>
</footer>
