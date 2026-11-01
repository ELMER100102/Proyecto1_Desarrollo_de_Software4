{{-- Mejora progresiva: los efectos ocultan contenido solo si hay JavaScript (html.js).
     Si efectos.js no carga en 3s, html.sin-efectos deja todo visible. --}}
<script>
    document.documentElement.classList.add('js');
    setTimeout(function () { if (!window.__efectos) { document.documentElement.classList.add('sin-efectos'); } }, 3000);
</script>

{{-- INICIO --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
{{-- GALERIA --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css">
<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/web/css/animate.css') }}">

<link rel="stylesheet" href="{{ asset('assets/web/css/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/magnific-popup.css') }}">

<link rel="stylesheet" href="{{ asset('assets/web/css/style.css') }}">

{{-- TEMA AMRIT: siempre al final, sobrescribe el aspecto de la plantilla.
     ?v= cambia cada vez que se edita el archivo, así el navegador no usa una copia vieja. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;1,300&family=Inter:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/web/css/tema-amrit.css') }}?v={{ filemtime(public_path('assets/web/css/tema-amrit.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/efectos.css') }}?v={{ filemtime(public_path('assets/web/css/efectos.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/noticias.css') }}?v={{ filemtime(public_path('assets/web/css/noticias.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/menu-carta.css') }}?v={{ filemtime(public_path('assets/web/css/menu-carta.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/modal-pedido.css') }}?v={{ filemtime(public_path('assets/web/css/modal-pedido.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/web/css/panel-pedido.css') }}?v={{ filemtime(public_path('assets/web/css/panel-pedido.css')) }}">
{{-- Mensajes de validaciones.js (compartido con el admin) --}}
<link rel="stylesheet" href="{{ asset('assets/validaciones.css') }}?v={{ filemtime(public_path('assets/validaciones.css')) }}">

