<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presupuesto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'cliente_id', 'lista_precio_id',
        'multiplicador', 'mo_m2', 'mo_ml', 'mo_unidad',
        'estado', 'fecha', 'fecha_vencimiento', 'observaciones',
        'total', 'orden_trabajo_id',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'fecha'             => 'date',
        'fecha_vencimiento' => 'date',
        'multiplicador'     => 'decimal:4',
        'mo_m2'             => 'decimal:2',
        'mo_ml'             => 'decimal:2',
        'mo_unidad'         => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function cliente()      { return $this->belongsTo(Cliente::class); }
    public function listaPrecio()  { return $this->belongsTo(ListaPrecio::class); }
    public function ordenTrabajo() { return $this->belongsTo(OrdenTrabajo::class); }
    public function items()        { return $this->hasMany(PresupuestoItem::class)->orderBy('orden'); }
    public function createdBy()    { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function updatedBy()    { return $this->belongsTo(\App\Models\User::class, 'updated_by'); }

    // ── Helpers ───────────────────────────────────────────────────

    public function numeroFormateado(): string
    {
        return 'P-' . str_pad($this->numero, 4, '0', STR_PAD_LEFT);
    }

    public function recalcularTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->saveQuietly();
    }

    public static function proximoNumero(): int
    {
        return (static::withTrashed()->max('numero') ?? 0) + 1;
    }

    public function estadoLabel(): string
    {
        return match($this->estado) {
            'borrador'  => 'Borrador',
            'enviado'   => 'Enviado',
            'aprobado'  => 'Aprobado',
            'rechazado' => 'Rechazado',
            default     => $this->estado,
        };
    }

    public function estadoColor(): string
    {
        return match($this->estado) {
            'borrador'  => '#888',
            'enviado'   => '#2196f3',
            'aprobado'  => '#4caf50',
            'rechazado' => '#e53935',
            default     => '#888',
        };
    }
}
