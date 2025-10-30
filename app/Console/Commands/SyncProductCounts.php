<?php

namespace App\Console\Commands;

use App\Models\CategoryMapping;
use App\Services\API\MagentoApiService;
use Illuminate\Console\Command;

class SyncProductCounts extends Command
{
    protected $signature = 'magento:sync-product-counts';
    protected $description = 'Sync product counts for all category mappings from Magento';

    protected $magentoService;

    public function __construct(MagentoApiService $magentoService)
    {
        parent::__construct();
        $this->magentoService = $magentoService;
    }

    public function handle()
    {
        $this->info('?? Syncing product counts from Magento...');
        $this->newLine();
        
        $mappings = CategoryMapping::where('is_active', true)->get();
        
        if ($mappings->isEmpty()) {
            $this->warn('No active category mappings found.');
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($mappings->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $updated = 0;
        $errors = 0;

        foreach ($mappings as $mapping) {
            try {
                // Obtener el conteo de productos desde Magento
                $result = $this->magentoService->getProductsByCategory(
                    $mapping->magento_category_id,
                    1, // pageSize mínimo, solo queremos el total
                    1  // primera página
                );

                if ($result['success']) {
                    $mapping->product_count = $result['total'] ?? 0;
                    $mapping->last_synced_at = now();
                    $mapping->save();
                    $updated++;
                } else {
                    $errors++;
                }

            } catch (\Exception $e) {
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('? Product counts synchronized!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Errors', $errors],
                ['Total', $mappings->count()],
            ]
        );

        return Command::SUCCESS;
    }
}