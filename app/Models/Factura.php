<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
      public const IVA_POR_DEFECTO = 13;
 
    protected $table = 'facturas';
    protected $primaryKey = 'id_factura';
    public $timestamps = false;
 
    protected $fillable = [
        'numero', 'fecha', 'cliente', 'tipo_pago',
        'subtotal', 'total_impuesto', 'total', 'estado',
    ];
 
    protected $casts = [
        'fecha'          => 'datetime',
        'subtotal'       => 'decimal:2',
        'total_impuesto' => 'decimal:2',
        'total'          => 'decimal:2',
    ];
 
    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'id_factura', 'id_factura');
    }
 
    public function estaAnulada(): bool
    {
        return $this->estado === 'anulada';
    }
}
