<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MagentoSku;
use App\Models\SyncConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncMagentoSkus extends Command
{
    protected $signature = 'magento:sync-skus';
    protected $description = 'Sync all SKUs with prices from Magento to local database';

    public function handle()
    {
        $this->info('Starting Magento SKU synchronization with prices...');
        
        try {
            $config = SyncConfiguration::getMagentoApiConfig();
            $magentoUrl = $config['base_url'];
            $magentoToken = $config['api_token'];
            
            $allProducts = [];
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
                    'searchCriteria[filterGroups][0][filters][0][field]' => 'status',
                    'searchCriteria[filterGroups][0][filters][0][value]' => 1,
                    'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
                    'searchCriteria[filterGroups][1][filters][0][field]' => 'visibility',
                    'searchCriteria[filterGroups][1][filters][0][value]' => 4,
                    'searchCriteria[filterGroups][1][filters][0][condition_type]' => 'eq',
                    'fields' => 'items[sku,price,custom_attributes]'
                ]);
                
                if (!$response->successful()) {
                    $this->error("Failed to fetch page {$page}");
                    break;
                }

                $data = $response->json();
                $items = $data['items'] ?? [];
                
                foreach ($items as $item) {
                    if (isset($item['sku'])) {
                        $product = [
                            'sku' => $item['sku'],
                            'price' => $item['price'] ?? null,
                            'special_price' => null,
                            'special_from_date' => null,
                            'special_to_date' => null
                        ];

                        // Extraer precios especiales de custom_attributes
                        if (isset($item['custom_attributes'])) {
                            foreach ($item['custom_attributes'] as $attr) {
                                if ($attr['attribute_code'] === 'special_price') {
                                    $product['special_price'] = $attr['value'];
                                }
                                if ($attr['attribute_code'] === 'special_from_date') {
                                    $product['special_from_date'] = $attr['value'];
                                }
                                if ($attr['attribute_code'] === 'special_to_date') {
                                    $product['special_to_date'] = $attr['value'];
                                }
                            }
                        }

                        $allProducts[] = $product;
                    }
                }

                $totalCount = $data['total_count'] ?? 0;
                
                $this->info("Fetched page {$page}: " . count($items) . " products (Total so far: " . count($allProducts) . ")");
                
                if (count($items) < $pageSize) {
                    $this->info("Last page reached (less than {$pageSize} items)");
                    break;
                }
                
                $page++;
                
            } while (true);
            
            $this->info("Total products fetched: " . count($allProducts));
            
            // Sincronizar a base de datos
            $this->info("Syncing to database...");
            $synced = MagentoSku::syncFromMagento($allProducts);
            
            $this->info("✅ Successfully synced {$synced} products with prices to database");
            
            Log::info("Magento SKUs and prices synchronized", [
                'total_products' => $synced,
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
