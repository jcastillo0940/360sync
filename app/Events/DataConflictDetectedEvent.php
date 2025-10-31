<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataConflictDetectedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $entityType;
    public $entityId;
    public $icgData;
    public $magentoData;
    public $conflictFields;
    public $timestamp;

    public function __construct($entityType, $entityId, $icgData, $magentoData, $conflictFields)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->icgData = $icgData;
        $this->magentoData = $magentoData;
        $this->conflictFields = $conflictFields;
        $this->timestamp = now();
    }
}