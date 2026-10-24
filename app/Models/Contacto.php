<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $table = 'contacto';
    protected $primaryKey = 'id_contacto';
    public $timestamps = false;

    protected $fillable = ['nombre_empresa', 'direccion', 'telefono', 'correo', 'facebook', 'instagram', 'logo'];
}
