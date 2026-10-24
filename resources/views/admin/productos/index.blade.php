@extends('admin.layouts.app')

@section('titulo', 'Gestión de Productos')
@section('encabezado', 'Gestión de Productos')

@section('boton')
    <a href="{{ route('admin.productos.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nuevo producto
    </a>
@endsection

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th class="no-sort" style="width: 90px;">Imagen</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end" title="Último costo de compra">Costo</th>
                        <th class="text-center"
                            title="(Precio − costo) / precio. Aproximado: ambos tal como se pagan o se cobran.">Margen</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Disponible</th>
                        <th class="text-center">Visible</th>
                        <th class="no-sort text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>
                                @if ($item->imagen)
                                    <img src="{{ asset('storage/' . $item->imagen) }}" class="rounded"
                                        style="width: 70px; height: 50px; object-fit: cover;" alt="{{ $item->nombre }}">
                                @else
                                    <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted"
                                        style="width: 70px; height: 50px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->nombre }}</div>
                                @if ($item->descripcion)
                                    <div class="small text-muted">{{ $item->descripcion }}</div>
                                @endif
                            </td>
                            <td>{{ $item->categoria->nombre }}</td>
                            <td class="text-end" data-order="{{ $item->precio }}">
                                ₡{{ number_format($item->precio, 0, ',', '.') }}
                            </td>

                            {{-- Costo (última compra) y margen aproximado --}}
                            <td class="text-end" data-order="{{ $item->costo ?? -1 }}">
                                {{ $item->costo !== null ? '₡' . number_format($item->costo, 0, ',', '.') : '—' }}
                            </td>
                            @php
                                $margen =
                                    $item->costo !== null && $item->precio > 0
                                        ? round((($item->precio - $item->costo) / $item->precio) * 100)
                                        : null;
                            @endphp
                            <td class="text-center" data-order="{{ $margen ?? -1000 }}">
                                @if ($margen === null)
                                    <span class="text-muted">—</span>
                                @elseif ($margen < 0)
                                    <span class="badge text-bg-danger">{{ $margen }}%</span>
                                @elseif ($margen < 20)
                                    <span class="badge text-bg-warning">{{ $margen }}%</span>
                                @else
                                    <span class="badge text-bg-success">{{ $margen }}%</span>
                                @endif
                            </td>

                            {{-- Stock: NULL = sin control, 0 = agotado, 1-5 = pocas unidades --}}
                            <td class="text-center" data-order="{{ $item->stock ?? -1 }}">
                                @if (is_null($item->stock))
                                    <span class="text-muted">—</span>
                                @elseif ($item->stock == 0)
                                    <span class="badge text-bg-danger">0</span>
                                @elseif ($item->stock <= 5)
                                    <span class="badge text-bg-warning">{{ $item->stock }}</span>
                                @else
                                    <span class="badge text-bg-light border">{{ $item->stock }}</span>
                                @endif
                            </td>

                            {{-- Interruptores de un clic --}}
                            <td class="text-center" data-order="{{ $item->disponible ? 1 : 0 }}">
                                <div class="form-check form-switch d-flex justify-content-center m-0">
                                    <input class="form-check-input switch-producto" type="checkbox" role="switch"
                                        data-url="{{ route('admin.productos.alternar', $item) }}" data-campo="disponible"
                                        aria-label="Disponible: {{ $item->nombre }}" @checked($item->disponible)>
                                </div>
                            </td>
                            <td class="text-center" data-order="{{ $item->visible ? 1 : 0 }}">
                                <div class="form-check form-switch d-flex justify-content-center m-0">
                                    <input class="form-check-input switch-producto" type="checkbox" role="switch"
                                        data-url="{{ route('admin.productos.alternar', $item) }}" data-campo="visible"
                                        aria-label="Visible: {{ $item->nombre }}" @checked($item->visible)>
                                </div>
                            </td>

                            <td class="text-center text-nowrap">
                                <a href="{{ route('admin.movimientos.index', ['producto' => $item->id_producto]) }}"
                                    class="btn btn-sm btn-outline-dark" title="Historial de stock">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <a href="{{ route('admin.productos.edit', $item) }}" class="btn btn-sm btn-dark"
                                    title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.productos.destroy', $item) }}" method="POST"
                                    class="d-inline form-eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).on('change', '.switch-producto', function () {
        var $sw = $(this); //interruptor del evento
        var estado = $sw.is(':checked'); //comprueba si el cb este marcado

        $sw.prop('disabled', true); //evita que se haga clic mientras Laravel procesa la solicitud.

        $.ajax({
            url: $sw.data('url'),
            type: 'POST',
            data: {
                _method: 'PATCH',
                _token: $('meta[name="csrf-token"]').attr('content'),
                campo: $sw.data('campo'),
                valor: estado ? 1 : 0
            }
        })
        .done(function (r) {
            if (r && typeof r.valor !== 'undefined') {
                $sw.prop('checked', !!r.valor);
            }
        })
        .fail(function () {
            $sw.prop('checked', !estado);
            alert('No se pudo guardar el cambio. Intenta de nuevo.');
        })
        .always(function () {
            $sw.prop('disabled', false);
        });
    });
</script>
@endsection
