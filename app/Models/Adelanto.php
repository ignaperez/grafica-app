<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adelanto extends Model
{
    use SoftDeletes;

    protected $table = 'adelantos';

    protected $fillable = [
        'empleado_id', 'empleado_pago_id',
        'fecha', 'monto', 'observaciones', 'created_by',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function empleado()  { return $this->belongsTo(Empleado::class); }
    public function pago()      { return $this->belongsTo(EmpleadoPago::class, 'empleado_pago_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    /** ¿Ya fue saldado en una liquidación? */
    public function saldado(): bool
    {
        return ! is_null($this->empleado_pago_id);
    }
}
