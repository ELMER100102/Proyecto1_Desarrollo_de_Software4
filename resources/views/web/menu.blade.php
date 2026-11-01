@extends('web.layouts.app')

@section('titulo', 'Menú')

@section('contenido')
    {{-- ENCABEZADO DE LA CARTA --}}
    <header class="carta-encabezado">
        <div class="container">
            <div class="carta-encabezado__fila">
                {{-- EDITAR: etiqueta izquierda (texto provisional) --}}
                <span class="carta-etiqueta">La carta</span>

                <h1 class="carta-encabezado__titulo" data-reveal="lineas">Menú</h1>

                {{-- EDITAR: etiqueta derecha (texto provisional) --}}
                <span class="carta-etiqueta">Pan · Repostería · Bebidas</span>
            </div>

            {{-- EDITAR: frase bajo el título (texto provisional) --}}
            <p class="carta-encabezado__frase" data-reveal="blur-in">Recién horneado, cada día.</p>
        </div>
    </header>

    @if ($categorias->isEmpty())
        <div class="container">
            <p class="carta-vacia">Pronto tendremos productos disponibles.</p>
        </div>
    @else
        {{-- CATEGORÍAS: todas en un solo recorrido. La carta es informativa:
             para pedir se usa la página "Mi pedido". --}}
        <div class="carta" id="carta">
            @foreach ($categorias as $categoria)
                <section class="carta-cat" id="{{ $categoria->ancla() }}"
                    aria-labelledby="{{ $categoria->ancla() }}-titulo">
                    <div class="container">
                        <div class="carta-cat__linea" data-reveal="linea"></div>

                        <div class="carta-cat__rejilla">
                            <div class="carta-cat__cabeza">
                                <h2 class="carta-cat__titulo" id="{{ $categoria->ancla() }}-titulo"
                                    tabindex="-1" data-reveal="blur-in">{{ $categoria->nombre }}</h2>

                                {{-- Frase corta solo si la categoría tiene descripción --}}
                                @if (!empty($categoria->descripcion))
                                    <p class="carta-cat__frase" data-reveal="blur-in">{{ $categoria->descripcion }}</p>
                                @endif
                            </div>

                            <ul class="carta-platos" data-reveal="stagger">
                                @foreach ($categoria->productos as $producto)
                                    <li class="carta-plato {{ $producto->disponible ? '' : 'carta-plato--agotado' }}">
                                        <div class="carta-plato__linea1">
                                            <h3 class="carta-plato__nombre">{{ $producto->nombre }}</h3>

                                            <span class="carta-plato__precio">
                                                @if (!$producto->disponible)
                                                    <span class="carta-plato__estado">Agotado</span>
                                                @elseif (!is_null($producto->stock) && $producto->stock <= 5)
                                                    <span class="carta-plato__estado carta-plato__estado--ultimas">Últimas unidades</span>
                                                @endif
                                                <span>₡{{ number_format($producto->precio, 0, ',', '.') }}</span>
                                            </span>
                                        </div>

                                        @if ($producto->descripcion)
                                            <p class="carta-plato__desc">{{ $producto->descripcion }}</p>
                                        @endif
                                    </li>
                                @endforeach

                                {{-- Con cantidad impar, una celda vacía mantiene las líneas de la cuadrícula --}}
                                @if ($categoria->productos->count() % 2 === 1)
                                    <li class="carta-plato carta-plato--vacio" aria-hidden="true"></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        {{-- BARRA DE CATEGORÍAS FIJA (abajo) + píldora del pedido --}}
        <div class="carta-dock" id="cartaDock">
            @include('web.parts.carta-barra', ['etiqueta' => 'Categorías del menú'])

            @if ($pedidosConfig?->disponible())
                <a class="carta-pildora" id="cartaPildora" href="{{ route('pedido.create') }}#tuPedido" hidden>
                    Ver pedido · <span class="contador-carrito">0</span>
                </a>
            @endif
        </div>
    @endif
@endsection
