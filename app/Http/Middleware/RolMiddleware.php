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

            // Redirigir al home del rol correspondiente
            $home = match($usuario?->rol) {
                'admin'      => route('dashboard'),
                'ventas'     => route('inicio'),
                'produccion' => route('inicio'),
                default      => route('login'),
            };

            return redirect($home)->with('error', 'No tenés permiso para acceder a esa sección.');
        }

        return $next($request);
    }
}
