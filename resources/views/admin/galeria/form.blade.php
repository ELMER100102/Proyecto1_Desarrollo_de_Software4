{{-- Formulario de foto de la galería (crear y editar). $foto no existe al crear. --}}
@php($foto = $foto ?? null)

<div class="card">
    <div class="card-body p-4">

        <form action="{{ $foto ? route('admin.galeria.update', $foto->id_foto) : route('admin.galeria.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($foto)
                @method('PUT')
            @endif

            <div class="row g-4">
                {{-- old(): conserva lo que escribió el usuario --}}
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
                        rows="4" placeholder="Descripción breve de la foto" required>{{ old('descripcion', $foto?->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @include('admin.parts.campo-imagen', [
                    'editando' => (bool) $foto,
                    'actual'   => $foto?->imagen,
                    'alt'      => '',
                ])

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Guardar foto</button>
                    <a href="{{ route('admin.galeria.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>
