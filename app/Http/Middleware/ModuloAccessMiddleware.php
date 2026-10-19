<?php

namespace App\Http\Middleware;

use App\Support\Permisos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso por MÓDULO y ACCIÓN.
 *
 * Resuelve el nombre de la ruta a (módulo, acción) con `App\Support\Permisos`
 * y verifica que el usuario tenga esa acción.
 *
 * **El módulo manda.** Antes el `rol:` de las rutas era un techo y el módulo
 * solo podía recortar: no se podía darle Clientes a producción por más que se
 * tildara el checkbox. Ahora el rol quedó como la plantilla que se propone al
 * crear el usuario, y lo que decide el acceso es lo que esté marcado acá.
 *
 * El rol se sigue usando para otras cosas: qué tablero ve al entrar y, en el
 * caso del colocador, que solo vea los vehículos que le asignaron
 * (`VehiculoPloteo::scopeVisiblesPara`).
 */
class ModuloAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = auth()->user();

        // Sin sesión → que se encargue 'auth'. El Administrador principal pasa siempre.
        if (! $usuario || $usuario->esSuper()) {
            return $next($request);
        }

        $nombre = $request->route()?->getName() ?? '';

        // Alta / búsqueda de cliente al vuelo: libre para quien cargue trabajos,
        // remitos, presupuestos o facturas (sin el módulo Clientes completo).
        if (in_array($nombre, Permisos::CLIENTE_COMPARTIDAS, true)) {
            return $usuario->puedeCrearClientes()
                ? $next($request)
                : $this->denegar($request, $usuario);
        }

        $modulo = Permisos::moduloDe($nombre);

        if ($modulo === null) {
            return $next($request);   // ruta sin módulo (login, perfil, inicio…)
        }

        // La sección de usuarios es exclusiva del Administrador principal
        if ($modulo === '__super__') {
            return $this->denegar($request, $usuario);
        }

        $accion = Permisos::accionDe($nombre, $request->route()?->methods() ?? []);

        if (! $usuario->puede($modulo, $accion)) {
            return $this->denegar($request, $usuario, $modulo, $accion);
        }

        return $next($request);
    }

    private function denegar(Request $request, $usuario, ?string $modulo = null, ?string $accion = null): Response
    {
        $etiqueta = $modulo ? (\App\Models\User::MODULOS[$modulo] ?? $modulo) : null;

        $msg = $etiqueta && $accion
            ? 'No tenés permiso para ' . mb_strtolower(Permisos::ACCIONES[$accion] ?? $accion)
              . ' en ' . $etiqueta . '.'
            : 'No tenés acceso a esa sección.';

        if ($request->expectsJson()) {
            abort(403, $msg);
        }

        // Si tiene permiso de VER el módulo, se lo devuelve ahí en vez de
        // echarlo al inicio: el caso típico es "puede mirar pero no editar".
        if ($modulo && $accion !== 'ver' && $usuario->puede($modulo, 'ver')) {
            return back()->with('error', $msg);
        }

        $home = $usuario->rol === 'admin' ? 'dashboard' : 'inicio';

        return redirect()->route($home)->with('error', $msg);
    }
}
