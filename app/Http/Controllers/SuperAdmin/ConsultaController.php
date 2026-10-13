<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\ConsultaContacto;
use App\Models\Consulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Consultas del formulario de contacto del landing. El respaldo de lo que
 * llega por mail: si un envío falla o se pierde, acá está igual.
 */
class ConsultaController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->query('filtro', 'todas');

        $consultas = Consulta::query()
            ->when($filtro === 'sin_leer', fn ($q) => $q->whereNull('leida_at'))
            ->when($filtro === 'con_error', fn ($q) => $q->where('mail_enviado', false))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $totales = [
            'todas'     => Consulta::count(),
            'sin_leer'  => Consulta::whereNull('leida_at')->count(),
            'con_error' => Consulta::where('mail_enviado', false)->count(),
        ];

        return view('super-admin.consultas.index', compact('consultas', 'totales', 'filtro'));
    }

    public function marcarLeida(Consulta $consulta)
    {
        // Alterna: permite volver a marcarla como pendiente
        $consulta->update(['leida_at' => $consulta->leida() ? null : now()]);

        return back();
    }

    /** Reintenta el mail de una consulta cuyo envío falló. */
    public function reenviar(Consulta $consulta)
    {
        $destinos = array_filter((array) config('contacto.emails'));

        try {
            Mail::to($destinos)->send(new ConsultaContacto($consulta));
            $consulta->update(['mail_enviado' => true, 'mail_error' => null]);

            return back()->with('success', 'Consulta reenviada a ' . implode(' y ', $destinos) . '.');
        } catch (\Throwable $e) {
            $consulta->update(['mail_error' => substr($e->getMessage(), 0, 1000)]);

            return back()->with('error', 'No se pudo reenviar: ' . $e->getMessage());
        }
    }

    public function destroy(Consulta $consulta)
    {
        $consulta->delete();   // SoftDeletes

        return back()->with('success', 'Consulta eliminada.');
    }
}
