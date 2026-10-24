<h2>Nuevo mensaje desde el sitio web</h2>

<p><strong>Nombre:</strong> {{ $datos['nombre'] }}</p>
<p><strong>Correo:</strong> {{ $datos['correo'] }}</p>
<p><strong>Asunto:</strong> {{ $datos['asunto'] }}</p>

<p><strong>Mensaje:</strong></p>
{{-- 
El e() escapa el HTML, porque el texto lo escribe un visitante y no debe interpretarse como código.
nl2br conserva los saltos de línea.
 --}}
<p>{!! nl2br(e($datos['mensaje'])) !!}</p>