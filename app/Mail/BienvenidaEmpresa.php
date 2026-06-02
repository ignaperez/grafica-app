<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaEmpresa extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $nombreEmpresa
     * @param  string  $loginUrl       URL de acceso al panel del tenant
     * @param  array   $usuarios        [['rol'=>'admin','email'=>...,'password'=>...,'nombre'=>...], ...]
     */
    public function __construct(
        public readonly string $nombreEmpresa,
        public readonly string $loginUrl,
        public readonly array  $usuarios,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bienvenido a plote.ar — Acceso para {$this->nombreEmpresa}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-empresa',
        );
    }
}
