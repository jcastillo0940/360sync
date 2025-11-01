<?php

namespace App\Services\Workflows;

use App\Models\Execution;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\SyncConfiguration;
use App\Models\MagentoSku;

class StockUpdateWorkflow extends BaseWorkflow
{
    protected $warehouseMapping = [];

    public function execute(Execution $execution)
    {
        $this->execution = $execution;
        
        try {
            $this->start();

            $this->loadWarehouseMapping();

            if ($this->execution->action === 'partial') {
                $this->executePartial();
            } else {
                $this->executeTotal();
            }

            $this->generateCsvAndUpload();

            $this->complete("Stock update completed: {$this->successCount} products updated, {$this->failedCount} failed, {$this->skippedCount} skipped");

        } catch (\Exception $e) {
            $this->fail("Stock update failed: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function loadWarehouseMapping()
    {
        $config = $this->execution->configuration_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $this->warehouseMapping = $config['warehouse_mapping'] ?? [
            'B01' => '1',
            'B02' => '2',
            'B03' => '3',
            'B04' => '4',
            'B05' => '5',
            'B06' => '6',
            'B07' => '7',
            'B08' => '8',
            'B09' => '9',
            'B10' => '10',
            'B11' => '11',
            'B12' => '12',
            'B13' => '13',
        ];

        $this->log('INFO', 'Loaded warehouse mapping: ' . count($this->warehouseMapping) . ' warehouses');
    }

    protected function executePartial()
    {
        $skus = $this->getSkus();
        $this->totalItems = count($skus);

        $this->log('INFO', "Starting partial stock update for {$this->totalItems} SKUs");

        foreach ($skus as $index => $sku) {
            try {
                $this->updateProductStock($sku);
                $this->updateProgress($index + 1, $this->totalItems);

            } catch (\Exception $e) {
                $this->log('ERROR', "Error updating stock for SKU {$sku}: " . $e->getMessage(), $sku);
                $this->failedCount++;
            }
        }
    }

    protected function executeTotal()
{
    $this->log('INFO', 'Starting total stock update');

    $magentoSkus = MagentoSku::getAllSkusArray();
    $this->log('INFO', 'Found ' . count($magentoSkus) . ' SKUs in Magento');

    if (empty($magentoSkus)) {
        throw new \Exception('No SKUs found. Run: php artisan magento:sync-skus');
    }

    $this->totalItems = count($magentoSkus);
    $processedCount = 0;

    $page = 1;
    $perPage = 100;

    do {
        $result = $this->icgApi->getProducts($page, $perPage, []);
        
        if (!$result['success']) {
            throw new \Exception("ICG API error: " . ($result['error'] ?? 'Unknown'));
        }

        $products = $result['data'];
        
        if (empty($products)) {
            break;
        }

        foreach ($products as $product) {
            try {
                $sku = $product['ARTCOD'] ?? null;
                
                if (!$sku || !in_array($sku, $magentoSkus)) {
                    $this->skippedCount++;
                    continue;
                }

                $webVisb = $product['WEBVISB'] ?? 'F';
                if (strtoupper($webVisb) !== 'T') {
                    $this->skippedCount++;
                    continue;
                }

                $this->updateProductStock($sku, $product);
                $processedCount++;
                
                if ($processedCount % 100 === 0) {
                    $this->updateProgress($processedCount, $this->totalItems);
                }

            } catch (\Exception $e) {
                $this->log('ERROR', "Error on SKU {$sku}: " . $e->getMessage(), $sku);
                $this->failedCount++;
            }
        }

        $this->log('INFO', "Page {$page} done. Processed: {$processedCount}");
        
        if ($result['is_last_page'] ?? false) {
            break;
        }
        
        $page++;
        
    } while (true);

    $this->updateProgress($this->totalItems, $this->totalItems);
    $this->log('INFO', "Total completed: {$processedCount} products");
}

    protected function prepareDateFilters()
    {
        $filters = [];
        
        $config = $this->execution->configuration_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $dateFilter = $config['date_filter'] ?? $this->execution->date_filter ?? 'none';

        if ($dateFilter === 'none') {
            return $filters;
        }

        $now = Carbon::now();
        $fechaDesde = null;

        switch ($dateFilter) {
            case 'today':
                $fechaDesde = $now->copy()->startOfDay();
                break;
            
            case 'yesterday':
                $fechaDesde = $now->copy()->subDay()->startOfDay();
                break;
            
            case 'last_week':
                $fechaDesde = $now->copy()->subWeek()->startOfDay();
                break;
            
            case 'custom':
                if (isset($config['fecha_desde'])) {
                    $fechaDesde = Carbon::parse($config['fecha_desde']);
                } elseif ($this->execution->date_from) {
                    $fechaDesde = Carbon::parse($this->execution->date_from);
                }
                break;
        }

        if ($fechaDesde) {
            $filters['fecha_desde'] = $fechaDesde->format('Y-m-d\TH:i:s.v\Z');
            
            $fechaHasta = isset($config['fecha_hasta']) 
                ? Carbon::parse($config['fecha_hasta']) 
                : ($this->execution->date_to ? Carbon::parse($this->execution->date_to) : $now);
            
            $filters['fecha_hasta'] = $fechaHasta->format('Y-m-d\TH:i:s.v\Z');
        }

        return $filters;
    }

    protected function updateProductStock($sku, $icgProduct = null)
    {
        if (!$icgProduct) {
            $result = $this->icgApi->getProductBySku($sku);
            
            if (!$result['success']) {
                throw new \Exception("Product not found in ICG: {$sku}");
            }
            
            $icgProduct = $result['data'];
        }

        $webVisb = $icgProduct['WEBVISB'] ?? 'F';
        if (strtoupper($webVisb) !== 'T') {
            $this->skippedCount++;
            return;
        }

        $appliedStock = $icgProduct['APPLIEDSTOCK'] ?? 'T';
        $manageStock = strtoupper($appliedStock) === 'T';

        if (!$manageStock) {
            $result = $this->magentoApi->updateStock($sku, 0, false, false);
            
            if ($result['success']) {
                $this->log('SUCCESS', "Stock management disabled for SKU {$sku}", $sku);
                $this->successCount++;
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error');
            }
            
            return;
        }

        $stockBySource = $this->prepareStockBySources($icgProduct);

        if (empty($stockBySource)) {
            $this->log('WARNING', "No valid stock sources found for SKU {$sku}", $sku);
            $this->skippedCount++;
            return;
        }

        // ⭐ OPTIMIZACIÓN 3: Actualización en batch (1 API call en lugar de 13)
        $result = $this->magentoApi->updateStockBySources($sku, $stockBySource);

        if ($result['success']) {
            $totalStock = array_sum(array_column($stockBySource, 'qty'));
            $this->log('SUCCESS', "Stock updated for SKU {$sku}: {$totalStock} units", $sku);
            $this->successCount++;
        } else {
            throw new \Exception($result['error'] ?? 'Unknown error updating MSI');
        }
    }

    protected function prepareStockBySources($icgProduct)
    {
        $stockBySource = [];

        foreach ($icgProduct['Stocks'] ?? [] as $stock) {
            $almacenId = $stock['AlmacenId'] ?? null;
            
            if (!$almacenId || !isset($this->warehouseMapping[$almacenId])) {
                continue;
            }

            $sourceCode = $this->warehouseMapping[$almacenId];
            $disponible = (int) ($stock['Disponible'] ?? 0);

            $stockBySource[$sourceCode] = [
                'qty' => max(0, $disponible),
                'status' => $disponible > 0 ? 1 : 0
            ];
        }

        return $stockBySource;
    }

    protected function generateCsvAndUpload()
    {
        $this->log('INFO', 'Generating CSV file...');

        $csvData = $this->collectCsvData();

        if (empty($csvData)) {
            $this->log('WARNING', 'No data to generate CSV');
            return;
        }

        $config = $this->execution->configuration_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $csvFilename = $config['csv_filename'] ?? 'update_stock_' . date('YmdHis') . '.csv';
        $result = $this->csvGenerator->generateStockUpdateCsv($csvData, $csvFilename);

        if (!$result['success']) {
            $this->log('ERROR', "Failed to generate CSV: {$result['error']}");
            return;
        }

        $this->execution->update([
            'csv_filename' => $result['filename'],
            'csv_path' => $result['filepath']
        ]);

        $this->log('SUCCESS', "CSV generated: {$result['filename']} ({$result['rows']} rows)");

        $this->uploadToFtp($result['filepath'], $result['filename']);
    }

    protected function collectCsvData()
    {
        return [];
    }
}
