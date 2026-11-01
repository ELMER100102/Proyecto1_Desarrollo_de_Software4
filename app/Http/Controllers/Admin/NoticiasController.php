<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManejaImagenes;
use App\Http\Controllers\Controller;
use App\Models\Noticia;
use Illuminate\Http\Request;

class NoticiasController extends Controller
{
    use ManejaImagenes;

    private const CARPETA = 'uploads/noticias';

    public function index()
    {
        $data = Noticia::all();
        return view('admin.noticias.index', compact('data'));
    }

    public function create()
    {
        return view('admin.noticias.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(nueva: true), $this->mensajes());
        $datos['imagen'] = $this->reemplazarImagen($request, self::CARPETA);

        Noticia::create($datos);

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia creada correctamente.');
    }

    public function edit(Noticia $noticia)
    {
        return view('admin.noticias.edit', compact('noticia'));
    }

    public function update(Request $request, Noticia $noticia)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());
        $datos['imagen'] = $this->reemplazarImagen($request, self::CARPETA, $noticia->imagen);

        $noticia->update($datos);

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia actualizada correctamente.');
    }

    public function destroy(Noticia $noticia)
    {
        $this->borrarImagen($noticia->imagen);
        $noticia->delete();

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia eliminada correctamente.');
    }

    private function reglas(bool $nueva = false): array
    {
        return [
            'titulo'    => 'required|string|max:255',
            'contenido' => 'required|string|max:10000',   // columna TEXT: noticias largas, con párrafos
            'imagen'    => $this->reglaImagen(obligatoria: $nueva),
        ];
    }

    private function mensajes(): array
    {
        return [
            'titulo.required'    => 'El título es obligatorio.',
            'titulo.max'         => 'El título no puede superar los 255 caracteres.',
            'contenido.required' => 'El contenido es obligatorio.',
            'contenido.max'      => 'El contenido no puede superar los 10.000 caracteres.',
        ] + $this->mensajesImagen();
    }
}
