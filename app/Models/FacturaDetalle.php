<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $table = 'factura_detalles';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_factura', 'id_producto', 'descripcion', 'cantidad',
        'precio', 'impuesto_porcentaje', 'subtotal', 'stock_descontado',
    ];

    protected $casts = [
        'precio'              => 'decimal:2',
        'impuesto_porcentaje' => 'decimal:2',
        'subtotal'            => 'decimal:2',
        'stock_descontado'    => 'boolean',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}