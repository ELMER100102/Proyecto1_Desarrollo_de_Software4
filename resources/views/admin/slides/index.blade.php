@extends('admin.layouts.app')

@section('titulo', 'Gestión de Slider')
@section('encabezado', 'Gestión de Slider')

@section('boton')
    <a href="{{ route('admin.slides.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nuevo slide
    </a>
@endsection

@section('contenido')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th class="no-sort" style="width: 140px;">Imagen</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th class="no-sort text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>
                                <img src="{{ asset('storage/' . $item->imagen) }}" class="img-fluid rounded"
                                    style="width: 120px; height: 70px; object-fit: cover;"
                                    alt="{{ $item->titulo }}">
                            </td>
                            <td class="fw-semibold">{{ $item->titulo }}</td>
                            <td>{{ $item->descripcion }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.slides.edit', $item) }}" class="btn btn-sm btn-dark"
                                    title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.slides.destroy', $item) }}" method="POST"
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