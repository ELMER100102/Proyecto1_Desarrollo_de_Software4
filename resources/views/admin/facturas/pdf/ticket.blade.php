{{-- Ticket de 80 mm. El ancho del papel se define en el controlador (226.77 pt). --}}
@php
    $m = fn ($n) => '₡' . number_format($n, 2, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ str_pad($factura->numero, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 6mm 4mm; }
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 8.5px; color: #000; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .centro { text-align: center; }
        .derecha { text-align: right; }
        .negrita { font-weight: bold; }
        .linea { border-top: 1px dashed #000; margin: 6px 0; }
        .nombre-negocio { font-size: 11px; font-weight: bold; }
        .total td { font-size: 12px; font-weight: bold; padding-top: 3px; }
        .anulada { border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px; margin-bottom: 6px; }
        .pequeno { font-size: 7px; }
    </style>
</head>
<body>

    @if ($factura->estaAnulada())
        <div class="anulada">*** ANULADO ***</div>
    @endif

    <div class="centro">
        <div class="nombre-negocio">{{ $negocio['nombre'] }}</div>
        <div>Céd.: {{ $negocio['cedula'] }}</div>
        <div>Tel.: {{ $negocio['telefono'] }}</div>
        <div>{{ $negocio['direccion'] }}</div>
    </div>

    <div class="linea"></div>

    <div class="negrita">Comprobante N.º {{ str_pad($factura->numero, 6, '0', STR_PAD_LEFT) }}</div>
    <div>Fecha: {{ $factura->fecha->format('d/m/Y H:i') }}</div>
    <div>Cliente: {{ $factura->cliente }}</div>
    <div>Pago: {{ ucfirst($factura->tipo_pago) }}</div>

    <div class="linea"></div>

    <table>
        @foreach ($factura->detalles as $d)
            <tr>
                <td colspan="2" class="negrita">{{ $d->descripcion }}</td>
            </tr>
            <tr>
                <td>{{ $d->cantidad }} x {{ $m($d->precio) }}</td>
                <td class="derecha">{{ $m($d->subtotal) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="linea"></div>

    <table>
        <tr>
            <td>Subtotal (sin IVA)</td>
            <td class="derecha">{{ $m($factura->subtotal) }}</td>
        </tr>
        <tr>
            <td>IVA incluido</td>
            <td class="derecha">{{ $m($factura->total_impuesto) }}</td>
        </tr>
        <tr class="total">
            <td>TOTAL</td>
            <td class="derecha">{{ $m($factura->total) }}</td>
        </tr>
    </table>

    <div class="linea"></div>

    <div class="centro">¡Gracias por su compra!</div>
    <div class="centro pequeno" style="margin-top: 4px;">{{ $negocio['leyenda'] }}</div>

</body>
</html>