@extends('web.layouts.app')

@section('titulo', 'Inicio')

@section('contenido')

    {{-- CARROUSEL 2 --}}
    <section class="home-slider owl-carousel img" style="background-image: url(images/bg_1.jpg);">

        @forelse ($slides as $item)
            <div class="slider-item" style="background-image: url('{{ asset('storage/' . $item->imagen) }}');">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

                        <div class="col-md-7 col-sm-12 text-center ftco-animate">
                            <span class="subheading">Bienvenido</span>
                            <h1 class="mb-4 ">{{ $item->titulo }}</h1>
                            <p class="mb-4 mb-md-5">{{ $item->descripcion }}</p>
                            <p><a href="{{ route('menu') }}" class="btn btn-primary p-3 px-xl-4 py-xl-3">Ver el menú</a>
                                <a
                                    href="{{ route('contacto') }}"class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Contáctanos</a>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="slider-item" style="background-image: url('{{ asset('storage/' . $item->imagen) }}');">
                <div class="overlay"></div>
                <div class="container">
                    <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

                        <div class="col-md-7 col-sm-12 text-center ftco-animate">
                            <span class="subheading">Bienvenido</span>
                            {{ $institucion?->nombre_empresa ? ' a ' . $institucion->nombre_empresa : '' }}
                            <p class="lead">Pan y repostería hechos con cariño.</p>

                            <p><a href="{{ route('menu') }}" class="btn btn-primary p-3 px-xl-4 py-xl-3">Ver el menú</a>
                                <a
                                    href="{{ route('contacto') }}"class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Contáctanos</a>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        @endforelse
    </section>

    <!-- ÚLTIMAS NOTICIAS -->
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 heading-section ftco-animate text-center">
                    <h2 class="mb-4">Conoce nuestras últimas novedades</h2>
                    <p class="flip"><span class="deg1"></span><span class="deg2"></span><span class="deg3"></span></p>
                    <p class="mt-5">
                        Descubre las últimas noticias, nuevos sabores y deliciosas creaciones
                        que tenemos para vos. En nuestra panadería trabajamos cada día con
                        dedicación y cariño para llevar a tu mesa productos frescos y de calidad.
                    </p>
                </div>
            </div>
            <div class="row d-flex">
                @foreach ($noticias as $item)
                    <div class="col-md-4 d-flex ftco-animate">
                        <div class="blog-entry align-self-stretch">

                            <a href="{{ route('noticia', $item->id_noticia) }}" class="block-20"
                                style="background-image: url('{{ asset('storage/' . $item->imagen) }}');">
                            </a>

                            <div class="text py-4 d-block">
                                <h3 class="heading mt-2">
                                    <a href="{{ route('noticia', $item->id_noticia) }}">
                                        {{ $item->titulo }}
                                    </a>
                                </h3>

                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

