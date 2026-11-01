<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Datos del negocio. Hay una sola fila (puede no existir todavía). */
class Contacto extends Model
{
    protected $table = 'contacto';
    protected $primaryKey = 'id_contacto';
    public $timestamps = false;

    protected $fillable = ['nombre_empresa', 'direccion', 'telefono', 'correo', 'facebook', 'instagram', 'logo'];

    /** La fila de datos del negocio, o null. Se consulta una sola vez por petición. */
    public static function actual(): ?self
    {
        return once(fn () => static::first());
    }
}
