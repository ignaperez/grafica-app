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

    /** URL pública de la foto de la fichada (o null). */
    public function fotoUrl(): ?string
    {
        return $this->foto
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto)
            : null;
    }
}
