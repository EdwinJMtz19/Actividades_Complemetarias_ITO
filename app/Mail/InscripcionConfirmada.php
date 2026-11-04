<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmación de una inscripción a un grupo (HU-18). Se envía en cola tras confirmar el alta. */
class InscripcionConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inscripcion $inscripcion) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscripción confirmada: '.$this->inscripcion->grupo->actividad->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.inscripcion-confirmada');
    }
}
