<?php

namespace App\Listeners;

use App\Events\SyncFailedEvent;
use App\Mail\SyncErrorEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSyncErrorListener
{
    public function __construct()
    {
        //
    }

    public function handle(SyncFailedEvent $event): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');

            Mail::to($adminEmail)->send(
                new SyncErrorEmail(
                    $event->syncType,
                    $event->errorMessage,
                    $event->errorDetails
                )
            );

            Log::info('Email de error de sincronización enviado', [
                'sync_type' => $event->syncType,
                'email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de error de sincronización: ' . $e->getMessage());
        }
    }
}