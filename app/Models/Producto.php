<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'tipo',
        'nombre',
        'descripcion',
        'precio',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    /**
     * Trabajos que usan este producto.
     */
    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }
}
