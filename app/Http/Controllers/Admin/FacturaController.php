<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contacto;
use App\Models\Factura;
use App\Models\Producto;
use App\Services\FacturaService;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Comprobantes de la caja (POS). La lógica de emitir y anular está en FacturaService. */
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
                'impuesto'  => $p->tarifaIva(),
            ])
            ->values();

        return view('admin.facturas.create', compact('productos'));
    }

    public function store(Request $request, FacturaService $facturas): RedirectResponse
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

        // Si el mismo producto viene dos veces, se suman las cantidades: [id_producto => cantidad]
        $cantidades = collect($datos['items'])
            ->groupBy('id_producto')
            ->map(fn ($grupo) => (int) $grupo->sum('cantidad'))
            ->all();

        try {
            $factura = $facturas->vender((string) ($datos['cliente'] ?? ''), $datos['tipo_pago'], $cantidades);
        } catch (DomainException $e) {
            // Error de negocio (sin stock, agotado): vuelve al formulario conservando lo digitado
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.facturas.index')
            ->with('success', "Comprobante #{$factura->numero} creado.")
            ->with('imprimir', $factura->id_factura);
    }

    public function anular(Factura $factura, FacturaService $facturas): RedirectResponse
    {
        if ($factura->estaAnulada()) {
            return back()->with('error', 'Ese comprobante ya estaba anulado.');
        }

        $facturas->anular($factura);

        return back()->with('success', "Comprobante #{$factura->numero} anulado y stock devuelto.");
    }

    // ------------------------------------------------------------------
    // PDF

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

    /** Encabezado del comprobante: datos de la tabla contacto, con respaldo en config/negocio.php */
    private function datosNegocio(): array
    {
        $c = Contacto::actual();

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
}
