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
        'cuit',
        'condicion_iva',
        'telefono',
        'email',
        'direccion',
        'lista_precio_id',
    ];

    /**
     * Etiqueta legible de la condición IVA.
     */
    public function condicionIvaLabel(): string
    {
        return match ($this->condicion_iva) {
            'responsable_inscripto' => 'Responsable Inscripto',
            'monotributo'           => 'Monotributo',
            'exento'                => 'Exento',
            'consumidor_final'      => 'Consumidor Final',
            default                 => $this->condicion_iva ?? '—',
        };
    }

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
