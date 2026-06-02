<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MailAccount;
use Illuminate\Http\Request;

class MailAccountController extends Controller
{
    public function index()
    {
        $cuentas = MailAccount::orderBy('email')->get();
        return view('super-admin.mail.index', compact('cuentas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario'   => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/'],
            'dominio'   => ['required', 'string'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower($request->usuario) . '@' . $request->dominio;

        if (MailAccount::where('email', $email)->exists()) {
            return back()->withInput()->with('error', "La cuenta {$email} ya existe.");
        }

        MailAccount::create([
            'email'    => $email,
            'password' => MailAccount::hashPassword($request->password),
            'maildir'  => $request->dominio . '/' . strtolower($request->usuario) . '/',
            'quota'    => 0,
            'active'   => 1,
        ]);

        return back()->with('success', "Cuenta {$email} creada correctamente.");
    }

    public function resetPassword(Request $request, MailAccount $mailAccount)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $password = $request->password;
        $mailAccount->update([
            'password' => MailAccount::hashPassword($password),
        ]);

        return back()->with('password_reset_mail', [
            'email'    => $mailAccount->email,
            'password' => $password,
        ]);
    }

    public function destroy(MailAccount $mailAccount)
    {
        $email = $mailAccount->email;
        $mailAccount->delete();
        return back()->with('success', "Cuenta {$email} eliminada.");
    }

    public function toggle(MailAccount $mailAccount)
    {
        $mailAccount->update(['active' => !$mailAccount->active]);
        $estado = $mailAccount->active ? 'activada' : 'desactivada';
        return back()->with('success', "Cuenta {$mailAccount->email} {$estado}.");
    }
}
