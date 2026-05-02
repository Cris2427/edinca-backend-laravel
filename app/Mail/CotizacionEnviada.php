<?php

namespace App\Mail;

use App\Models\Cotizacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CotizacionEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cotizacion $cotizacion) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cotización de su proyecto - EDINCA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cotizacion-enviada',
        );
    }
}
