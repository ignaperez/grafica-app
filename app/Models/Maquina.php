<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maquina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maquinas';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }
}
