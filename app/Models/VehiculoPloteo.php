<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiculoPloteo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orden_trabajo_id',
        'cliente_id',
        'instalador_id',
        'terminado_at',
        'created_by',
        'presupuesto_id',
        'presupuestado_manual',
        'patente',
        'marca_id',
        'modelo_id',
        'marca',
        'modelo',
        'fecha_ploteo',
        'observaciones',
        'refe',
        'tipo_ploteo',
        'sector',
        'foto_antes_frente',
        'foto_antes_atras',
        'foto_antes_izq',
        'foto_antes_der',
        'foto_despues_frente',
        'foto_despues_atras',
        'foto_despues_izq',
        'foto_despues_der',
    ];

    protected $casts = [
        'fecha_ploteo'         => 'date',
        'terminado_at'         => 'datetime',
        'presupuestado_manual' => 'boolean',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id');
    }

    public function presupuestado(): bool
    {
        return ! is_null($this->presupuesto_id) || $this->presupuestado_manual;
    }

    /** Las cuatro vistas del "después", que es lo que carga el colocador al terminar. */
    public const FOTOS_DESPUES = ['foto_despues_frente', 'foto_despues_atras', 'foto_despues_izq', 'foto_despues_der'];

    /** Terminado = lo marcó el colocador. Es explícito, no se deduce de las fotos. */
    public function terminado(): bool
    {
        return $this->terminado_at !== null;
    }

    public function scopeTerminados($query)
    {
        return $query->whereNotNull('terminado_at');
    }

    public function scopePendientes($query)
    {
        return $query->whereNull('terminado_at');
    }

    /** Colocador tercerizado al que se le asignó el vehículo. */
    public function instalador()
    {
        return $this->belongsTo(User::class, 'instalador_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Acota el listado a lo que un usuario puede ver. El instalador ve solo lo
     * asignado a él o lo que cargó él; el resto ve todo.
     */
    public function scopeVisiblesPara($query, ?User $user)
    {
        if (! $user || ! $user->esInstalador()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('instalador_id', $user->id)
              ->orWhere('created_by', $user->id);
        });
    }

    /** Imágenes / archivos de referencia (antes era la columna única `refe`). */
    public function referencias()
    {
        return $this->hasMany(VehiculoArchivo::class, 'vehiculo_ploteo_id')
            ->orderBy('orden')
            ->orderBy('id');
    }

    public function marcaRel()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function modeloRel()
    {
        return $this->belongsTo(ModeloVehiculo::class, 'modelo_id');
    }

    public static function sectores(): array
    {
        return [
            'puerta_izq'    => 'Puerta izquierda',
            'puerta_der'    => 'Puerta derecha',
            'capot'         => 'Capot',
            'techo'         => 'Techo',
            'baul'          => 'Baúl',
            'caja_de_carga' => 'Caja de carga',
        ];
    }

    public static function camposfotos(): array
    {
        return [
            'foto_antes_frente'   => 'Antes — Frente',
            'foto_antes_atras'    => 'Antes — Atrás',
            'foto_antes_izq'      => 'Antes — Izquierda',
            'foto_antes_der'      => 'Antes — Derecha',
            'foto_despues_frente' => 'Después — Frente',
            'foto_despues_atras'  => 'Después — Atrás',
            'foto_despues_izq'    => 'Después — Izquierda',
            'foto_despues_der'    => 'Después — Derecha',
        ];
    }
}
