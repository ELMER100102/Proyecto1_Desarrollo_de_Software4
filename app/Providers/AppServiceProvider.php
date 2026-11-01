<?php

namespace App\Providers;

use App\Models\ConfigPedido;
use App\Models\Contacto;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Datos que usan todas las vistas del sitio (header, footer, contacto, menú...).
        // once(): aunque el composer corre en cada vista web.*, se consulta una sola vez por petición.
        View::composer('web.*', function ($view) {
            $view->with('institucion', Contacto::actual());
            $view->with('pedidosConfig', once(fn () => ConfigPedido::first()));
        });
    }
}
