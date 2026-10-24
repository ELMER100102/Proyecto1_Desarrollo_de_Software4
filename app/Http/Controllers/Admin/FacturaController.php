<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contacto;
use App\Models\Factura;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{

    public function index()
    {
        $data = Factura::orderByDesc('numero')->get();

        // Las anuladas no suman en los totales
        $validas = $data->where('estado', 'emitida');

        return view('admin.facturas.index', [
            'data'          => $data,
            'totalFacturas' => $validas->count(),
            'totalContado'  => $validas->where('tipo_pago', 'contado')->sum('total'),
            'totalCredito'  => $validas->where('tipo_pago', 'credito')->sum('total'),
        ]);
    }

    public function create()
    {
        // Solo lo que se puede vender: disponible y, si controla stock, con existencias
        $productos = Producto::with('categoria')
            ->where('disponible', true)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            })
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => [
                'id'        => $p->id_producto,
                'nombre'    => $p->nombre,
                'categoria' => $p->categoria->nombre ?? '',
                'precio'    => (float) $p->precio,
                'stock'     => $p->stock,   // null = sin control
                'impuesto'  => (float) ($p->impuesto ?? Factura::IVA_POR_DEFECTO),
            ])
            ->values();

        return view('admin.facturas.create', compact('productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_pago'           => 'required|in:contado,credito',
            'cliente'             => 'nullable|string|max:150|required_if:tipo_pago,credito',
            'items'               => 'required|array|min:1|max:100',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad'    => 'required|integer|min:1|max:1000',
        ], [
            'cliente.required_if' => 'En una venta a crédito el nombre del cliente es obligatorio.',
            'items.required'      => 'Agregue al menos un producto a la venta.',
            'items.min'           => 'Agregue al menos un producto a la venta.',
        ]);

        // Si el mismo producto viene dos veces, se suman las cantidades
        $cantidades = collect($datos['items'])
            ->groupBy('id_producto')
            ->map(fn ($grupo) => (int) $grupo->sum('cantidad'));

        try {
            $factura = DB::transaction(function () use ($datos, $cantidades) {

                // Bloquea las filas para que dos ventas simultáneas no descuenten el mismo stock
                $productos = Producto::whereIn('id_producto', $cantidades->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_producto');

                $subtotal  = 0;
                $total     = 0;
                $lineas    = [];
                $descontar = [];   // [producto, cantidad]: se aplica cuando ya existe el número de factura

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

                    $tarifa     = (float) ($p->impuesto ?? Factura::IVA_POR_DEFECTO);
                    $totalLinea = round($p->precio * $cantidad, 2);
                    $base       = round($totalLinea / (1 + $tarifa / 100), 2);

                    $subtotal += $base;
                    $total    += $totalLinea;

                    $controlaStock = $p->stock !== null;

                    $lineas[] = [
                        'id_producto'         => $p->id_producto,
                        'descripcion'         => $p->nombre,
                        'cantidad'            => $cantidad,
                        'precio'              => $p->precio,
                        'impuesto_porcentaje' => $tarifa,
                        'subtotal'            => $totalLinea,
                        'stock_descontado'    => $controlaStock,
                    ];

                    if ($controlaStock) {
                        $descontar[] = [$p, $cantidad];
                    }
                }

                $subtotal = round($subtotal, 2);
                $total    = round($total, 2);

                // Consecutivo: dentro de la transacción y con bloqueo
                $numero = ((int) Factura::lockForUpdate()->max('numero')) + 1;

                $factura = Factura::create([
                    'numero'         => $numero,
                    'fecha'          => now(),
                    'cliente'        => $datos['cliente'] ?: 'Cliente de contado',
                    'tipo_pago'      => $datos['tipo_pago'],
                    'subtotal'       => $subtotal,
                    'total_impuesto' => round($total - $subtotal, 2),
                    'total'          => $total,
                    'estado'         => 'emitida',
                ]);

                $factura->detalles()->createMany($lineas);

                // Descuenta el stock y deja el movimiento anotado (regla C incluida)
                foreach ($descontar as [$p, $cantidad]) {
                    $p->moverStock(-$cantidad, 'venta', 'Factura #' . str_pad($numero, 6, '0', STR_PAD_LEFT));
                }

                return $factura;
            });
        } catch (DomainException $e) {
            // Error de negocio (sin stock, agotado): vuelve al formulario conservando lo digitado
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.facturas.index')
            ->with('success', "Comprobante #{$factura->numero} creado.")
            ->with('imprimir', $factura->id_factura);
    }

    public function anular(Factura $factura): RedirectResponse
    {
        if ($factura->estaAnulada()) {
            return back()->with('error', 'Ese comprobante ya estaba anulado.');
        }

        DB::transaction(function () use ($factura) {
            $factura->load('detalles');

            foreach ($factura->detalles as $d) {
                // Solo devuelve stock si esa línea lo había descontado
                if (! $d->stock_descontado || ! $d->id_producto) {
                    continue;
                }

                $p = Producto::lockForUpdate()->find($d->id_producto);

                // Devuelve el stock (si el producto sigue controlándolo) y anota el movimiento
                $p?->moverStock(
                    $d->cantidad,
                    'anulacion',
                    'Anulación factura #' . str_pad($factura->numero, 6, '0', STR_PAD_LEFT)
                );
            }

            $factura->update(['estado' => 'anulada']);
        });

        return back()->with('success', "Comprobante #{$factura->numero} anulado y stock devuelto.");
    }


    private function datosNegocio(): array
    {
        $c = Contacto::first();

        $logo = $c?->logo ? public_path('storage/' . $c->logo) : null;

        return [
            'nombre'    => $c?->nombre_empresa ?: config('negocio.nombre'),
            'direccion' => $c?->direccion ?: config('negocio.direccion'),
            'telefono'  => $c?->telefono ?: config('negocio.telefono'),
            'correo'    => $c?->correo,
            'logo'      => ($logo && is_file($logo)) ? $logo : null,   // ruta en disco, la que pide Dompdf
            'cedula'    => config('negocio.cedula'),                    // no existe en la tabla contacto
            'leyenda'   => config('negocio.leyenda'),
        ];
    }

    public function pdfCarta(Factura $factura)
    {
        $factura->load('detalles');

        return Pdf::loadView('admin.facturas.pdf.carta', [
            'factura' => $factura,
            'negocio' => $this->datosNegocio(),
        ])
            ->setPaper('letter', 'portrait')
            ->stream("comprobante-{$factura->numero}-carta.pdf");
    }

    public function pdfTicket(Factura $factura)
    {
        $factura->load('detalles');

        // 80 mm de ancho = 226.77 pt. El alto crece con la cantidad de líneas.
        $alto = 330 + ($factura->detalles->count() * 40);

        return Pdf::loadView('admin.facturas.pdf.ticket', [
            'factura' => $factura,
            'negocio' => $this->datosNegocio(),
        ])
            ->setPaper([0, 0, 226.77, $alto], 'portrait')
            ->stream("comprobante-{$factura->numero}-ticket.pdf");
    }
}