<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SyncErrorEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $syncType;
    public $errorMessage;
    public $errorDetails;
    public $timestamp;

    public function __construct($syncType, $errorMessage, $errorDetails = [])
    {
        $this->syncType = $syncType;
        $this->errorMessage = $errorMessage;
        $this->errorDetails = $errorDetails;
        $this->timestamp = now();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Error de Sincronización - ' . $this->syncType,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sync-error',
            with: [
                'syncType' => $this->syncType,
                'errorMessage' => $this->errorMessage,
                'errorDetails' => $this->errorDetails,
                'timestamp' => $this->timestamp->format('d/m/Y H:i:s'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}