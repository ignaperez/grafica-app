<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiculoPloteo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orden_trabajo_id',
        'cliente_id',
        'patente',
        'marca',
        'modelo',
        'fecha_ploteo',
        'observaciones',
        'refe',
        'tipo_ploteo',
        'sector',
        'foto_antes_frente',
        'foto_antes_atras',
        'foto_antes_izq',
        'foto_antes_der',
        'foto_despues_frente',
        'foto_despues_atras',
        'foto_despues_izq',
        'foto_despues_der',
    ];

    protected $casts = [
        'fecha_ploteo' => 'date',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public static function sectores(): array
    {
        return [
            'puerta_izq'    => 'Puerta izquierda',
            'puerta_der'    => 'Puerta derecha',
            'capot'         => 'Capot',
            'techo'         => 'Techo',
            'baul'          => 'Baúl',
            'caja_de_carga' => 'Caja de carga',
        ];
    }

    public static function camposfotos(): array
    {
        return [
            'foto_antes_frente'   => 'Antes — Frente',
            'foto_antes_atras'    => 'Antes — Atrás',
            'foto_antes_izq'      => 'Antes — Izquierda',
            'foto_antes_der'      => 'Antes — Derecha',
            'foto_despues_frente' => 'Después — Frente',
            'foto_despues_atras'  => 'Después — Atrás',
            'foto_despues_izq'    => 'Después — Izquierda',
            'foto_despues_der'    => 'Después — Derecha',
        ];
    }
}
