<?php

namespace App\Listeners;

use App\Events\SyncFailedEvent;
use Illuminate\Support\Facades\Log;

class LogSyncFailedListener
{
    public function __construct()
    {
        //
    }

    public function handle(SyncFailedEvent $event): void
    {
        Log::error('Sincronización fallida', [
            'sync_type' => $event->syncType,
            'error_message' => $event->errorMessage,
            'error_details' => $event->errorDetails,
            'user_id' => $event->userId,
            'timestamp' => $event->timestamp,
        ]);
    }
}