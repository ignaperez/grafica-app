<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Imagen o archivo de referencia de un vehículo a plotear (frente, lateral,
 * detalle del logo, el arte, etc). Espeja a TrabajoArchivo.
 */
class VehiculoArchivo extends Model
{
    use SoftDeletes;

    protected $table = 'vehiculo_archivos';

    protected $fillable = [
        'vehiculo_ploteo_id',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamanio',
        'orden',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(VehiculoPloteo::class, 'vehiculo_ploteo_id');
    }

    /**
     * GOTCHA multi-tenancy: los archivos del tenant viven en
     * storage/tenant{id}/app/public, pero public/storage apunta al storage
     * CENTRAL, así que Storage::url() daría 404. Se sirve por una ruta de la
     * app, detrás del login (mismo patrón que TrabajoArchivo y las fichadas).
     */
    public function getUrlAttribute(): string
    {
        return route('vehiculos-ploteo.archivo', $this->id);
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->nombre_original, PATHINFO_EXTENSION));
    }

    /** ¿Se puede mostrar como miniatura directa en el navegador? */
    public function getEsImagenAttribute(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true);
    }

    public function getTamanioFormateadoAttribute(): string
    {
        $bytes = $this->tamanio ?? 0;

        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 0)    . ' KB';

        return $bytes . ' B';
    }
}
