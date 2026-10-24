<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Compra::with('proveedor')->orderByDesc('id_compra')->get();

        $registradas = $data->where('estado', 'registrada');

        return view('admin.compras.index', [
            'data'         => $data,
            'totalCompras' => $registradas->count(),
            'montoTotal'   => $registradas->sum('total'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();

        // Se puede comprar cualquier producto, incluso uno agotado
        $productos = Producto::with('categoria')
            ->orderBy('nombre')
            ->get()
            ->map(fn($p) => [
                'id'        => $p->id_producto,
                'nombre'    => $p->nombre,
                'categoria' => $p->categoria->nombre ?? '',
                'stock'     => $p->stock,                                        // null = sin control
                'costo'     => $p->costo !== null ? (float) $p->costo : null,    // último costo
            ])
            ->values();

        return view('admin.compras.create', compact('proveedores', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_proveedor'        => 'required|integer|exists:proveedores,id_proveedor',
            'documento'           => 'nullable|string|max:50',
            'fecha'               => 'required|date|before_or_equal:today',
            'nota'                => 'nullable|string|max:255',
            'items'               => 'required|array|min:1|max:100',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad'    => 'required|integer|min:1|max:100000',
            'items.*.costo'       => 'required|numeric|min:0|max:99999999.99',
        ], [
            'items.required'        => 'Agregue al menos un producto a la compra.',
            'items.min'             => 'Agregue al menos un producto a la compra.',
            'fecha.before_or_equal' => 'La fecha de la compra no puede ser futura.',
        ]);

        [$compra, $sinControl] = DB::transaction(function () use ($datos) {

            // Bloquea los productos para que ninguna venta cambie el stock a mitad de camino
            $ids = collect($datos['items'])->pluck('id_producto')->unique();
            $productos = Producto::whereIn('id_producto', $ids)->lockForUpdate()->get()->keyBy('id_producto');

            $total      = 0;
            $lineas     = [];
            $aplicar    = [];   // [producto, cantidad, costo]
            $sinControl = [];

            foreach ($datos['items'] as $item) {
                $p        = $productos[$item['id_producto']];
                $cantidad = (int) $item['cantidad'];
                $costo    = round((float) $item['costo'], 2);
                $subtotal = round($costo * $cantidad, 2);

                $total += $subtotal;

                $lineas[] = [
                    'id_producto'    => $p->id_producto,
                    'descripcion'    => $p->nombre,
                    'cantidad'       => $cantidad,
                    'costo_unitario' => $costo,
                    'subtotal'       => $subtotal,
                    'stock_sumado'   => $p->stock !== null,
                ];

                $aplicar[] = [$p, $cantidad, $costo];

                if ($p->stock === null) {
                    $sinControl[$p->id_producto] = $p->nombre;
                }
            }

            $compra = Compra::create([
                'id_proveedor' => $datos['id_proveedor'],
                'documento'    => $datos['documento'] ?? null,
                // El formulario manda solo el día; se le pone la hora actual
                'fecha'        => Carbon::parse($datos['fecha'])->setTimeFrom(now()),
                'total'        => round($total, 2),
                'nota'         => $datos['nota'] ?? null,
                'estado'       => 'registrada',
            ]);

            $compra->detalles()->createMany($lineas);

            // Suma el stock (si el producto lo controla) y guarda el último costo
            foreach ($aplicar as [$p, $cantidad, $costo]) {
                $p->moverStock($cantidad, 'compra', 'Compra #' . $compra->codigo());

                $p->costo = $costo;
                $p->save();
            }

            return [$compra, $sinControl];
        });

        $respuesta = redirect()
            ->route('admin.compras.index')
            ->with('success', "Compra #{$compra->codigo()} registrada.");

        if ($sinControl) {
            $respuesta->with(
                'aviso',
                'No se actualizó el stock de: ' . implode(', ', $sinControl) .
                    ' (no controlan existencias). Si quiere controlarlo, ponga un stock inicial en el producto.'
            );
        }

        return $respuesta;
    }

    public function anular(Compra $compra): RedirectResponse
    {
        if ($compra->estaAnulada()) {
            return back()->with('error', 'Esa compra ya estaba anulada.');
        }

        try {
            DB::transaction(function () use ($compra) {
                $compra->load('detalles');

                foreach ($compra->detalles as $d) {
                    if (! $d->stock_sumado || ! $d->id_producto) {
                        continue;
                    }

                    $p = Producto::lockForUpdate()->find($d->id_producto);

                    $p?->moverStock(-$d->cantidad, 'anulacion', 'Anulación compra #' . $compra->codigo());
                }

                $compra->update(['estado' => 'anulada']);
            });
        } catch (DomainException $e) {
            return back()->with('error', 'No se puede anular: parte de esa mercadería ya se vendió. ' . $e->getMessage());
        }

        return back()->with('success', "Compra #{$compra->codigo()} anulada y stock descontado.");
    }
}
