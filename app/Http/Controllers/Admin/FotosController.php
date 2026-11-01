<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManejaImagenes;
use App\Http\Controllers\Controller;
use App\Models\Foto;
use Illuminate\Http\Request;

class FotosController extends Controller
{
    use ManejaImagenes;

    private const CARPETA = 'uploads/galeria';

    public function index()
    {
        $data = Foto::all();
        return view('admin.galeria.index', compact('data'));
    }

    public function create()
    {
        return view('admin.galeria.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(nueva: true), $this->mensajes());
        $datos['imagen'] = $this->reemplazarImagen($request, self::CARPETA);

        Foto::create($datos);

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Foto creada correctamente.');
    }

    public function edit(Foto $foto)
    {
        return view('admin.galeria.edit', compact('foto'));
    }

    public function update(Request $request, Foto $foto)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['imagen'] = $this->reemplazarImagen($request, self::CARPETA, $foto->imagen);

        $foto->update($datos);

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Foto actualizada correctamente.');
    }

    public function destroy(Foto $foto)
    {
        $this->borrarImagen($foto->imagen);
        $foto->delete();

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Foto eliminada correctamente.');
    }

    private function reglas(bool $nueva = false): array
    {
        return [
            'descripcion' => 'required|string|max:255',
            'imagen'      => $this->reglaImagen(obligatoria: $nueva),
        ];
    }

    private function mensajes(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
        ] + $this->mensajesImagen();
    }
}
