@extends('admin.layouts.app')

@section('titulo', 'Gestión de Categorías')
@section('encabezado', 'Gestión de Categorías')

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- FORMULARIO --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6 mb-3">Nueva categoría</h3>
                    <form action="{{ route('admin.categorias.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Agregar</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- LISTADO --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover align-middle datatable">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Productos</th>
                                <th class="no-sort text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->nombre }}</td>
                                    <td class="text-center">{{ $item->productos_count }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.categorias.edit', $item) }}"
                                            class="btn btn-sm btn-dark" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.categorias.destroy', $item) }}" method="POST"
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
        </div>

    </div>
@endsection