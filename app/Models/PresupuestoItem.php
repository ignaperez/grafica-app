<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoItem extends Model
{
    protected $fillable = [
        'presupuesto_id', 'maquina_id', 'material_id',
        'descripcion', 'unidad',
        'ancho', 'alto', 'largo', 'cantidad',
        'precio_unitario', 'subtotal', 'orden',
    ];

    protected $casts = [
        'ancho'           => 'decimal:4',
        'alto'            => 'decimal:4',
        'largo'           => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function presupuesto() { return $this->belongsTo(Presupuesto::class); }
    public function maquina()     { return $this->belongsTo(Maquina::class); }
    public function material()    { return $this->belongsTo(Material::class); }

    /**
     * Medida total según unidad:
     * m2  → ancho × alto × cantidad
     * ml  → largo × cantidad
     * u   → cantidad
     */
    public function medidaTotal(): float
    {
        return match($this->unidad) {
            'm2'     => (float)$this->ancho * (float)$this->alto * (int)$this->cantidad,
            'ml'     => (float)$this->largo * (int)$this->cantidad,
            default  => (int)$this->cantidad,
        };
    }

    public function unidadLabel(): string
    {
        return match($this->unidad) {
            'ml'     => 'ml',
            'unidad' => 'u',
            default  => 'm²',
        };
    }
}
