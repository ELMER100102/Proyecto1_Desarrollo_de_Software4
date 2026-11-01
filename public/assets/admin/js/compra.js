// Nueva compra (admin/compras/create): detalle de productos y total en el navegador.
// Usa las constantes `productos` y `previos` que define la vista. El servidor vuelve a calcular todo.
const carrito = {}; // id_producto -> { cantidad, costo }

const dinero = n => '₡' + Number(n).toLocaleString('es-CR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});
const redondear = n => Math.round(n * 100) / 100;
const porId = id => productos.find(p => p.id === Number(id));

/* ---------- LISTA DE PRODUCTOS ---------- */
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
        const stock = p.stock === null ? 'Sin control de stock' : 'Stock: ' + p.stock;
        const costo = p.costo === null ? '' : ' · Último costo: ' + dinero(p.costo);

        $lista.append(
            $('<button>', {
                type: 'button',
                class: 'list-group-item list-group-item-action agregar-producto',
                'data-id': p.id
            })
            .append($('<span>', {
                class: 'fw-semibold',
                text: p.nombre
            }))
            .append($('<small>', {
                class: 'd-block text-muted',
                text: p.categoria + ' · ' + stock + costo
            }))
        );
    });
}

/* ---------- DETALLE ---------- */
// Se dibuja solo al agregar o quitar productos; al escribir cantidades
// solo se recalculan los totales (así el cursor no se pierde).
function pintarCarrito() {
    const $tbody = $('#carrito tbody').empty();
    const ids = Object.keys(carrito);

    ids.forEach(function(id) {
        const p = porId(id);
        const linea = carrito[id];

        const $nombre = $('<td>')
            .append($('<div>', {
                text: p.nombre
            }))
            .append($('<input>', {
                type: 'hidden',
                name: 'items[' + id + '][id_producto]',
                value: id
            }));

        if (p.stock === null) {
            $nombre.append($('<small>', {
                class: 'text-warning-emphasis',
                text: 'Sin control de stock: se registra la compra, pero el stock no cambia.'
            }));
        }

        $tbody.append(
            $('<tr>')
            .append($nombre)
            .append($('<td>').append($('<input>', {
                type: 'number',
                min: 1,
                step: 1,
                required: true,
                class: 'form-control form-control-sm in-cantidad',
                name: 'items[' + id + '][cantidad]',
                'data-id': id,
                value: linea.cantidad
            })))
            .append($('<td>').append($('<input>', {
                type: 'number',
                min: 0,
                step: '0.01',
                required: true,
                class: 'form-control form-control-sm in-costo',
                name: 'items[' + id + '][costo]',
                'data-id': id,
                value: linea.costo
            })))
            .append($('<td>', {
                class: 'text-end',
                id: 'linea-' + id
            }))
            .append($('<td>', {
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
            ))
        );
    });

    if (ids.length === 0) {
        $tbody.append($('<tr>').append($('<td>', {
            colspan: 5,
            class: 'text-center text-muted py-4',
            text: 'Toque un producto de la lista para agregarlo.'
        })));
    }

    recalcular();
}

function recalcular() {
    let total = 0;

    Object.keys(carrito).forEach(function(id) {
        const linea = redondear(carrito[id].cantidad * carrito[id].costo);
        total += linea;
        $('#linea-' + id).text(dinero(linea));
    });

    $('#totalCompra').text(dinero(total));
    $('#btnGuardar').prop('disabled', Object.keys(carrito).length === 0);
}

/* ---------- EVENTOS ---------- */
$('#buscarProducto').on('input', pintarProductos);
$('#buscarProducto').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
    }
});

$(document).on('click', '.agregar-producto', function() {
    const id = $(this).data('id');
    const p = porId(id);

    if (carrito[id]) {
        carrito[id].cantidad += 1;
    } else {
        carrito[id] = {
            cantidad: 1,
            costo: p.costo !== null ? p.costo : 0
        };
    }
    pintarCarrito();
});

$(document).on('click', '.btn-quitar', function() {
    delete carrito[$(this).data('id')];
    pintarCarrito();
});

$(document).on('input', '.in-cantidad', function() {
    carrito[$(this).data('id')].cantidad = parseInt($(this).val(), 10) || 0;
    recalcular();
});

$(document).on('input', '.in-costo', function() {
    carrito[$(this).data('id')].costo = parseFloat($(this).val()) || 0;
    recalcular();
});

// Evita el doble clic (dos compras iguales)
$('#formCompra').on('submit', function() {
    $('#btnGuardar').prop('disabled', true).text('Guardando...');
});

/* ---------- INICIO ---------- */
Object.keys(previos).forEach(function(id) {
    if (porId(id)) {
        carrito[id] = {
            cantidad: parseInt(previos[id].cantidad, 10) || 1,
            costo: parseFloat(previos[id].costo) || 0
        };
    }
});

pintarProductos();
pintarCarrito();
