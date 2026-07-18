<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fichada extends Model
{
    use SoftDeletes;

    protected $table = 'fichadas';

    protected $fillable = [
        'empleado_id',
        'tipo',
        'momento',
        'origen',
        'foto',
    ];

    protected $casts = [
        'momento' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * URL de la foto de la fichada. Se sirve por una ruta de la app (no por
     * /storage/…) porque los archivos viven en el storage del tenant y el symlink
     * central no los alcanza.
     */
    public function fotoUrl(): ?string
    {
        return $this->foto ? route('rrhh.fichadas.foto', $this->id) : null;
    }
}
