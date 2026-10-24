@extends('admin.layouts.app')

@section('titulo', 'Información de Contacto')
@section('encabezado', 'Información de Contacto')

@section('contenido')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-4">

            <form action="{{ route('admin.contacto.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">

                    {{-- LOGO --}}
                    <div class="col-12 col-md-3 text-center">
                        <label class="form-label d-block">Logo de la institución</label>
                        <img src="{{ $data->logo ? asset('storage/' . $data->logo) : '' }}"
                            id="vista-previa" class="img-fluid rounded border mb-2"
                            style="width: 160px; height: 160px; object-fit: cover;" alt="Logo">
                    </div>

                    <div class="col-12 col-md-9">
                        <label for="logo" class="form-label">Seleccionar archivo</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo"
                            name="logo" accept="image/png, image/jpeg">
                        <div class="form-text">Formato: JPG, JPEG, PNG. Tamaño máximo: 2 MB.</div>
                        @error('logo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DATOS --}}
                    <div class="col-12">
                        <label for="nombre_empresa" class="form-label">Nombre de la institución</label>
                        <input type="text" class="form-control @error('nombre_empresa') is-invalid @enderror"
                            id="nombre_empresa" name="nombre_empresa" placeholder="Nombre de la institución"
                            value="{{ old('nombre_empresa', $data->nombre_empresa) }}" required>
                        @error('nombre_empresa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion"
                            name="direccion" placeholder="Dirección, provincia, cantón, distrito"
                            value="{{ old('direccion', $data->direccion) }}" required>
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono"
                            name="telefono" placeholder="(506) 0000-0000" value="{{ old('telefono', $data->telefono) }}"
                            required>
                        @error('telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control @error('correo') is-invalid @enderror" id="correo"
                            name="correo" placeholder="correo@ejemplo.com" value="{{ old('correo', $data->correo) }}"
                            required>
                        @error('correo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="facebook" class="form-label">Facebook</label>
                        <input type="url" class="form-control @error('facebook') is-invalid @enderror" id="facebook"
                            name="facebook" placeholder="https://www.facebook.com/ejemplo"
                            value="{{ old('facebook', $data->facebook) }}">
                        @error('facebook')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="instagram" class="form-label">Instagram</label>
                        <input type="url" class="form-control @error('instagram') is-invalid @enderror" id="instagram"
                            name="instagram" placeholder="https://www.instagram.com/ejemplo"
                            value="{{ old('instagram', $data->instagram) }}">
                        @error('instagram')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-dark w-100">Guardar información</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
