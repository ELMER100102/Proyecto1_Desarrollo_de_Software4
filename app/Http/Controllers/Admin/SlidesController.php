<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlidesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Slide::all();
        return view('admin.slides.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.slides.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'titulo.max'           => 'El título no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'imagen.required'      => 'Debe seleccionar una imagen.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser JPG o PNG.',
            'imagen.max'           => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $ruta = $request->file('imagen')->store('uploads/slides', 'public');

        Slide::create([
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen'      => $ruta,
        ]);

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Slide $slide)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slide $slide)
    {
        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, Slide $slide)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'titulo.max'           => 'El título no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser JPG o PNG.',
            'imagen.max'           => 'La imagen no puede pesar más de 2 MB.',
        ]);

        $datos = [
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
        ];

        if ($request->hasFile('imagen')) {
            Storage::disk('public')->delete($slide->imagen);
            $datos['imagen'] = $request->file('imagen')->store('uploads/slides', 'public');
        }

        $slide->update($datos);

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slide $slide)
    {
        Storage::disk('public')->delete($slide->imagen);

        $slide->delete();

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide eliminado correctamente.');
    }
}
