// vista previa
$("#imagen, #logo").on("change", function () {
    const archivo = this.files[0];

    if (archivo) {
        $("#vista-previa")
            .attr("src", URL.createObjectURL(archivo))
            .removeClass("d-none");
    }
});

//Advertencia antes de eliminar
$(document).on('submit', '.form-eliminar', function (e) {
    if (!confirm('¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.')) {
        e.preventDefault();
    }
});

//tablas
$(document).ready(function () {
    $(".datatable").DataTable({
        columnDefs: [{ targets: "no-sort", orderable: false }],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Sin registros",
            infoFiltered: "(filtrado de _MAX_ en total)",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay registros para mostrar",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior",
            },
        },
    });
});

//cambio de oscuro a claro
$(function () {
    function aplicarTema(tema) {
        $('html').attr('data-bs-theme', tema);
        // Luna en modo claro (para pasar a oscuro) y sol en modo oscuro
        $('#iconoTema').attr('class', tema === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars');
    }

    aplicarTema($('html').attr('data-bs-theme') || 'light');

    $('#btnTema').on('click', function () {
        var nuevo = $('html').attr('data-bs-theme') === 'dark' ? 'light' : 'dark';
        aplicarTema(nuevo);
        try { localStorage.setItem('tema-admin', nuevo); } catch (e) {}
    });
});
