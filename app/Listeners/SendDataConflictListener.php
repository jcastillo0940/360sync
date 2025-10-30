<?php

namespace App\Listeners;

use App\Events\DataConflictDetectedEvent;
use App\Mail\DataConflictEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDataConflictListener
{
    public function __construct()
    {
        //
    }

    public function handle(DataConflictDetectedEvent $event): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');

            Mail::to($adminEmail)->send(
                new DataConflictEmail(
                    $event->entityType,
                    $event->entityId,
                    $event->icgData,
                    $event->magentoData,
                    $event->conflictFields
                )
            );

            Log::info('Email de conflicto de datos enviado', [
                'entity_type' => $event->entityType,
                'entity_id' => $event->entityId,
                'email' => $adminEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de conflicto de datos: ' . $e->getMessage());
        }
    }
}