<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\TrabajoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrabajoArchivoController extends Controller
{
    // Extensiones permitidas
    private const EXTENSIONES = [
        'jpg','jpeg','png','gif','bmp','webp','tif','tiff',
        'pdf','ai','eps','svg','psd','cdr','indd',
    ];

    public function store(Request $request, Trabajo $trabajo)
    {
        $request->validate([
            'tipo'     => 'required|in:imprimir,referencia',
            'archivos' => 'required|array|min:1',
            'archivos.*' => [
                'file',
                'max:102400', // 100 MB
                function ($attr, $file, $fail) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (!in_array($ext, self::EXTENSIONES)) {
                        $fail("Extensión .$ext no permitida.");
                    }
                },
            ],
        ]);

        foreach ($request->file('archivos') as $file) {
            $ext      = strtolower($file->getClientOriginalExtension());
            $nombre   = Str::uuid() . '.' . $ext;
            $ruta     = $file->storeAs("trabajo_archivos/{$trabajo->id}", $nombre, 'public');

            TrabajoArchivo::create([
                'trabajo_id'      => $trabajo->id,
                'tipo'            => $request->tipo,
                'nombre_original' => $file->getClientOriginalName(),
                'ruta'            => $ruta,
                'mime_type'       => $file->getMimeType(),
                'tamanio'         => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Archivo(s) subido(s) correctamente.');
    }

    public function destroy(TrabajoArchivo $trabajoArchivo)
    {
        $trabajoArchivo->delete(); // soft delete — el archivo físico se conserva para auditoría

        return back()->with('success', 'Archivo eliminado.');
    }
}
