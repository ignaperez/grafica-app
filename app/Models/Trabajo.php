<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orden_trabajo_id',
        'cliente_id',
        'tipo_trabajo_id',
        'material_id',
        'maquina_id',
        'producto_id',
        'tipo',
        'descripcion',
        'medidas',
        'cantidad',
        'unidad',
        'estado',
        'fecha_entrega',
        'fecha_carga',
        'precio_unitario',
        'ancho',
        'alto',
        'largo',
    ];

    protected $casts = [
        'fecha_carga'   => 'datetime',
        'fecha_entrega' => 'date',
    ];

    public function producto()    { return $this->belongsTo(Producto::class); }
    public function orden()       { return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id'); }
    public function cliente()     { return $this->belongsTo(Cliente::class); }
    public function tipoTrabajo() { return $this->belongsTo(TipoTrabajo::class, 'tipo_trabajo_id'); }
    public function material()    { return $this->belongsTo(Material::class, 'material_id'); }
    public function maquina()     { return $this->belongsTo(Maquina::class, 'maquina_id'); }

    public function archivos()
    {
        return $this->hasMany(TrabajoArchivo::class);
    }

    public function archivosImprimir()
    {
        return $this->hasMany(TrabajoArchivo::class)->where('tipo', 'imprimir');
    }

    public function referencias()
    {
        return $this->hasMany(TrabajoArchivo::class)->where('tipo', 'referencia');
    }

    public function getM2Attribute(): float
    {
        return round(($this->ancho ?? 0) * ($this->alto ?? 0) * ($this->cantidad ?? 1), 4);
    }

    // ── Medidas según la unidad ─────────────────────────────────────

    public function unidadLabel(): string
    {
        return match ($this->unidad) {
            'ml'     => 'ml',
            'unidad' => 'unidad',
            default  => 'm²',
        };
    }

    /** Medida de UNA pieza: "2,00 m × 1,50 m" (m²) o "3,00 m" (ml). */
    public function medidaUnitariaTexto(): ?string
    {
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');

        if ($this->unidad === 'ml') {
            return $this->largo ? $fmt($this->largo) . ' m' : null;
        }

        if ($this->unidad === 'unidad') {
            return null;
        }

        return ($this->ancho || $this->alto)
            ? $fmt($this->ancho) . ' m × ' . $fmt($this->alto) . ' m'
            : null;
    }

    /**
     * Total en la unidad del trabajo. Antes las vistas hacían siempre
     * ancho × alto × cantidad, aunque el trabajo fuera por metro lineal.
     */
    public function medidaTotal(): float
    {
        $cant = (float) ($this->cantidad ?: 1);

        return round(match ($this->unidad) {
            'ml'     => (float) ($this->largo ?? 0) * $cant,
            'unidad' => $cant,
            default  => (float) ($this->ancho ?? 0) * (float) ($this->alto ?? 0) * $cant,
        }, 2);
    }

    /** El total ya formateado con su unidad: "9,00 m²", "6,00 ml", "3 u.". */
    public function medidaTotalTexto(): string
    {
        if ($this->unidad === 'unidad') {
            return ((int) ($this->cantidad ?: 1)) . ' u.';
        }

        return number_format($this->medidaTotal(), 2, ',', '.') . ' ' . $this->unidadLabel();
    }
}
