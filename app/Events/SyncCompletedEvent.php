<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SyncCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $syncType;
    public $recordsProcessed;
    public $recordsSuccess;
    public $recordsFailed;
    public $duration;
    public $userId;
    public $timestamp;

    public function __construct($syncType, $recordsProcessed, $recordsSuccess, $recordsFailed, $duration, $userId = null)
    {
        $this->syncType = $syncType;
        $this->recordsProcessed = $recordsProcessed;
        $this->recordsSuccess = $recordsSuccess;
        $this->recordsFailed = $recordsFailed;
        $this->duration = $duration;
        $this->userId = $userId;
        $this->timestamp = now();
    }
}