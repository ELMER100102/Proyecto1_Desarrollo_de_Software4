@extends('admin.layouts.app')

@section('titulo', 'Editar Noticia')
@section('encabezado', 'Editar Noticia')

@section('contenido')
    <div class="card">
        <div class="card-body p-4">
            
            <form action="{{ route('admin.noticias.update', $noticia->id_noticia) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- old(): conserva lo que escribió el usuario --}}
                    <div class="col-12">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo"
                            name="titulo" value="{{ old('titulo', $noticia->titulo) }}" placeholder="Título de la noticia"
                            required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="contenido" class="form-label">Contenido</label>
                        <textarea class="form-control @error('contenido') is-invalid @enderror" id="contenido" name="contenido" rows="4"
                            placeholder="Contenido breve de la noticia" required>{{ old('contenido', $noticia->contenido) }}</textarea>
                        @error('contenido')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label d-block">Imagen actual</label>
                        <img src="{{ asset('storage/' . $noticia->imagen) }}" id="vista-previa"
                            class="img-fluid rounded border" 
                            style="width: 100%; max-width: 280px; aspect-ratio: 16/9; object-fit: cover;"
                            alt="{{ $noticia->titulo }}">
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
                        <button type="submit" class="btn btn-dark">Guardar noticia</button>
                        <a href="{{ route('admin.noticias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection
