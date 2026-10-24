<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slides';
    protected $primaryKey = 'id_slide';
    public $timestamps = false;

    protected $fillable = ['titulo', 'descripcion', 'imagen'];
}
