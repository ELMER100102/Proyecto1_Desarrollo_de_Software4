<?php

namespace App\Http\Controllers;

use App\Models\ConfigPedido;
use App\Models\Pedido;
use App\Models\Producto;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pedidos desde el sitio público: carrito -> finalizar pedido -> seguimiento.
 * El navegador solo manda "qué producto y cuántos". Precios, totales, disponibilidad
 * y stock los decide siempre el servidor.
 */
class PedidoController extends Controller
{
    /** Página "Mi pedido": carrito + datos del cliente */
    public function create()
    {
        $config = ConfigPedido::actual();
        $productos = [];

        if ($config->disponible()) {
            // Solo lo que se puede pedir hoy. El JS usa este mapa para dibujar el carrito.
            $productos = Producto::where('visible', true)
                ->where('disponible', true)
                ->get()
                ->mapWithKeys(fn (Producto $p) => [
                    $p->id_producto => [
                        'nombre' => $p->nombre,
                        'precio' => (float) $p->precio,
                        'imagen' => $p->imagen ? asset('storage/' . $p->imagen) : null,
                        'stock'  => $p->stock,   // null = sin control de stock
                    ],
                ])
                ->all();
        }

        return view('web.pedidos.create', compact('config', 'productos'));
    }

    /** Registra el pedido */
    public function store(Request $request)
    {
        $config = ConfigPedido::actual();

        if (! $config->disponible()) {
            return redirect()->route('pedido.create')
                ->withErrors(['general' => 'Por el momento no estamos recibiendo pedidos.']);
        }

        // Campo trampa para robots: una persona nunca lo ve ni lo llena
        if ($request->filled('sitio_web')) {
            abort(422);
        }

        $exigeTelefono = fn () => in_array($request->input('tipo'), [Pedido::TIPO_LLEVAR, Pedido::TIPO_DOMICILIO], true);
        $exigeMesa = fn () => $config->usa_mesas && $request->input('tipo') === Pedido::TIPO_LOCAL;
        $exigeDireccion = fn () => $request->input('tipo') === Pedido::TIPO_DOMICILIO;

        $datos = $request->validate([
            'tipo'      => ['required', Rule::in(array_keys($config->tiposActivos()))],
            'cliente'   => ['required', 'string', 'max:80'],
            'telefono'  => [Rule::requiredIf($exigeTelefono), 'nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{7,25}$/'],
            'mesa'      => [Rule::requiredIf($exigeMesa), 'nullable', 'string', 'max:20'],
            'direccion' => [Rule::requiredIf($exigeDireccion), 'nullable', 'string', 'max:255'],
            'nota'      => ['nullable', 'string', 'max:255'],
            'items'     => ['required', 'array', 'min:1', 'max:50'],
            'items.*'   => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'tipo.required'      => 'Elige cómo quieres recibir tu pedido.',
            'tipo.in'            => 'Ese tipo de pedido no está disponible.',
            'cliente.required'   => 'Escribe tu nombre.',
            'telefono.required'  => 'Necesitamos un teléfono para avisarte cuando esté listo.',
            'telefono.regex'     => 'El teléfono solo puede tener números, espacios, + y guiones.',
            'mesa.required'      => 'Indica tu número de mesa.',
            'direccion.required' => 'Escribe la dirección de entrega.',
            'items.required'     => 'Tu pedido está vacío.',
            'items.min'          => 'Tu pedido está vacío.',
            'items.*.integer'    => 'Hay una cantidad que no es válida.',
            'items.*.min'        => 'Hay una cantidad que no es válida.',
            'items.*.max'        => 'Puedes pedir hasta 99 unidades de cada producto.',
        ]);

        // items llega como [id_producto => cantidad]; las claves deben ser números
        $lineas = [];
        foreach ($datos['items'] as $id => $cantidad) {
            if (! ctype_digit((string) $id)) {
                abort(422);
            }
            $lineas[(int) $id] = (int) $cantidad;
        }

        // Datos que no corresponden al tipo elegido no se guardan
        $datos['mesa'] = $datos['tipo'] === Pedido::TIPO_LOCAL ? ($datos['mesa'] ?? null) : null;
        $datos['direccion'] = $datos['tipo'] === Pedido::TIPO_DOMICILIO ? ($datos['direccion'] ?? null) : null;

        try {
            $pedido = $this->crearPedido($datos, $lineas);
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()->route('pedido.seguimiento', $pedido->codigo)->with('nuevo', true);
    }

    /** Página de seguimiento (se entra con el código secreto del pedido) */
    public function seguimiento(Pedido $pedido)
    {
        $pedido->load('detalles');
        $config = ConfigPedido::actual();

        return view('web.pedidos.seguimiento', compact('pedido', 'config'));
    }

    /** Estado actual en JSON: lo consulta la página de seguimiento cada pocos segundos */
    public function estado(Pedido $pedido): JsonResponse
    {
        return response()->json([
            'estado'   => $pedido->estado,
            'etiqueta' => $pedido->etiquetaEstado(),
            'mensaje'  => $pedido->mensajeSeguimiento(),
            'abierto'  => $pedido->estaAbierto(),
        ])->header('Cache-Control', 'no-store');
    }

    // ------------------------------------------------------------------

    /**
     * Guarda el pedido. Si dos clientes piden a la vez y chocan en el número de turno
     * (índice único), se reintenta con el turno siguiente.
     */
    private function crearPedido(array $datos, array $lineas): Pedido
    {
        for ($intento = 1; ; $intento++) {
            try {
                return DB::transaction(fn () => $this->guardar($datos, $lineas));
            } catch (UniqueConstraintViolationException $e) {
                if ($intento >= 3) {
                    throw $e;
                }
            }
        }
    }

    private function guardar(array $datos, array $lineas): Pedido
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
            'mesa'         => $datos['mesa'],
            'cliente'      => $datos['cliente'],
            'telefono'     => $datos['telefono'] ?? null,
            'direccion'    => $datos['direccion'],
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
        $referencia = 'Pedido #' . str_pad($pedido->id_pedido, 6, '0', STR_PAD_LEFT);

        foreach ($lineas as $id => $cantidad) {
            $productos->get($id)->moverStock(-$cantidad, 'venta', $referencia);
        }

        return $pedido;
    }
}
