@extends('admin.layouts.app')

@section('titulo', 'Gestión de Proveedores')
@section('encabezado', 'Gestión de Proveedores')

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
                    <h3 class="h6 mb-3">Nuevo proveedor</h3>
                    <form action="{{ route('admin.proveedores.store') }}" method="POST">
                        @csrf
                        @include('admin.proveedores.campos', ['data' => null])
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
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th class="text-center">Compras</th>
                                <th class="no-sort text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->nombre }}</td>
                                    <td>{{ $item->telefono ?: '—' }}</td>
                                    <td>{{ $item->correo ?: '—' }}</td>
                                    <td class="text-center">{{ $item->compras_count }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.proveedores.edit', $item) }}"
                                            class="btn btn-sm btn-dark" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.proveedores.destroy', $item) }}" method="POST"
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