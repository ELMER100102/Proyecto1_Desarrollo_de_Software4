{{-- Buscador del catálogo CABYS (columna izquierda de crear/editar producto).
     Lo maneja assets/admin/js/cabys.js: "Cargar" pasa código, descripción e IVA al formulario. --}}
{{-- BÚSQUEDA EN EL CATÁLOGO CABYS --}}
<div class="col-12 col-xl-5">
    <div class="card">
        <div class="card-header bg-white">
            <h3 class="h6 mb-0">Buscar en el catálogo CABYS</h3>
        </div>
        <div class="card-body">
            <label for="cabysBuscar" class="form-label">Producto</label>
            <input type="search" id="cabysBuscar" class="form-control" autocomplete="off"
        data-url="{{ route('admin.cabys.buscar') }}"
                placeholder="Ej: pan, queque, jugo de tomate">
            <div class="form-text mb-3">
                Escriba al menos 3 letras y la lista se filtra sola. Pulse "Cargar" para pasar el
                código al formulario.
            </div>

            <div style="max-height: 480px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Descripción</th>
                            <th>Código</th>
                            <th class="text-end">IVA</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cabysResultados">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Escriba al menos 3 letras para buscar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
