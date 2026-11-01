// Buscador CABYS en crear/editar producto (vista admin/productos/cabys.blade.php).
// Filtra mientras se escribe y "Cargar" pasa el resultado a los campos del formulario.
$(function () {
    const urlBuscar = $('#cabysBuscar').data('url');
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
});
