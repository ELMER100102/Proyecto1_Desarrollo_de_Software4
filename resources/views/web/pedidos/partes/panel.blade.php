{{-- PANEL LATERAL DEL PEDIDO (dos pasos): 1 Tu pedido (lo dibuja panel-pedido.js con el carrito
     y los data-* de las tarjetas) y 2 Tus datos (formulario que envía el pedido).
     Si el servidor devolvió errores, el panel se abre solo en el paso que corresponde. --}}
@php
    $tipos = $config->tiposActivos();
    $tipoInicial = old('tipo', array_key_exists('local', $tipos) ? 'local' : array_key_first($tipos));
    // Errores del carrito (items / notas) → paso 1; los demás → paso 2
    $clavesPedido = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'items') || str_starts_with($k, 'notas'));
    $clavesDatos = collect($errors->keys())->diff($clavesPedido);
    $pasoError = $clavesPedido->isNotEmpty() ? 'pedido' : ($clavesDatos->isNotEmpty() ? 'datos' : '');
    $usaTelefono = array_key_exists('llevar', $tipos) || array_key_exists('domicilio', $tipos);
    $usaMesa = $config->usa_mesas && array_key_exists('local', $tipos);
    $usaDireccion = array_key_exists('domicilio', $tipos);
@endphp
<dialog class="panel-pedido" id="panelPedido" aria-labelledby="panelPedidoTitulo" data-abrir="{{ $pasoError }}">
    <div class="panel-pedido__marco">
        <header class="panel-pedido__cabecera">
            <span class="panel-pedido__icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18"><path d="M5 8h14l-1 12H6L5 8z" /><path d="M9 8V6a3 3 0 0 1 6 0v2" /></svg>
            </span>
            <h2 class="panel-pedido__titulo" id="panelPedidoTitulo" tabindex="-1">Tu pedido</h2>
            <button type="button" class="panel-pedido__cerrar" data-cerrar aria-label="Cerrar">
                <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M3 3l10 10M13 3L3 13" /></svg>
            </button>
        </header>

        {{-- Indicador de pasos --}}
        <ol class="panel-indicador" aria-label="Pasos del pedido">
            <li data-indicador="1" aria-current="step">1 Tu pedido</li>
            <li class="panel-indicador__guion" aria-hidden="true">—</li>
            <li data-indicador="2">2 Tus datos</li>
        </ol>
        <p class="sr-only" id="panelAnuncio" aria-live="polite"></p>

        <div class="panel-pedido__pasos">
            {{-- PASO 1: Tu pedido --}}
            <div class="panel-pedido__paso" data-paso="1">
                <div class="panel-pedido__cuerpo">
                    @if ($clavesPedido->isNotEmpty())
                        <div class="panel-pedido__error" role="alert">
                            @foreach ($clavesPedido as $clave)
                                @foreach ($errors->get($clave) as $mensaje)
                                    <p>{{ $mensaje }}</p>
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    <ul class="panel-pedido__lineas" aria-label="Productos en tu pedido"></ul>

                    {{-- Vacío --}}
                    <div class="panel-pedido__vacio" hidden>
                        <svg viewBox="0 0 24 24" width="40" height="40" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8z" /><path d="M9 8V6a3 3 0 0 1 6 0v2" /></svg>
                        <p>Tu pedido está vacío</p>
                        <button type="button" class="btn" data-elegir>Elegir productos</button>
                    </div>

                    <section class="panel-pedido__algo" aria-labelledby="panelAlgoTitulo" hidden>
                        <h3 class="panel-pedido__subtitulo" id="panelAlgoTitulo">¿Algo más?</h3>
                        <ul class="panel-pedido__sugerencias"></ul>
                    </section>
                </div>
            </div>

            {{-- PASO 2: Tus datos --}}
            <div class="panel-pedido__paso" data-paso="2" hidden>
                <div class="panel-pedido__cuerpo">
                    <form class="panel-datos" id="formPanel" action="{{ route('pedido.store') }}" method="POST"
                        data-validar-form novalidate data-hay-old="{{ session()->hasOldInput() ? 1 : 0 }}">
                        @csrf

                        {{-- validaciones.js escribe aquí "Revisa N campos." --}}
                        <div class="validar-error" data-validar-resumen role="status"></div>

                        @if ($clavesDatos->isNotEmpty())
                            <div class="panel-pedido__error" role="alert">
                                <p>Revisa estos datos:</p>
                                <ul>
                                    @foreach ($clavesDatos as $clave)
                                        <li>{{ $errors->first($clave) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Campo trampa para robots: está oculto, una persona nunca lo llena --}}
                        <div class="d-none" aria-hidden="true">
                            <label for="pSitioWeb">No llenar este campo</label>
                            <input type="text" id="pSitioWeb" name="sitio_web" tabindex="-1" autocomplete="off">
                        </div>

                        {{-- Productos y notas del carrito: los escribe panel-pedido.js al enviar --}}
                        <div id="pItems"></div>

                        {{-- 1. Tipo de pedido (solo los activos; con uno solo, no se pregunta) --}}
                        @if (count($tipos) > 1)
                            <fieldset class="campo" aria-describedby="pErrTipo">
                                <legend class="campo__etiqueta">¿Cómo quieres tu pedido?</legend>
                                <div class="campo__opciones">
                                    @foreach ($tipos as $clave => $etiqueta)
                                        <label class="opcion-tipo">
                                            <input type="radio" name="tipo" value="{{ $clave }}" @checked($tipoInicial === $clave)>
                                            <span>{{ $etiqueta }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="campo__error" id="pErrTipo" role="alert" @unless ($errors->has('tipo')) hidden @endunless>{{ $errors->first('tipo') }}</p>
                            </fieldset>
                        @else
                            <input type="hidden" name="tipo" value="{{ array_key_first($tipos) }}">
                        @endif

                        {{-- 2. Nombre --}}
                        <div class="campo">
                            <label class="campo__etiqueta" for="pCliente">Nombre</label>
                            <input class="campo__control" type="text" id="pCliente" name="cliente" maxlength="80"
                                autocomplete="name" value="{{ old('cliente') }}" aria-describedby="pErrCliente"
                                data-validar="requerido|maxlen:80" data-msg-requerido="Escribe tu nombre."
                                data-msg-maxlen="El nombre puede tener hasta 80 caracteres."
                                @error('cliente') aria-invalid="true" @enderror>
                            <p class="campo__error" id="pErrCliente" role="alert" @unless ($errors->has('cliente')) hidden @endunless>{{ $errors->first('cliente') }}</p>
                        </div>

                        {{-- 3. Teléfono (para llevar y a domicilio) --}}
                        @if ($usaTelefono)
                            <div class="campo" data-tipos="llevar,domicilio">
                                <label class="campo__etiqueta" for="pTelefono">Teléfono</label>
                                <input class="campo__control" type="tel" id="pTelefono" name="telefono" maxlength="25"
                                    inputmode="tel" autocomplete="tel" pattern="[0-9+\-\s\(\)]{7,25}"
                                    value="{{ old('telefono') }}" aria-describedby="pAyudaTel pErrTelefono"
                                    {{-- Solo existe en llevar y domicilio, donde siempre es obligatorio --}}
                                    data-validar="requerido|telefono"
                                    data-msg-requerido="Necesitamos un teléfono para avisarte cuando esté listo."
                                    data-msg-telefono="El teléfono solo puede tener números, espacios, + y guiones (de 7 a 25 caracteres)."
                                    @error('telefono') aria-invalid="true" @enderror>
                                <p class="campo__ayuda" id="pAyudaTel">Lo usamos solo para avisarte de este pedido.</p>
                                <p class="campo__error" id="pErrTelefono" role="alert" @unless ($errors->has('telefono')) hidden @endunless>{{ $errors->first('telefono') }}</p>
                            </div>
                        @endif

                        {{-- 4. Mesa (comer aquí, si el negocio usa mesas). ?mesa= en la URL la precarga (QR por mesa) --}}
                        @if ($usaMesa)
                            <div class="campo" data-tipos="local">
                                <label class="campo__etiqueta" for="pMesa">Número de mesa</label>
                                <input class="campo__control" type="text" id="pMesa" name="mesa" maxlength="20"
                                    inputmode="numeric" value="{{ old('mesa', request('mesa')) }}" aria-describedby="pErrMesa"
                                    {{-- patron = la misma regex que el servidor y chk_pedidos_mesa (rechaza "01") --}}
                                    data-validar="requerido|entero|min:1|max:999|patron" data-patron="^[1-9][0-9]{0,2}$"
                                    data-solo-digitos data-msg-requerido="Indica tu número de mesa."
                                    data-msg="La mesa debe ser un número entre 1 y 999."
                                    @error('mesa') aria-invalid="true" @enderror>
                                <p class="campo__error" id="pErrMesa" role="alert" @unless ($errors->has('mesa')) hidden @endunless>{{ $errors->first('mesa') }}</p>
                            </div>
                        @endif

                        {{-- 5. Dirección (a domicilio) --}}
                        @if ($usaDireccion)
                            <div class="campo" data-tipos="domicilio">
                                <label class="campo__etiqueta" for="pDireccion">Dirección de entrega</label>
                                <textarea class="campo__control" id="pDireccion" name="direccion" rows="3" maxlength="255"
                                    autocomplete="street-address" aria-describedby="pErrDireccion"
                                    data-validar="requerido|maxlen:255" data-msg-requerido="Escribe la dirección de entrega."
                                    data-msg-maxlen="La dirección puede tener hasta 255 caracteres."
                                    @error('direccion') aria-invalid="true" @enderror>{{ old('direccion') }}</textarea>
                                <p class="campo__error" id="pErrDireccion" role="alert" @unless ($errors->has('direccion')) hidden @endunless>{{ $errors->first('direccion') }}</p>
                            </div>
                        @endif

                        {{-- 6. Nota general del pedido (no es la nota por producto) --}}
                        <div class="campo campo--nota">
                            <button type="button" class="campo__desplegar" aria-expanded="{{ old('nota') ? 'true' : 'false' }}"
                                aria-controls="pNotaCaja">Agregar una nota al pedido</button>
                            <div id="pNotaCaja" @unless (old('nota')) hidden @endunless>
                                <label class="campo__etiqueta" for="pNota">Nota del pedido (opcional)</label>
                                <textarea class="campo__control" id="pNota" name="nota" rows="2" maxlength="255"
                                    aria-describedby="pErrNota" data-validar="maxlen:255"
                                    data-msg-maxlen="La nota del pedido puede tener hasta 255 caracteres.">{{ old('nota') }}</textarea>
                                <p class="campo__error" id="pErrNota" role="alert" @unless ($errors->has('nota')) hidden @endunless>{{ $errors->first('nota') }}</p>
                            </div>
                        </div>

                        {{-- 7. Recordar nombre, teléfono y tipo en este dispositivo (la dirección no se guarda) --}}
                        <label class="campo__casilla" for="pRecordar">
                            <input type="checkbox" id="pRecordar">
                            <span>Recordar mis datos en este dispositivo</span>
                        </label>
                    </form>
                </div>
            </div>
        </div>

        {{-- Pie fijo: cambia su contenido según el paso --}}
        <footer class="panel-pedido__pie" hidden>
            <div data-pie="1">
                <p class="panel-pedido__total">
                    <span>Total <small>IVA incluido</small></span>
                    <span data-total></span>
                </p>
                {{-- Sin JavaScript es un enlace al formulario de esta página (respaldo) --}}
                <a href="{{ route('pedido.create') }}#tusDatos" class="panel-pedido__finalizar" data-continuar>
                    <span>Continuar</span>
                    <span class="panel-pedido__finalizar-total"><span data-total></span></span>
                </a>
                <p class="panel-pedido__aviso">Pagas directamente en el local.</p>
            </div>
            <div data-pie="2" hidden>
                <p class="panel-pedido__total">
                    <span data-resumen></span>
                </p>
                <div class="panel-pedido__botones">
                    <button type="button" class="btn" data-volver>Volver</button>
                    <button type="submit" form="formPanel" class="panel-pedido__finalizar" data-enviar>
                        <span data-enviar-texto>Enviar pedido</span>
                        <span><span data-total></span></span>
                    </button>
                </div>
                <p class="panel-pedido__aviso">Pagas directamente en el local.</p>
            </div>
        </footer>
    </div>
</dialog>
