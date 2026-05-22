<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoPago extends Model
{
    protected $table = 'empleado_pagos';

    protected $fillable = [
        'empleado_id',
        'desde',
        'hasta',
        'horas_normales',
        'horas_extras',
        'monto_hora_normal',
        'monto_hora_extra',
        'monto_total',
        'observaciones',
    ];

    protected $casts = [
        'desde'  => 'date',
        'hasta'  => 'date',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
