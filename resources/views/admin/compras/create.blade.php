@extends('admin.layouts.app')

@section('titulo', 'Nueva compra')
@section('encabezado', 'Nueva compra')

@section('boton')
    <a href="{{ route('admin.compras.index') }}" class="btn btn-outline-secondary">
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

    <form id="formCompra" action="{{ route('admin.compras.store') }}" method="POST">
        @csrf
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Datos de la compra</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="id_proveedor" class="form-label">Proveedor</label>
                    <select id="id_proveedor" name="id_proveedor"
                        class="form-select @error('id_proveedor') is-invalid @enderror" required>
                        <option value="" disabled @selected(!old('id_proveedor'))>Seleccione un proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id_proveedor }}" @selected(old('id_proveedor') == $proveedor->id_proveedor)>
                                {{ $proveedor->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        ¿No está? <a href="{{ route('admin.proveedores.index') }}">Agregue el proveedor</a> primero.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" id="fecha" name="fecha" max="{{ now()->format('Y-m-d') }}"
                            class="form-control @error('fecha') is-invalid @enderror"
                            value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-6">
                        <label for="documento" class="form-label">N.º factura proveedor</label>
                        <input type="text" id="documento" name="documento" maxlength="50"
                            class="form-control @error('documento') is-invalid @enderror" value="{{ old('documento') }}"
                            placeholder="Opcional">
                    </div>
                </div>

                <div class="mt-3">
                    <label for="nota" class="form-label">Nota</label>
                    <input type="text" id="nota" name="nota" maxlength="255"
                        class="form-control @error('nota') is-invalid @enderror" value="{{ old('nota') }}"
                        placeholder="Opcional">
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">

            {{-- IZQUIERDA: datos de la compra + productos --}}
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header bg-white">
                        <h3 class="h6 mb-0">Productos</h3>
                    </div>
                    <div class="card-body">
                        <input type="search" id="buscarProducto" class="form-control mb-3" autocomplete="off"
                            placeholder="Buscar producto o categoría">
                        <div id="listaProductos" class="list-group" style="max-height: 380px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>

            {{-- DERECHA: detalle --}}
            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header bg-white">
                        <h3 class="h6 mb-0">Detalle de la compra</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                            <table id="carrito" class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th style="width: 110px;">Cantidad</th>
                                        <th style="width: 140px;">Costo unitario</th>
                                        <th class="text-end" style="width: 130px;">Subtotal</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end fs-4 fw-semibold">
                            <span class="me-4">Total</span>
                            <span id="totalCompra">₡0,00</span>
                        </div>
                        <div class="form-text text-end">Escriba el costo tal como lo pagó al proveedor.</div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.compras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" id="btnGuardar" class="btn btn-dark" disabled>Guardar compra</button>
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
        const previos = @json(old('items', [])); // si la validación falló, se recupera el detalle
        const carrito = {}; // id_producto -> { cantidad, costo }

        const dinero = n => '₡' + Number(n).toLocaleString('es-CR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const redondear = n => Math.round(n * 100) / 100;
        const porId = id => productos.find(p => p.id === Number(id));

        /* ---------- LISTA DE PRODUCTOS ---------- */
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
                const stock = p.stock === null ? 'Sin control de stock' : 'Stock: ' + p.stock;
                const costo = p.costo === null ? '' : ' · Último costo: ' + dinero(p.costo);

                $lista.append(
                    $('<button>', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action agregar-producto',
                        'data-id': p.id
                    })
                    .append($('<span>', {
                        class: 'fw-semibold',
                        text: p.nombre
                    }))
                    .append($('<small>', {
                        class: 'd-block text-muted',
                        text: p.categoria + ' · ' + stock + costo
                    }))
                );
            });
        }

        /* ---------- DETALLE ---------- */
        // Se dibuja solo al agregar o quitar productos; al escribir cantidades
        // solo se recalculan los totales (así el cursor no se pierde).
        function pintarCarrito() {
            const $tbody = $('#carrito tbody').empty();
            const ids = Object.keys(carrito);

            ids.forEach(function(id) {
                const p = porId(id);
                const linea = carrito[id];

                const $nombre = $('<td>')
                    .append($('<div>', {
                        text: p.nombre
                    }))
                    .append($('<input>', {
                        type: 'hidden',
                        name: 'items[' + id + '][id_producto]',
                        value: id
                    }));

                if (p.stock === null) {
                    $nombre.append($('<small>', {
                        class: 'text-warning-emphasis',
                        text: 'Sin control de stock: se registra la compra, pero el stock no cambia.'
                    }));
                }

                $tbody.append(
                    $('<tr>')
                    .append($nombre)
                    .append($('<td>').append($('<input>', {
                        type: 'number',
                        min: 1,
                        step: 1,
                        required: true,
                        class: 'form-control form-control-sm in-cantidad',
                        name: 'items[' + id + '][cantidad]',
                        'data-id': id,
                        value: linea.cantidad
                    })))
                    .append($('<td>').append($('<input>', {
                        type: 'number',
                        min: 0,
                        step: '0.01',
                        required: true,
                        class: 'form-control form-control-sm in-costo',
                        name: 'items[' + id + '][costo]',
                        'data-id': id,
                        value: linea.costo
                    })))
                    .append($('<td>', {
                        class: 'text-end',
                        id: 'linea-' + id
                    }))
                    .append($('<td>', {
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
                    ))
                );
            });

            if (ids.length === 0) {
                $tbody.append($('<tr>').append($('<td>', {
                    colspan: 5,
                    class: 'text-center text-muted py-4',
                    text: 'Toque un producto de la lista para agregarlo.'
                })));
            }

            recalcular();
        }

        function recalcular() {
            let total = 0;

            Object.keys(carrito).forEach(function(id) {
                const linea = redondear(carrito[id].cantidad * carrito[id].costo);
                total += linea;
                $('#linea-' + id).text(dinero(linea));
            });

            $('#totalCompra').text(dinero(total));
            $('#btnGuardar').prop('disabled', Object.keys(carrito).length === 0);
        }

        /* ---------- EVENTOS ---------- */
        $('#buscarProducto').on('input', pintarProductos);
        $('#buscarProducto').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        $(document).on('click', '.agregar-producto', function() {
            const id = $(this).data('id');
            const p = porId(id);

            if (carrito[id]) {
                carrito[id].cantidad += 1;
            } else {
                carrito[id] = {
                    cantidad: 1,
                    costo: p.costo !== null ? p.costo : 0
                };
            }
            pintarCarrito();
        });

        $(document).on('click', '.btn-quitar', function() {
            delete carrito[$(this).data('id')];
            pintarCarrito();
        });

        $(document).on('input', '.in-cantidad', function() {
            carrito[$(this).data('id')].cantidad = parseInt($(this).val(), 10) || 0;
            recalcular();
        });

        $(document).on('input', '.in-costo', function() {
            carrito[$(this).data('id')].costo = parseFloat($(this).val()) || 0;
            recalcular();
        });

        // Evita el doble clic (dos compras iguales)
        $('#formCompra').on('submit', function() {
            $('#btnGuardar').prop('disabled', true).text('Guardando...');
        });

        /* ---------- INICIO ---------- */
        Object.keys(previos).forEach(function(id) {
            if (porId(id)) {
                carrito[id] = {
                    cantidad: parseInt(previos[id].cantidad, 10) || 1,
                    costo: parseFloat(previos[id].costo) || 0
                };
            }
        });

        pintarProductos();
        pintarCarrito();
    </script>
@endsection
