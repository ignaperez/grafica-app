<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'lista_precio_id',
    ];

    /**
     * Órdenes de trabajo del cliente.
     */
    public function ordenesTrabajo()
    {
        return $this->hasMany(OrdenTrabajo::class);
    }

    /**
     * Lista de precios asignada al cliente.
     */
    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class, 'lista_precio_id');
    }
}
