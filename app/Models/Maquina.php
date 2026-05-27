<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maquina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maquinas';

    protected $fillable = [
        'nombre', 'descripcion', 'activo',
        'tipo_trabajo_id',
        'costo_m2', 'costo_ml', 'costo_unidad',
    ];

    protected $casts = [
        'costo_m2'     => 'decimal:2',
        'costo_ml'     => 'decimal:2',
        'costo_unidad' => 'decimal:2',
        'activo'       => 'boolean',
    ];

    public function tipoTrabajo()
    {
        return $this->belongsTo(TipoTrabajo::class);
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    /**
     * Materiales compatibles con esta máquina.
     * El pivot incluye los costos de mano de obra por combinación.
     */
    public function materiales()
    {
        return $this->belongsToMany(Material::class, 'maquina_material')
            ->withTimestamps();
    }
}
