<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RemitoCai extends Model
{
    use SoftDeletes;

    protected $table = 'remito_cais';

    protected $fillable = [
        'codigo', 'punto_venta', 'tipo_cbte',
        'numero_desde', 'numero_hasta', 'ultimo_numero',
        'vencimiento', 'activo', 'notas',
    ];

    protected $casts = [
        'vencimiento'   => 'date',
        'activo'        => 'boolean',
        'numero_desde'  => 'integer',
        'numero_hasta'  => 'integer',
        'ultimo_numero' => 'integer',
        'punto_venta'   => 'integer',
    ];

    // -------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------

    public function remitos()
    {
        return $this->hasMany(Remito::class);
    }

    // -------------------------------------------------------
    // Helpers de estado
    // -------------------------------------------------------

    /**
     * Devuelve el CAI válido PARA UNA FECHA dada (no vencido a esa fecha, activo,
     * con números disponibles) o null. La vigencia se evalúa contra la fecha del
     * remito, no contra hoy: así un remito con fecha del día 4 puede usar el CAI
     * que vencía el día 4 aunque hoy ya esté vencido.
     */
    public static function vigenteParaFecha($fecha): ?self
    {
        $fecha = Carbon::parse($fecha)->toDateString();

        return static::where('activo', true)
            ->whereDate('vencimiento', '>=', $fecha)
            ->whereRaw('ultimo_numero < numero_hasta')
            ->orderByDesc('id')
            ->first();
    }

    /** Devuelve el CAI activo vigente HOY (no vencido, con números disponibles) o null. */
    public static function vigente(): ?self
    {
        return static::vigenteParaFecha(now());
    }

    /** Reserva el siguiente número. Devuelve el número asignado o null si no hay stock. */
    public function reservarNumero(): ?int
    {
        if ($this->ultimo_numero >= $this->numero_hasta) {
            return null;
        }
        $this->increment('ultimo_numero');
        $this->refresh();
        return $this->ultimo_numero;
    }

    /** Números totales del rango. */
    public function totalNumeros(): int
    {
        return $this->numero_hasta - $this->numero_desde + 1;
    }

    /** Números ya usados. */
    public function usados(): int
    {
        return $this->ultimo_numero - $this->numero_desde + 1;
    }

    /** Números restantes. */
    public function restantes(): int
    {
        return $this->numero_hasta - $this->ultimo_numero;
    }

    /** Porcentaje de uso (0–100). */
    public function porcentajeUso(): int
    {
        if ($this->totalNumeros() === 0) return 100;
        return (int) round(($this->usados() / $this->totalNumeros()) * 100);
    }

    /** ¿Está vencido? */
    public function vencido(): bool
    {
        return $this->vencimiento->isPast();
    }

    /** ¿Quedan menos del 10% de los números disponibles? */
    public function casiAgotado(): bool
    {
        return $this->restantes() <= max(1, (int) ($this->totalNumeros() * 0.10));
    }

    /** Número formateado PPPP-NNNNNNNN */
    public static function formatearNumero(int $puntoVenta, int $numero): string
    {
        return str_pad($puntoVenta, 4, '0', STR_PAD_LEFT)
             . '-'
             . str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    /** Etiqueta de estado para la UI. */
    public function estadoLabel(): string
    {
        if (!$this->activo)          return 'Inactivo';
        if ($this->vencido())        return 'Vencido';
        if ($this->restantes() <= 0) return 'Agotado';
        if ($this->casiAgotado())    return 'Casi agotado';
        return 'Vigente';
    }

    public function estadoColor(): string
    {
        return match($this->estadoLabel()) {
            'Vigente'       => 'var(--green, #4caf50)',
            'Casi agotado'  => '#f59e0b',
            'Vencido',
            'Agotado',
            'Inactivo'      => 'var(--ac)',
            default         => 'var(--txd)',
        };
    }
}
