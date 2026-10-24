@extends('admin.layouts.app')

@section('titulo', 'Nuevo Producto')
@section('encabezado', 'Nuevo Producto')

@section('contenido')
    <div class="row g-4 align-items-start">

        {{-- BÚSQUEDA EN EL CATÁLOGO CABYS --}}
        <div class="col-12 col-xl-5">
            <div class="card">
                <div class="card-header bg-white">
                    <h3 class="h6 mb-0">Buscar en el catálogo CABYS</h3>
                </div>
                <div class="card-body">
                    <label for="cabysBuscar" class="form-label">Producto</label>
                    <input type="search" id="cabysBuscar" class="form-control" autocomplete="off"
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

        {{-- FORMULARIO --}}
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @include('admin.productos.form')

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-dark">Guardar producto</button>
                            <a href="{{ route('admin.productos.index') }}"
                                class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        const urlBuscar = "{{ route('admin.cabys.buscar') }}";
        let temporizador = null;   // espera a que el usuario deje de escribir
        let controlador = null;    // permite cancelar la búsqueda anterior
        let resultados = [];       // resultados de la última búsqueda

        function pintarEstado(texto, clase) {
            $('#cabysResultados').empty().append(
                $('<tr>').append(
                    $('<td>', {
                        colspan: 4,
                        class: 'text-center py-4 ' + (clase || 'text-muted'),
                        text: texto
                    })
                )
            );
        }

        function buscarCabys(texto) {
            if (controlador) {
                controlador.abort();
            }
            controlador = new AbortController();

            pintarEstado('Buscando...');

            fetch(urlBuscar + '?q=' + encodeURIComponent(texto), {
                headers: { 'Accept': 'application/json' },
                signal: controlador.signal
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Error ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    resultados = data.resultados;

                    if (resultados.length === 0) {
                        pintarEstado('Sin resultados para "' + texto + '".');
                        return;
                    }

                    const $tbody = $('#cabysResultados').empty();

                    resultados.forEach(function (item, indice) {
                        const impuesto = item.impuesto !== null ? item.impuesto + '%' : '-';

                        $tbody.append(
                            $('<tr>')
                                .append($('<td>').text(item.descripcion))
                                .append($('<td>', { class: 'text-nowrap small' }).text(item.codigo))
                                .append($('<td>', { class: 'text-end' }).text(impuesto))
                                .append(
                                    $('<td>', { class: 'text-end' }).append(
                                        $('<button>', {
                                            type: 'button',
                                            class: 'btn btn-sm btn-outline-dark btn-cargar',
                                            'data-indice': indice,
                                            text: 'Cargar'
                                        })
                                    )
                                )
                        );
                    });
                })
                .catch(function (error) {
                    if (error.name === 'AbortError') {
                        return; // la búsqueda se canceló porque llegó otra más nueva
                    }
                    pintarEstado('No se pudo consultar el catálogo.', 'text-danger');
                });
        }

        // Filtra mientras se escribe, esperando una pausa corta
        $('#cabysBuscar').on('input', function () {
            clearTimeout(temporizador);

            const texto = $(this).val().trim();

            if (texto.length < 3) {
                if (controlador) {
                    controlador.abort();
                }
                pintarEstado('Escriba al menos 3 letras para buscar.');
                return;
            }

            temporizador = setTimeout(function () {
                buscarCabys(texto);
            }, 350);
        });

        // Pasa el resultado elegido al formulario
        $(document).on('click', '.btn-cargar', function () {
            const item = resultados[$(this).data('indice')];

            $('#cabys_codigo').val(item.codigo);
            $('#cabys_descripcion').val(item.descripcion);
            $('#impuesto').val(item.impuesto !== null ? item.impuesto : '');
        });
    </script>
@endsection
