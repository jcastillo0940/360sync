<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncLog;
use App\Models\ApiLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GenerateReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'report:generate 
                            {--period=daily : Período del reporte (daily, weekly, monthly)}
                            {--format=txt : Formato de salida (txt, csv, json)}';

    /**
     * The console command description.
     */
    protected $description = 'Generar reporte de sincronizaciones';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $period = $this->option('period');
        $format = $this->option('format');
        
        $this->info("📊 Generando reporte {$period}...");

        try {
            $dateRange = $this->getDateRange($period);
            $data = $this->collectData($dateRange);
            $filename = $this->generateReport($data, $format, $period);

            $this->newLine();
            $this->info("✅ Reporte generado: {$filename}");
            $this->displaySummary($data);
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function getDateRange($period): array
    {
        $end = Carbon::now();
        
        switch ($period) {
            case 'daily':
                $start = Carbon::now()->startOfDay();
                break;
            case 'weekly':
                $start = Carbon::now()->startOfWeek();
                break;
            case 'monthly':
                $start = Carbon::now()->startOfMonth();
                break;
            default:
                $start = Carbon::now()->startOfDay();
        }

        return ['start' => $start, 'end' => $end];
    }

    protected function collectData($dateRange): array
    {
        $syncLogs = SyncLog::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->get();
        $apiLogs = ApiLog::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->get();

        return [
            'period' => [
                'start' => $dateRange['start']->format('Y-m-d H:i:s'),
                'end' => $dateRange['end']->format('Y-m-d H:i:s'),
            ],
            'sync' => [
                'total' => $syncLogs->count(),
                'completed' => $syncLogs->where('status', 'completed')->count(),
                'failed' => $syncLogs->where('status', 'failed')->count(),
                'pending' => $syncLogs->where('status', 'pending')->count(),
            ],
            'api' => [
                'total' => $apiLogs->count(),
                'success' => $apiLogs->where('status_code', '<', 400)->count(),
                'errors' => $apiLogs->where('status_code', '>=', 400)->count(),
                'avg_response_time' => round($apiLogs->avg('response_time'), 2),
            ],
        ];
    }

    protected function generateReport($data, $format, $period): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "reports/sync_report_{$period}_{$timestamp}.{$format}";

        switch ($format) {
            case 'json':
                $content = json_encode($data, JSON_PRETTY_PRINT);
                break;
            case 'csv':
                $content = $this->generateCsv($data);
                break;
            default:
                $content = $this->generateTxt($data);
        }

        Storage::put($filename, $content);
        
        return $filename;
    }

    protected function generateTxt($data): string
    {
        $txt = "==============================================\n";
        $txt .= "REPORTE DE SINCRONIZACIÓN\n";
        $txt .= "==============================================\n\n";
        $txt .= "Período: {$data['period']['start']} - {$data['period']['end']}\n\n";
        $txt .= "--- SINCRONIZACIONES ---\n";
        $txt .= "Total: {$data['sync']['total']}\n";
        $txt .= "Completadas: {$data['sync']['completed']}\n";
        $txt .= "Fallidas: {$data['sync']['failed']}\n";
        $txt .= "Pendientes: {$data['sync']['pending']}\n\n";
        $txt .= "--- API CALLS ---\n";
        $txt .= "Total: {$data['api']['total']}\n";
        $txt .= "Exitosas: {$data['api']['success']}\n";
        $txt .= "Errores: {$data['api']['errors']}\n";
        $txt .= "Tiempo promedio: {$data['api']['avg_response_time']}ms\n";
        
        return $txt;
    }

    protected function generateCsv($data): string
    {
        $csv = "Métrica,Valor\n";
        $csv .= "Período Inicio,{$data['period']['start']}\n";
        $csv .= "Período Fin,{$data['period']['end']}\n";
        $csv .= "Sincronizaciones Total,{$data['sync']['total']}\n";
        $csv .= "Sincronizaciones Completadas,{$data['sync']['completed']}\n";
        $csv .= "Sincronizaciones Fallidas,{$data['sync']['failed']}\n";
        $csv .= "API Calls Total,{$data['api']['total']}\n";
        $csv .= "API Calls Exitosas,{$data['api']['success']}\n";
        $csv .= "API Calls Errores,{$data['api']['errors']}\n";
        
        return $csv;
    }

    protected function displaySummary($data): void
    {
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Sincronizaciones Total', $data['sync']['total']],
                ['Completadas', $data['sync']['completed']],
                ['Fallidas', $data['sync']['failed']],
                ['API Calls', $data['api']['total']],
                ['Errores API', $data['api']['errors']],
            ]
        );
    }
}