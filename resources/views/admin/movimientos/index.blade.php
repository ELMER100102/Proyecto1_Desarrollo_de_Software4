@extends('admin.layouts.app')

@section('titulo', 'Movimientos de stock')
@section('encabezado', 'Movimientos de stock')

@section('contenido')
    {{-- FILTRO POR PRODUCTO --}}
    <form method="GET" action="{{ route('admin.movimientos.index') }}" class="row g-2 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <select name="producto" class="form-select" onchange="this.form.submit()" aria-label="Filtrar por producto">
                <option value="">Todos los productos</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id_producto }}" @selected($idProducto == $producto->id_producto)>
                        {{ $producto->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        @if ($idProducto)
            <div class="col-auto">
                <a href="{{ route('admin.movimientos.index') }}" class="btn btn-outline-secondary">Quitar filtro</a>
            </div>
        @endif
    </form>

    @php
        $etiquetas = [
            'venta'      => ['Venta', 'text-bg-primary'],
            'compra'     => ['Compra', 'text-bg-success'],
            'produccion' => ['Producción', 'text-bg-info'],
            'ajuste'     => ['Ajuste', 'text-bg-warning'],
            'anulacion'  => ['Anulación', 'text-bg-secondary'],
        ];
    @endphp

    <div class="card">
        <div class="card-body">
            <table class="table table-hover align-middle datatable" data-order='[[0,"desc"]]'>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Stock resultante</th>
                        <th>Referencia</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $m)
                        <tr>
                            <td data-order="{{ $m->fecha->timestamp }}" class="text-nowrap">
                                {{ $m->fecha->format('d/m/Y H:i') }}
                            </td>
                            <td>{{ $m->producto->nombre }}</td>
                            <td>
                                <span class="badge {{ $etiquetas[$m->tipo][1] }}">{{ $etiquetas[$m->tipo][0] }}</span>
                            </td>
                            <td class="text-end fw-semibold {{ $m->cantidad > 0 ? 'text-success' : 'text-danger' }}"
                                data-order="{{ $m->cantidad }}">
                                {{ $m->cantidad > 0 ? '+' : '' }}{{ $m->cantidad }}
                            </td>
                            <td class="text-end">{{ $m->stock_resultante }}</td>
                            <td>{{ $m->referencia ?: '—' }}</td>
                            <td>{{ $m->nota ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($data->count() >= 1000)
                <div class="form-text">Se muestran los últimos 1000 movimientos. Filtre por producto para ver más.</div>
            @endif
        </div>
    </div>
@endsection