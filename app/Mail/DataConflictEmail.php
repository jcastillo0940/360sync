<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataConflictEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $entityType;
    public $entityId;
    public $icgData;
    public $magentoData;
    public $conflictFields;
    public $timestamp;

    public function __construct($entityType, $entityId, $icgData, $magentoData, $conflictFields)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->icgData = $icgData;
        $this->magentoData = $magentoData;
        $this->conflictFields = $conflictFields;
        $this->timestamp = now();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Conflicto de Datos - ' . $this->entityType . ' #' . $this->entityId,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.data-conflict',
            with: [
                'entityType' => $this->entityType,
                'entityId' => $this->entityId,
                'icgData' => $this->icgData,
                'magentoData' => $this->magentoData,
                'conflictFields' => $this->conflictFields,
                'timestamp' => $this->timestamp->format('d/m/Y H:i:s'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}