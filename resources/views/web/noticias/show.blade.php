@extends('web.layouts.app')

@section('titulo', 'Noticia')

@section('contenido')
    @php
        // El contenido se guarda como texto plano: cada salto de línea separa un párrafo.
        $texto = trim((string) $noticia->contenido);
        $parrafos = collect(preg_split('/\R+/u', $texto))->map(fn ($p) => trim($p))->filter()->values();

        // Hasta ~700 caracteres todo va dentro de la foto. Si es más largo, dentro va el
        // primer párrafo (cortado en oraciones si pasa de ~450) y el resto sigue debajo.
        $enFoto = $parrafos;
        $debajo = collect();
        if (mb_strlen($texto) > 700 && $parrafos->isNotEmpty()) {
            $primero = $parrafos->first();
            $debajo = $parrafos->slice(1)->values();

            if (mb_strlen($primero) > 450) {
                $oraciones = preg_split('/(?<=[.!?…])\s+/u', $primero);
                $corto = array_shift($oraciones);
                while ($oraciones && mb_strlen($corto . ' ' . $oraciones[0]) <= 400) {
                    $corto .= ' ' . array_shift($oraciones);
                }
                if ($oraciones) {
                    $debajo->prepend(implode(' ', $oraciones));
                }
                $primero = $corto;
            }
            $enFoto = collect([$primero]);
        }
    @endphp

    <article class="noticia">
        {{-- FONDO FIJO: la foto queda quieta detrás de todo el artículo; la portada y la
             continuación se desplazan por encima (sin fondo propio) --}}
        @if ($noticia->imagen)
            <div class="noticia__fondo" aria-hidden="true">
                <img class="noticia__img" src="{{ asset('storage/' . $noticia->imagen) }}" alt=""
                    width="1600" height="1000" decoding="async" fetchpriority="high">
                <span class="noticia__velo"></span>
            </div>
        @endif

        {{-- PORTADA: título arriba y primer párrafo abajo, sobre la foto --}}
        <header class="noticia-hero">

            <div class="container noticia-hero__contenido">
                <div class="noticia-hero__arriba">
                    {{-- RECONOCE LA RUTA ANTERIOR Y ACOMODA LA RUTA Y EL BOTON ACORDE A LA PAGINA PREVIA --}}
                    @if (rtrim(url()->previous(), '/') === rtrim(route('inicio'), '/'))
                        <a href="{{ route('inicio') }}" class="enlace-nav enlace-nav--claro"><span aria-hidden="true">&larr;</span> Inicio</a>
                    @else
                        <a href="{{ route('noticias') }}" class="enlace-nav enlace-nav--claro"><span aria-hidden="true">&larr;</span> Noticias</a>
                    @endif

                    {{-- Fecha: la tabla de noticias no guarda fecha. Si se agrega, va aquí como etiqueta pequeña. --}}

                    <h1 class="noticia-hero__titulo" data-reveal="lineas">{{ $noticia->titulo }}</h1>
                </div>

                <div class="noticia-hero__abajo {{ $debajo->isNotEmpty() ? 'noticia-hero__abajo--sigue' : '' }}" data-reveal="fade-up">
                    @foreach ($enFoto as $parrafo)
                        <p>{{ $parrafo }}</p>
                    @endforeach

                    @if ($debajo->isNotEmpty())
                        <a href="#noticiaContinuacion" class="enlace-nav enlace-nav--claro">Leer más <span aria-hidden="true">&darr;</span></a>
                    @endif
                </div>
            </div>
        </header>

        {{-- CONTINUACIÓN del artículo (solo si no cupo en la portada) --}}
        @if ($debajo->isNotEmpty())
            <section class="noticia-cuerpo" id="noticiaContinuacion" aria-label="Continuación de la noticia">
                <div class="container">
                    <div class="noticia-cuerpo__texto">
                        @foreach ($debajo as $parrafo)
                            <p>{{ $parrafo }}</p>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
