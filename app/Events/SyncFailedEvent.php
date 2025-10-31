<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SyncFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $syncType;
    public $errorMessage;
    public $errorDetails;
    public $userId;
    public $timestamp;

    public function __construct($syncType, $errorMessage, $errorDetails = [], $userId = null)
    {
        $this->syncType = $syncType;
        $this->errorMessage = $errorMessage;
        $this->errorDetails = $errorDetails;
        $this->userId = $userId;
        $this->timestamp = now();
    }
}