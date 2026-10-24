<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Producto::with('categoria')->orderBy('nombre')->get();
        return view('admin.productos.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = new Producto([
            'id_categoria' => $request->categoria,   // permite /productos/create?categoria=3
            'disponible'   => true,
            'visible'      => true,
        ]);
        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.productos.create', compact('data', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('uploads/productos', 'public');
        }
        if (isset($datos['stock']) && (int) $datos['stock'] === 0) {
            $datos['disponible'] = false;
        }

        $producto = Producto::create($datos);

        if ($producto->stock) {
            MovimientoStock::registrar($producto, 'ajuste', $producto->stock, null, 'Stock inicial');
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('admin.productos.edit', ['data' => $producto, 'categorias' => $categorias]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        $stockAnterior = $producto->stock;          // lo que hay guardado

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {

                Storage::disk('public')->delete($producto->imagen);
            }
            $datos['imagen'] = $request->file('imagen')->store('uploads/productos', 'public');
        }
        
        $producto->update($datos);

        $stockNuevo = $producto->stock;

        if ($stockNuevo !== null && $stockNuevo !== $stockAnterior) {
            MovimientoStock::registrar(
                $producto,
                'ajuste',
                $stockNuevo - ($stockAnterior ?? 0),
                null,
                $stockAnterior === null ? 'Inicio del control de stock' : 'Ajuste manual'
            );
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function alternar(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'campo' => 'required|in:disponible,visible',
            'valor' => 'required|boolean',
        ]);

        $producto->update([$datos['campo'] => $datos['valor']]);

        return response()->json([
            'campo' => $datos['campo'],
            'valor' => $producto->{$datos['campo']},
        ]);
    }

    public function buscarCabys(Request $request)
    {
        $texto = trim((string) $request->query('q', ''));

        if (mb_strlen($texto) < 3) {
            return response()->json(['resultados' => []]);
        }

        try {
            $resultados = Cache::remember('cabys_' . md5(mb_strtolower($texto)), now()->addDay(), function () use ($texto) {
                $respuesta = Http::timeout(8)
                    ->acceptJson()
                    ->get('https://api.hacienda.go.cr/fe/cabys', ['q' => $texto, 'top' => 20])
                    ->throw()
                    ->json();

                // AJUSTAR AQUÍ si el formato de Hacienda es distinto
                $lista = $respuesta['cabys'] ?? (array_is_list($respuesta) ? $respuesta : []);

                return collect($lista)->map(fn($item) => [
                    'codigo'      => $item['codigo'] ?? '',
                    'descripcion' => $item['descripcion'] ?? '',
                    'impuesto'    => $item['impuesto'] ?? null,
                ])->values()->all();
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => 'No se pudo consultar el catálogo.'], 502);
        }

        return response()->json(['resultados' => $resultados]);
    }

    private function reglas(): array
    {
        return [
            'id_categoria'      => 'required|exists:categorias,id_categoria',
            'nombre'            => 'required|string|max:150',
            'descripcion'       => 'nullable|string|max:255',
            'precio'            => 'required|numeric|min:0|max:99999999.99',
            'imagen'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'disponible'        => 'required|boolean',
            'visible'           => 'required|boolean',
            'stock' => 'nullable|integer|min:0|max:100000',
            'cabys_codigo'      => ['nullable', 'digits:13'],
            'cabys_descripcion' => ['nullable', 'string', 'max:255'],
            'impuesto'          => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'id_categoria.required' => 'Seleccione una categoría.',
            'id_categoria.exists'   => 'La categoría seleccionada no existe.',
            'nombre.required'       => 'El nombre es obligatorio.',
            'nombre.max'            => 'El nombre no puede superar los 150 caracteres.',
            'descripcion.max'       => 'La descripción no puede superar los 255 caracteres.',
            'precio.required'       => 'El precio es obligatorio.',
            'precio.numeric'        => 'El precio debe ser un número.',
            'precio.min'            => 'El precio no puede ser negativo.',
            'imagen.image'          => 'El archivo debe ser una imagen.',
            'imagen.mimes'          => 'La imagen debe ser JPG o PNG.',
            'imagen.max'            => 'La imagen no puede pesar más de 2 MB.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min'     => 'El stock no puede ser negativo.',
        ];
    }
}
