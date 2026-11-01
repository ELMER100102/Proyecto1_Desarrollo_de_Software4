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
    {{-- Datos para factura-venta.js --}}
    <script>
        const productos = @json($productos);
        const previos = @json(old('items', [])); // si la validación falló, se recupera el carrito
    </script>
    <script src="{{ asset('assets/admin/js/factura-venta.js') }}?v={{ filemtime(public_path('assets/admin/js/factura-venta.js')) }}"></script>
@endsection
