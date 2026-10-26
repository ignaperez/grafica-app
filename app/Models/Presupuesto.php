<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presupuesto extends Model
{
    use SoftDeletes;

    /** Texto por defecto del recuadro "Condiciones y notas" (editable por presupuesto en Observaciones). */
    public const CONDICIONES_DEFAULT = 'Precios expresados en pesos argentinos. Se requiere seña del 50% para iniciar producción; saldo contra entrega.';

    protected static function booted(): void
    {
        // Cada presupuesto nuevo genera automáticamente su fila de Seguimiento.
        static::created(function (self $presupuesto) {
            Seguimiento::firstOrCreate(
                ['presupuesto_id' => $presupuesto->id],
                ['estado' => 'presupuestado']
            );
        });
    }

    protected $fillable = [
        'numero', 'cliente_id', 'lista_precio_id',
        'multiplicador', 'alicuota_iva', 'precio_con_iva', 'mo_m2', 'mo_ml', 'mo_unidad',
        'estado', 'fecha', 'fecha_vencimiento', 'observaciones', 'nota_interna',
        'total', 'orden_trabajo_id', 'agrupado_en_id',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'fecha'             => 'date',
        'fecha_vencimiento' => 'date',
        'multiplicador'     => 'decimal:4',
        'alicuota_iva'      => 'decimal:2',
        'precio_con_iva'    => 'boolean',
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
    public function facturas()     { return $this->hasMany(Factura::class)->orderByDesc('id'); }
    public function remitos()      { return $this->hasMany(Remito::class)->orderByDesc('id'); }
    public function createdBy()    { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function updatedBy()    { return $this->belongsTo(\App\Models\User::class, 'updated_by'); }

    /** El presupuesto nuevo en el que SE AGRUPÓ este (si se agrupó). */
    public function agrupadoEn()   { return $this->belongsTo(self::class, 'agrupado_en_id'); }

    /** Los presupuestos que se agruparon EN este. */
    public function agrupados()    { return $this->hasMany(self::class, 'agrupado_en_id')->orderBy('numero'); }

    // ── Helpers ───────────────────────────────────────────────────

    public function numeroFormateado(): string
    {
        return 'P-' . str_pad($this->numero, 4, '0', STR_PAD_LEFT);
    }

    // ── IVA ───────────────────────────────────────────────────
    //
    // precio_unitario / subtotal / total se guardan SIEMPRE como FINAL (IVA
    // incluido). Acá se deriva el desglose. Cómo se muestra depende del cliente.

    public function alicuota(): float
    {
        return (float) ($this->alicuota_iva ?? 21);
    }

    /**
     * ¿Se discrimina el IVA (neto + IVA aparte, estilo Factura A)?
     * RI y Monotributo sí; Consumidor Final / Exento / etc. muestran IVA
     * contenido (informativo, ya incluido en el total).
     */
    public function discriminaIva(): bool
    {
        return in_array($this->cliente?->condicion_iva, ['responsable_inscripto', 'monotributo'], true);
    }

    /** Neto de un ítem (le saca el IVA al subtotal final). */
    public function netoDe($subtotalFinal): float
    {
        return round(((float) $subtotalFinal) / (1 + $this->alicuota() / 100), 2);
    }

    /** Neto total = suma de los netos por línea (así la tabla cierra exacta). */
    public function netoTotal(): float
    {
        return round($this->items->sum(fn ($it) => $this->netoDe($it->subtotal)), 2);
    }

    /** IVA = diferencia hasta el total final (que es exacto). */
    public function ivaMonto(): float
    {
        return round((float) $this->total - $this->netoTotal(), 2);
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

    /** ¿Se agrupó dentro de otro presupuesto? Entonces no se factura solo. */
    public function estaAgrupado(): bool
    {
        return $this->agrupado_en_id !== null;
    }

    /**
     * ¿Se puede agrupar en uno nuevo? No si ya está agrupado (evita cadenas),
     * si ya se facturó, si él mismo agrupa a otros (sería anidar), o si está
     * rechazado. Usa los `withCount` del listado si están cargados.
     */
    public function puedeAgruparse(): bool
    {
        $facturas  = $this->facturado_count
            ?? $this->facturas()->where('estado', '!=', 'anulada')->count();
        $agrupados = $this->agrupados_count ?? $this->agrupados()->count();

        return ! $this->estaAgrupado()
            && $this->estado !== 'rechazado'
            && (int) $facturas === 0
            && (int) $agrupados === 0;
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
