<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManejaImagenes;
use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlidesController extends Controller
{
    use ManejaImagenes;

    private const CARPETA = 'uploads/slides';

    public function index()
    {
        $data = Slide::all();
        return view('admin.slides.index', compact('data'));
    }

    public function create()
    {
        return view('admin.slides.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(nuevo: true), $this->mensajes());
        $datos['imagen'] = $this->reemplazarImagen($request, self::CARPETA);

        Slide::create($datos);

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide creado correctamente.');
    }

    public function edit(Slide $slide)
    {
        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, Slide $slide)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['imagen'] = $this->reemplazarImagen($request, self::CARPETA, $slide->imagen);

        $slide->update($datos);

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide actualizado correctamente.');
    }

    public function destroy(Slide $slide)
    {
        $this->borrarImagen($slide->imagen);
        $slide->delete();

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide eliminado correctamente.');
    }

    private function reglas(bool $nuevo = false): array
    {
        return [
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen'      => $this->reglaImagen(obligatoria: $nuevo),
        ];
    }

    private function mensajes(): array
    {
        return [
            'titulo.required'      => 'El título es obligatorio.',
            'titulo.max'           => 'El título no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
        ] + $this->mensajesImagen();
    }
}
