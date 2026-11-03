<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTrabajo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_trabajos';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    /**
     * Id del tipo "OTROS" (lo crea si no existiera). Se usa como fallback:
     * un trabajo que se guarda sin tipo queda en OTROS, nunca sin tipo.
     */
    public static function otrosId(): int
    {
        return static::firstOrCreate(
            ['nombre' => 'OTROS'],
            ['activo' => true, 'descripcion' => 'Servicios sin un tipo específico'],
        )->id;
    }
}
