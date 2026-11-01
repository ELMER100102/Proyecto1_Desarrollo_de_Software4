<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }

    /** Categorías con sus productos visibles (sin las vacías). La usan el menú y "Mi pedido". */
    public static function paraCarta(): Collection
    {
        return static::with(['productos' => fn ($q) => $q->where('visible', true)->orderBy('nombre')])
            ->orderBy('nombre')
            ->get()
            ->filter(fn (self $categoria) => $categoria->productos->isNotEmpty())
            ->values();
    }

    /** Ancla para la barra de categorías: slug del nombre + id (el id evita repetidos o vacíos) */
    public function ancla(): string
    {
        return 'cat-' . Str::slug($this->nombre) . '-' . $this->id_categoria;
    }
}
