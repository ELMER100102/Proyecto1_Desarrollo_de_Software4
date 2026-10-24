@extends('admin.layouts.app')

@section('titulo', 'Gestión de Galería')
@section('encabezado', 'Gestión de Galería')

@section('boton')
    <a href="{{ route('admin.galeria.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nueva foto
    </a>
@endsection

@section('contenido')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">

        @forelse ($data as $item)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100">
                    <img src="{{ asset('storage/' . $item->imagen) }}" class="card-img-top"
                        style="aspect-ratio: 4/3; object-fit: cover;" alt="Foto">
                    <div class="card-body d-flex flex-column">
                        <p class="small mb-3">{{ $item->descripcion }}</p>
                        <div class="mt-auto">
                            <a href="{{ route('admin.galeria.edit', $item) }}" class="btn btn-sm btn-dark" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.galeria.destroy', $item) }}" method="POST"
                                class="d-inline form-eliminar">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">No hay imágenes para mostrar.</p>
            </div>
        @endforelse

    </div>
@endsection
