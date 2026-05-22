<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orden_trabajo_id',
        'cliente_id',
        'tipo_trabajo_id',
        'material_id',
        'maquina_id',
        'producto_id',
        'tipo',
        'descripcion',
        'medidas',
        'cantidad',
        'estado',
        'fecha_entrega',
        'fecha_carga',
        'precio_unitario',
        'ancho',
        'alto',
    ];

    protected $casts = [
        'fecha_carga'   => 'datetime',
        'fecha_entrega' => 'date',
    ];

    public function producto()    { return $this->belongsTo(Producto::class); }
    public function orden()       { return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id'); }
    public function cliente()     { return $this->belongsTo(Cliente::class); }
    public function tipoTrabajo() { return $this->belongsTo(TipoTrabajo::class, 'tipo_trabajo_id'); }
    public function material()    { return $this->belongsTo(Material::class, 'material_id'); }
    public function maquina()     { return $this->belongsTo(Maquina::class, 'maquina_id'); }

    public function archivos()
    {
        return $this->hasMany(TrabajoArchivo::class);
    }

    public function archivosImprimir()
    {
        return $this->hasMany(TrabajoArchivo::class)->where('tipo', 'imprimir');
    }

    public function referencias()
    {
        return $this->hasMany(TrabajoArchivo::class)->where('tipo', 'referencia');
    }

    public function getM2Attribute(): float
    {
        return round(($this->ancho ?? 0) * ($this->alto ?? 0) * ($this->cantidad ?? 1), 4);
    }
}
