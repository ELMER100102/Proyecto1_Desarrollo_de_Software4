@extends('admin.layouts.app')

@section('titulo', 'Editar Producto')
@section('encabezado', 'Editar Producto')

@section('contenido')
    <div class="row g-4 align-items-start">
        @include('admin.productos.cabys')

        {{-- FORMULARIO --}}
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.productos.update', $data) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @include('admin.productos.form')

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-dark">Guardar cambios</button>
                            <a href="{{ route('admin.productos.index') }}"
                                class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/cabys.js') }}?v={{ filemtime(public_path('assets/admin/js/cabys.js')) }}"></script>
@endsection
