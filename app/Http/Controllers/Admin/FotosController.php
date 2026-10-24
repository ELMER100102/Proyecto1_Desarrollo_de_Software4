<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Foto;
use Illuminate\Http\Request;

class FotosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Foto::all();
        return view('admin.galeria.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.galeria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'imagen.required'      => 'Debe seleccionar una imagen.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser JPG o PNG.',
            'imagen.max'           => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $ruta = $request->file('imagen')->store('uploads/galeria', 'public');

        Foto::create([
            'descripcion' => $request->descripcion,
            'imagen'      => $ruta,
        ]);

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Foto creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Foto $foto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Foto $foto)
    {
        return view('admin.galeria.edit', compact('foto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Foto $foto)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser JPEG, JPG o PNG.',
            'imagen.max'           => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $datos = [
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
        ];

        if ($request->hasFile('imagen')) {
            Storage::disk('public')->delete($foto->imagen);
            $datos['imagen'] = $request->file('imagen')->store('uploads/galeria', 'public');
        }

        $foto->update($datos);

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Foto actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Foto $foto)
    {
        Storage::disk('public')->delete($foto->imagen);

        $foto->delete();

        return redirect()->route('admin.galeria.index')
            ->with('success', 'Foto eliminada correctamente.');
    }
}
