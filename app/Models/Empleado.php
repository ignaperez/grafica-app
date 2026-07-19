<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'apellido',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function fichadas(): HasMany
    {
        return $this->hasMany(Fichada::class);
    }

    public function detalle()
    {
        return $this->hasOne(EmpleadoDetalle::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim(($this->nombre ?? '') . ' ' . ($this->apellido ?? ''));
    }
    public function pagos()
    {
        return $this->hasMany(EmpleadoPago::class);
    }

    public function adelantos()
    {
        return $this->hasMany(Adelanto::class);
    }
    }
