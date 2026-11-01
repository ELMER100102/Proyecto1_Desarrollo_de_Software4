<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManejaImagenes;
use App\Http\Controllers\Controller;
use App\Models\Contacto;
use Illuminate\Http\Request;

/** Datos del negocio: hay una sola fila en la tabla contacto */
class ContactoController extends Controller
{
    use ManejaImagenes;

    public function edit()
    {
        $data = Contacto::firstOrNew();
        return view('admin.contacto.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'direccion'      => 'required|string|max:255',
            'telefono'       => 'required|string|max:50',
            'correo'         => 'required|email|max:100',
            'facebook'       => 'nullable|url|max:255',
            'instagram'      => 'nullable|url|max:255',
            'logo'           => $this->reglaImagen(),
        ], $this->mensajesImagen('logo'));

        $contacto = Contacto::firstOrNew();
        $datos['logo'] = $this->reemplazarImagen($request, 'uploads/logo', $contacto->logo, 'logo');

        $contacto->fill($datos)->save();

        return redirect()->route('admin.contacto.edit')
            ->with('success', 'Información actualizada correctamente.');
    }
}
