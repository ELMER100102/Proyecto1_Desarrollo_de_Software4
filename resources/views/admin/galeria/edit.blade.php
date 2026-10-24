@extends('admin.layouts.app')

@section('titulo', 'Editar Foto')
@section('encabezado', 'Editar Foto')

@section('contenido')
    <div class="card">
        <div class="card-body p-4">

            <form action="{{ route('admin.galeria.update', $foto->id_foto) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- old(): conserva lo que escribió el usuario --}}
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                            rows="4" placeholder="Descripción breve del slide" required>{{ old('descripcion', $foto->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label d-block">Imagen actual</label>
                        <img src="{{ asset('storage/' . $foto->imagen) }}" id="vista-previa"
                            class="img-fluid rounded border"
                            style="width: 100%; max-width: 280px; aspect-ratio: 16/9; object-fit: cover;"
                            alt="">
                    </div>

                    <div class="col-12">
                        <label for="imagen" class="form-label">Imagen</label>
                        <input type="file" class="form-control @error('imagen') is-invalid @enderror" id="imagen"
                            name="imagen" accept="image/jpg, image/png, image/jpeg">
                        <div class="form-text">Formato: JPG, JPEG, PNG. Tamaño máximo: 2 MB.</div>
                        @error('imagen')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        {{-- <img src="" id="vista-previa" class="img-fluid rounded border mt-3 d-none"
                            style="max-width: 280px; aspect-ratio: 16/9; object-fit: cover;" alt="Vista previa"> --}}
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Guardar foto</button>
                        <a href="{{ route('admin.galeria.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection
