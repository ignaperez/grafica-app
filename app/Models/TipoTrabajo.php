<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTrabajo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_trabajos';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }
}
