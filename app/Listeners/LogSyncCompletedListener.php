<?php

namespace App\Listeners;

use App\Events\SyncCompletedEvent;
use Illuminate\Support\Facades\Log;

class LogSyncCompletedListener
{
    public function __construct()
    {
        //
    }

    public function handle(SyncCompletedEvent $event): void
    {
        Log::info('Sincronización completada', [
            'sync_type' => $event->syncType,
            'records_processed' => $event->recordsProcessed,
            'records_success' => $event->recordsSuccess,
            'records_failed' => $event->recordsFailed,
            'duration' => $event->duration,
            'user_id' => $event->userId,
            'timestamp' => $event->timestamp,
        ]);
    }
}