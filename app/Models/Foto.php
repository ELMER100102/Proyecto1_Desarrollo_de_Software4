<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'fotos';
    protected $primaryKey = 'id_foto';
    public $timestamps = false;

    protected $fillable = ['descripcion', 'imagen'];
}
