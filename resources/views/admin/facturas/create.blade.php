@extends('admin.layouts.app')

@section('titulo', 'Nueva venta')
@section('encabezado', 'Nueva venta')

@section('boton')
    <a href="{{ route('admin.facturas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
@endsection

@section('contenido')
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formVenta" action="{{ route('admin.facturas.store') }}" method="POST">
        @csrf
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Datos de la venta</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="tipo_pago" class="form-label">Tipo de pago</label>
                    <select id="tipo_pago" name="tipo_pago" class="form-select @error('tipo_pago') is-invalid @enderror">
                        <option value="contado" @selected(old('tipo_pago', 'contado') === 'contado')>Contado</option>
                        <option value="credito" @selected(old('tipo_pago') === 'credito')>Crédito</option>
                    </select>
                </div>

                <div>
                    <label for="cliente" class="form-label">
                        Cliente <span id="clienteObligatorio" class="text-danger d-none">*</span>
                    </label>
                    <input type="text" id="cliente" name="cliente" maxlength="150"
                        class="form-control @error('cliente') is-invalid @enderror" value="{{ old('cliente') }}"
                        placeholder="Cliente de contado">
                    <div class="form-text">Opcional al contado; obligatorio a crédito.</div>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header bg-white">
                        <h3 class="h6 mb-0">Productos</h3>
                    </div>
                    <div class="card-body">
                        <input type="search" id="buscarProducto" class="form-control mb-3" autocomplete="off"
                            placeholder="Buscar producto o categoría">
                        <div id="aviso" class="alert alert-warning py-2 d-none"></div>
                        <div id="listaProductos" class="list-group" style="max-height: 420px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header bg-white">
                        <h3 class="h6 mb-0">Detalle de la venta</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                            <table id="carrito" class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 140px;">Cantidad</th>
                                        <th class="text-end">Total</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <table class="text-end">
                                <tr>
                                    <td class="text-muted pe-4">Subtotal (sin IVA)</td>
                                    <td id="subtotalVenta">₡0,00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted pe-4">IVA incluido</td>
                                    <td id="ivaVenta">₡0,00</td>
                                </tr>
                                <tr class="fs-4 fw-semibold">
                                    <td class="pe-4">Total</td>
                                    <td id="totalVenta">₡0,00</td>
                                </tr>
                            </table>
                        </div>
                        <div id="items"></div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.facturas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" id="btnGuardar" class="btn btn-dark" disabled>Guardar venta</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection

