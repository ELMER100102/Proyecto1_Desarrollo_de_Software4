<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\FacturaService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Pantalla de pedidos del admin (caja / cocina).
 * Se llama "PedidoAdminController" para no confundirla con el PedidoController del sitio público.
 */
class PedidoAdminController extends Controller
{
    /** Pantalla principal. ?ver=abiertos (por defecto) | hoy */
    public function index(Request $request)
    {
        $ver = $request->query('ver') === 'hoy' ? 'hoy' : 'abiertos';

        return view('admin.pedidos.index', [
            'ver'           => $ver,
            'pedidos'       => $this->consulta($ver),
            'totalAbiertos' => Pedido::whereIn('estado', Pedido::ABIERTOS)->count(),
            'totalHoy'      => Pedido::where('fecha_dia', now()->toDateString())->count(),
            'config'        => ConfigPedido::actual(),
        ]);
    }

    /** Solo las tarjetas (HTML): la pantalla las vuelve a pedir cuando hay cambios */
    public function lista(Request $request)
    {
        $ver = $request->query('ver') === 'hoy' ? 'hoy' : 'abiertos';

        return view('admin.pedidos._lista', [
            'ver'     => $ver,
            'pedidos' => $this->consulta($ver),
        ]);
    }

    /**
     * Resumen liviano que consulta el navegador cada pocos segundos (en todas las pantallas del admin).
     *   ?desde=<id del último pedido que ya conocía> para saber cuántos son nuevos
     */
    public function resumen(Request $request): JsonResponse
    {
        $desde = max(0, (int) $request->query('desde', 0));

        $ultimo = (int) Pedido::max('id_pedido');
        $abiertos = Pedido::whereIn('estado', Pedido::ABIERTOS)->count();
        $nuevos = $desde > 0 ? Pedido::where('id_pedido', '>', $desde)->count() : 0;

        $texto = null;
        if ($ultimo > 0) {
            $p = Pedido::find($ultimo);
            $texto = $p ? $p->turnoVisible() . ' · ' . $p->cliente . ' · ' . $p->etiquetaTipo() : null;
        }

        return response()->json([
            'pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'abiertos'   => $abiertos,
            'ultimo'     => $ultimo,
            'nuevos'     => $nuevos,
            'texto'      => $texto,
            // Cambia cuando entra un pedido o alguno cambia de estado
            'version'    => md5($abiertos . '|' . $ultimo . '|' . Pedido::max('fecha_estado')),
        ])->header('Cache-Control', 'no-store');
    }

    /** Avanza un pedido al siguiente estado o lo cancela. Solo se permiten los pasos válidos. */
    public function cambiarEstado(Request $request, int $id, FacturaService $facturas)
    {
        $nuevo = $request->validate([
            'estado' => ['required', Rule::in(['preparando', 'listo', 'entregado', 'cancelado'])],
        ])['estado'];

        try {
            $pedido = DB::transaction(function () use ($id, $nuevo, $facturas) {
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
                    $facturas->emitirDesdePedido($pedido);
                }

                return $pedido;
            });
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$pedido->turnoVisible()}: {$pedido->etiquetaEstado()}.");
    }

    // ------------------------------------------------------------------

    /** Devuelve el stock de las líneas que lo descontaron y las marca para no devolverlo dos veces */
    private function devolverStock(Pedido $pedido): void
    {
        $referencia = 'Cancelación pedido #' . str_pad($pedido->id_pedido, 6, '0', STR_PAD_LEFT);

        foreach ($pedido->detalles()->where('stock_descontado', true)->get() as $detalle) {
            // El producto pudo borrarse después de pedirse (id_producto queda en NULL)
            $producto = $detalle->id_producto
                ? Producto::lockForUpdate()->find($detalle->id_producto)
                : null;

            $producto?->moverStock($detalle->cantidad, 'anulacion', $referencia);

            $detalle->update(['stock_descontado' => false]);
        }
    }

    private function consulta(string $ver)
    {
        $consulta = Pedido::with('detalles');

        if ($ver === 'hoy') {
            // Todo lo de hoy, lo más reciente primero
            return $consulta->where('fecha_dia', now()->toDateString())
                ->orderByDesc('fecha')->limit(150)->get();
        }

        // Abiertos: el más antiguo primero (el que lleva más tiempo esperando)
        return $consulta->whereIn('estado', Pedido::ABIERTOS)
            ->orderBy('fecha')->limit(150)->get();
    }
}
