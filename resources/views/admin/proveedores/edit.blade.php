@extends('admin.layouts.app')

@section('titulo', 'Editar Proveedor')
@section('encabezado', 'Editar Proveedor')

@section('contenido')
    <div class="row">
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.proveedores.update', $data) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('admin.proveedores.campos')

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark">Guardar cambios</button>
                            <a href="{{ route('admin.proveedores.index') }}"
                                class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection