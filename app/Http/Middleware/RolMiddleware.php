<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = auth()->user();

        // Aplanamos por si los roles vienen como 'admin,ventas' en vez de dos args separados
        $rolesPermitidos = [];
        foreach ($roles as $rol) {
            foreach (explode(',', $rol) as $r) {
                $rolesPermitidos[] = trim($r);
            }
        }

        if (!$usuario || !in_array($usuario->rol, $rolesPermitidos)) {
            if ($request->expectsJson()) {
                abort(403, 'Acceso no autorizado.');
            }

            // Redirigir al home del rol correspondiente. Sin usuario → login;
            // con usuario → su tablero. El `match` por rol dejaba al instalador
            // (y a cualquier rol nuevo) cayendo en el default = login, así que
            // tocar una URL ajena le parecía que se le había cerrado la sesión.
            $home = $usuario
                ? ($usuario->rol === 'admin' ? route('dashboard') : route('inicio'))
                : route('login');

            return redirect($home)->with('error', 'No tenés permiso para acceder a esa sección.');
        }

        return $next($request);
    }
}
