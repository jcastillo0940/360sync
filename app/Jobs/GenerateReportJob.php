<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportId;
    protected $filters;

    public $tries = 2;
    public $timeout = 300; // 5 minutos

    /**
     * Create a new job instance.
     */
    public function __construct($reportId, array $filters = [])
    {
        $this->reportId = $reportId;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(ReportService $reportService): void
    {
        $report = Report::findOrFail($this->reportId);

        try {
            $report->update(['status' => 'processing']);

            // Generar el reporte
            $data = $reportService->generateData($report->type, $this->filters);
            
            // Crear archivo PDF o Excel
            $filename = 'reports/' . $report->type . '_' . time() . '.pdf';
            $content = $reportService->generatePDF($data, $report->type);
            
            Storage::put($filename, $content);

            $report->update([
                'status' => 'completed',
                'file_path' => $filename,
                'completed_at' => now()
            ]);

            // Notificar al usuario
            SendEmailJob::dispatch(
                $report->user->email,
                'Reporte generado exitosamente',
                'emails.report-ready',
                [
                    'report' => $report,
                    'download_url' => route('reports.download', $report->id)
                ]
            );

        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Manejar el fallo del job
     */
    public function failed(\Throwable $exception): void
    {
        $report = Report::find($this->reportId);
        
        if ($report) {
            $report->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage()
            ]);
        }

        \Log::error('Error generando reporte: ' . $exception->getMessage(), [
            'report_id' => $this->reportId
        ]);
    }
}