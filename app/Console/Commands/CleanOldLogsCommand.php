<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncLog;
use App\Models\ApiLog;
use Carbon\Carbon;

class CleanOldLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:clean 
                            {--days=30 : Número de días a mantener}
                            {--type= : Tipo de log (sync, api, all)}';

    /**
     * The console command description.
     */
    protected $description = 'Limpiar logs antiguos del sistema';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $type = $this->option('type') ?? 'all';
        
        $this->info("🧹 Limpiando logs más antiguos de {$days} días...");
        
        $cutoffDate = Carbon::now()->subDays($days);
        $totalDeleted = 0;

        try {
            // Limpiar SyncLogs
            if ($type === 'sync' || $type === 'all') {
                $syncDeleted = SyncLog::where('created_at', '<', $cutoffDate)->delete();
                $totalDeleted += $syncDeleted;
                $this->info("✓ Logs de sincronización eliminados: {$syncDeleted}");
            }

            // Limpiar ApiLogs
            if ($type === 'api' || $type === 'all') {
                $apiDeleted = ApiLog::where('created_at', '<', $cutoffDate)->delete();
                $totalDeleted += $apiDeleted;
                $this->info("✓ Logs de API eliminados: {$apiDeleted}");
            }

            $this->newLine();
            $this->info("✅ Total de logs eliminados: {$totalDeleted}");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}