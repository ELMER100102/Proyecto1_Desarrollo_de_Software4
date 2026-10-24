<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DomainException;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_categoria',
        'nombre',
        'descripcion',
        'precio',
        'costo',
        'imagen',
        'stock',
        'disponible',
        'visible',
        'cabys_codigo',
        'cabys_descripcion',
        'impuesto',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'visible'    => 'boolean',
        'precio'     => 'decimal:2',
        'costo'      => 'decimal:2',
        'stock'      => 'integer',
    
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function moverStock(int $cantidad, string $tipo, ?string $referencia = null, ?string $nota = null): bool
    {
        if ($this->stock === null) {
            return false;   // no controla stock: no se hace nada
        }

        $anterior = $this->stock;
        $nuevo = $anterior + $cantidad;

        if ($nuevo < 0) {
            throw new DomainException(
                "El stock de «{$this->nombre}» quedaría negativo (hay {$anterior} y se intenta restar " . abs($cantidad) . ').'
            );
        }

        $this->stock = $nuevo;

        if ($nuevo === 0) {
            $this->disponible = false;
        } elseif ($anterior === 0) {
            $this->disponible = true;
        }
        $this->save();

        MovimientoStock::registrar($this, $tipo, $cantidad, $referencia, $nota);

        return true;
    }
}
