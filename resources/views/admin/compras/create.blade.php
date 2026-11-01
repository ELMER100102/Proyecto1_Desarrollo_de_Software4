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
    {{-- Datos para compra.js --}}
    <script>
        const productos = @json($productos);
        const previos = @json(old('items', [])); // si la validación falló, se recupera el detalle
    </script>
    <script src="{{ asset('assets/admin/js/compra.js') }}?v={{ filemtime(public_path('assets/admin/js/compra.js')) }}"></script>
@endsection
