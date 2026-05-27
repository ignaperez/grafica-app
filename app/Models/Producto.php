<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'tipo',
        'nombre',
        'descripcion',
        'precio',
        'tipo_trabajo_id',
        'material_id',
        'unidad',
        'costo_mano_obra',
        'activo',
    ];

    protected $casts = [
        'precio'         => 'decimal:2',
        'costo_mano_obra'=> 'decimal:2',
        'activo'         => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function tipoTrabajo()
    {
        return $this->belongsTo(TipoTrabajo::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    // ── Helpers de cálculo ────────────────────────────────────────

    /**
     * Costo total del servicio según la unidad, usando costos del material
     * y la máquina asociada (si se pasa), más mano de obra.
     *
     * No guarda nada — solo calcula para presupuestación.
     *
     * @param  \App\Models\Maquina|null  $maquina
     * @return float
     */
    public function costoBase(?Maquina $maquina = null): float
    {
        $campo = match ($this->unidad) {
            'ml'     => 'costo_ml',
            'unidad' => 'costo_unidad',
            default  => 'costo_m2',
        };

        $costoMaterial = (float) optional($this->material)->{$campo};
        $costoMaquina  = $maquina ? (float) $maquina->{$campo} : 0.0;
        $manoObra      = (float) $this->costo_mano_obra;

        return $costoMaterial + $costoMaquina + $manoObra;
    }
}
