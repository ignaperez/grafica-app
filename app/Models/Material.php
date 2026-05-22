<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiales';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }
}
