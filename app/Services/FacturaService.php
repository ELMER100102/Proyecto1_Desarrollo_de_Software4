<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Producto;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Comprobantes: venta en caja (POS), comprobante de un pedido entregado y anulación.
 * Los precios llevan el IVA incluido; aquí se separa la base y el impuesto.
 */
class FacturaService
{
    /**
     * Venta desde la caja. Descuenta el stock de los productos que lo controlan.
     *
     * @param  array<int, int>  $cantidades  [id_producto => cantidad]
     * @throws DomainException si un producto está agotado o no alcanza el stock
     */
    public function vender(string $cliente, string $tipoPago, array $cantidades): Factura
    {
        return DB::transaction(function () use ($cliente, $tipoPago, $cantidades) {
            // Bloquea las filas para que dos ventas simultáneas no descuenten el mismo stock
            $productos = Producto::whereIn('id_producto', array_keys($cantidades))
                ->lockForUpdate()
                ->get()
                ->keyBy('id_producto');

            $lineas = [];

            foreach ($cantidades as $id => $cantidad) {
                $p = $productos[$id];

                if (! $p->disponible) {
                    throw new DomainException("«{$p->nombre}» está marcado como agotado.");
                }
                if ($p->stock !== null && $p->stock < $cantidad) {
                    throw new DomainException(
                        "Stock insuficiente de «{$p->nombre}»: quedan {$p->stock} y se piden {$cantidad}. " .
                        'Si ya horneó más, actualice el stock del producto.'
                    );
                }

                $lineas[] = [
                    'id_producto'         => $p->id_producto,
                    'descripcion'         => $p->nombre,
                    'cantidad'            => $cantidad,
                    'precio'              => $p->precio,
                    'impuesto_porcentaje' => $p->tarifaIva(),
                    'stock_descontado'    => $p->stock !== null,
                ];
            }

            $factura = $this->emitir($cliente, $tipoPago, $lineas);

            // Descuenta el stock y deja el movimiento anotado (moverStock ignora los que no lo controlan)
            foreach ($cantidades as $id => $cantidad) {
                $productos[$id]->moverStock(-$cantidad, 'venta', 'Factura #' . $factura->codigo());
            }

            return $factura;
        });
    }

    /**
     * Comprobante de un pedido entregado.
     *
     * NO toca el stock: el pedido ya lo descontó al crearse (y lo devolvería si se cancelara),
     * así que las líneas quedan con stock_descontado = 0 y anular esta factura no devuelve existencias.
     * Debe llamarse dentro de una DB::transaction (el consecutivo usa lockForUpdate).
     */
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

        $lineas = $pedido->detalles->map(fn ($d) => [
            'id_producto'         => $d->id_producto,
            'descripcion'         => $d->descripcion,
            'cantidad'            => $d->cantidad,
            'precio'              => $d->precio,
            'impuesto_porcentaje' => $productos->get($d->id_producto)?->tarifaIva() ?? (float) Factura::IVA_POR_DEFECTO,
            'stock_descontado'    => false,
        ])->all();

        // Se paga en el local al recibir
        $factura = $this->emitir($pedido->cliente, 'contado', $lineas);

        $pedido->update(['id_factura' => $factura->id_factura]);

        return $factura;
    }

    /** Anula el comprobante y devuelve el stock de las líneas que lo habían descontado */
    public function anular(Factura $factura): void
    {
        DB::transaction(function () use ($factura) {
            $referencia = 'Anulación factura #' . $factura->codigo();

            foreach ($factura->detalles()->where('stock_descontado', true)->get() as $d) {
                Producto::moverStockDe($d->id_producto, $d->cantidad, 'anulacion', $referencia);
            }

            $factura->update(['estado' => 'anulada']);
        });
    }

    /**
     * Calcula los totales, toma el siguiente consecutivo y guarda la factura con sus líneas.
     * Cada línea trae: id_producto, descripcion, cantidad, precio (IVA incluido),
     * impuesto_porcentaje y stock_descontado.
     */
    private function emitir(?string $cliente, string $tipoPago, array $lineas): Factura
    {
        $subtotal = 0;
        $total    = 0;

        foreach ($lineas as &$linea) {
            $totalLinea = round($linea['precio'] * $linea['cantidad'], 2);
            $subtotal  += round($totalLinea / (1 + $linea['impuesto_porcentaje'] / 100), 2);
            $total     += $totalLinea;

            $linea['subtotal'] = $totalLinea;
        }
        unset($linea);

        $subtotal = round($subtotal, 2);
        $total    = round($total, 2);

        // Consecutivo: dentro de la transacción y con bloqueo
        $numero = ((int) Factura::lockForUpdate()->max('numero')) + 1;

        $factura = Factura::create([
            'numero'         => $numero,
            'fecha'          => now(),
            'cliente'        => $cliente ?: 'Cliente de contado',
            'tipo_pago'      => $tipoPago,
            'subtotal'       => $subtotal,
            'total_impuesto' => round($total - $subtotal, 2),
            'total'          => $total,
            'estado'         => 'emitida',
        ]);

        $factura->detalles()->createMany($lineas);

        return $factura;
    }
}
