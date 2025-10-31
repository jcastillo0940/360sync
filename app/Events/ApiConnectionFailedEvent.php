<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiConnectionFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $apiName;
    public $endpoint;
    public $errorMessage;
    public $statusCode;
    public $retryAttempts;
    public $timestamp;

    public function __construct($apiName, $endpoint, $errorMessage, $statusCode = null, $retryAttempts = 0)
    {
        $this->apiName = $apiName;
        $this->endpoint = $endpoint;
        $this->errorMessage = $errorMessage;
        $this->statusCode = $statusCode;
        $this->retryAttempts = $retryAttempts;
        $this->timestamp = now();
    }
}