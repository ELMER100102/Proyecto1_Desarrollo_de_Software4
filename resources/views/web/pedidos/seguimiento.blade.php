{{-- resources/views/web/pedido/seguimiento.blade.php
     Requiere: $pedido (con detalles) y $config. --}}
@extends('web.layouts.app')

@section('titulo', 'Seguimiento de tu pedido')

@section('contenido')
    @php
        $pasos = ['pendiente' => 'Recibido', 'preparando' => 'Preparando', 'listo' => 'Listo', 'entregado' => 'Entregado'];
        $indice = array_search($pedido->estado, array_keys($pasos), true);   // false si está cancelado
    @endphp

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">

                    {{-- data-limpiar="1": al recién crear el pedido, se vacía el carrito del navegador --}}
                    <div class="pedido-caja" id="seguimiento"
                        data-url="{{ route('pedido.estado', $pedido->codigo) }}"
                        data-limpiar="{{ session('nuevo') ? '1' : '0' }}"
                        data-abierto="{{ $pedido->estaAbierto() ? '1' : '0' }}"
                        data-estado="{{ $pedido->estado }}"
                        data-turno="{{ $pedido->turnoVisible() }}">

                        @if (session('nuevo'))
                            <div class="alert alert-success" role="status">¡Pedido registrado! Guarda esta página para ver cómo va.</div>
                        @endif

                        <p class="mb-0 text-muted">{{ $pedido->etiquetaTipo() }}
                            @if ($pedido->mesa) · Mesa {{ $pedido->mesa }} @endif
                        </p>
                        <h2 class="mb-1">{{ $pedido->turnoVisible() }}</h2>
                        <p class="mb-3">A nombre de <strong>{{ $pedido->cliente }}</strong></p>

                        <p class="h5 mb-1">Estado: <span id="estadoTexto">{{ $pedido->etiquetaEstado() }}</span></p>
                        <p id="estadoMensaje" class="mb-3" aria-live="polite">{{ $pedido->mensajeSeguimiento() }}</p>

                        {{-- Solo aparece si el navegador soporta notificaciones y aún no se decidió --}}
                        <p class="mb-3 d-none" id="bloqueAvisos">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAvisos">
                                <i class="bi bi-bell"></i> Avisarme cuando cambie
                            </button>
                        </p>

                        <ol class="pasos-pedido {{ $pedido->estado === 'cancelado' ? 'd-none' : '' }}" id="pasosPedido">
                            @foreach ($pasos as $clave => $etiqueta)
                                <li data-paso="{{ $clave }}"
                                    class="{{ $indice !== false && $loop->index < $indice ? 'hecho' : '' }} {{ $indice !== false && $loop->index === $indice ? 'actual' : '' }}">
                                    {{ $etiqueta }}
                                </li>
                            @endforeach
                        </ol>
                        <p id="avisoCancelado" class="text-danger {{ $pedido->estado === 'cancelado' ? '' : 'd-none' }}">
                            Este pedido fue cancelado.
                        </p>

                        <hr>

                        <ul class="list-unstyled mb-3">
                            @foreach ($pedido->detalles as $d)
                                <li class="d-flex justify-content-between">
                                    <span>{{ $d->cantidad }} × {{ $d->descripcion }}</span>
                                    <span>₡{{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="d-flex justify-content-between font-weight-bold fw-bold border-top pt-2">
                            <span>Total</span>
                            <span>₡{{ number_format($pedido->total, 0, ',', '.') }}</span>
                        </p>

                        @if ($pedido->tipo === 'domicilio' && $pedido->direccion)
                            <p class="small mb-1"><strong>Entrega en:</strong> {{ $pedido->direccion }}</p>
                        @endif
                        @if ($pedido->nota)
                            <p class="small mb-1"><strong>Nota:</strong> {{ $pedido->nota }}</p>
                        @endif
                        @if ($config->mensaje_final)
                            <p class="small mt-3 mb-0">{{ $config->mensaje_final }}</p>
                        @endif
                    </div>

                    <p class="text-center mt-4">
                        <a href="{{ route('menu') }}" class="btn btn-primary">Volver al menú</a>
                    </p>

                </div>
            </div>
        </div>
    </section>
@endsection
