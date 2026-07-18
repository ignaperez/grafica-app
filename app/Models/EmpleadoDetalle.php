<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpleadoDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'empleados_detalles';

    protected $fillable = [
        'empleado_id',
        'dni',
        'cuil',
        'fecha_nacimiento',
        'fecha_ingreso',
        'direccion',
        'telefono',
        'email',
        'categoria',
        'valor_hora',
        'horas_jornada',
        'horario_ingreso',
        'horario_egreso',
        'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
        'valor_hora'       => 'decimal:2',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
