<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SyncStartedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $syncType;
    public $source;
    public $destination;
    public $userId;
    public $timestamp;

    public function __construct($syncType, $source, $destination, $userId = null)
    {
        $this->syncType = $syncType;
        $this->source = $source;
        $this->destination = $destination;
        $this->userId = $userId;
        $this->timestamp = now();
    }
}