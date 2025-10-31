<?php

namespace App\Jobs;

use App\Models\Execution;
use App\Models\ExecutionLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $execution;

    /**
     * Create a new job instance.
     */
    public function __construct(Execution $execution)
    {
        $this->execution = $execution;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $this->log('INFO', 'Workflow execution started');
            $this->log('INFO', "Workflow: {$this->execution->workflow->name}");

            // Obtener el tipo de workflow
            $workflowType = $this->execution->workflow->type;
            
            if (empty($workflowType)) {
                throw new Exception("Workflow type is empty or null. Workflow ID: {$this->execution->workflow->id}");
            }

            // Obtener la clase del workflow
            $workflowClass = $this->getWorkflowClass($workflowType);

            // Instanciar y ejecutar el workflow
            $workflow = app($workflowClass);
            $workflow->execute($this->execution);

        } catch (Exception $e) {
            $this->log('ERROR', 'Workflow failed: ' . $e->getMessage());
            
            $this->execution->markAsFailed(
                'Execution failed: ' . $e->getMessage(),
                $e->getMessage()
            );

            $this->log('CRITICAL', 'Job failed: ' . $e->getMessage());
            
            Log::error('ProcessWorkflowJob failed', [
                'execution_id' => $this->execution->id,
                'workflow_id' => $this->execution->workflow_id,
                'workflow_type' => $this->execution->workflow->type ?? 'UNKNOWN',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Mapear tipo de workflow a clase
     */
    protected function getWorkflowClass($type)
    {
        $workflowMap = [
            'product_creation' => \App\Services\Workflows\ProductCreationWorkflow::class,
            'stock_update' => \App\Services\Workflows\StockUpdateWorkflow::class,
            'price_update' => \App\Services\Workflows\PriceUpdateWorkflow::class,
            'offer_update' => \App\Services\Workflows\OfferUpdateWorkflow::class,
        ];

        if (isset($workflowMap[$type])) {
            return $workflowMap[$type];
        }

        throw new Exception("No workflow mapping found for type: '{$type}'. Available types: " . implode(', ', array_keys($workflowMap)));
    }

    protected function log($level, $message)
    {
        ExecutionLog::create([
            'execution_id' => $this->execution->id,
            'level' => $level,
            'message' => $message,
            'logged_at' => now(),
        ]);
    }
}