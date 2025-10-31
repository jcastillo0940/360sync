<?php

namespace App\Services\Workflows;

use App\Models\Execution;
use App\Models\ExecutionLog;
use App\Services\API\IcgApiService;
use App\Services\API\MagentoApiService;
use App\Services\CsvGeneratorService;
use App\Services\FtpService;
use Illuminate\Support\Facades\Log;

abstract class BaseWorkflow
{
    protected $execution;
    protected $icgApi;
    protected $magentoApi;
    protected $csvGenerator;
    protected $ftpService;

    protected $successCount = 0;
    protected $failedCount = 0;
    protected $skippedCount = 0;
    protected $totalItems = 0;

    public function __construct()
    {
        $this->icgApi = app(IcgApiService::class);
        $this->magentoApi = app(MagentoApiService::class);
        $this->csvGenerator = app(CsvGeneratorService::class);
        $this->ftpService = app(FtpService::class);
    }

    /**
     * Método principal que debe implementar cada workflow
     */
    abstract public function execute(Execution $execution);

    /**
     * Iniciar ejecución
     */
    protected function start()
    {
        $this->execution->markAsStarted();
        $this->log('INFO', "Starting workflow: {$this->execution->workflow->name}");
    }

    /**
     * Finalizar ejecución exitosamente
     */
    protected function complete($message = 'Workflow completed successfully')
    {
        $this->execution->update([
            'total_items' => $this->totalItems,
            'success_count' => $this->successCount,
            'failed_count' => $this->failedCount,
            'skipped_count' => $this->skippedCount,
        ]);

        $this->execution->markAsCompleted('completed_success', $message);
        $this->log('SUCCESS', $message);
    }

    /**
     * Finalizar ejecución con error
     */
    protected function fail($message, $details = null)
    {
        $this->execution->update([
            'total_items' => $this->totalItems,
            'success_count' => $this->successCount,
            'failed_count' => $this->failedCount,
            'skipped_count' => $this->skippedCount,
        ]);

        $this->execution->markAsFailed($message, $details);
        $this->log('ERROR', $message);
    }

    /**
     * Agregar log a la ejecución
     */
    protected function log($level, $message, $sku = null, $currentPage = null, $totalPages = null)
    {
        ExecutionLog::create([
            'execution_id' => $this->execution->id,
            'level' => strtoupper($level),
            'message' => $message,
            'sku' => $sku,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'logged_at' => now(),
        ]);

        // También log en Laravel
        $logMessage = "[{$this->execution->job_id}] {$message}";
        
        switch (strtoupper($level)) {
            case 'ERROR':
            case 'CRITICAL':
                Log::error($logMessage);
                break;
            case 'WARNING':
                Log::warning($logMessage);
                break;
            default:
                Log::info($logMessage);
        }
    }

    /**
     * Actualizar progreso
     */
   protected function updateProgress($current, $total)
{
    $percentage = $total > 0 ? round(($current / $total) * 100, 2) : 0;
    
    $this->execution->update([
        'total_items' => $total,
        'processed_items' => $current,
        'progress' => $percentage,
        'success_count' => $this->successCount,
        'failed_count' => $this->failedCount,
        'skipped_count' => $this->skippedCount,
    ]);

    $this->log('INFO', "Progress: {$current}/{$total} ({$percentage}%) - Success: {$this->successCount}, Failed: {$this->failedCount}, Skipped: {$this->skippedCount}");
}
    /**
     * Subir CSV a FTP
     */
    protected function uploadToFtp($csvPath, $csvFilename)
    {
        if (!$this->ftpService->isEnabled()) {
            $this->log('WARNING', 'FTP is disabled, skipping upload');
            return false;
        }

        $this->log('INFO', 'Uploading CSV to FTP...');

        $result = $this->ftpService->uploadFile($csvPath, $csvFilename);

        if ($result['success']) {
            $this->log('SUCCESS', "CSV uploaded to FTP: {$csvFilename}");
            $this->execution->update(['ftp_uploaded' => true]);
            return true;
        } else {
            $this->log('ERROR', "FTP upload failed: {$result['error']}");
            return false;
        }
    }

    /**
     * Obtener SKUs desde la ejecución
     */
    protected function getSkus()
    {
        if (empty($this->execution->skus)) {
            return [];
        }

        $skus = preg_split('/[\s,;\n\r]+/', $this->execution->skus);
        $skus = array_filter(array_map('trim', $skus));

        return array_values($skus);
    }

    /**
     * Verificar si debe filtrar por WEBVISB
     */
    protected function shouldFilterByWebvisb()
    {
        $filterConfig = \App\Models\Configuration::where('key', 'filter_by_webvisb')->value('value') ?? 'true';
        return $filterConfig === 'true';
    }
}
