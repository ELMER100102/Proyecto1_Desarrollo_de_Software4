@extends('admin.layouts.app')

@section('titulo', 'Editar Categoría')
@section('encabezado', 'Editar Categoría')

@section('contenido')
    <div class="row g-4 align-items-start">

        {{-- FORMULARIO --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.categorias.update', $data) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre"
                                name="nombre" value="{{ old('nombre', $data->nombre) }}" maxlength="100" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark">Guardar cambios</button>
                            <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- PRODUCTOS DE LA CATEGORÍA --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h3 class="h6 mb-0">Productos de esta categoría ({{ $data->productos->count() }})</h3>
                </div>

                @if ($data->productos->isEmpty())
                    <div class="card-body text-muted">
                        Esta categoría no tiene productos, así que se puede eliminar.
                    </div>
                @else
                    <div class="card-body">
                        <table class="table table-hover align-middle datatable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Disponible</th>
                                    <th class="text-center">Visible</th>
                                    <th class="no-sort text-center" style="width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->productos as $producto)
                                    <tr>
                                        <td class="fw-semibold">{{ $producto->nombre }}</td>
                                        <td class="text-end" data-order="{{ $producto->precio }}">
                                            ₡{{ number_format($producto->precio, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $producto->disponible ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                {{ $producto->disponible ? 'Disponible' : 'Agotado' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $producto->visible ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                                {{ $producto->visible ? 'Visible' : 'Oculto' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.productos.edit', $producto) }}"
                                                class="btn btn-sm btn-dark" title="Editar producto">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.productos.destroy', $producto) }}" method="POST"
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
                @endif
            </div>
        </div>

    </div>
@endsection
