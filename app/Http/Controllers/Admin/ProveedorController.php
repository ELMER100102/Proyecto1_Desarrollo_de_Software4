<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index()
    {
        $data = Proveedor::withCount('compras')->orderBy('nombre')->get();
        return view('admin.proveedores.index', compact('data'));
    }

    public function store(Request $request): RedirectResponse
    {
        Proveedor::create($request->validate($this->reglas(), $this->mensajes()));
        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor agregado.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('admin.proveedores.edit', ['data' => $proveedor]);
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($request->validate($this->reglas($proveedor), $this->mensajes()));
        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if ($proveedor->compras()->exists()) {
            return back()->with('error', 'No se puede eliminar: el proveedor tiene compras registradas.');
        }

        $proveedor->delete();

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor eliminado.');
    }

    private function reglas(?Proveedor $proveedor = null): array
    {
        return [
            'nombre'   => [
                'required', 'string', 'max:150',
                Rule::unique('proveedores', 'nombre')->ignore($proveedor?->id_proveedor, 'id_proveedor'),
            ],
            'telefono' => 'nullable|string|max:30',
            'correo'   => 'nullable|email|max:150',
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.unique' => 'Ya existe un proveedor con ese nombre.',
            'correo.email'  => 'Escriba un correo válido.',
        ];
    }
}