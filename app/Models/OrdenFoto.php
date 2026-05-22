<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenFoto extends Model
{
    //
    protected $fillable = ['ruta', 'orden_trabajo_id'];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }
}
