<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────

    private function throttleKey(Request $request): string
    {
        return 'sa-login.' . Str::lower($request->string('email')) . '|' . $request->ip();
    }

    // ── Login / Logout ────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::check() && Auth::user()->is_super_admin) {
            return redirect()->route('super-admin.empresas.index');
        }
        return view('super-admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // ── Rate limiting: 5 intentos por email+IP, lockout 15 min ────────
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutos = (int) ceil($seconds / 60);
            return back()
                ->withErrors(['email' => "Demasiados intentos fallidos. Volvé a intentar en {$minutos} minuto(s)."])
                ->withInput();
        }

        // ── Intento de autenticación ───────────────────────────────────────
        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 900); // ventana de 15 minutos
            return back()
                ->withErrors(['email' => 'Credenciales inválidas.'])
                ->withInput();
        }

        // ── Verificar que sea super-admin ─────────────────────────────────
        if (! Auth::user()->is_super_admin) {
            Auth::logout();
            RateLimiter::hit($key, 900);
            return back()
                ->withErrors(['email' => 'No tenés acceso al panel de administración.'])
                ->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('super-admin.empresas.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('super-admin.login');
    }

    // ── Cambiar contraseña ────────────────────────────────────────────────

    public function showCambiarClave()
    {
        return view('super-admin.cambiar-clave');
    }

    public function cambiarClave(Request $request)
    {
        $request->validate([
            'clave_actual' => ['required'],
            'nueva_clave'  => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ], [
            'nueva_clave.min'        => 'La nueva contraseña debe tener al menos 12 caracteres.',
            'nueva_clave.mixed_case' => 'Debe incluir mayúsculas y minúsculas.',
            'nueva_clave.numbers'    => 'Debe incluir al menos un número.',
            'nueva_clave.confirmed'  => 'La confirmación no coincide.',
        ]);

        $usuario = Auth::user();

        if (! Hash::check($request->clave_actual, $usuario->password)) {
            return back()->withErrors(['clave_actual' => 'La contraseña actual es incorrecta.']);
        }

        $usuario->update(['password' => Hash::make($request->nueva_clave)]);

        // Cerrar todas las demás sesiones (invalidar sesión actual y regenerar)
        Auth::logoutOtherDevices($request->nueva_clave);

        return back()->with('success', '✓ Contraseña actualizada correctamente.');
    }
}
