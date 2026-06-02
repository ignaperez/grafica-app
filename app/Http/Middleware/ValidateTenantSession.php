<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Previene que sesiones del dominio central (super-admin) o de otro tenant
 * sean usadas como sesiones válidas en un tenant.
 *
 * Al hacer login en un tenant se estampa `_tenant_id` en la sesión.
 * Si un request llega autenticado pero con `_tenant_id` diferente al tenant
 * actual (o sin ese campo), se rechaza y fuerza re-login.
 */
class ValidateTenantSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionTenantId = session('_tenant_id');
            $currentTenantId = tenant('id');

            if ($sessionTenantId !== $currentTenantId) {
                // Sesión de otro contexto → forzar logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Sesión expirada. Por favor ingresá nuevamente.']);
            }
        }

        return $next($request);
    }
}
