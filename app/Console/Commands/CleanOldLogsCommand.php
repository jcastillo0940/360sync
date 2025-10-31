<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExecutionLog;
use Carbon\Carbon;

class CleanOldLogsCommand extends Command
{
    protected $signature = 'logs:clean
                            {--days=30 : Número de días a mantener}';

    protected $description = 'Limpiar logs antiguos del sistema';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        
        $this->info("🧹 Limpiando logs más antiguos de {$days} días...");
        
        $cutoffDate = Carbon::now()->subDays($days);
        $totalDeleted = 0;

        try {
            // Limpiar ExecutionLogs de la BD
            $execDeleted = ExecutionLog::where('logged_at', '<', $cutoffDate)->delete();
            $totalDeleted += $execDeleted;
            $this->info("✓ Logs de BD eliminados: {$execDeleted}");

            // Limpiar archivos .log antiguos en storage/logs
            $logPath = storage_path('logs');
            $filesDeleted = 0;
            
            $files = glob($logPath . '/*.log');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== 'laravel.log') {
                    $fileTime = filemtime($file);
                    if ($fileTime < $cutoffDate->timestamp) {
                        unlink($file);
                        $filesDeleted++;
                    }
                }
            }
            
            $this->info("✓ Archivos .log eliminados: {$filesDeleted}");
            $totalDeleted += $filesDeleted;

            $this->newLine();
            $this->info("✅ Total eliminado: {$totalDeleted} registros/archivos");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
