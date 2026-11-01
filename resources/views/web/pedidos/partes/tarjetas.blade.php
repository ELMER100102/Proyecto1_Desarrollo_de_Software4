{{-- PRODUCTOS POR CATEGORÍA: toda la tarjeta abre la ventana de pedido --}}
<div class="carta carta--pedido" id="carta">
    @foreach ($categorias as $categoria)
        <section class="carta-cat" id="{{ $categoria->ancla() }}"
            aria-labelledby="{{ $categoria->ancla() }}-titulo">
            <div class="container">
                <div class="carta-cat__linea" data-reveal="linea"></div>

                <div class="carta-cat__cabeza">
                    <h2 class="carta-cat__titulo" id="{{ $categoria->ancla() }}-titulo"
                        tabindex="-1" data-reveal="blur-in">{{ $categoria->nombre }}</h2>

                    {{-- Frase corta solo si la categoría tiene descripción --}}
                    @if (!empty($categoria->descripcion))
                        <p class="carta-cat__frase" data-reveal="blur-in">{{ $categoria->descripcion }}</p>
                    @endif
                </div>

                <ul class="carta-tarjetas" data-reveal="stagger">
                    @foreach ($categoria->productos as $producto)
                        @php
                            // Se puede pedir si el producto está disponible (aquí los pedidos ya están abiertos)
                            $pedible = $producto->disponible;
                            $pocas = $producto->disponible && !is_null($producto->stock) && $producto->stock <= 5;
                        @endphp
                        <li>
                            {{-- Pedible: toda la tarjeta es un botón (.abrir-pedido, NO .btn-agregar-pedido,
                                 que en pedido.js suma 1 al instante). Si no, es solo informativa. --}}
                            @if ($pedible)
                                <button type="button" class="carta-tarjeta abrir-pedido"
                                    data-id="{{ $producto->id_producto }}"
                                    data-nombre="{{ $producto->nombre }}"
                                    data-precio="{{ (float) $producto->precio }}"
                                    data-descripcion="{{ $producto->descripcion }}"
                                    data-imagen="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : '' }}"
                                    data-stock="{{ $producto->stock ?? '' }}"
                                    data-categoria="{{ $categoria->id_categoria }}">
                                    <span class="sr-only">Agregar al pedido: </span>
                            @else
                                <div class="carta-tarjeta carta-tarjeta--agotada">
                            @endif

                                <span class="carta-tarjeta__foto">
                                    @if ($producto->imagen)
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt=""
                                            width="136" height="136" loading="lazy" decoding="async">
                                    @else
                                        <span class="carta-tarjeta__inicial" aria-hidden="true">{{ mb_substr($producto->nombre, 0, 1) }}</span>
                                    @endif
                                </span>

                                <span class="carta-tarjeta__panel">
                                    <span class="carta-tarjeta__nombre">{{ $producto->nombre }}</span>

                                    @if ($producto->descripcion)
                                        <span class="carta-tarjeta__desc">{{ $producto->descripcion }}</span>
                                    @endif

                                    <span class="carta-tarjeta__pie">
                                        <span class="carta-tarjeta__precio">
                                            ₡{{ number_format($producto->precio, 0, ',', '.') }}
                                            @if (!$producto->disponible)
                                                <span class="carta-tarjeta__estado">Agotado</span>
                                            @elseif ($pocas)
                                                <span class="carta-tarjeta__estado">Últimas unidades</span>
                                            @endif
                                        </span>

                                        @if ($pedible)
                                            <span class="carta-tarjeta__mas" aria-hidden="true">
                                                <svg class="icono-mas" viewBox="0 0 16 16" width="14" height="14"><path d="M8 2v12M2 8h12" /></svg>
                                                <svg class="icono-check" viewBox="0 0 16 16" width="14" height="14"><path d="M2.5 8.5l3.5 3.5 7.5-8" /></svg>
                                            </span>
                                        @endif
                                    </span>
                                </span>

                            @if ($pedible)
                                </button>
                            @else
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endforeach
</div>
