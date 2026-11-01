{{-- Formulario de noticia (crear y editar). $noticia no existe al crear. --}}
@php($noticia = $noticia ?? null)

<div class="card">
    <div class="card-body p-4">

        <form action="{{ $noticia ? route('admin.noticias.update', $noticia->id_noticia) : route('admin.noticias.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($noticia)
                @method('PUT')
            @endif

            <div class="row g-4">
                {{-- old(): conserva lo que escribió el usuario --}}
                <div class="col-12">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo"
                        name="titulo" value="{{ old('titulo', $noticia?->titulo) }}" placeholder="Título de la noticia"
                        required>
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="contenido" class="form-label">Contenido</label>
                    <textarea class="form-control @error('contenido') is-invalid @enderror" id="contenido" name="contenido"
                        rows="10" maxlength="10000" placeholder="Contenido de la noticia. Separa los párrafos con un salto de línea."
                        required>{{ old('contenido', $noticia?->contenido) }}</textarea>
                    @error('contenido')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('admin.parts.campo-imagen', [
                    'editando' => (bool) $noticia,
                    'actual'   => $noticia?->imagen,
                    'alt'      => $noticia?->titulo,
                ])

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Guardar noticia</button>
                    <a href="{{ route('admin.noticias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>
