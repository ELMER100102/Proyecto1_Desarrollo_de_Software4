{{-- VENTANA DE PEDIDO: la llena modal-pedido.js con los data-* de la tarjeta que se tocó --}}
<dialog class="modal-pedido" id="modalPedido" aria-labelledby="modalPedidoTitulo"
    data-url-pedido="{{ route('pedido.create') }}#tuPedido">
    <div class="modal-pedido__marco">

        {{-- Vista 1: detalle del producto --}}
        <div class="modal-pedido__vista" data-vista="detalle">
            {{-- Cabecera compacta: aparece cuando la foto sale de la vista --}}
            <div class="modal-pedido__compacta" aria-hidden="true">
                <span class="modal-pedido__compacta-nombre" data-campo="nombre"></span>
            </div>
            <button type="button" class="modal-pedido__cerrar" data-cerrar aria-label="Cerrar">
                <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M3 3l10 10M13 3L3 13" /></svg>
            </button>

            <div class="modal-pedido__cuerpo">
                <figure class="modal-pedido__foto">
                    <img alt="" width="520" height="325" decoding="async">
                </figure>
                <div class="modal-pedido__centinela" aria-hidden="true"></div>

                <div class="modal-pedido__info">
                    <h2 class="modal-pedido__nombre" id="modalPedidoTitulo" tabindex="-1" data-campo="nombre"></h2>
                    <p class="modal-pedido__precio" data-campo="precio"></p>
                    <p class="modal-pedido__desc" data-campo="descripcion"></p>
                </div>

                {{-- Aquí irá la franja "Personaliza tu pedido" (opciones como nivel de picante o
                     ingredientes a quitar) cuando el servidor la soporte: mejora futura --}}

                {{-- Nota por producto: se guarda en el carrito y llega al pedido, al admin y al seguimiento --}}
                <div class="modal-pedido__tarjeta modal-pedido__nota">
                    <label class="modal-pedido__etiqueta" for="modalNota">Instrucciones especiales</label>
                    <textarea class="modal-pedido__campo" id="modalNota" rows="3" maxlength="200"
                        data-validar="maxlen:200" data-msg-maxlen="La nota de un producto puede tener hasta 200 caracteres."
                        placeholder="Ej.: sin azúcar, bien tostado…"
                        aria-describedby="modalNotaContador modalNotaAviso"></textarea>
                    <span class="modal-pedido__contador" id="modalNotaContador">0/200</span>
                    <p class="modal-pedido__aviso-nota" id="modalNotaAviso">
                        <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><circle cx="8" cy="8" r="6.5" /><path d="M8 7.2v4M8 4.8v.1" /></svg>
                        {{-- EDITAR: aviso bajo las instrucciones especiales --}}
                        <span>Haremos lo posible por atender tu solicitud, pero no podemos garantizar todos los cambios.</span>
                    </p>
                </div>

                <div class="modal-pedido__tarjeta modal-pedido__cantidad">
                    <span class="modal-pedido__etiqueta" id="modalCantidadEtiqueta">Cantidad</span>
                    <div class="modal-pedido__selector" role="group" aria-labelledby="modalCantidadEtiqueta">
                        <button type="button" class="modal-pedido__paso" data-menos aria-label="Quitar una unidad">
                            <svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M3 8h10" /></svg>
                        </button>
                        <output class="modal-pedido__numero" aria-live="polite">1</output>
                        <button type="button" class="modal-pedido__paso" data-mas aria-label="Agregar una unidad">
                            <svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M8 3v10M3 8h10" /></svg>
                        </button>
                    </div>
                    <p class="modal-pedido__tope" data-campo="tope" hidden></p>
                </div>

                <p class="modal-pedido__error" role="alert" hidden></p>
            </div>

            <div class="modal-pedido__pie">
                <button type="button" class="modal-pedido__confirmar">
                    <span>Agregar al pedido</span>
                    <span data-campo="total"></span>
                </button>
            </div>
        </div>

        {{-- Vista 2: confirmación --}}
        <div class="modal-pedido__vista modal-pedido__vista--confirmacion" data-vista="confirmacion" hidden>
            <button type="button" class="modal-pedido__cerrar modal-pedido__cerrar--fijo" data-cerrar aria-label="Cerrar">
                <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M3 3l10 10M13 3L3 13" /></svg>
            </button>

            <div class="modal-pedido__cuerpo">
                <div class="modal-pedido__hecho">
                    <svg class="modal-pedido__check" viewBox="0 0 52 52" width="56" height="56" aria-hidden="true">
                        <circle cx="26" cy="26" r="24" />
                        <path d="M15 27l7 7 15-16" />
                    </svg>
                    <p class="modal-pedido__mensaje" tabindex="-1" data-campo="mensaje"></p>
                    <div class="modal-pedido__acciones">
                        <a class="btn modal-pedido__ver" data-ver-pedido href="{{ route('pedido.create') }}#tuPedido">Ver mi pedido</a>
                        <button type="button" class="btn" data-cerrar>Seguir eligiendo</button>
                    </div>
                </div>

                <section class="modal-pedido__tambien" aria-labelledby="modalTambienTitulo" hidden>
                    <h3 class="modal-pedido__etiqueta" id="modalTambienTitulo">También piden</h3>
                    <ul class="modal-pedido__lista"></ul>
                </section>
            </div>
        </div>

    </div>
</dialog>
