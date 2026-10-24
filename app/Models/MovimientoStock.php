<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoStock extends Model
{
    protected $table = 'movimientos_stock';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'fecha',
        'tipo',
        'cantidad',
        'stock_resultante',
        'referencia',
        'nota',
    ];

    protected $casts = [
        'fecha'            => 'datetime',
        'cantidad'         => 'integer',
        'stock_resultante' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * Anota un movimiento. Se llama DESPUÉS de que el producto ya tiene su
     * stock nuevo, porque guarda cuánto quedó (stock_resultante).
     */
    public static function registrar(
        Producto $producto,
        string $tipo,
        int $cantidad,
        ?string $referencia = null,
        ?string $nota = null
    ): self {
        return static::create([
            'id_producto'      => $producto->id_producto,
            'fecha'            => now(),
            'tipo'             => $tipo,
            'cantidad'         => $cantidad,
            'stock_resultante' => (int) $producto->stock,
            'referencia'       => $referencia,
            'nota'             => $nota,
        ]);
    }
}
