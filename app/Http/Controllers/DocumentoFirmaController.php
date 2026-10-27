<?php

namespace App\Http\Controllers;

use App\Services\FirmaDocumentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Firmar documentos (órdenes de compra): se sube la OC en PDF o imagen, se
 * ubica la firma ológrafa guardada arrastrándola en el navegador y se descarga
 * el PDF firmado. La firma se guarda una vez por usuario y se reusa.
 */
class DocumentoFirmaController extends Controller
{
    public function index()
    {
        return view('documentos.firmar', ['tieneFirma' => (bool) auth()->user()->firma]);
    }

    /** Guarda / reemplaza la firma PNG del usuario. */
    public function guardarFirma(Request $request)
    {
        $request->validate([
            'firma' => 'required|image|mimes:png|max:5120',
        ], [
            'firma.mimes' => 'La firma tiene que ser un PNG (idealmente con fondo transparente).',
        ]);

        $user = auth()->user();

        // Borrar la anterior si había.
        if ($user->firma) {
            Storage::disk('public')->delete($user->firma);
        }

        $ruta = $request->file('firma')->storeAs(
            'firmas',
            'user-' . $user->id . '-' . Str::random(6) . '.png',
            'public'
        );

        $user->update(['firma' => $ruta]);

        return back()->with('success', 'Firma guardada.');
    }

    public function eliminarFirma()
    {
        $user = auth()->user();

        if ($user->firma) {
            Storage::disk('public')->delete($user->firma);
            $user->update(['firma' => null]);
        }

        return back()->with('success', 'Firma eliminada.');
    }

    /**
     * Sirve la firma guardada (preview en el navegador). Va por ruta de la app,
     * no por Storage::url(): en contexto tenant el archivo vive en
     * storage/tenant{id}/ y public/storage apunta al central (daría 404).
     */
    public function firmaImg()
    {
        $user = auth()->user();
        abort_if(! $user->firma || ! Storage::disk('public')->exists($user->firma), 404);

        return Storage::disk('public')->response($user->firma);
    }

    /** Estampa la firma sobre la OC y devuelve el PDF firmado. */
    public function procesar(Request $request, FirmaDocumentoService $service)
    {
        $request->validate([
            'oc'     => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:102400',
            'pagina' => 'required|integer|min:1',
            'x'      => 'required|numeric|min:0|max:1',
            'y'      => 'required|numeric|min:0|max:1',
            'w'      => 'required|numeric|min:0.02|max:1',
        ]);

        $user = auth()->user();

        if (! $user->firma || ! Storage::disk('public')->exists($user->firma)) {
            return back()->with('error', 'Primero subí tu firma.');
        }

        $firmaPath = Storage::disk('public')->path($user->firma);
        $ocPath    = $request->file('oc')->getRealPath();

        try {
            $pdf = $service->firmar(
                $ocPath,
                $firmaPath,
                (int) $request->pagina,
                (float) $request->x,
                (float) $request->y,
                (float) $request->w,
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Firma de documento falló: ' . $e->getMessage());

            return back()->with('error',
                'No se pudo procesar el documento. Si es un PDF, probá exportarlo de nuevo o subir una imagen (JPG) de la orden.');
        }

        $base   = pathinfo($request->file('oc')->getClientOriginalName(), PATHINFO_FILENAME);
        $nombre = Str::slug($base) ?: 'orden';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '-firmado.pdf"',
        ]);
    }
}
