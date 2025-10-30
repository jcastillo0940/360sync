<?php

namespace App\Jobs;

use App\Models\Execution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StockUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora
    public $tries = 1;

    protected $execution;

    public function __construct(Execution $execution)
    {
        $this->execution = $execution;
    }

    public function handle()
    {
        // Delegar al ProcessWorkflowJob existente
        (new ProcessWorkflowJob($this->execution))->handle();
    }
}