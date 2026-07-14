<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesión única por usuario. Si la sesión actual no coincide con la registrada
 * en el usuario (porque inició sesión en otra PC, o el admin principal forzó el
 * cierre), se cierra esta sesión y se redirige al login.
 */
class SingleSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = auth()->user();

        if ($usuario && $usuario->session_id && $usuario->session_id !== $request->session()->getId()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(401, 'Sesión cerrada: se inició sesión en otra PC.');
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Se cerró tu sesión porque se inició sesión con este usuario en otra PC.',
            ]);
        }

        return $next($request);
    }
}
