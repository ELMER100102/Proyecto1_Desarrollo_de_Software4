<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigPedido;
use Illuminate\Http\Request;

class ConfigPedidoController extends Controller
{
    public function edit()
    {
        return view('admin.pedidos.edit', ['config' => ConfigPedido::actual()]);
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'pedidos_activos'  => ['required', 'boolean'],
            'acepta_local'     => ['required', 'boolean'],
            'acepta_llevar'    => ['required', 'boolean'],
            'acepta_domicilio' => ['required', 'boolean'],
            'usa_mesas'        => ['required', 'boolean'],
            'mensaje_final'    => ['nullable', 'string', 'max:255'],
        ]);

        $algunTipo = $datos['acepta_local'] || $datos['acepta_llevar'] || $datos['acepta_domicilio'];

        if ($datos['pedidos_activos'] && ! $algunTipo) {
            return back()->withInput()
                ->withErrors(['acepta_local' => 'Activa al menos un tipo de pedido.']);
        }

        ConfigPedido::actual()->update($datos);

        return redirect()->route('admin.pedidos.edit')->with('success', 'Ajustes de pedidos guardados.');
    }
}
