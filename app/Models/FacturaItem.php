<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaItem extends Model
{
    protected $fillable = [
        'factura_id', 'descripcion', 'cantidad',
        'precio_unitario', 'subtotal', 'alicuota_iva', 'orden',
    ];

    protected $casts = [
        'cantidad'        => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function factura() { return $this->belongsTo(Factura::class); }
}
