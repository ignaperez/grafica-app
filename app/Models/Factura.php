<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'presupuesto_id', 'cliente_id', 'created_by',
        'tipo', 'punto_venta', 'numero', 'fecha',
        'cae', 'cae_vencimiento', 'estado',
        'doc_tipo', 'doc_nro', 'concepto',
        'imp_neto', 'imp_iva', 'imp_total',
        'observaciones',
        'nc_tipo', 'nc_pto_vta', 'nc_nro',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'cae_vencimiento' => 'date',
        'imp_neto'        => 'decimal:2',
        'imp_iva'         => 'decimal:2',
        'imp_total'       => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function cliente()      { return $this->belongsTo(Cliente::class); }
    public function presupuesto()  { return $this->belongsTo(Presupuesto::class); }
    public function createdBy()    { return $this->belongsTo(User::class, 'created_by'); }
    public function items()        { return $this->hasMany(FacturaItem::class)->orderBy('orden'); }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function numeroFormateado(): string
    {
        return sprintf('%04d-%08d', $this->punto_venta, $this->numero);
    }

    public function tipoLabel(): string
    {
        return match((int) $this->tipo) {
            1  => 'Factura A',
            6  => 'Factura B',
            11 => 'Factura C',
            3  => 'N. Crédito A',
            8  => 'N. Crédito B',
            13 => 'N. Crédito C',
            default => "Comprobante {$this->tipo}",
        };
    }

    public function estadoLabel(): string
    {
        return match($this->estado) {
            'emitida'  => 'Emitida',
            'anulada'  => 'Anulada',
            default    => 'Pendiente',
        };
    }

    public function estadoColor(): string
    {
        return match($this->estado) {
            'emitida'  => '#22c55e',
            'anulada'  => '#ef4444',
            default    => '#f59e0b',
        };
    }

    public function docTipoLabel(): string
    {
        return match((int) $this->doc_tipo) {
            80 => 'CUIT',
            96 => 'DNI',
            99 => 'Consumidor Final',
            default => "Doc. {$this->doc_tipo}",
        };
    }

    /**
     * URL del QR obligatorio según normativa ARCA (RG 4291).
     */
    public function qrUrl(): string
    {
        $data = base64_encode(json_encode([
            'ver'        => 1,
            'fecha'      => $this->fecha->format('Y-m-d'),
            'cuit'       => (int) config('arca.cuit'),
            'ptoVta'     => $this->punto_venta,
            'tipoCmp'    => $this->tipo,
            'nroCmp'     => $this->numero,
            'importe'    => (float) $this->imp_total,
            'moneda'     => 'PES',
            'ctz'        => 1,
            'tipoDocRec' => $this->doc_tipo,
            'nroDocRec'  => (int) ($this->doc_nro ?? 0),
            'tipoCodAut' => 'E',
            'codAut'     => (int) $this->cae,
        ]));

        return 'https://www.afip.gob.ar/fe/qr/?p=' . $data;
    }

    public function tieneCAE(): bool
    {
        return ! empty($this->cae);
    }
}
