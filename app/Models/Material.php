<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiales';

    protected $fillable = [
        'nombre', 'unidad', 'descripcion', 'activo',
        'costo_m2', 'costo_ml', 'costo_unidad',
    ];

    protected $casts = [
        'costo_m2'     => 'decimal:2',
        'costo_ml'     => 'decimal:2',
        'costo_unidad' => 'decimal:2',
        'activo'       => 'boolean',
    ];

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    /**
     * Máquinas compatibles con este material.
     */
    public function maquinas()
    {
        return $this->belongsToMany(Maquina::class, 'maquina_material')
            ->withTimestamps();
    }
}
