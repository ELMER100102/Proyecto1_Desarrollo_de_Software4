@extends('web.layouts.app')

@section('titulo', 'Galería')

@section('contenido')
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 heading-section ftco-animate text-center">
                    <h2 class="mb-4">Galería</h2>
                    <p class="flip"><span class="deg1"></span><span class="deg2"></span><span class="deg3"></span></p>
                    <p class="mt-5">
                        Echa un vistazo a nuestros hornos y delicias recién hechas.
                        Descubre la textura de nuestro pan artesanal, la frescura de
                        nuestra repostería y cada rincón preparado con amor para ti.
                    </p>
                </div>
            </div>
            <div class="container py-5">
                <div class="row g-3">
                    @forelse ($fotos as $item)
                        <div class="col-6 col-md-4 col-lg-3 ftco-animate">
                            <!-- Imagen con el efecto de Fancybox y la plantilla de fondo -->
                            <a href="{{ asset('storage/' . $item->imagen) }}" data-fancybox="galeria"
                                data-caption="{{ $item->descripcion }}"
                                class="gallery img d-flex align-items-center rounded"
                                style="background-image: url('{{ asset('storage/' . $item->imagen) }}'); height: 220px; background-size: cover; background-position: center;">
                                <div class="icon mb-4 d-flex align-items-center justify-content-center">
                                    <span class="icon-search"></span>
                                </div>
                            </a>

                            <p class="small mt-2 mb-0 text-center">{{ $item->descripcion }}</p>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No hay imágenes para mostrar.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
