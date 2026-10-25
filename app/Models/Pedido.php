<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    public const TIPO_LOCAL     = 'local';
    public const TIPO_LLEVAR    = 'llevar';
    public const TIPO_DOMICILIO = 'domicilio';

    public const TIPOS = [
        self::TIPO_LOCAL     => 'Comer aquí',
        self::TIPO_LLEVAR    => 'Para llevar',
        self::TIPO_DOMICILIO => 'A domicilio',
    ];

    public const ESTADOS = [
        'pendiente'  => 'Pendiente',
        'preparando' => 'Preparando',
        'listo'      => 'Listo',
        'entregado'  => 'Entregado',
        'cancelado'  => 'Cancelado',
    ];

    /** Estados que siguen "abiertos" (se muestran en la pantalla de cocina/caja) */
    public const ABIERTOS = ['pendiente', 'preparando', 'listo'];

    /**
     * Siguiente paso permitido desde cada estado.
     * Un pedido solo avanza en orden; cancelar se permite mientras esté abierto.
     */
    public const SIGUIENTE = [
        'pendiente'  => 'preparando',
        'preparando' => 'listo',
        'listo'      => 'entregado',
    ];

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'fecha_dia', 'turno', 'codigo', 'tipo', 'mesa', 'cliente', 'telefono',
        'direccion', 'nota', 'total', 'estado', 'id_factura', 'fecha', 'fecha_estado',
    ];

    protected $casts = [
        'fecha_dia'    => 'date',
        'fecha'        => 'datetime',
        'fecha_estado' => 'datetime',
        'total'        => 'decimal:2',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class, 'id_pedido', 'id_pedido');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }

    /** El seguimiento público se busca por el código secreto, no por el id */
    public function getRouteKeyName(): string
    {
        return 'codigo';
    }

    public function etiquetaTipo(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function etiquetaEstado(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function estaAbierto(): bool
    {
        return in_array($this->estado, self::ABIERTOS, true);
    }

    /** Estado al que puede pasar con el botón principal, o null si ya terminó */
    public function siguienteEstado(): ?string
    {
        return self::SIGUIENTE[$this->estado] ?? null;
    }

    /** "Turno 12" (lo que el cliente ve y la cocina grita) */
    public function turnoVisible(): string
    {
        return 'Turno ' . $this->turno;
    }

    /** Texto que ve el cliente en la pantalla de seguimiento */
    public function mensajeSeguimiento(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'Recibimos tu pedido. En un momento lo empezamos a preparar.',
            'preparando' => 'Estamos preparando tu pedido.',
            'listo'      => $this->tipo === self::TIPO_DOMICILIO
                                ? 'Tu pedido está listo y va en camino.'
                                : '¡Tu pedido está listo!',
            'entregado'  => 'Pedido entregado. ¡Gracias por tu compra!',
            'cancelado'  => 'Este pedido fue cancelado. Si tienes dudas, comunícate con el local.',
            default      => '',
        };
    }
}
