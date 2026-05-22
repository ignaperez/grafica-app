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
    ];

    protected $casts = [
        'multiplicador' => 'decimal:2',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
