@extends('web.layouts.app')

@section('titulo', 'Noticia')

@section('contenido')
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">

                {{-- RECONOCE LA RUTA ANTERIOR Y ACOMODA LA RUTA Y EL BOTON ACORDE A LA PAGINA PREVIA
                 --}}
                @if (rtrim(url()->previous(), '/') === rtrim(route('inicio'), '/'))
                    <a href="{{ route('inicio') }}" class="btn btn-outline-secondary btn-sm mb-4">&larr; Volver al inicio</a>
                @else
                    <a href="{{ route('noticias') }}" class="btn btn-outline-secondary btn-sm mb-4">&larr; Volver a
                        noticias</a>
                @endif

                <article>
                    <h1 class="h2 mb-4">{{ $noticia->titulo }}</h1>

                    <img src="{{ asset('storage/' . $noticia->imagen) }}" class="img-fluid rounded mb-4" alt="{{ $noticia->titulo }}">

                    <div>
                        <p>{{ $noticia->contenido }}</p>
                    </div>
                </article>

            </div>
        </div>
    </section>
@endsection
