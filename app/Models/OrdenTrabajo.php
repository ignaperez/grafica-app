<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenTrabajo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orden_trabajos';

    protected $fillable = [
        'cliente_id',
        'fecha_recibido',
        'observaciones',
        'estado',
        'activo',
    ];

    protected $guarded = ['cliente'];

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id')->withDefault([
            'nombre' => 'Sin especificar',
        ]);
    }

    public function fotos()
    {
        return $this->hasMany(OrdenFoto::class);
    }
}
