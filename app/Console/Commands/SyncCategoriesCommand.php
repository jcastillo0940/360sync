<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SyncService;
use App\Events\SyncStartedEvent;
use App\Events\SyncCompletedEvent;
use App\Events\SyncFailedEvent;

class SyncCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:categories 
                            {--force : Forzar sincronización completa}';

    /**
     * The console command description.
     */
    protected $description = 'Sincronizar categorías desde ICG hacia Magento';

    /**
     * Execute the console command.
     */
    public function handle(SyncService $syncService): int
    {
        $this->info('🚀 Iniciando sincronización de categorías...');
        
        $startTime = microtime(true);
        
        event(new SyncStartedEvent('categories', 'ICG', 'Magento', auth()->id()));

        try {
            $force = $this->option('force');

            $this->info('Opciones: Force=' . ($force ? 'Sí' : 'No'));

            $result = $syncService->syncCategories([
                'force' => $force,
            ]);

            $duration = round(microtime(true) - $startTime, 2);

            event(new SyncCompletedEvent(
                'categories',
                $result['processed'],
                $result['success'],
                $result['failed'],
                $duration
            ));

            $this->newLine();
            $this->info('✅ Sincronización completada');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Procesados', $result['processed']],
                    ['Exitosos', $result['success']],
                    ['Fallidos', $result['failed']],
                    ['Duración', $duration . 's'],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            
            event(new SyncFailedEvent('categories', $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]));

            $this->error('❌ Error: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}