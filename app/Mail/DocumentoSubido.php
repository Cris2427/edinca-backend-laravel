<?php

namespace App\Mail;

use App\Models\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoSubido extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Documento $documento,
        public string $rutaArchivo
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo documento de su proyecto - EDINCA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.documento-subido',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->rutaArchivo)
                ->as($this->documento->nombre_original)
                ->withMime('application/pdf'),
        ];
    }
}
