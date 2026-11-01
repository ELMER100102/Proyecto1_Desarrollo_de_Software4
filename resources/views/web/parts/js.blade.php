{{-- Librerías --}}
<script src="{{ asset('assets/web/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/web/js/bootstrap.bundle.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
<script src="{{ asset('assets/web/js/jquery-migrate-3.0.1.min.js') }}"></script>
<script src="{{ asset('assets/web/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/web/js/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('assets/web/js/jquery.stellar.min.js') }}"></script>
<script src="{{ asset('assets/web/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/web/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/web/js/aos.js') }}"></script>
<script src="{{ asset('assets/web/js/scrollax.min.js') }}"></script>

{{-- ?v=: si pedido.js cambia, el navegador no usa una copia vieja --}}
<script src="{{ asset('assets/web/js/pedido.js') }}?v={{ filemtime(public_path('assets/web/js/pedido.js')) }}"></script>
<script src="{{ asset('assets/web/js/main.js') }}"></script>

{{-- Validaciones en el navegador (compartido con el admin). El servidor siempre vuelve a validar --}}
<script src="{{ asset('assets/validaciones.js') }}?v={{ filemtime(public_path('assets/validaciones.js')) }}" defer></script>

{{-- Efectos (revelados y transición entre páginas) --}}
<script src="{{ asset('assets/web/js/efectos.js') }}?v={{ filemtime(public_path('assets/web/js/efectos.js')) }}" defer></script>
<script src="{{ asset('assets/web/js/menu-carta.js') }}?v={{ filemtime(public_path('assets/web/js/menu-carta.js')) }}" defer></script>
<script src="{{ asset('assets/web/js/modal-pedido.js') }}?v={{ filemtime(public_path('assets/web/js/modal-pedido.js')) }}" defer></script>
<script src="{{ asset('assets/web/js/panel-pedido.js') }}?v={{ filemtime(public_path('assets/web/js/panel-pedido.js')) }}" defer></script>

