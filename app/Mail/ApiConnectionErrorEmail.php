<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApiConnectionErrorEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $apiName;
    public $endpoint;
    public $errorMessage;
    public $statusCode;
    public $timestamp;
    public $retryAttempts;

    public function __construct($apiName, $endpoint, $errorMessage, $statusCode = null, $retryAttempts = 0)
    {
        $this->apiName = $apiName;
        $this->endpoint = $endpoint;
        $this->errorMessage = $errorMessage;
        $this->statusCode = $statusCode;
        $this->retryAttempts = $retryAttempts;
        $this->timestamp = now();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Error de Conexión API - ' . $this->apiName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.api-connection-error',
            with: [
                'apiName' => $this->apiName,
                'endpoint' => $this->endpoint,
                'errorMessage' => $this->errorMessage,
                'statusCode' => $this->statusCode,
                'retryAttempts' => $this->retryAttempts,
                'timestamp' => $this->timestamp->format('d/m/Y H:i:s'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}