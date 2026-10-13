<?php

namespace App\Mail;

use App\Models\Consulta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

/**
 * Consulta del formulario de contacto del landing.
 *
 * El Reply-To apunta al que consultó, así responder desde el cliente de correo
 * le llega directo a él y no a noreply@plote.ar.
 */
class ConsultaContacto extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Consulta $consulta) {}

    public function envelope(): Envelope
    {
        $asunto = 'Consulta web';
        if ($this->consulta->servicio) {
            $asunto .= ' — ' . $this->consulta->servicio;
        }
        $asunto .= ' — ' . $this->consulta->nombre;

        return new Envelope(
            subject: $asunto,
            replyTo: [new Address($this->consulta->email, $this->consulta->nombre)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consulta-contacto',
        );
    }
}
