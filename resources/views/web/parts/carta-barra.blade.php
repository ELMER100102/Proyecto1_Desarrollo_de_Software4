{{-- Barra fija de categorías (menú y "Mi pedido"). Requiere: $categorias y $etiqueta (aria-label) --}}
<nav class="carta-barra" id="cartaBarra" aria-label="{{ $etiqueta }}">
    <ul class="carta-barra__lista">
        @foreach ($categorias as $categoria)
            <li>
                <a class="carta-barra__enlace"
                    href="#{{ $categoria->ancla() }}">{{ $categoria->nombre }}</a>
            </li>
        @endforeach
    </ul>
</nav>
