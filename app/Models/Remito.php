<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remito extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'cliente_id', 'presupuesto_id', 'factura_id',
        'orden_trabajo_id', 'created_by',
        'fecha', 'estado', 'tipo', 'observaciones',
        'remito_cai_id', 'numero_fiscal', 'punto_venta',
        'cod_autorizacion', 'cod_autorizacion_vto',
    ];

    protected $casts = [
        'fecha'                => 'date',
        'numero_fiscal'        => 'integer',
        'punto_venta'          => 'integer',
        'cod_autorizacion_vto' => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function cliente()      { return $this->belongsTo(Cliente::class); }
    public function presupuesto()  { return $this->belongsTo(Presupuesto::class); }
    public function factura()      { return $this->belongsTo(Factura::class); }
    public function ordenTrabajo() { return $this->belongsTo(OrdenTrabajo::class); }
    public function createdBy()    { return $this->belongsTo(User::class, 'created_by'); }
    public function items()        { return $this->hasMany(RemitoItem::class)->orderBy('orden'); }
    public function remitoCai()    { return $this->belongsTo(RemitoCai::class); }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Número formateado: con CAI → PPPP-NNNNNNNN, sin CAI → R-XXXX */
    public function numeroFormateado(): string
    {
        if ($this->numero_fiscal && $this->punto_venta) {
            return RemitoCai::formatearNumero($this->punto_venta, $this->numero_fiscal);
        }
        return 'R-' . str_pad($this->numero, 4, '0', STR_PAD_LEFT);
    }

    /** ¿Tiene número fiscal (CAI asignado)? */
    public function tieneCai(): bool
    {
        return !is_null($this->numero_fiscal);
    }

    /** ¿Tiene código de autorización electrónico (WSREMV1)? */
    public function tieneAutorizacion(): bool
    {
        return !empty($this->cod_autorizacion);
    }

    /** Número formateado para remito electrónico: PPPP-NNNNNNNN */
    public function numeroElectronicoFormateado(): string
    {
        if ($this->tipo === 'electronico' && $this->numero_fiscal && $this->punto_venta) {
            return str_pad($this->punto_venta, 4, '0', STR_PAD_LEFT)
                 . '-'
                 . str_pad($this->numero_fiscal, 8, '0', STR_PAD_LEFT);
        }
        return $this->numeroFormateado();
    }

    /** Próximo número para remitos INTERNOS (secuencia propia, sin mezclar con oficiales). */
    public static function proximoNumero(): int
    {
        return (static::withTrashed()->where('tipo', 'interno')->max('numero') ?? 0) + 1;
    }

    /** Próximo número para remitos OFICIALES (contador separado, solo para referencia interna). */
    public static function proximoNumeroOficial(): int
    {
        return (static::withTrashed()->where('tipo', 'oficial')->max('numero') ?? 0) + 1;
    }

    public function estadoLabel(): string
    {
        return match($this->estado) {
            'pendiente'  => 'Pendiente',
            'entregado'  => 'Entregado',
            'cancelado'  => 'Cancelado',
            default      => 'Pendiente',
        };
    }

    public function estadoColor(): string
    {
        return match($this->estado) {
            'pendiente' => '#e0960a',
            'entregado' => '#3fb96a',
            'cancelado' => '#e05050',
            default     => '#e0960a',
        };
    }
}
