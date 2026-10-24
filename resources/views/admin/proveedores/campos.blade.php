{{-- Campos compartidos por "nuevo" y "editar". $data es el Proveedor, o null al crear. --}}
<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
        value="{{ old('nombre', $data?->nombre) }}" maxlength="150" required>
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="telefono" class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
    <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono"
        value="{{ old('telefono', $data?->telefono) }}" maxlength="30">
    @error('telefono')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="correo" class="form-label">Correo <span class="text-muted">(opcional)</span></label>
    <input type="email" class="form-control @error('correo') is-invalid @enderror" id="correo" name="correo"
        value="{{ old('correo', $data?->correo) }}" maxlength="150">
    @error('correo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>