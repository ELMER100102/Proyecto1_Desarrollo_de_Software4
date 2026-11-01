{{-- resources/views/web/pedido/crear.blade.php
     Página "Mi pedido". Requiere: $config (ConfigPedido), $productos (mapa id => datos)
     y $categorias (categorías con sus productos visibles, para las tarjetas). --}}
@extends('web.layouts.app')

@section('titulo', 'Mi pedido')

@section('contenido')
    <section class="ftco-section">
        <div class="container">

            <div class="row justify-content-center mb-4">
                <div class="col-md-8 text-center">
                    <h2 class="mb-2">Mi pedido</h2>
                    <p>Elige tus productos, revisa tu pedido y completa tus datos. Pagas directamente en el local.</p>
                </div>
            </div>

            {{-- Lo llena pedido.js si el cliente tiene un pedido en curso en este navegador --}}
            <div id="pedidoActivo" class="alert alert-info text-center d-none">
                Tienes un pedido en curso.
                <a href="#" id="pedidoActivoEnlace" class="alert-link">Ver el seguimiento</a>
            </div>

            @if (! $config->disponible())
                <p class="text-center">Por el momento no estamos recibiendo pedidos desde el sitio.</p>
                <p class="text-center"><a href="{{ route('menu') }}" class="btn btn-primary">Ver el menú</a></p>
            @else
                {{-- Datos para el JavaScript (precios y existencias actuales) --}}
                <script type="application/json" id="datos-pedido">{!! json_encode(['productos' => $productos], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 pl-3 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>
    </section>

    @if ($config->disponible() && $categorias->isNotEmpty())
        @include('web.pedidos.partes.tarjetas')

        {{-- BARRA DE CATEGORÍAS FIJA (abajo) + píldora que baja al resumen del pedido --}}
        <div class="carta-dock" id="cartaDock">
            @include('web.parts.carta-barra', ['etiqueta' => 'Categorías de productos'])

            <a class="carta-pildora" id="cartaPildora" href="#tuPedido" hidden>
                Ver pedido · <span class="contador-carrito">0</span>
            </a>
        </div>

        @include('web.pedidos.partes.modal')
        @include('web.pedidos.partes.panel')
    @endif

    <section class="ftco-section pt-0">
        <div class="container">
            @if ($config->disponible())
                @include('web.pedidos.partes.formulario')
            @endif
        </div>
    </section>
@endsection
