<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MagentoSku;
use App\Models\SyncConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncMagentoSkus extends Command
{
    protected $signature = 'magento:sync-skus';
    protected $description = 'Sync all SKUs from Magento to local database';

    public function handle()
    {
        $this->info('Starting Magento SKU synchronization...');
        
        try {
            $config = SyncConfiguration::getMagentoApiConfig();
            $magentoUrl = $config['base_url'];
            $magentoToken = $config['api_token'];
            
            $allSkus = [];
            $page = 1;
            $pageSize = 100;
            
            do {
                $this->info("Fetching page {$page}...");
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $magentoToken,
                ])
                ->timeout(30)
                ->get($magentoUrl . '/rest/V1/products', [
                    'searchCriteria[pageSize]' => $pageSize,
                    'searchCriteria[currentPage]' => $page,
                    // ⭐ Filtro 1: Solo productos activos (status = 1)
                    'searchCriteria[filterGroups][0][filters][0][field]' => 'status',
                    'searchCriteria[filterGroups][0][filters][0][value]' => 1,
                    'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
                    // ⭐ Filtro 2: Solo productos visibles individualmente (visibility = 4)
                    'searchCriteria[filterGroups][1][filters][0][field]' => 'visibility',
                    'searchCriteria[filterGroups][1][filters][0][value]' => 4,
                    'searchCriteria[filterGroups][1][filters][0][condition_type]' => 'eq',
                    'fields' => 'items[sku]'
                ]);
                
                if (!$response->successful()) {
                    $this->error("Failed to fetch page {$page}");
                    break;
                }
                
                $data = $response->json();
                $items = $data['items'] ?? [];
                
                if (empty($items)) {
                    $this->info("No more items found on page {$page}");
                    break;
                }
                
                foreach ($items as $item) {
                    if (isset($item['sku'])) {
                        $allSkus[] = $item['sku'];
                    }
                }
                
                $this->info("Fetched page {$page}: " . count($items) . " SKUs (Total so far: " . count($allSkus) . ")");
                
                // ⭐ Continuar mientras haya items
                if (count($items) < $pageSize) {
                    $this->info("Last page reached (less than {$pageSize} items)");
                    break;
                }
                
                $page++;
                
                // ⭐ Límite de seguridad
                if ($page > 200) {
                    $this->warn("Reached page limit (200). Stopping.");
                    break;
                }
                
            } while (true);
            
            $this->info("Total SKUs fetched: " . count($allSkus));
            
            if (empty($allSkus)) {
                $this->warn("No SKUs found to sync");
                return Command::SUCCESS;
            }
            
            // Sincronizar a base de datos
            $this->info("Syncing to database...");
            $synced = MagentoSku::syncFromMagento($allSkus);
            
            $this->info("✅ Successfully synced {$synced} SKUs to database");
            
            Log::info("Magento SKUs synchronized", [
                'total_skus' => $synced,
                'synced_at' => now()
            ]);
            
            return Command::SUCCESS;
            

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            
            Log::error("Magento SKU sync failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}