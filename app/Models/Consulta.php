<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Consulta del formulario de contacto del landing (plote.ar).
 * Vive en la base CENTRAL — el landing no inicializa tenancy.
 */
class Consulta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre', 'email', 'telefono', 'servicio', 'mensaje',
        'ip', 'user_agent', 'mail_enviado', 'mail_error', 'leida_at',
    ];

    protected function casts(): array
    {
        return [
            'mail_enviado' => 'boolean',
            'leida_at'     => 'datetime',
        ];
    }

    public function leida(): bool
    {
        return $this->leida_at !== null;
    }

    /** Link de WhatsApp al que consultó, con su mensaje citado. */
    public function whatsappUrl(): ?string
    {
        $tel = preg_replace('/\D/', '', (string) $this->telefono);
        if (strlen($tel) < 8) {
            return null;
        }

        // Si vino sin país, se asume Argentina móvil (54 9)
        if (! str_starts_with($tel, '54')) {
            $tel = '549' . ltrim($tel, '0');
        }

        return 'https://wa.me/' . $tel;
    }
}
