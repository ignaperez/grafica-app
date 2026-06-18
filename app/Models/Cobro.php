<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cobro extends Model
{
    use SoftDeletes;

    /** Formas de pago (control interno — sin relación con ARCA). */
    public const FORMAS = [
        'efectivo'         => 'Efectivo',
        'transferencia'    => 'Transferencia',
        'cuenta_corriente' => 'Cuenta corriente',
        'cheque'           => 'Cheque',
        'echeq'            => 'E-Cheq',
        'tarjeta'          => 'Tarjeta',
        'otro'             => 'Otro',
    ];

    protected $fillable = [
        'factura_id', 'created_by',
        'monto', 'forma_pago', 'fecha', 'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function factura()   { return $this->belongsTo(Factura::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function formaLabel(): string
    {
        return self::FORMAS[$this->forma_pago] ?? $this->forma_pago;
    }
}
