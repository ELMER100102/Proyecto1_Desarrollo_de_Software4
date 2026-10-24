@extends('web.layouts.app')

@section('titulo', 'Menú')

@section('contenido')
    <section class="ftco-section">
        <div class="container">

            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 heading-section text-center">
                    <h2 class="mb-4">Nuestro Menú Artesanal</h2>
                    <p class="flip"><span class="deg1"></span><span class="deg2"></span><span class="deg3"></span></p>
                    <p class="mt-5">
                        Explora nuestra selección diaria de panes recién horneados, repostería dulce y salada,
                        y deliciosas bebidas preparadas al momento. Todo elaborado con ingredientes de la más alta
                        calidad para endulzar y acompañar cada uno de tus momentos.
                    </p>
                </div>
            </div>

            @if ($categorias->isEmpty())
                <p class="text-center">Pronto tendremos productos disponibles.</p>
            @else
                <section class="ftco-menu">
                    <div class="container-fluid">
                        <div class="row d-md-flex">

                            <div class="col-12 p-md-5">
                                <div class="row">

                                    {{-- PESTAÑAS --}}
                                    @php
                                        $limite = 5; // cuántas pestañas se ven directamente
                                        $visibles = $categorias->take($limite);
                                        $ocultas = $categorias->slice($limite);
                                    @endphp

                                    <div class="col-md-12 nav-link-wrap mb-5">
                                        <div class="nav nav-pills" id="v-pills-tab" role="tablist">

                                            <a class="nav-link active" id="v-pills-0-tab" data-toggle="pill"
                                                data-bs-toggle="pill" href="#v-pills-0" role="tab"
                                                aria-controls="v-pills-0" aria-selected="true">Todos</a>

                                            @foreach ($visibles as $categoria)
                                                <a class="nav-link" id="v-pills-{{ $categoria->id_categoria }}-tab"
                                                    data-toggle="pill" data-bs-toggle="pill"
                                                    href="#v-pills-{{ $categoria->id_categoria }}" role="tab"
                                                    aria-controls="v-pills-{{ $categoria->id_categoria }}"
                                                    aria-selected="false">{{ $categoria->nombre }}</a>
                                            @endforeach

                                            {{-- Las demás categorías van dentro de "Más" --}}
                                            @if ($ocultas->isNotEmpty())
                                                <div class="dropdown">
                                                    <a class="nav-link dropdown-toggle" id="tab-mas" href="#"
                                                        role="button" data-toggle="dropdown" data-bs-toggle="dropdown"
                                                        aria-expanded="false">Más</a>

                                                    <div class="dropdown-menu" aria-labelledby="tab-mas">
                                                        @foreach ($ocultas as $categoria)
                                                            <a class="dropdown-item"
                                                                id="v-pills-{{ $categoria->id_categoria }}-tab"
                                                                data-toggle="pill" data-bs-toggle="pill"
                                                                href="#v-pills-{{ $categoria->id_categoria }}"
                                                                role="tab"
                                                                aria-controls="v-pills-{{ $categoria->id_categoria }}"
                                                                aria-selected="false">{{ $categoria->nombre }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                    </div>

                                    {{-- PANELES --}}
                                    <div class="col-md-12 d-flex align-items-center">
                                        <div class="tab-content w-100" id="v-pills-tabContent">

                                            {{-- Todos: recorre $todos (antes usaba $categoria, que aquí no existe) --}}
                                            <div class="tab-pane fade show active" id="v-pills-0" role="tabpanel"
                                                aria-labelledby="v-pills-0-tab">
                                                <div class="row">
                                                    @foreach ($todos as $producto)
                                                        @include('web.parts.producto-card', [
                                                            'producto' => $producto,
                                                        ])
                                                    @endforeach
                                                </div>
                                            </div>

                                            @foreach ($categorias as $categoria)
                                                <div class="tab-pane fade" id="v-pills-{{ $categoria->id_categoria }}"
                                                    role="tabpanel"
                                                    aria-labelledby="v-pills-{{ $categoria->id_categoria }}-tab">
                                                    <div class="row">
                                                        @foreach ($categoria->productos as $producto)
                                                            @include('web.parts.producto-card', [
                                                                'producto' => $producto,
                                                            ])
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            @endif

        </div>
    </section>
@endsection

<section>
    <script>
        $(document).on('click', '.dropdown-menu .dropdown-item', function() {
            $('#tab-mas').addClass('active');
        });
        $(document).on('click', '.nav-pills > .nav-link', function() {
            $('#tab-mas').removeClass('active');
        });
    </script>
</section>
