<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    /** Minutos de inactividad permitidos antes de cerrar la sesión. */
    const TIMEOUT_MINUTES = 30;

    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check() || !Auth::user()->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acceso denegado'], 403);
            }
            return redirect()->route('super-admin.login');
        }

        // ── Cierre automático por inactividad ────────────────────────────────
        $key      = 'sa_last_activity';
        $lastActivity = session($key);

        if ($lastActivity && (now()->timestamp - $lastActivity) > self::TIMEOUT_MINUTES * 60) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Sesión expirada por inactividad.'], 401);
            }

            return redirect()
                ->route('super-admin.login')
                ->with('error', 'Tu sesión se cerró por inactividad (' . self::TIMEOUT_MINUTES . ' min).');
        }

        // Renovar timestamp en cada request
        session([$key => now()->timestamp]);

        return $next($request);
    }
}
