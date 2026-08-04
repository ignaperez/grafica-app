<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModeloVehiculo extends Model
{
    use SoftDeletes;

    protected $table = 'modelos_vehiculo';

    protected $fillable = ['marca_id', 'nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }
}
