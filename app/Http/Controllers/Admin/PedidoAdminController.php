<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigPedido;
use App\Models\Pedido;
use App\Services\PedidoService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $ver = $this->vista($request);

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
        $ver = $this->vista($request);

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

        $p = Pedido::latest('id_pedido')->first();
        $ultimo = (int) $p?->id_pedido;
        $abiertos = Pedido::whereIn('estado', Pedido::ABIERTOS)->count();
        $nuevos = $desde > 0 ? Pedido::where('id_pedido', '>', $desde)->count() : 0;
        $texto = $p ? $p->turnoVisible() . ' · ' . $p->cliente . ' · ' . $p->etiquetaTipo() : null;

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

    /** Avanza un pedido al siguiente estado o lo cancela (botones de cada tarjeta) */
    public function cambiarEstado(Request $request, int $id, PedidoService $pedidos)
    {
        $nuevo = $request->validate([
            'estado' => ['required', Rule::in(['preparando', 'listo', 'entregado', 'cancelado'])],
        ])['estado'];

        try {
            $pedido = $pedidos->cambiarEstado($id, $nuevo);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$pedido->turnoVisible()}: {$pedido->etiquetaEstado()}.");
    }

    // ------------------------------------------------------------------

    /** Pedidos de la pantalla: ?ver=abiertos (por defecto) | hoy */
    private function vista(Request $request): string
    {
        return $request->query('ver') === 'hoy' ? 'hoy' : 'abiertos';
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
