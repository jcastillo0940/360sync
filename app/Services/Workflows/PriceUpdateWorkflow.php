<?php

namespace App\Services\Workflows;

use App\Models\Execution;
use App\Models\SyncConfiguration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\MagentoSku;

class PriceUpdateWorkflow extends BaseWorkflow
{
    /**
     * Ejecutar workflow de actualización de precios
     */
    public function execute(Execution $execution)
    {
        $this->execution = $execution;
        
        try {
            $this->start();

            if ($this->execution->action === 'partial') {
                $this->executePartial();
            } else {
                $this->executeTotal();
            }

            $this->generateCsvAndUpload();

            $this->complete("Price update completed: {$this->successCount} products updated, {$this->failedCount} failed, {$this->skippedCount} skipped");

        } catch (\Exception $e) {
            $this->fail("Price update failed: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Ejecución parcial (por SKUs específicos)
     */
    protected function executePartial()
    {
        $skus = $this->getSkus();
        $this->totalItems = count($skus);

        $this->log('INFO', "Starting partial price update for {$this->totalItems} SKUs");

        foreach ($skus as $index => $sku) {
            try {
                $this->updateProductPrice($sku);
                $this->updateProgress($index + 1, $this->totalItems);

            } catch (\Exception $e) {
                $this->log('ERROR', "Error updating price for SKU {$sku}: " . $e->getMessage(), $sku);
                $this->failedCount++;
            }
        }
    }

    /**
     * Ejecución total (todos los productos)
     */
    protected function executeTotal()
    {
        $this->log('INFO', 'Starting total price update');

        // ⭐ Cargar SKUs desde tabla local
        $this->log('INFO', 'Loading SKUs from local database...');
        $magentoSkus = MagentoSku::getAllSkusArray();
        $this->log('INFO', 'Found ' . count($magentoSkus) . ' SKUs in Magento (from local database)');

        if (empty($magentoSkus)) {
            throw new \Exception('No SKUs found in local database. Please run: php artisan magento:sync-skus');
        }

        $filters = $this->prepareDateFilters();
        
        if (!empty($filters)) {
            $this->log('INFO', 'Using date filters', $filters);
        }

        $page = 1;
        $perPage = 100;
        $processedCount = 0;
        $totalProcessed = 0;

        do {
            $this->log('INFO', "Fetching page {$page}...");
            
            $result = $this->icgApi->getProducts($page, $perPage, $filters);
            
            if (!$result['success']) {
                throw new \Exception("Failed to fetch products from ICG: " . ($result['error'] ?? 'Unknown error'));
            }

            $products = $result['data'];
            $productsCount = count($products);
            
            $this->log('INFO', "Page {$page}: {$productsCount} products retrieved from ICG");

            if ($productsCount == 0) {
                break;
            }

            foreach ($products as $product) {
                try {
                    $sku = $product['ARTCOD'] ?? null;
                    
                    if (!$sku) {
                        $this->skippedCount++;
                        continue;
                    }

                    // ⭐ OPTIMIZACIÓN 2: Filtrar en memoria (sin API calls)
                    if (!in_array($sku, $magentoSkus)) {
                        $this->skippedCount++;
                        continue;
                    }

                    $webVisb = $product['WEBVISB'] ?? 'F';
                    if (strtoupper($webVisb) !== 'T') {
                        $this->skippedCount++;
                        continue;
                    }

                    $this->updateProductPrice($sku, $product);
                    $processedCount++;
                    $totalProcessed++;
                    
                    if ($processedCount % 10 === 0) {
                        $this->log('INFO', "Processed {$totalProcessed} products so far...");
                    }

                } catch (\Exception $e) {
                    $this->log('ERROR', "Error processing product {$sku}: " . $e->getMessage(), $sku);
                    $this->failedCount++;
                }
            }

            $this->log('INFO', "Page {$page} completed. Total processed: {$totalProcessed}");
            
            if ($result['is_last_page'] ?? false) {
                break;
            }
            
            $page++;
            
            //if ($page > 100) {
                //$this->log('WARNING', 'Reached page limit (100). Stopping execution.');
                //break;
            //}
            
        } while (true);

        $this->log('INFO', "Total price update completed: {$totalProcessed} products processed");
    }

    /**
     * Preparar filtros de fecha
     */
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

    /**
     * Actualizar precio de un producto
     */
    protected function updateProductPrice($sku, $icgProduct = null)
    {
        // Si no tenemos los datos del producto, obtenerlos de ICG
        if (!$icgProduct) {
            $result = $this->icgApi->getProductBySku($sku);
            
            if (!$result['success']) {
                throw new \Exception("Product not found in ICG: {$sku}");
            }
            
            $icgProduct = $result['data'];
        }

        // Verificar WEBVISB
        $webVisb = $icgProduct['WEBVISB'] ?? 'F';
        if (strtoupper($webVisb) !== 'T') {
            $this->skippedCount++;
            return;
        }

        // Obtener configuración de tarifa
        $config = $this->execution->configuration_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $tariffId = $config['tariff_id'] ?? 12;

        // ⭐ Obtener precios y fechas de oferta
        $price = (float) ($icgProduct['PVPTARIF'] ?? 0);
        $specialPrice = isset($icgProduct['PVPOFER']) && $icgProduct['PVPOFER'] > 0 
            ? (float) $icgProduct['PVPOFER'] 
            : null;
        
        // ⭐ Obtener fechas de oferta (si existen)
        $specialFromDate = $icgProduct['PVPOFER_DESDE'] ?? null;
        $specialToDate = $icgProduct['PVPOFER_HASTA'] ?? null;

        // ⭐ Actualizar precio en Magento (con fechas)
        $result = $this->magentoApi->updatePrice($sku, $price, $specialPrice, $specialFromDate, $specialToDate);

        if ($result['success']) {
            $priceInfo = "€{$price}";
            if ($specialPrice) {
                $priceInfo .= " (Special: €{$specialPrice}";
                if ($specialFromDate && $specialToDate) {
                    $fromDate = Carbon::parse($specialFromDate)->format('m/d');
                    $toDate = Carbon::parse($specialToDate)->format('m/d');
                    $priceInfo .= " from {$fromDate} to {$toDate}";
                }
                $priceInfo .= ")";
            } else {
                $priceInfo .= " (No active offer)";
            }
            $this->log('SUCCESS', "Price updated for SKU {$sku}: {$priceInfo}", $sku);
            $this->successCount++;
        } else {
            throw new \Exception($result['error'] ?? 'Unknown error');
        }
    }

    /**
     * Generar CSV y subir a FTP
     */
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

        $csvFilename = $config['csv_filename'] ?? 'update_precios_' . date('YmdHis') . '.csv';
        $result = $this->csvGenerator->generatePriceUpdateCsv($csvData, $csvFilename);

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

    /**
     * Recolectar datos para el CSV
     */
    protected function collectCsvData()
    {
        return [];
    }
}
