<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactoController extends Controller
{
    public function edit()
    {
        $data = Contacto::firstOrNew();
        return view('admin.contacto.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'direccion'      => 'required|string|max:255',
            'telefono'       => 'required|string|max:50',
            'correo'         => 'required|email|max:100',
            'facebook'       => 'nullable|url|max:255',
            'instagram'      => 'nullable|url|max:255',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $contacto = Contacto::firstOrNew();

        $datos = $request->only([
            'nombre_empresa',
            'direccion',
            'telefono',
            'correo',
            'facebook',
            'instagram',
        ]);

        if ($request->hasFile('logo')) {
            if ($contacto->logo) {
                Storage::disk('public')->delete($contacto->logo);
            }
            $datos['logo'] = $request->file('logo')->store('uploads/logo', 'public');
        }

        $contacto->fill($datos)->save();

        return redirect()->route('admin.contacto.edit')
            ->with('success', 'Información actualizada correctamente.');
    }
}
