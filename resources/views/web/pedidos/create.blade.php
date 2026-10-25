{{-- resources/views/web/pedido/crear.blade.php
     Página "Mi pedido". Requiere: $config (ConfigPedido) y $productos (mapa id => datos). --}}
@extends('web.layouts.app')

@section('titulo', 'Mi pedido')

@section('contenido')
    <section class="ftco-section">
        <div class="container">

            <div class="row justify-content-center mb-4">
                <div class="col-md-8 text-center">
                    <h2 class="mb-2">Mi pedido</h2>
                    <p>Revisa tu pedido y completa tus datos. Pagas directamente en el local.</p>
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

                <div id="avisoCarrito" class="alert alert-warning d-none" role="status"></div>

                <form id="formPedido" action="{{ route('pedido.store') }}" method="POST">
                    @csrf

                    {{-- Campo trampa para robots: está oculto, una persona nunca lo llena --}}
                    <div class="d-none" aria-hidden="true">
                        <label for="sitio_web">No llenar este campo</label>
                        <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- El JavaScript escribe aquí items[id]=cantidad al enviar --}}
                    <div id="itemsOcultos"></div>

                    <div class="row">

                        {{-- Lista de productos --}}
                        <div class="col-lg-7 mb-4">
                            <div class="pedido-caja">
                                <h3 class="h5 mb-3">Tu pedido</h3>

                                <p id="pedidoVacio" class="mb-3">
                                    Aún no has agregado productos.
                                    <a href="{{ route('menu') }}">Ver el menú</a>
                                </p>

                                <div id="tablaPedido" class="table-responsive d-none">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th class="text-right text-end">Subtotal</th>
                                                <th><span class="sr-only visually-hidden">Quitar</span></th>
                                            </tr>
                                        </thead>
                                        <tbody id="lineasPedido"></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-right text-end">Total</th>
                                                <th class="text-right text-end" id="totalPedido">₡0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Datos del cliente --}}
                        <div class="col-lg-5 mb-4">
                            <div class="pedido-caja">
                                <h3 class="h5 mb-3">Tus datos</h3>

                                {{-- Solo aparecen los tipos que el negocio activó en el admin --}}
                                <fieldset class="mb-3">
                                    <legend class="h6">¿Cómo quieres tu pedido?</legend>
                                    @php $tipoMarcado = old('tipo', array_key_first($config->tiposActivos())); @endphp
                                    @foreach ($config->tiposActivos() as $clave => $etiqueta)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipo"
                                                id="tipo_{{ $clave }}" value="{{ $clave }}"
                                                @checked($tipoMarcado === $clave) required>
                                            <label class="form-check-label" for="tipo_{{ $clave }}">{{ $etiqueta }}</label>
                                        </div>
                                    @endforeach
                                </fieldset>

                                <div class="form-group mb-3">
                                    <label for="cliente">Nombre</label>
                                    <input type="text" id="cliente" name="cliente" class="form-control"
                                        maxlength="80" value="{{ old('cliente') }}" autocomplete="name" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="campoTelefono" id="etiquetaTelefono">Teléfono</label>
                                    <input type="tel" id="campoTelefono" name="telefono" class="form-control"
                                        maxlength="25" value="{{ old('telefono') }}" autocomplete="tel">
                                </div>

                                @if ($config->usa_mesas && $config->acepta_local)
                                    <div class="form-group mb-3" data-tipos="local">
                                        <label for="campoMesa">Número de mesa</label>
                                        <input type="text" id="campoMesa" name="mesa" class="form-control"
                                            maxlength="20" value="{{ old('mesa') }}">
                                    </div>
                                @endif

                                @if ($config->acepta_domicilio)
                                    <div class="form-group mb-3" data-tipos="domicilio">
                                        <label for="campoDireccion">Dirección de entrega</label>
                                        <textarea id="campoDireccion" name="direccion" class="form-control"
                                            rows="2" maxlength="255">{{ old('direccion') }}</textarea>
                                    </div>
                                @endif

                                <div class="form-group mb-4">
                                    <label for="nota">Nota (opcional)</label>
                                    <input type="text" id="nota" name="nota" class="form-control"
                                        maxlength="255" value="{{ old('nota') }}"
                                        placeholder="Ej.: sin azúcar, para regalo...">
                                </div>

                                <button type="submit" id="btnEnviarPedido" class="btn btn-primary btn-block w-100" disabled>
                                    Enviar pedido
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            @endif

        </div>
    </section>
@endsection
