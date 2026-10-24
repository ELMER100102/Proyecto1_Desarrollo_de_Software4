@extends('admin.layouts.app')

@section('titulo', 'Comprobantes de venta')
@section('encabezado', 'Comprobantes de venta')

@section('boton')
    <a href="{{ route('admin.facturas.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nueva venta
    </a>
@endsection

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success d-flex flex-wrap align-items-center gap-2">
            <span class="me-auto">{{ session('success') }}</span>

            {{-- Justo después de crear: imprimir con un clic --}}
            @if (session('imprimir'))
                <a href="{{ route('admin.facturas.pdf.carta', session('imprimir')) }}" target="_blank"
                    class="btn btn-sm btn-dark">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Carta
                </a>
                <a href="{{ route('admin.facturas.pdf.ticket', session('imprimir')) }}" target="_blank"
                    class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-receipt me-1"></i>Ticket
                </a>
            @endif
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- RESUMEN (las anuladas no cuentan) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Comprobantes emitidos</div>
                    <div class="fs-4 fw-semibold">{{ $totalFacturas }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Ventas de contado</div>
                    <div class="fs-4 fw-semibold">₡{{ number_format($totalContado, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Ventas a crédito</div>
                    <div class="fs-4 fw-semibold">₡{{ number_format($totalCredito, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- data-order="[[0,'desc']]" deja el más reciente arriba --}}
            <table class="table table-hover align-middle datatable" data-order='[[0,"desc"]]'>
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Pago</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="no-sort text-center" style="width: 190px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr class="{{ $item->estaAnulada() ? 'text-muted' : '' }}">
                            <td>{{ str_pad($item->numero, 6, '0', STR_PAD_LEFT) }}</td>
                            <td data-order="{{ $item->fecha->timestamp }}">{{ $item->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->cliente }}</td>
                            <td>{{ ucfirst($item->tipo_pago) }}</td>
                            <td class="text-end" data-order="{{ $item->total }}">
                                <span class="{{ $item->estaAnulada() ? 'text-decoration-line-through' : '' }}">
                                    ₡{{ number_format($item->total, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($item->estaAnulada())
                                    <span class="badge text-bg-secondary">Anulada</span>
                                @else
                                    <span class="badge text-bg-success">Emitida</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('admin.facturas.pdf.carta', $item) }}" target="_blank"
                                    class="btn btn-sm btn-dark" title="PDF tamaño carta">Carta</a>
                                <a href="{{ route('admin.facturas.pdf.ticket', $item) }}" target="_blank"
                                    class="btn btn-sm btn-outline-dark" title="Ticket de 80 mm">Ticket</a>

                                @unless ($item->estaAnulada())
                                    <form action="{{ route('admin.facturas.anular', $item) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Anular el comprobante #{{ $item->numero }}? Se devolverá el stock de los productos.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection