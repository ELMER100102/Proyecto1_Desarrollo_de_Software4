@extends('web.layouts.app')

@section('titulo', 'Noticias')

@section('contenido')

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 heading-section ftco-animate text-center">
                    <h2 class="mb-4">Nuestras Noticias</h2>
                    <p class="flip"><span class="deg1"></span><span class="deg2"></span><span class="deg3"></span></p>
                    <p class="mt-5">
                        Mantente al día con las últimas noticias de nuestra panadería.
                        Descubre nuevos productos, promociones especiales, eventos y todo
                        lo que tenemos preparado para endulzar tus días.
                    </p>
                </div>
            </div>
            <div class="d-flex flex-column gap-4">
                @forelse ($noticias as $item)
                    <article class="row g-3 pb-4 mb-4 border-bottom">
                        <div class="col-12 col-md-3">
                            <img src="{{ asset('storage/' . $item->imagen) }}" class="img-fluid rounded" alt="Noticia">
                        </div>
                        <div class="col-12 col-md-9">
                            <h3 class="h5">{{ $item->titulo }}</h3>
                            <p>{{ $item->contenido }}</p>
                            <a href="{{ route('noticia', $item->id_noticia) }}" class="btn btn-primary btn-sm">Leer más</a>
                        </div>
                    </article>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No hay imágenes para mostrar.</p>
                    </div>
                @endforelse

            </div>

            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-center">
                    <div class="block-27 text-center">
                        {{ $noticias->links() }}
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection
