{{-- Formulario de slide (crear y editar). $slide no existe al crear. --}}
@php($slide = $slide ?? null)

<div class="card">
    <div class="card-body p-4">

        <form action="{{ $slide ? route('admin.slides.update', $slide->id_slide) : route('admin.slides.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($slide)
                @method('PUT')
            @endif

            <div class="row g-4">
                {{-- old(): conserva lo que escribió el usuario --}}
                <div class="col-12">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo"
                        name="titulo" value="{{ old('titulo', $slide?->titulo) }}" placeholder="Título del slide"
                        required>
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                        rows="4" placeholder="Descripción breve del slide" required>{{ old('descripcion', $slide?->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('admin.parts.campo-imagen', [
                    'editando' => (bool) $slide,
                    'actual'   => $slide?->imagen,
                    'alt'      => $slide?->titulo,
                ])

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Guardar slide</button>
                    <a href="{{ route('admin.slides.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>
