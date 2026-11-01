<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\ConfigPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\PedidoService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pedidos desde el sitio público: carrito -> finalizar pedido -> seguimiento.
 * El navegador solo manda "qué producto y cuántos". Precios, totales, disponibilidad
 * y stock los decide siempre el servidor (PedidoService).
 */
class PedidoController extends Controller
{
    /** Página "Mi pedido": carrito + datos del cliente */
    public function create()
    {
        $config = ConfigPedido::actual();
        $productos = [];
        $categorias = collect();

        if ($config->disponible()) {
            // Productos por categoría para las tarjetas de la página (misma consulta que el menú)
            $categorias = Categoria::paraCarta();

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

        return view('web.pedidos.create', compact('config', 'productos', 'categorias'));
    }

    /** Registra el pedido */
    public function store(Request $request, PedidoService $pedidos)
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

        $datos = $request->validate($this->reglas($request, $config), $this->mensajes());

        $lineas = $this->lineas($datos['items']);
        $notas = $this->notasProductos($datos['notas'] ?? [], $lineas);

        // Datos que no corresponden al tipo elegido no se guardan
        $datos['mesa'] = $datos['tipo'] === Pedido::TIPO_LOCAL ? ($datos['mesa'] ?? null) : null;
        $datos['direccion'] = $datos['tipo'] === Pedido::TIPO_DOMICILIO ? ($datos['direccion'] ?? null) : null;

        try {
            $pedido = $pedidos->crear($datos, $lineas, $notas);
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

    /** items llega como [id_producto => cantidad]; las claves deben ser números */
    private function lineas(array $items): array
    {
        $lineas = [];

        foreach ($items as $id => $cantidad) {
            if (! ctype_digit((string) $id)) {
                abort(422);
            }
            $lineas[(int) $id] = (int) $cantidad;
        }

        return $lineas;
    }

    /**
     * Notas por producto ("Instrucciones especiales"): solo de productos que vienen en el pedido,
     * sin etiquetas HTML y con los espacios normalizados; vacía = sin nota
     */
    private function notasProductos(array $notas, array $lineas): array
    {
        $limpias = [];

        foreach ($notas as $id => $texto) {
            if (! ctype_digit((string) $id) || ! isset($lineas[(int) $id])) {
                continue;
            }
            $limpia = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $texto)));
            if ($limpia !== '') {
                $limpias[(int) $id] = mb_substr($limpia, 0, 200);
            }
        }

        return $limpias;
    }

    private function reglas(Request $request, ConfigPedido $config): array
    {
        $tipo = $request->input('tipo');

        $exigeTelefono = in_array($tipo, [Pedido::TIPO_LLEVAR, Pedido::TIPO_DOMICILIO], true);
        $exigeMesa = $config->usa_mesas && $tipo === Pedido::TIPO_LOCAL;
        $exigeDireccion = $tipo === Pedido::TIPO_DOMICILIO;

        return [
            'tipo'      => ['required', Rule::in(array_keys($config->tiposActivos()))],
            'cliente'   => ['required', 'string', 'max:80'],
            'telefono'  => [Rule::requiredIf($exigeTelefono), 'nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{7,25}$/'],
            'mesa'      => [Rule::requiredIf($exigeMesa), 'nullable', 'string', 'regex:/^[1-9][0-9]{0,2}$/'],
            'direccion' => [Rule::requiredIf($exigeDireccion), 'nullable', 'string', 'max:255'],
            'nota'      => ['nullable', 'string', 'max:255'],
            'items'     => ['required', 'array', 'min:1', 'max:50'],
            'items.*'   => ['required', 'integer', 'min:1', 'max:99'],
            'notas'     => ['nullable', 'array', 'max:50'],
            'notas.*'   => ['nullable', 'string', 'max:200'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'tipo.required'      => 'Elige cómo quieres recibir tu pedido.',
            'tipo.in'            => 'Ese tipo de pedido no está disponible.',
            'cliente.required'   => 'Escribe tu nombre.',
            'cliente.string'     => 'El nombre no es válido.',
            'cliente.max'        => 'El nombre puede tener hasta 80 caracteres.',
            'telefono.required'  => 'Necesitamos un teléfono para avisarte cuando esté listo.',
            'telefono.string'    => 'El teléfono no es válido.',
            'telefono.max'       => 'El teléfono puede tener hasta 25 caracteres.',
            'telefono.regex'     => 'El teléfono solo puede tener números, espacios, + y guiones.',
            'mesa.required'      => 'Indica tu número de mesa.',
            'mesa.string'        => 'El número de mesa no es válido.',
            'mesa.regex'         => 'La mesa debe ser un número entre 1 y 999.',
            'direccion.required' => 'Escribe la dirección de entrega.',
            'direccion.string'   => 'La dirección no es válida.',
            'direccion.max'      => 'La dirección puede tener hasta 255 caracteres.',
            'nota.string'        => 'La nota del pedido no es válida.',
            'nota.max'           => 'La nota del pedido puede tener hasta 255 caracteres.',
            'items.required'     => 'Tu pedido está vacío.',
            'items.array'        => 'Tu pedido no es válido.',
            'items.min'          => 'Tu pedido está vacío.',
            'items.max'          => 'Tu pedido puede tener hasta 50 productos distintos.',
            'items.*.required'   => 'Hay una cantidad que no es válida.',
            'items.*.integer'    => 'Hay una cantidad que no es válida.',
            'items.*.min'        => 'Hay una cantidad que no es válida.',
            'items.*.max'        => 'Puedes pedir hasta 99 unidades de cada producto.',
            'notas.array'        => 'Las notas de los productos no son válidas.',
            'notas.max'          => 'Las notas de los productos no son válidas.',
            'notas.*.string'     => 'Hay una nota de producto que no es válida.',
            'notas.*.max'        => 'La nota de un producto puede tener hasta 200 caracteres.',
        ];
    }
}
