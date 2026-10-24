@extends('admin.layouts.app')

@section('titulo', 'Compras')
@section('encabezado', 'Compras')

@section('boton')
    <a href="{{ route('admin.compras.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nueva compra
    </a>
@endsection

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('aviso'))
        <div class="alert alert-warning">{{ session('aviso') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- RESUMEN (las anuladas no cuentan) --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Compras registradas</div>
                    <div class="fs-4 fw-semibold">{{ $totalCompras }}</div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Monto comprado</div>
                    <div class="fs-4 fw-semibold">₡{{ number_format($montoTotal, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover align-middle datatable" data-order='[[0,"desc"]]'>
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Documento</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="no-sort text-center" style="width: 90px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr class="{{ $item->estaAnulada() ? 'text-muted' : '' }}">
                            <td>{{ $item->codigo() }}</td>
                            <td data-order="{{ $item->fecha->timestamp }}">{{ $item->fecha->format('d/m/Y') }}</td>
                            <td>{{ $item->proveedor->nombre }}</td>
                            <td>{{ $item->documento ?: '—' }}</td>
                            <td class="text-end" data-order="{{ $item->total }}">
                                <span class="{{ $item->estaAnulada() ? 'text-decoration-line-through' : '' }}">
                                    ₡{{ number_format($item->total, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($item->estaAnulada())
                                    <span class="badge text-bg-secondary">Anulada</span>
                                @else
                                    <span class="badge text-bg-success">Registrada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @unless ($item->estaAnulada())
                                    <form action="{{ route('admin.compras.anular', $item) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Anular la compra #{{ $item->codigo() }}? Se descontará del stock lo que había sumado.');">
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