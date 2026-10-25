{{-- resources/views/admin/pedidos/_lista.blade.php
     Tarjetas de pedidos. Requiere: $pedidos y $ver. La usan index y el refresco automático. --}}
@php
    $colores = [
        'pendiente'  => 'warning',
        'preparando' => 'info',
        'listo'      => 'success',
        'entregado'  => 'secondary',
        'cancelado'  => 'danger',
    ];
    $textoBoton = [
        'preparando' => 'Empezar a preparar',
        'listo'      => 'Marcar listo',
        'entregado'  => 'Marcar entregado',
    ];
@endphp

@forelse ($pedidos as $pedido)
    @php
        $color = $colores[$pedido->estado] ?? 'secondary';
        $siguiente = $pedido->siguienteEstado();
        $minutos = (int) abs(now()->diffInMinutes($pedido->fecha));
    @endphp

    <div class="col-md-6 col-xl-4">
        <div class="card h-100 border-start border-4 border-{{ $color }}">

            <div class="card-header d-flex justify-content-between align-items-start gap-2">
                <div>
                    <span class="h5 mb-0">{{ $pedido->turnoVisible() }}</span>
                    <span class="badge text-bg-{{ $color }} ms-1">{{ $pedido->etiquetaEstado() }}</span>
                    <div class="small text-body-secondary">
                        {{ $pedido->etiquetaTipo() }}@if ($pedido->mesa) · Mesa {{ $pedido->mesa }}@endif
                    </div>
                </div>
                <div class="text-end small text-body-secondary">
                    {{ $pedido->fecha->format('H:i') }}
                    @if ($pedido->estaAbierto())
                        <br>hace {{ $minutos }} min
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="fw-semibold">{{ $pedido->cliente }}</div>

                @if ($pedido->telefono)
                    <div class="small">
                        <i class="bi bi-telephone"></i>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $pedido->telefono) }}">{{ $pedido->telefono }}</a>
                    </div>
                @endif
                @if ($pedido->direccion)
                    <div class="small"><i class="bi bi-geo-alt"></i> {{ $pedido->direccion }}</div>
                @endif
                @if ($pedido->nota)
                    <div class="small fst-italic text-body-secondary mt-1">«{{ $pedido->nota }}»</div>
                @endif

                <ul class="list-unstyled my-3">
                    @foreach ($pedido->detalles as $d)
                        <li class="d-flex justify-content-between">
                            <span><strong>{{ $d->cantidad }}</strong> × {{ $d->descripcion }}</span>
                            <span class="text-body-secondary">₡{{ number_format($d->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="d-flex justify-content-between fw-bold border-top pt-2">
                    <span>Total</span>
                    <span>₡{{ number_format($pedido->total, 0, ',', '.') }}</span>
                </div>
            </div>

            @if ($pedido->estaAbierto() && $siguiente)
                <div class="card-footer bg-transparent d-flex gap-2">
                    <form action="{{ route('admin.pedidos.estado', $pedido->id_pedido) }}" method="POST" class="flex-grow-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ $siguiente }}">
                        <button type="submit" class="btn btn-dark w-100">{{ $textoBoton[$siguiente] }}</button>
                    </form>

                    <form action="{{ route('admin.pedidos.estado', $pedido->id_pedido) }}" method="POST"
                        onsubmit="return confirm('¿Cancelar el {{ $pedido->turnoVisible() }}?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="cancelado">
                        <button type="submit" class="btn btn-outline-danger">Cancelar</button>
                    </form>
                </div>
            @endif

            {{-- Pedido entregado: acceso directo a su comprobante --}}
            @if ($pedido->estado === 'entregado' && $pedido->id_factura)
                <div class="card-footer bg-transparent d-flex gap-2">
                    <a href="{{ route('admin.facturas.pdf.ticket', $pedido->id_factura) }}" target="_blank"
                        class="btn btn-outline-secondary flex-grow-1">
                        <i class="bi bi-receipt"></i> Ticket
                    </a>
                    <a href="{{ route('admin.facturas.pdf.carta', $pedido->id_factura) }}" target="_blank"
                        class="btn btn-outline-secondary flex-grow-1">
                        <i class="bi bi-file-earmark-pdf"></i> Carta
                    </a>
                </div>
            @endif

        </div>
    </div>
@empty
    <div class="col-12">
        <p class="text-center text-body-secondary py-5 mb-0">
            {{ $ver === 'hoy' ? 'Hoy todavía no hay pedidos.' : 'No hay pedidos abiertos. ¡Todo al día!' }}
        </p>
    </div>
@endforelse
