<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seguimiento extends Model
{
    use SoftDeletes;

    protected $table = 'seguimientos';

    /** Estados del proceso, en orden. [label, bg, text]. */
    public const ESTADOS = [
        'presupuestado' => ['PRESUPUESTADO',   '#9e1b1b', '#ffffff'],
        'suministro'    => ['SUMINISTRO',      '#ef5f7a', '#ffffff'],
        'orden_compra'  => ['ORDEN DE COMPRA', '#f2d21e', '#3a3a00'],
        'devengado'     => ['DEVENGADO',       '#4bbd7a', '#0b2a17'],
        'facturado'     => ['FACTURADO',       '#e8e8e8', '#333333'],
        'orden_pago'    => ['ORDEN DE PAGO',   '#b39ddb', '#241a3a'],
        'cobrado'       => ['COBRADO',         '#d7c9ec', '#2a2140'],
    ];

    protected $fillable = [
        'presupuesto_id', 'factura_id',
        'fecha_manual', 'numero_manual', 'monto_manual',
        'area_oficina', 'detalle', 'orden_compra', 'monto_op',
        'estado', 'observaciones', 'pasado_a', 'fecha_pago',
    ];

    protected $casts = [
        'fecha_pago'   => 'date',
        'fecha_manual' => 'date',
        'monto_op'     => 'decimal:2',
        'monto_manual' => 'decimal:2',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function presupuesto() { return $this->belongsTo(Presupuesto::class); }
    public function factura()     { return $this->belongsTo(Factura::class); }

    // ── Datos automáticos (leídos del presupuesto / factura) ─────────────────

    /** Fila cargada a mano (proceso que viene del sistema anterior). */
    public function esManual(): bool
    {
        return is_null($this->presupuesto_id);
    }

    /**
     * ¿Su presupuesto se agrupó dentro de otro? Entonces este proceso no sigue
     * solo: se factura y se cobra desde el agrupador. **NO suma en los totales**
     * (si no, el mismo trabajo se contaría dos veces en "presupuestado").
     */
    public function estaAgrupado(): bool
    {
        return $this->presupuesto?->agrupado_en_id !== null;
    }

    /** Leyenda para el campo `detalle` cuando se agrupa. */
    public static function leyendaAgrupado(string $numero): string
    {
        return 'Agrupado en ' . $numero;
    }

    /**
     * Agrega una leyenda al `detalle` sin pisar lo que haya escrito a mano.
     * Si ya está (por reintento), no la duplica.
     */
    public function agregarAlDetalle(string $texto): void
    {
        $actual = trim((string) $this->detalle);

        if ($actual !== '' && str_contains($actual, $texto)) {
            return;
        }

        $this->update(['detalle' => $actual === '' ? $texto : $actual . ' · ' . $texto]);
    }

    /** Fecha de referencia: la del presupuesto o la cargada a mano. */
    public function fechaRef()
    {
        return $this->presupuesto?->fecha ?? $this->fecha_manual;
    }

    /** N° de presupuesto: el del sistema o el cargado a mano. */
    public function numeroRef(): string
    {
        return $this->presupuesto?->numeroFormateado()
            ?? ($this->numero_manual ?: '—');
    }

    public function montoBase(): float
    {
        return $this->presupuesto
            ? (float) $this->presupuesto->total
            : (float) ($this->monto_manual ?? 0);
    }

    // ── Cálculos (sobre el MONTO del presupuesto) ────────────────────────────

    /** IVA contenido en el monto = monto − monto/1,21. */
    public function iva21(): float
    {
        return round($this->montoBase() * 0.21 / 1.21, 2);
    }

    public function cinco(): float
    {
        // 5% sobre el monto SIN IVA (neto = monto / 1,21).
        return round($this->montoBase() / 1.21 * 0.05, 2);
    }

    public function totalHernan(): float
    {
        return round($this->iva21() + $this->cinco(), 2);
    }

    /** Los cálculos se muestran recién cuando hay fecha de pago cargada. */
    public function mostrarCalculos(): bool
    {
        return ! is_null($this->fecha_pago);
    }

    // ── Helpers de estado ────────────────────────────────────────────────────

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado][0] ?? strtoupper((string) $this->estado);
    }

    public function estadoBg(): string
    {
        return self::ESTADOS[$this->estado][1] ?? '#333';
    }

    public function estadoText(): string
    {
        return self::ESTADOS[$this->estado][2] ?? '#fff';
    }
}
