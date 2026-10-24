<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Noticia;
use Illuminate\Http\Request;

class NoticiasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Noticia::all();
        return view('admin.noticias.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.noticias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'contenido' => 'required|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'titulo.max'           => 'El título no puede superar los 255 caracteres.',
            'contenido.required'   => 'El contenido es obligatorio.',
            'contenido.max'      => 'El contenido no puede superar los 255 caracteres.',
            'imagen.required'      => 'Debe seleccionar una imagen.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser JPG o PNG.',
            'imagen.max'           => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $ruta = $request->file('imagen')->store('uploads/noticias', 'public');

        Noticia::create([
            'titulo'      => $request->titulo,
            'contenido' => $request->contenido,
            'imagen'      => $ruta,
        ]);

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Noticia $noticia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Noticia $noticia)
    {
        return view('admin.noticias.edit', compact('noticia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Noticia $noticia)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'contenido'   => 'required|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'titulo.max'           => 'El título no puede superar los 255 caracteres.',
            'contenido.required'   => 'El contenido es obligatorio.',
            'contenido.max'      => 'El contenido no puede superar los 255 caracteres.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser JPG o PNG.',
            'imagen.max'           => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $datos = [
            'titulo'      => $request->titulo,
            'contenido' => $request->contenido,
        ];

        if ($request->hasFile('imagen')) {
            Storage::disk('public')->delete($noticia->imagen);
            $datos['imagen'] = $request->file('imagen')->store('uploads/noticias', 'public');
        }

        $noticia->update($datos);

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Noticia $noticia)
    {
        Storage::disk('public')->delete($noticia->imagen);

        $noticia->delete();

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Noticia eliminada correctamente.');
    }
}
