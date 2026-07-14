<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Estampar el tenant en la sesión para evitar session bleeding entre contextos
        session(['_tenant_id' => tenant('id')]);

        // Sesión única: registramos el id de esta sesión en el usuario. Cualquier
        // sesión anterior (otra PC) deja de coincidir → se cierra en su próximo request.
        $request->user()->forceFill(['session_id' => $request->session()->getId()])->saveQuietly();

        $rol = $request->user()->rol;

        $destino = match($rol) {
            'admin'      => route('dashboard'),
            'ventas'     => route('inicio'),
            'produccion' => route('inicio'),
            default      => route('inicio'),
        };

        return redirect()->intended($destino);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Limpiar el marcador de sesión única
        $request->user()?->forceFill(['session_id' => null])->saveQuietly();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
