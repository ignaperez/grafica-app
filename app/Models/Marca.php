<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use SoftDeletes;

    protected $fillable = ['nombre', 'activo', 'modelo_libre'];

    /** Marca sin catalogo de modelos: el modelo se escribe a mano (lancha, trailer...). */
    protected $casts = ['activo' => 'boolean', 'modelo_libre' => 'boolean'];

    public function modelos()
    {
        return $this->hasMany(ModeloVehiculo::class);
    }
}
