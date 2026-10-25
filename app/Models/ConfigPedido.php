<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ajustes de pedidos. Hay una sola fila (id = 1).
 * Con esto el mismo sistema sirve para una panadería (local + llevar)
 * o para un restaurante (local + llevar + domicilio) sin tocar código.
 */
class ConfigPedido extends Model
{
    protected $table = 'config_pedidos';
    public $timestamps = false;

    protected $fillable = [
        'pedidos_activos', 'acepta_local', 'acepta_llevar',
        'acepta_domicilio', 'usa_mesas', 'mensaje_final',
    ];

    protected $casts = [
        'pedidos_activos'  => 'boolean',
        'acepta_local'     => 'boolean',
        'acepta_llevar'    => 'boolean',
        'acepta_domicilio' => 'boolean',
        'usa_mesas'        => 'boolean',
    ];

    /** La fila de ajustes (la crea si no existe) */
    public static function actual(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    /** Tipos de pedido que el negocio tiene activados: ['local' => 'Comer aquí', ...] */
    public function tiposActivos(): array
    {
        $tipos = [];

        if ($this->acepta_local)     { $tipos[Pedido::TIPO_LOCAL]     = Pedido::TIPOS[Pedido::TIPO_LOCAL]; }
        if ($this->acepta_llevar)    { $tipos[Pedido::TIPO_LLEVAR]    = Pedido::TIPOS[Pedido::TIPO_LLEVAR]; }
        if ($this->acepta_domicilio) { $tipos[Pedido::TIPO_DOMICILIO] = Pedido::TIPOS[Pedido::TIPO_DOMICILIO]; }

        return $tipos;
    }

    /** ¿El sitio debe mostrar el carrito? Hace falta el interruptor y al menos un tipo */
    public function disponible(): bool
    {
        return $this->pedidos_activos && count($this->tiposActivos()) > 0;
    }
}