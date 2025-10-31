<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SyncReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $syncType;
    public $recordsProcessed;
    public $recordsSuccess;
    public $recordsFailed;
    public $duration;
    public $timestamp;

    public function __construct($syncType, $recordsProcessed, $recordsSuccess, $recordsFailed, $duration)
    {
        $this->syncType = $syncType;
        $this->recordsProcessed = $recordsProcessed;
        $this->recordsSuccess = $recordsSuccess;
        $this->recordsFailed = $recordsFailed;
        $this->duration = $duration;
        $this->timestamp = now();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte de Sincronización - ' . $this->syncType,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sync-report',
            with: [
                'syncType' => $this->syncType,
                'recordsProcessed' => $this->recordsProcessed,
                'recordsSuccess' => $this->recordsSuccess,
                'recordsFailed' => $this->recordsFailed,
                'duration' => $this->duration,
                'timestamp' => $this->timestamp->format('d/m/Y H:i:s'),
                'successRate' => $this->recordsProcessed > 0 
                    ? round(($this->recordsSuccess / $this->recordsProcessed) * 100, 2) 
                    : 0,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}