{{-- Librerías --}}
<script src="{{ asset('assets/admin/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/dataTables.min.js') }}"></script>

{{-- Validaciones en el navegador (compartido con el sitio). El servidor siempre vuelve a validar --}}
<script src="{{ asset('assets/validaciones.js') }}?v={{ filemtime(public_path('assets/validaciones.js')) }}" defer></script>

{{-- NUESTRO CODIGO --}}
<script src="{{ asset('assets/admin/js/pedidos-admin.js') }}"></script>
<script src="{{ asset('assets/admin/js/app.js') }}"></script>
