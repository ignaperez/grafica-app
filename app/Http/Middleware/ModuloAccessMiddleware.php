<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controla el acceso por MÓDULO. Mapea el nombre de la ruta a un módulo y
 * verifica que el usuario lo tenga habilitado. El Administrador principal
 * (es_super) pasa siempre. Las rutas sin módulo (dashboard, perfil, etc.) son
 * libres. La sección de usuarios es exclusiva del Administrador principal.
 */
class ModuloAccessMiddleware
{
    /** Prefijo del nombre de ruta → módulo. */
    private const MAPA = [
        'ordenes-trabajo'  => 'ordenes',
        'ordenes.'         => 'ordenes',
        'trabajos-libres'  => 'ordenes',
        'trabajos.'        => 'ordenes',
        'trabajo-archivos' => 'ordenes',
        'vehiculos-ploteo' => 'ordenes',
        'clientes'         => 'clientes',
        'presupuestos'     => 'presupuestos',
        'facturas'         => 'facturas',
        'cobros'           => 'facturas',
        'remitos'          => 'remitos',
        'remito-cais'      => 'remitos',
        'seguimientos'     => 'seguimiento',
        'productos'        => 'servicios',
        'catalogo'         => 'servicios',
        'listas-precios'   => 'servicios',
        'tipo-trabajos'    => 'configuracion',
        'materiales'       => 'configuracion',
        'maquinas'         => 'configuracion',
        'configuracion'    => 'configuracion',
        'rrhh.'            => 'rrhh',
        'papelera'         => 'papelera',
        'usuarios'         => '__super__',   // solo Administrador principal
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = auth()->user();

        // Sin sesión → dejar que 'auth' se encargue. Super → acceso total.
        if (!$usuario || $usuario->esSuper()) {
            return $next($request);
        }

        $nombre = $request->route()?->getName() ?? '';
        $modulo = $this->moduloDeRuta($nombre);

        if ($modulo === null) {
            return $next($request); // ruta sin módulo → libre
        }

        // Sección de usuarios: exclusiva del Administrador principal
        if ($modulo === '__super__' || !$usuario->puedeModulo($modulo)) {
            return $this->denegar($request, $usuario);
        }

        return $next($request);
    }

    private function moduloDeRuta(string $nombre): ?string
    {
        foreach (self::MAPA as $prefijo => $modulo) {
            if (str_starts_with($nombre, $prefijo)) {
                return $modulo;
            }
        }
        return null;
    }

    private function denegar(Request $request, $usuario): Response
    {
        if ($request->expectsJson()) {
            abort(403, 'No tenés acceso a ese módulo.');
        }

        $home = $usuario->rol === 'admin' ? 'dashboard' : 'inicio';

        return redirect()->route($home)->with('error', 'No tenés acceso a esa sección.');
    }
}
