<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Http\Request;

class MovimientoStockController extends Controller
{
    public function index(Request $request)
    {
        $idProducto = $request->integer('producto') ?: null;

        $data = MovimientoStock::with('producto')
            ->when($idProducto, fn ($q) => $q->where('id_producto', $idProducto))
            ->orderByDesc('id_movimiento')
            ->limit(1000)
            ->get();

        $productos = Producto::orderBy('nombre')->get(['id_producto', 'nombre']);

        return view('admin.movimientos.index', compact('data', 'productos', 'idProducto'));
    }
}