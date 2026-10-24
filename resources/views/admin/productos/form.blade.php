{{-- Campos compartidos por create y edit. Requiere: $data (Producto) y $categorias --}}
<div class="row g-4">

    <div class="col-12 col-md-6">
        <label for="id_categoria" class="form-label">Categoría</label>
        <select id="id_categoria" name="id_categoria" class="form-select @error('id_categoria') is-invalid @enderror"
            required>
            <option value="" disabled @selected(!old('id_categoria', $data->id_categoria))>Seleccione una categoría</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id_categoria }}" @selected(old('id_categoria', $data->id_categoria) == $categoria->id_categoria)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_categoria')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="precio" class="form-label">Precio (IVA incluido)</label>
        <div class="input-group">
            <span class="input-group-text">₡</span>
            <input type="number" step="0.01" min="0" id="precio" name="precio"
                class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $data->precio) }}"
                required>
            @error('precio')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <label for="nombre" class="form-label">Nombre del producto</label>
        <input type="text" id="nombre" name="nombre" maxlength="150"
            class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $data->nombre) }}"
            placeholder="Ej: Baguette artesanal" required>
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción corta (la ve el cliente)</label>
        <textarea id="descripcion" name="descripcion" rows="3" maxlength="255"
            class="form-control @error('descripcion') is-invalid @enderror"
            placeholder="Ej: Crujiente por fuera, suave por dentro">{{ old('descripcion', $data->descripcion) }}</textarea>
        @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- IMAGEN --}}
    <div class="col-12 col-md-4">
        <label class="form-label d-block">Imagen</label>
        <img src="{{ $data->imagen ? asset('storage/' . $data->imagen) : '' }}" id="vista-previa"
            class="img-fluid rounded border {{ $data->imagen ? '' : 'd-none' }}"
            style="width: 100%; max-width: 240px; aspect-ratio: 4/3; object-fit: cover;" alt="Vista previa">
    </div>

    <div class="col-12 col-md-8">
        <label for="imagen" class="form-label">{{ $data->imagen ? 'Cambiar imagen' : 'Seleccionar imagen' }}</label>
        <input type="file" id="imagen" name="imagen" accept="image/jpg, image/png, image/jpeg"
            class="form-control @error('imagen') is-invalid @enderror">
        <div class="form-text">
            Formato: JPG, JPEG, PNG. Tamaño máximo: 2 MB.
            @if ($data->imagen)
                Si no selecciona un archivo, se conserva la imagen actual.
            @endif
        </div>
        @error('imagen')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- STOCK (opcional) --}}
    <div class="col-12 col-md-6">
        <label for="stock" class="form-label">Stock (opcional)</label>
        <input type="number" step="1" min="0" id="stock" name="stock"
            class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $data->stock) }}"
            placeholder="Sin control">
        <div class="form-text">
            Déjelo vacío si no controla existencias de este producto. Si lo cambia a 0, el producto pasa a
            agotado; si sube de 0 a más, vuelve a disponible.
        </div>
        @error('stock')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- INTERRUPTORES --}}
    {{-- El hidden con valor 0 va primero: si el checkbox se desmarca, llega el 0 --}}
    <div class="col-12 col-md-6">
        <input type="hidden" name="disponible" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="disponible" name="disponible"
                value="1" @checked(old('disponible', $data->disponible))>
            <label class="form-check-label" for="disponible">Disponible hoy (si se apaga, se muestra como
                agotado)</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <input type="hidden" name="visible" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="visible" name="visible" value="1"
                @checked(old('visible', $data->visible))>
            <label class="form-check-label" for="visible">Visible en el menú</label>
        </div>
    </div>

    {{-- CABYS (opcional; luego lo rellenará la búsqueda) --}}
    <div class="col-12">
        <hr class="my-0">
        <h3 class="h6 mt-3 mb-0">Catálogo CABYS (opcional)</h3>
    </div>

    <div class="col-12 col-md-4">
        <label for="cabys_codigo" class="form-label">Código CABYS</label>
        <input type="text" id="cabys_codigo" name="cabys_codigo" maxlength="13"
            class="form-control @error('cabys_codigo') is-invalid @enderror"
            value="{{ old('cabys_codigo', $data->cabys_codigo) }}">
    </div>

    <div class="col-12 col-md-5">
        <label for="cabys_descripcion" class="form-label">Descripción oficial</label>
        <input type="text" id="cabys_descripcion" name="cabys_descripcion" maxlength="255"
            class="form-control @error('cabys_descripcion') is-invalid @enderror"
            value="{{ old('cabys_descripcion', $data->cabys_descripcion) }}">
    </div>

    <div class="col-12 col-md-3">
        <label for="impuesto" class="form-label">Impuesto (%)</label>
        <input type="number" step="0.01" min="0" max="100" id="impuesto" name="impuesto"
            class="form-control @error('impuesto') is-invalid @enderror"
            value="{{ old('impuesto', $data->impuesto) }}">
    </div>

</div>
