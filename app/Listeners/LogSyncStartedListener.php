<?php

namespace App\Listeners;

use App\Events\SyncStartedEvent;
use Illuminate\Support\Facades\Log;

class LogSyncStartedListener
{
    public function __construct()
    {
        //
    }

    public function handle(SyncStartedEvent $event): void
    {
        Log::info('Sincronización iniciada', [
            'sync_type' => $event->syncType,
            'source' => $event->source,
            'destination' => $event->destination,
            'user_id' => $event->userId,
            'timestamp' => $event->timestamp,
        ]);
    }
}