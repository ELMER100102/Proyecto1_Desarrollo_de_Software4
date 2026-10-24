<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor', 'documento', 'fecha', 'total', 'nota', 'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'id_compra', 'id_compra');
    }

    public function estaAnulada(): bool
    {
        return $this->estado === 'anulada';
    }

    /** Número visible: 000012 */
    public function codigo(): string
    {
        return str_pad($this->id_compra, 6, '0', STR_PAD_LEFT);
    }
}