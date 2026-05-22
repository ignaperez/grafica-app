<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TrabajoArchivo extends Model
{
    use SoftDeletes;

    protected $table = 'trabajo_archivos';

    protected $fillable = [
        'trabajo_id',
        'tipo',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamanio',
    ];

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->ruta);
    }

    public function getTamanioFormateadoAttribute(): string
    {
        $bytes = $this->tamanio ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 0)    . ' KB';
        return $bytes . ' B';
    }

    public function getIconoAttribute(): string
    {
        $ext = strtolower(pathinfo($this->nombre_original, PATHINFO_EXTENSION));
        return match(true) {
            in_array($ext, ['jpg','jpeg','png','gif','bmp','webp','tif','tiff']) => 'img',
            $ext === 'pdf'                                                        => 'pdf',
            in_array($ext, ['ai','eps','svg','cdr'])                             => 'vec',
            $ext === 'psd'                                                        => 'psd',
            default                                                               => 'arc',
        };
    }
}
