<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Reglas de negocio de los pedidos: crearlos desde el sitio y moverlos de estado desde el admin.
 * Precios, totales, disponibilidad y stock los decide siempre el servidor.
 */
class PedidoService
{
    public function __construct(private FacturaService $facturas)
    {
    }

    /**
     * Guarda el pedido y descuenta el stock. Si dos clientes piden a la vez y chocan en el
     * número de turno (índice único), se reintenta con el turno siguiente.
     *
     * @param  array  $datos   tipo, cliente, telefono, mesa, direccion, nota
     * @param  array<int, int>  $lineas  [id_producto => cantidad]
     * @param  array<int, string>  $notas  [id_producto => instrucciones especiales]
     * @throws DomainException si un producto ya no está disponible o no alcanza el stock
     */
    public function crear(array $datos, array $lineas, array $notas = []): Pedido
    {
        for ($intento = 1; ; $intento++) {
            try {
                return DB::transaction(fn () => $this->guardar($datos, $lineas, $notas));
            } catch (UniqueConstraintViolationException $e) {
                if ($intento >= 3) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Avanza un pedido al siguiente estado o lo cancela. Solo se permiten los pasos válidos.
     *
     * @throws DomainException si la pantalla estaba desactualizada (el paso ya no es válido)
     */
    public function cambiarEstado(int $id, string $nuevo): Pedido
    {
        return DB::transaction(function () use ($id, $nuevo) {
            // Se bloquea la fila para que dos personas no cambien el mismo pedido a la vez
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            $permitido = $nuevo === 'cancelado'
                ? $pedido->estaAbierto()
                : $pedido->siguienteEstado() === $nuevo;

            if (! $permitido) {
                throw new DomainException(
                    "El {$pedido->turnoVisible()} ya está «{$pedido->etiquetaEstado()}»: la pantalla estaba desactualizada."
                );
            }

            // Un pedido cancelado devuelve a la vitrina lo que había descontado
            if ($nuevo === 'cancelado') {
                $this->devolverStock($pedido);
            }

            $pedido->update(['estado' => $nuevo, 'fecha_estado' => now()]);

            // Al entregar se emite el comprobante (sin tocar stock: el pedido ya lo descontó)
            if ($nuevo === 'entregado') {
                $this->facturas->emitirDesdePedido($pedido);
            }

            return $pedido;
        });
    }

    // ------------------------------------------------------------------

    private function guardar(array $datos, array $lineas, array $notas): Pedido
    {
        // lockForUpdate: mientras se arma este pedido, nadie más puede llevarse esas mismas unidades
        $productos = Producto::whereIn('id_producto', array_keys($lineas))->lockForUpdate()->get()->keyBy('id_producto');

        $detalles = [];
        $total = 0.0;

        foreach ($lineas as $id => $cantidad) {
            $producto = $productos->get($id);

            if (! $producto || ! $producto->visible || ! $producto->disponible) {
                $nombre = $producto?->nombre ?? 'Un producto';
                throw new DomainException("«{$nombre}» ya no está disponible. Quítalo de tu pedido para continuar.");
            }

            // Con stock controlado no se puede pedir más de lo que hay
            if (! is_null($producto->stock) && $cantidad > $producto->stock) {
                throw new DomainException(
                    "De «{$producto->nombre}» solo quedan {$producto->stock}. Baja la cantidad para continuar."
                );
            }

            $subtotal = round((float) $producto->precio * $cantidad, 2);
            $total += $subtotal;

            $detalles[] = [
                'id_producto' => $producto->id_producto,
                'descripcion' => $producto->nombre,           // copia: el pedido no cambia si luego editan el producto
                'nota'        => $notas[$id] ?? null,
                'cantidad'    => $cantidad,
                'precio'      => $producto->precio,
                'subtotal'    => $subtotal,
                // 1 si esta línea va a descontar stock (producto con stock controlado)
                'stock_descontado' => is_null($producto->stock) ? 0 : 1,
            ];
        }

        $ahora = now();
        $hoy = $ahora->toDateString();

        // Turno: 1, 2, 3... y vuelve a empezar cada día
        $turno = (int) Pedido::where('fecha_dia', $hoy)->lockForUpdate()->max('turno') + 1;

        $pedido = Pedido::create([
            'fecha_dia'    => $hoy,
            'turno'        => $turno,
            'codigo'       => Str::random(32),
            'tipo'         => $datos['tipo'],
            'mesa'         => $datos['mesa'] ?? null,
            'cliente'      => $datos['cliente'],
            'telefono'     => $datos['telefono'] ?? null,
            'direccion'    => $datos['direccion'] ?? null,
            'nota'         => $datos['nota'] ?? null,
            'total'        => round($total, 2),
            'estado'       => 'pendiente',
            'fecha'        => $ahora,
            'fecha_estado' => $ahora,
        ]);

        $pedido->detalles()->createMany($detalles);

        // El stock se descuenta al pedir, no al entregar: así dos clientes no se llevan la misma última unidad,
        // y cuando llega a 0 el producto pasa solo a "Agotado" en el menú.
        // moverStock no hace nada con los productos que no controlan stock.
        foreach ($lineas as $id => $cantidad) {
            $productos->get($id)->moverStock(-$cantidad, 'venta', 'Pedido #' . $pedido->folio());
        }

        return $pedido;
    }

    /** Devuelve el stock de las líneas que lo descontaron y las marca para no devolverlo dos veces */
    private function devolverStock(Pedido $pedido): void
    {
        $referencia = 'Cancelación pedido #' . $pedido->folio();

        foreach ($pedido->detalles()->where('stock_descontado', true)->get() as $detalle) {
            // El producto pudo borrarse después de pedirse (id_producto queda en NULL)
            Producto::moverStockDe($detalle->id_producto, $detalle->cantidad, 'anulacion', $referencia);

            $detalle->update(['stock_descontado' => false]);
        }
    }
}
