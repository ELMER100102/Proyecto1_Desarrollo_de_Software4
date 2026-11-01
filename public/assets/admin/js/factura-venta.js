// Nueva venta (admin/facturas/create): carrito, totales e IVA en el navegador.
// Usa las constantes `productos` y `previos` que define la vista. El servidor vuelve a calcular todo.
const carrito = {}; // id_producto -> cantidad
let temporizadorAviso = null;

const dinero = n => '₡' + Number(n).toLocaleString('es-CR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});
const porId = id => productos.find(p => p.id === Number(id));

function avisar(texto) {
    clearTimeout(temporizadorAviso);
    $('#aviso').text(texto).removeClass('d-none');
    temporizadorAviso = setTimeout(() => $('#aviso').addClass('d-none'), 3500);
}

/* ---------- LISTA DE PRODUCTOS (filtra mientras se escribe) ---------- */
function pintarProductos() {
    const texto = $('#buscarProducto').val().trim().toLowerCase();
    const $lista = $('#listaProductos').empty();

    const visibles = productos.filter(p =>
        p.nombre.toLowerCase().includes(texto) || p.categoria.toLowerCase().includes(texto)
    );

    if (visibles.length === 0) {
        $lista.append($('<div>', {
            class: 'text-muted text-center py-4',
            text: 'No hay productos que coincidan.'
        }));
        return;
    }

    visibles.forEach(function(p) {
        const detalle = p.categoria + (p.stock !== null ? ' · Stock: ' + p.stock : '');

        $lista.append(
            $('<button>', {
                type: 'button',
                class: 'list-group-item list-group-item-action d-flex justify-content-between align-items-center agregar-producto',
                'data-id': p.id
            })
            .append(
                $('<span>')
                .append($('<span>', {
                    class: 'fw-semibold',
                    text: p.nombre
                }))
                .append($('<small>', {
                    class: 'd-block text-muted',
                    text: detalle
                }))
            )
            .append($('<span>', {
                class: 'text-nowrap ms-3',
                text: dinero(p.precio)
            }))
        );
    });
}

/* ---------- CARRITO ---------- */
function pintarCarrito() {
    const $tbody = $('#carrito tbody').empty();
    const $items = $('#items').empty();
    let total = 0,
        base = 0,
        indice = 0;

    Object.keys(carrito).forEach(function(id) {
        const p = porId(id);
        const cantidad = carrito[id];
        const linea = Math.round(p.precio * cantidad * 100) / 100;

        total += linea;
        base += linea / (1 + p.impuesto / 100);

        $tbody.append(
            $('<tr>')
            .append($('<td>').text(p.nombre))
            .append(
                $('<td>', {
                    class: 'text-center'
                }).append(
                    $('<div>', {
                        class: 'btn-group btn-group-sm'
                    })
                    .append($('<button>', {
                        type: 'button',
                        class: 'btn btn-outline-secondary btn-menos',
                        'data-id': id,
                        text: '−'
                    }))
                    .append($('<span>', {
                        class: 'btn btn-light disabled px-3',
                        text: cantidad
                    }))
                    .append($('<button>', {
                        type: 'button',
                        class: 'btn btn-outline-secondary btn-mas',
                        'data-id': id,
                        text: '+'
                    }))
                )
            )
            .append($('<td>', {
                class: 'text-end',
                text: dinero(linea)
            }))
            .append(
                $('<td>', {
                    class: 'text-end'
                }).append(
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-sm btn-outline-danger btn-quitar',
                        'data-id': id,
                        title: 'Quitar',
                        'aria-label': 'Quitar ' + p.nombre
                    }).append($('<i>', {
                        class: 'bi bi-trash'
                    }))
                )
            )
        );

        $items.append($('<input>', {
            type: 'hidden',
            name: 'items[' + indice + '][id_producto]',
            value: id
        }));
        $items.append($('<input>', {
            type: 'hidden',
            name: 'items[' + indice + '][cantidad]',
            value: cantidad
        }));
        indice++;
    });

    if (indice === 0) {
        $tbody.append(
            $('<tr>').append(
                $('<td>', {
                    colspan: 4,
                    class: 'text-center text-muted py-4',
                    text: 'Toque un producto de la lista para agregarlo.'
                })
            )
        );
    }

    $('#subtotalVenta').text(dinero(base));
    $('#ivaVenta').text(dinero(total - base));
    $('#totalVenta').text(dinero(total));
    $('#btnGuardar').prop('disabled', indice === 0);
}

function cambiar(id, delta) {
    const p = porId(id);
    if (!p) {
        return;
    }

    const nueva = (carrito[id] || 0) + delta;

    if (nueva <= 0) {
        delete carrito[id];
    } else if (p.stock !== null && nueva > p.stock) {
        avisar('Solo quedan ' + p.stock + ' de "' + p.nombre + '".');
        return;
    } else {
        carrito[id] = nueva;
    }
    pintarCarrito();
}

/* ---------- EVENTOS ---------- */
$('#buscarProducto').on('input', pintarProductos);

// Enter en el buscador no debe enviar el formulario
$('#buscarProducto').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
    }
});

$(document).on('click', '.agregar-producto', function() {
    cambiar($(this).data('id'), 1);
});
$(document).on('click', '.btn-mas', function() {
    cambiar($(this).data('id'), 1);
});
$(document).on('click', '.btn-menos', function() {
    cambiar($(this).data('id'), -1);
});
$(document).on('click', '.btn-quitar', function() {
    delete carrito[$(this).data('id')];
    pintarCarrito();
});

// Crédito => el cliente es obligatorio
function revisarCredito() {
    const credito = $('#tipo_pago').val() === 'credito';
    $('#cliente').prop('required', credito);
    $('#clienteObligatorio').toggleClass('d-none', !credito);
}
$('#tipo_pago').on('change', revisarCredito);

// Evita el doble clic (dos comprobantes iguales)
$('#formVenta').on('submit', function() {
    $('#btnGuardar').prop('disabled', true).text('Guardando...');
});

/* ---------- INICIO ---------- */
previos.forEach(function(it) {
    if (porId(it.id_producto)) {
        carrito[it.id_producto] = (carrito[it.id_producto] || 0) + Number(it.cantidad);
    }
});

revisarCredito();
pintarProductos();
pintarCarrito();
