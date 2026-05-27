<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListaPrecio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'multiplicador',
        'mo_m2',
        'mo_ml',
        'mo_unidad',
    ];

    protected $casts = [
        'multiplicador' => 'decimal:2',
        'mo_m2'         => 'decimal:2',
        'mo_ml'         => 'decimal:2',
        'mo_unidad'     => 'decimal:2',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
