<?php

namespace App\Http\Controllers;

use App\Mail\ConsultaContacto;
use App\Models\Consulta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Formulario de contacto del landing (plote.ar).
 *
 * Antes el formulario NO enviaba nada: el JS hacía preventDefault(), validaba,
 * limpiaba los campos y mostraba "¡Recibido! Te contactamos a la brevedad" sin
 * que los datos salieran del navegador. Toda consulta hecha desde la web se
 * perdió sin dejar rastro.
 *
 * Ahora: se GUARDA primero y después se manda el mail. Ese orden es a propósito
 * — si el envío falla, rebota o cae en spam, la consulta ya está en la base.
 */
class ContactoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot: un campo que el visitante no ve y los bots suelen llenar.
        // Se responde 200 fingiendo éxito para no darles una señal de detección.
        if ($request->filled('website')) {
            return response()->json(['ok' => true]);
        }

        // Tope por IP y por día. El `throttle:5,1` de la ruta corta las ráfagas;
        // esto corta el goteo sostenido (5 por minuto son 7.200 en un día).
        // Depende de que $request->ip() sea confiable: ver el trustProxies de
        // bootstrap/app.php, que estaba en '*' y permitía falsear la IP.
        $delDia = Consulta::where('ip', $request->ip())
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($delDia >= (int) config('contacto.max_por_ip_dia')) {
            Log::warning('Consulta web: tope diario por IP alcanzado', [
                'ip'          => $request->ip(),
                'del_dia'     => $delDia,
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => 'Ya recibimos varias consultas desde esta conexión hoy. '
                           . 'Escribinos por WhatsApp y te respondemos al toque.',
            ], 429);
        }

        $datos = $request->validate([
            'nombre'   => 'required|string|max:150',
            'email'    => 'required|email|max:150',
            'tel'      => 'nullable|string|max:50',
            'servicio' => 'nullable|string|max:100',
            'mensaje'  => 'required|string|max:5000',
        ], [], [
            'tel'     => 'teléfono',
            'mensaje' => 'mensaje',
        ]);

        $consulta = Consulta::create([
            'nombre'     => $datos['nombre'],
            'email'      => $datos['email'],
            'telefono'   => $datos['tel'] ?? null,
            'servicio'   => $datos['servicio'] ?? null,
            'mensaje'    => $datos['mensaje'],
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);

        // Mail a las dos casillas en el mismo envío (ambas en el To, así cada
        // una ve la consulta completa). Un fallo acá NO le rompe la respuesta
        // al visitante: la consulta ya quedó guardada.
        $destinos = array_filter((array) config('contacto.emails'));

        /*
         * Cortafuegos de reputación: tope de mails por hora.
         *
         * Si alguien logra pasar los otros límites, lo que NO se puede permitir
         * es que el servidor mande miles de mails: el dominio queda marcado como
         * spammer y dejan de llegar TODOS los mails del sistema, no solo estos.
         * Pasado el tope la consulta se sigue guardando y queda en el panel con
         * el motivo, así que no se pierde ninguna — solo no se envía.
         */
        $enviadosUltimaHora = Consulta::where('mail_enviado', true)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $tope = (int) config('contacto.max_mails_hora');

        if ($enviadosUltimaHora >= $tope) {
            $consulta->update([
                'mail_enviado' => false,
                'mail_error'   => "No se envió: se alcanzó el tope de {$tope} mails por hora "
                                . '(protección anti-abuso). La consulta quedó guardada; '
                                . 'se puede reenviar desde el panel.',
            ]);

            Log::warning('Consulta web: tope horario de mails alcanzado, no se envió', [
                'consulta_id'          => $consulta->id,
                'enviados_ultima_hora' => $enviadosUltimaHora,
                'tope'                 => $tope,
            ]);

            return response()->json([
                'ok'           => true,
                'whatsapp_url' => $this->whatsappUrl($consulta),
            ]);
        }

        try {
            Mail::to($destinos)->send(new ConsultaContacto($consulta));
            $consulta->update(['mail_enviado' => true]);
        } catch (\Throwable $e) {
            $consulta->update([
                'mail_enviado' => false,
                'mail_error'   => substr($e->getMessage(), 0, 1000),
            ]);
            Log::error('Consulta web: falló el envío del mail', [
                'consulta_id' => $consulta->id,
                'destinos'    => $destinos,
                'error'       => $e->getMessage(),
            ]);
        }

        // Link de WhatsApp con la consulta ya escrita, para que el visitante la
        // mande también por ahí. Lo abre el JS tras guardar.
        return response()->json([
            'ok'           => true,
            'whatsapp_url' => $this->whatsappUrl($consulta),
        ]);
    }

    /** wa.me con el mensaje pre-armado (el texto va urlencoded). */
    private function whatsappUrl(Consulta $consulta): ?string
    {
        $numero = preg_replace('/\D/', '', (string) config('contacto.whatsapp'));
        if ($numero === '') {
            return null;
        }

        $lineas = array_filter([
            'Hola! Les escribo desde la web.',
            '',
            'Nombre: ' . $consulta->nombre,
            $consulta->servicio ? 'Servicio: ' . $consulta->servicio : null,
            'Email: ' . $consulta->email,
            '',
            $consulta->mensaje,
        ], fn ($l) => $l !== null);

        return 'https://wa.me/' . $numero . '?text=' . rawurlencode(implode("\n", $lineas));
    }
}
