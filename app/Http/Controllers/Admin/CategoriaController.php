<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Las categorías se crean desde el listado (no hay pantalla "create") */
class CategoriaController extends Controller
{
    public function index()
    {
        $data = Categoria::withCount('productos')->orderBy('nombre')->get();
        return view('admin.categorias.index', compact('data'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        Categoria::create($datos);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        $categoria->load(['productos' => fn ($q) => $q->orderBy('nombre')]);
        return view('admin.categorias.edit', ['data' => $categoria]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($request->validate($this->reglas($categoria), $this->mensajes()));

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->exists()) {
            return redirect()->route('admin.categorias.index')
                ->with('error', 'No se puede eliminar: la categoría tiene productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }

    private function reglas(?Categoria $categoria = null): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('categorias', 'nombre')->ignore($categoria?->id_categoria, 'id_categoria'),
            ],
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
        ];
    }
}
