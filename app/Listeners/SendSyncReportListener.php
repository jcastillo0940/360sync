<?php

namespace App\Listeners;

use App\Events\SyncCompletedEvent;
use App\Mail\SyncReportEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSyncReportListener
{
    public function __construct()
    {
        //
    }

    public function handle(SyncCompletedEvent $event): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');

            Mail::to($adminEmail)->send(
                new SyncReportEmail(
                    $event->syncType,
                    $event->recordsProcessed,
                    $event->recordsSuccess,
                    $event->recordsFailed,
                    $event->duration
                )
            );

            Log::info('Reporte de sincronización enviado', [
                'sync_type' => $event->syncType,
                'email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando reporte de sincronización: ' . $e->getMessage());
        }
    }
}