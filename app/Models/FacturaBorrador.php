<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacturaBorrador extends Model
{
    use SoftDeletes;

    protected $table = 'factura_borradores';

    protected $fillable = [
        'created_by', 'cliente_id', 'datos', 'total', 'error',
    ];

    protected $casts = [
        'datos' => 'array',
        'total' => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function cliente()   { return $this->belongsTo(Cliente::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
