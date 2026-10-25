<?php

use App\Http\Controllers\Admin\ContactoController;
use App\Http\Controllers\Admin\FotosController;
use App\Http\Controllers\Admin\NoticiasController;
use App\Http\Controllers\Admin\SlidesController;
//adicional
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\FacturaController;
use App\Http\Controllers\Admin\{ProveedorController, CompraController, ConfigPedidoController, MovimientoStockController};
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\Web\WebController;
use App\Http\Controllers\Admin\PedidoAdminController;
use App\Http\Controllers\PwaController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('slides', SlidesController::class);
    Route::resource('noticias', NoticiasController::class);
    Route::resource('galeria', FotosController::class)->parameters(['galeria' => 'foto']);

    Route::get('contacto', [ContactoController::class, 'edit'])->name('contacto.edit');
    Route::put('contacto', [ContactoController::class, 'update'])->name('contacto.update');
    //Adicionales
    Route::resource('categorias', CategoriaController::class);
    Route::resource('productos', ProductoController::class);
    Route::patch('productos/{producto}/alternar', [ProductoController::class, 'alternar'])
        ->name('productos.alternar');
    Route::get('cabys', [ProductoController::class, 'buscarCabys'])
        ->middleware('throttle:60,1')
        ->name('cabys.buscar');

    //facturas
    Route::resource('facturas', FacturaController::class)->only(['index', 'create', 'store']);
    Route::patch('facturas/{factura}/anular', [FacturaController::class, 'anular'])->name('facturas.anular');
    Route::get('facturas/{factura}/carta',    [FacturaController::class, 'pdfCarta'])->name('facturas.pdf.carta');
    Route::get('facturas/{factura}/ticket',   [FacturaController::class, 'pdfTicket'])->name('facturas.pdf.ticket');
    //compras
    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])->except(['show', 'create']);
    Route::resource('compras', CompraController::class)->only(['index', 'create', 'store']);
    Route::patch('compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    Route::get('movimientos', [MovimientoStockController::class, 'index'])->name('movimientos.index');
    //pedidos
    Route::get('pedidos/edit', [ConfigPedidoController::class, 'edit'])->name('pedidos.edit');
    Route::put('pedidos/edit', [ConfigPedidoController::class, 'update'])->name('pedidos.edit.update');
    //pedidos admin
    Route::get('pedidos/resumen', [PedidoAdminController::class, 'resumen'])->name('pedidos.resumen');
    Route::get('pedidos/lista', [PedidoAdminController::class, 'lista'])->name('pedidos.lista');
    Route::get('pedidos', [PedidoAdminController::class, 'index'])->name('pedidos.index');
    Route::patch('pedidos/{id}/estado', [PedidoAdminController::class, 'cambiarEstado'])
        ->whereNumber('id')->name('pedidos.estado');
});

Route::get('/', [WebController::class, 'inicio'])->name('inicio');
Route::get('/noticias', [WebController::class, 'noticias'])->name('noticias');
Route::get('/noticia/{noticia}', [WebController::class, 'noticia'])->name('noticia');
Route::get('/galeria', [WebController::class, 'galeria'])->name('galeria');

Route::get('/contacto', [WebController::class, 'contacto'])->name('contacto');
Route::post('/contacto', [WebController::class, 'enviarContacto'])->name('contacto.enviar');
//Adicionales
Route::get('/menu', [WebController::class, 'menu'])->name('menu');
//pedidos
Route::get('pedido', [PedidoController::class, 'create'])->name('pedido.create');
Route::post('pedido', [PedidoController::class, 'store'])->middleware('throttle:6,1')->name('pedido.store');
Route::get('pedido/{pedido}', [PedidoController::class, 'seguimiento'])->name('pedido.seguimiento');
Route::get('pedido/{pedido}/estado', [PedidoController::class, 'estado'])->middleware('throttle:60,1')->name('pedido.estado');
//pwa
Route::get('manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('admin-manifest.webmanifest', [PwaController::class, 'manifestAdmin'])->name('pwa.manifest.admin');
Route::get('pwa/icono/{size}.png', [PwaController::class, 'icono'])->whereNumber('size')->name('pwa.icono');