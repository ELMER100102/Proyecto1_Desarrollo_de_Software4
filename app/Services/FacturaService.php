<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Producto;

/**
 * Emite el comprobante de un pedido entregado.
 *
 * Importante: NO toca el stock. El pedido ya lo descontó al crearse (y lo
 * devolvería si se cancelara), así que aquí las líneas quedan con
 * stock_descontado = 0 y anular esta factura no devuelve existencias.
 *
 * Debe llamarse dentro de una DB::transaction (el consecutivo usa lockForUpdate).
 */
class FacturaService
{
    public function emitirDesdePedido(Pedido $pedido): Factura
    {
        // Si ya tiene factura no se emite otra (doble clic, pantalla desactualizada)
        if ($pedido->id_factura) {
            return $pedido->factura;
        }

        $pedido->loadMissing('detalles');

        // Tarifa actual de cada producto; si fue borrado o no tiene, la del sistema
        $productos = Producto::whereIn('id_producto', $pedido->detalles->pluck('id_producto')->filter())
            ->get()
            ->keyBy('id_producto');

        $subtotal = 0;
        $total    = 0;
        $lineas   = [];

        foreach ($pedido->detalles as $d) {
            $tarifa     = (float) ($productos->get($d->id_producto)?->impuesto ?? Factura::IVA_POR_DEFECTO);
            $totalLinea = round($d->precio * $d->cantidad, 2);   // precios con IVA incluido, igual que en el POS
            $base       = round($totalLinea / (1 + $tarifa / 100), 2);

            $subtotal += $base;
            $total    += $totalLinea;

            $lineas[] = [
                'id_producto'         => $d->id_producto,
                'descripcion'         => $d->descripcion,
                'cantidad'            => $d->cantidad,
                'precio'              => $d->precio,
                'impuesto_porcentaje' => $tarifa,
                'subtotal'            => $totalLinea,
                'stock_descontado'    => false,
            ];
        }

        $subtotal = round($subtotal, 2);
        $total    = round($total, 2);

        $numero = ((int) Factura::lockForUpdate()->max('numero')) + 1;

        $factura = Factura::create([
            'numero'         => $numero,
            'fecha'          => now(),
            'cliente'        => $pedido->cliente ?: 'Cliente de contado',
            'tipo_pago'      => 'contado',   // se paga en el local al recibir
            'subtotal'       => $subtotal,
            'total_impuesto' => round($total - $subtotal, 2),
            'total'          => $total,
            'estado'         => 'emitida',
        ]);

        $factura->detalles()->createMany($lineas);

        $pedido->update(['id_factura' => $factura->id_factura]);

        return $factura;
    }
}
