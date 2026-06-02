<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaImpersonateController extends Controller
{
    public function login(Request $request)
    {
        $payload = $request->get('t', '');
        $sig     = $request->get('s', '');

        // 1. Verificar firma HMAC
        $expected = hash_hmac('sha256', $payload, config('app.key'));
        if (! hash_equals($expected, $sig)) {
            abort(403, 'Token inválido.');
        }

        // 2. Decodificar payload
        $data = json_decode(base64_decode($payload), true);
        if (! $data) {
            abort(403, 'Token malformado.');
        }

        // 3. Verificar expiración (3 minutos)
        if (now()->timestamp > ($data['exp'] ?? 0)) {
            abort(403, 'Token expirado. Volvé al panel super-admin y reintentá.');
        }

        // 4. Verificar que el token es para ESTE tenant (no para otro)
        if (($data['tid'] ?? '') !== tenant('id')) {
            abort(403, 'Token no válido para este tenant.');
        }

        // 5. Cerrar cualquier sesión anterior (evitar bleeding)
        if (Auth::check()) {
            Auth::logout();
        }

        // 6. Login como admin del tenant
        $admin = User::where('rol', 'admin')->first();
        if (! $admin) {
            abort(404, 'No hay usuario admin en este tenant.');
        }

        Auth::login($admin);
        $request->session()->regenerate();

        // 7. Estampar tenant en sesión
        session(['_tenant_id' => tenant('id')]);

        return redirect()->to('/inicio');
    }
}
