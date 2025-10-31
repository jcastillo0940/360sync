<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SyncService;
use App\Events\SyncStartedEvent;
use App\Events\SyncCompletedEvent;
use App\Events\SyncFailedEvent;

class SyncProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:products 
                            {--force : Forzar sincronización completa}
                            {--limit= : Limitar número de productos}';

    /**
     * The console command description.
     */
    protected $description = 'Sincronizar productos desde ICG hacia Magento';

    /**
     * Execute the console command.
     */
    public function handle(SyncService $syncService): int
    {
        $this->info('🚀 Iniciando sincronización de productos...');
        
        $startTime = microtime(true);
        
        event(new SyncStartedEvent('products', 'ICG', 'Magento', auth()->id()));

        try {
            $force = $this->option('force');
            $limit = $this->option('limit');

            $this->info('Opciones: Force=' . ($force ? 'Sí' : 'No') . ', Limit=' . ($limit ?? 'Sin límite'));

            $result = $syncService->syncProducts([
                'force' => $force,
                'limit' => $limit ? (int)$limit : null,
            ]);

            $duration = round(microtime(true) - $startTime, 2);

            event(new SyncCompletedEvent(
                'products',
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
            
            event(new SyncFailedEvent('products', $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]));

            $this->error('❌ Error: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}