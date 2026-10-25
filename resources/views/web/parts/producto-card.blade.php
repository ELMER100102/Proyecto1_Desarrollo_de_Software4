{{-- Tarjeta de producto con el estilo de la plantilla. Requiere: $producto
     $pedidosConfig lo comparte el View::composer de web.* (ConfigPedido o null). --}}
<div class="col-md-4 text-center">
    <div class="menu-wrap {{ $producto->disponible ? '' : 'opacity-75' }}">

        <div class="position-relative">
            @if ($producto->imagen)
                <div class="menu-img img mb-4"
                    style="background-image: url('{{ asset('storage/' . $producto->imagen) }}');"
                    role="img" aria-label="{{ $producto->nombre }}"></div>
            @else
                <div class="menu-img img mb-4 d-flex align-items-center justify-content-center text-muted">
                    <i class="bi bi-image fs-1"></i>
                </div>
            @endif

            @if (!$producto->disponible)
                <span class="etiqueta-producto etiqueta-agotado">Agotado</span>
            @elseif (!is_null($producto->stock) && $producto->stock <= 5)
                <span class="etiqueta-producto etiqueta-ultimas">Últimas unidades</span>
            @endif
        </div>

        <div class="text">
            <h3>{{ $producto->nombre }}</h3>

            @if ($producto->descripcion)
                <p>{{ $producto->descripcion }}</p>
            @endif

            <p class="price"><span>₡{{ number_format($producto->precio, 0, ',', '.') }}</span></p>

            {{-- Solo si el negocio recibe pedidos y el producto se puede pedir --}}
            @if ($pedidosConfig?->disponible() && $producto->disponible)
                <p>
                    <button type="button" class="btn btn-white btn-outline-white btn-agregar-pedido"
                        data-id="{{ $producto->id_producto }}">Agregar</button>
                </p>
            @endif
        </div>

    </div>
</div>