@section('scripts')
    <script>
        const productos = @json($productos);
        const previos = @json(old('items', [])); // si la validación falló, se recupera el carrito
        const carrito = {}; // id_producto -> cantidad
        let temporizadorAviso = null;

        const dinero = n => '₡' + Number(n).toLocaleString('es-CR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const porId = id => productos.find(p => p.id === Number(id));

        function avisar(texto) {
            clearTimeout(temporizadorAviso);
            $('#aviso').text(texto).removeClass('d-none');
            temporizadorAviso = setTimeout(() => $('#aviso').addClass('d-none'), 3500);
        }

        /* ---------- LISTA DE PRODUCTOS (filtra mientras se escribe) ---------- */
        function pintarProductos() {
            const texto = $('#buscarProducto').val().trim().toLowerCase();
            const $lista = $('#listaProductos').empty();

            const visibles = productos.filter(p =>
                p.nombre.toLowerCase().includes(texto) || p.categoria.toLowerCase().includes(texto)
            );

            if (visibles.length === 0) {
                $lista.append($('<div>', {
                    class: 'text-muted text-center py-4',
                    text: 'No hay productos que coincidan.'
                }));
                return;
            }

            visibles.forEach(function(p) {
                const detalle = p.categoria + (p.stock !== null ? ' · Stock: ' + p.stock : '');

                $lista.append(
                    $('<button>', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action d-flex justify-content-between align-items-center agregar-producto',
                        'data-id': p.id
                    })
                    .append(
                        $('<span>')
                        .append($('<span>', {
                            class: 'fw-semibold',
                            text: p.nombre
                        }))
                        .append($('<small>', {
                            class: 'd-block text-muted',
                            text: detalle
                        }))
                    )
                    .append($('<span>', {
                        class: 'text-nowrap ms-3',
                        text: dinero(p.precio)
                    }))
                );
            });
        }

        /* ---------- CARRITO ---------- */
        function pintarCarrito() {
            const $tbody = $('#carrito tbody').empty();
            const $items = $('#items').empty();
            let total = 0,
                base = 0,
                indice = 0;

            Object.keys(carrito).forEach(function(id) {
                const p = porId(id);
                const cantidad = carrito[id];
                const linea = Math.round(p.precio * cantidad * 100) / 100;

                total += linea;
                base += linea / (1 + p.impuesto / 100);

                $tbody.append(
                    $('<tr>')
                    .append($('<td>').text(p.nombre))
                    .append(
                        $('<td>', {
                            class: 'text-center'
                        }).append(
                            $('<div>', {
                                class: 'btn-group btn-group-sm'
                            })
                            .append($('<button>', {
                                type: 'button',
                                class: 'btn btn-outline-secondary btn-menos',
                                'data-id': id,
                                text: '−'
                            }))
                            .append($('<span>', {
                                class: 'btn btn-light disabled px-3',
                                text: cantidad
                            }))
                            .append($('<button>', {
                                type: 'button',
                                class: 'btn btn-outline-secondary btn-mas',
                                'data-id': id,
                                text: '+'
                            }))
                        )
                    )
                    .append($('<td>', {
                        class: 'text-end',
                        text: dinero(linea)
                    }))
                    .append(
                        $('<td>', {
                            class: 'text-end'
                        }).append(
                            $('<button>', {
                                type: 'button',
                                class: 'btn btn-sm btn-outline-danger btn-quitar',
                                'data-id': id,
                                title: 'Quitar',
                                'aria-label': 'Quitar ' + p.nombre
                            }).append($('<i>', {
                                class: 'bi bi-trash'
                            }))
                        )
                    )
                );

                $items.append($('<input>', {
                    type: 'hidden',
                    name: 'items[' + indice + '][id_producto]',
                    value: id
                }));
                $items.append($('<input>', {
                    type: 'hidden',
                    name: 'items[' + indice + '][cantidad]',
                    value: cantidad
                }));
                indice++;
            });

            if (indice === 0) {
                $tbody.append(
                    $('<tr>').append(
                        $('<td>', {
                            colspan: 4,
                            class: 'text-center text-muted py-4',
                            text: 'Toque un producto de la lista para agregarlo.'
                        })
                    )
                );
            }

            $('#subtotalVenta').text(dinero(base));
            $('#ivaVenta').text(dinero(total - base));
            $('#totalVenta').text(dinero(total));
            $('#btnGuardar').prop('disabled', indice === 0);
        }

        function cambiar(id, delta) {
            const p = porId(id);
            if (!p) {
                return;
            }

            const nueva = (carrito[id] || 0) + delta;

            if (nueva <= 0) {
                delete carrito[id];
            } else if (p.stock !== null && nueva > p.stock) {
                avisar('Solo quedan ' + p.stock + ' de "' + p.nombre + '".');
                return;
            } else {
                carrito[id] = nueva;
            }
            pintarCarrito();
        }

        /* ---------- EVENTOS ---------- */
        $('#buscarProducto').on('input', pintarProductos);

        // Enter en el buscador no debe enviar el formulario
        $('#buscarProducto').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        $(document).on('click', '.agregar-producto', function() {
            cambiar($(this).data('id'), 1);
        });
        $(document).on('click', '.btn-mas', function() {
            cambiar($(this).data('id'), 1);
        });
        $(document).on('click', '.btn-menos', function() {
            cambiar($(this).data('id'), -1);
        });
        $(document).on('click', '.btn-quitar', function() {
            delete carrito[$(this).data('id')];
            pintarCarrito();
        });

        // Crédito => el cliente es obligatorio
        function revisarCredito() {
            const credito = $('#tipo_pago').val() === 'credito';
            $('#cliente').prop('required', credito);
            $('#clienteObligatorio').toggleClass('d-none', !credito);
        }
        $('#tipo_pago').on('change', revisarCredito);

        // Evita el doble clic (dos comprobantes iguales)
        $('#formVenta').on('submit', function() {
            $('#btnGuardar').prop('disabled', true).text('Guardando...');
        });

        /* ---------- INICIO ---------- */
        previos.forEach(function(it) {
            if (porId(it.id_producto)) {
                carrito[it.id_producto] = (carrito[it.id_producto] || 0) + Number(it.cantidad);
            }
        });

        revisarCredito();
        pintarProductos();
        pintarCarrito();
    </script>
@endsection
