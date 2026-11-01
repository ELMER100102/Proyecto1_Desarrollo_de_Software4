{{-- Campo "imagen" con vista previa (app.js cambia #vista-previa al elegir un archivo).
     Requiere: $editando (bool), $actual (ruta en storage o null) y $alt.
     Al crear, la imagen es obligatoria; al editar, solo se cambia si se elige otra. --}}
@if ($editando)
    <div class="col-12 col-md-4">
        <label class="form-label d-block">Imagen actual</label>
        <img src="{{ $actual ? asset('storage/' . $actual) : '' }}" id="vista-previa"
            class="img-fluid rounded border"
            style="width: 100%; max-width: 280px; aspect-ratio: 16/9; object-fit: cover;"
            alt="{{ $alt }}">
    </div>
@endif

<div class="col-12">
    <label for="imagen" class="form-label">Imagen</label>
    <input type="file" class="form-control @error('imagen') is-invalid @enderror" id="imagen"
        name="imagen" accept="image/jpg, image/png, image/jpeg" @required(! $editando)>
    <div class="form-text">Formato: JPG, JPEG, PNG. Tamaño máximo: 2 MB.</div>
    @error('imagen')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror

    @unless ($editando)
        <img src="" id="vista-previa" class="img-fluid rounded border mt-3 d-none"
            style="max-width: 280px; aspect-ratio: 16/9; object-fit: cover;" alt="Vista previa">
    @endunless
</div>
