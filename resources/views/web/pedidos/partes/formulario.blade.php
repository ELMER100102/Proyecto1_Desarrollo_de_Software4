{{-- Formulario clásico de respaldo (sin JavaScript). Con el panel activo, panel-pedido.css lo oculta. --}}
<div id="avisoCarrito" class="alert alert-warning d-none" role="status"></div>

<form id="formPedido" action="{{ route('pedido.store') }}" method="POST" data-validar-form novalidate>
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
            <div class="pedido-caja" id="tuPedido">
                <h3 class="h5 mb-3">Tu pedido</h3>

                <p id="pedidoVacio" class="mb-3">
                    Aún no has agregado productos.
                    <a href="#carta">Elige tus productos</a>
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
            <div class="pedido-caja" id="tusDatos">
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
                        maxlength="80" value="{{ old('cliente') }}" autocomplete="name" required
                        data-validar="requerido|maxlen:80" data-msg-requerido="Escribe tu nombre."
                        data-msg-maxlen="El nombre puede tener hasta 80 caracteres.">
                </div>

                <div class="form-group mb-3">
                    <label for="campoTelefono" id="etiquetaTelefono">Teléfono</label>
                    <input type="tel" id="campoTelefono" name="telefono" class="form-control"
                        maxlength="25" value="{{ old('telefono') }}" autocomplete="tel"
                        {{-- pedido.js cambia data-validar a "requerido|telefono" en llevar y domicilio --}}
                        data-validar="telefono"
                        data-msg-requerido="Necesitamos un teléfono para avisarte cuando esté listo."
                        data-msg-telefono="El teléfono solo puede tener números, espacios, + y guiones (de 7 a 25 caracteres).">
                </div>

                @if ($config->usa_mesas && $config->acepta_local)
                    <div class="form-group mb-3" data-tipos="local">
                        <label for="campoMesa">Número de mesa</label>
                        <input type="text" id="campoMesa" name="mesa" class="form-control"
                            maxlength="20" inputmode="numeric" value="{{ old('mesa') }}"
                            data-validar="requerido|entero|min:1|max:999|patron" data-patron="^[1-9][0-9]{0,2}$"
                            data-solo-digitos data-msg-requerido="Indica tu número de mesa."
                            data-msg="La mesa debe ser un número entre 1 y 999.">
                    </div>
                @endif

                @if ($config->acepta_domicilio)
                    <div class="form-group mb-3" data-tipos="domicilio">
                        <label for="campoDireccion">Dirección de entrega</label>
                        <textarea id="campoDireccion" name="direccion" class="form-control"
                            rows="2" maxlength="255" data-validar="requerido|maxlen:255"
                            data-msg-requerido="Escribe la dirección de entrega."
                            data-msg-maxlen="La dirección puede tener hasta 255 caracteres.">{{ old('direccion') }}</textarea>
                    </div>
                @endif

                <div class="form-group mb-4">
                    <label for="nota">Nota (opcional)</label>
                    <input type="text" id="nota" name="nota" class="form-control"
                        maxlength="255" value="{{ old('nota') }}"
                        placeholder="Ej.: sin azúcar, para regalo..." data-validar="maxlen:255"
                        data-msg-maxlen="La nota del pedido puede tener hasta 255 caracteres.">
                </div>

                {{-- validaciones.js escribe aquí "Revisa N campos." --}}
                <div class="validar-error mb-2" data-validar-resumen role="status"></div>

                <button type="submit" id="btnEnviarPedido" class="btn btn-primary btn-block w-100" disabled>
                    Enviar pedido
                </button>
            </div>
        </div>

    </div>
</form>
