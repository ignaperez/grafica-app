<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemitoItem extends Model
{
    protected $fillable = [
        'remito_id', 'descripcion', 'cantidad', 'unidad', 'orden',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function remito() { return $this->belongsTo(Remito::class); }
}
