<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalles';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_compra', 'id_producto', 'descripcion', 'cantidad',
        'costo_unitario', 'subtotal', 'stock_sumado',
    ];

    protected $casts = [
        'costo_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'stock_sumado'   => 'boolean',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}