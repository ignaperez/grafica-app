<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminResetPassword extends Command
{
    protected $signature   = 'superadmin:reset-password {email? : Email del super-admin (opcional si hay uno solo)}';
    protected $description = 'Restablece la contraseña del super-admin desde la terminal del servidor.';

    public function handle(): int
    {
        $email = $this->argument('email');

        if ($email) {
            $usuario = User::where('email', $email)->where('is_super_admin', true)->first();
        } else {
            $superAdmins = User::where('is_super_admin', true)->get();

            if ($superAdmins->isEmpty()) {
                $this->error('No hay ningún super-admin en la base de datos.');
                return self::FAILURE;
            }

            if ($superAdmins->count() === 1) {
                $usuario = $superAdmins->first();
            } else {
                $opciones = $superAdmins->pluck('email')->toArray();
                $email    = $this->choice('¿Cuál super-admin querés restablecer?', $opciones);
                $usuario  = $superAdmins->firstWhere('email', $email);
            }
        }

        if (! $usuario) {
            $this->error("No se encontró un super-admin con email: {$email}");
            return self::FAILURE;
        }

        // Generar clave segura aleatoria
        $nuevaClave = Str::upper(Str::random(3))
                     . Str::lower(Str::random(5))
                     . rand(10, 99)
                     . Str::random(3);

        $usuario->update(['password' => Hash::make($nuevaClave)]);

        $this->newLine();
        $this->line('  <fg=green>✓ Contraseña restablecida para:</> ' . $usuario->email);
        $this->newLine();
        $this->line('  <fg=yellow>Nueva contraseña temporal:</>');
        $this->line('');
        $this->line('  <fg=white;bg=red;options=bold>  ' . $nuevaClave . '  </>');
        $this->newLine();
        $this->line('  <fg=gray>Ingresá al panel y cambiala de inmediato en Cambiar Clave.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
