<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailAccount extends Model
{
    protected $connection = 'mysql'; // BD central siempre
    protected $table      = 'mail_accounts';

    protected $fillable = ['email', 'password', 'maildir', 'quota', 'active'];

    protected $hidden = ['password'];

    /** Nombre de usuario (parte antes del @) */
    public function getUsuarioAttribute(): string
    {
        return explode('@', $this->email)[0];
    }

    /** Dominio (parte después del @) */
    public function getDominioAttribute(): string
    {
        return explode('@', $this->email)[1] ?? '';
    }

    /** Hashea la contraseña en formato SHA512-CRYPT compatible con Dovecot */
    public static function hashPassword(string $password): string
    {
        $salt = '$6$' . substr(str_replace(['+', '/', '='], ['.', '/', ''], base64_encode(random_bytes(12))), 0, 16);
        return '{SHA512-CRYPT}' . crypt($password, $salt);
    }
}
