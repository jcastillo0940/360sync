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

        $this->log('INFO', "Starting partial update for " . count($skus) . " SKUs");

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

    $magentoProducts = MagentoSku::select('sku', 'price', 'special_price', 'special_from_date', 'special_to_date')
        ->get()
        ->keyBy('sku');
    
    $lastSync = MagentoSku::max('synced_at');
    $this->log('INFO', 'Found ' . $magentoProducts->count() . ' SKUs in Magento (last sync: ' . ($lastSync ?? 'never') . ')');

    if ($magentoProducts->isEmpty()) {
        throw new \Exception('No SKUs found in local database. Please run: php artisan magento:sync-skus');
    }

    $this->totalItems = $magentoProducts->count();
    $processedCount = 0;

    foreach ($magentoProducts as $sku => $magentoProduct) {
        try {
            $result = $this->icgApi->getProductBySku($sku);
            
            if ($result['success']) {
                $icgProduct = $result['data'];
                $this->updateProductPriceOptimized($sku, $icgProduct, $magentoProduct);
            } else {
                $this->skippedCount++;
            }
            
            $processedCount++;
            
            if ($processedCount % 100 === 0) {
                $this->updateProgress($processedCount, $this->totalItems);
                $this->log('INFO', "Progress: {$processedCount}/{$this->totalItems} - Success: {$this->successCount}, Skipped: {$this->skippedCount}");
            }
            
        } catch (\Exception $e) {
            $this->log('ERROR', "Error processing SKU {$sku}: " . $e->getMessage(), $sku);
            $this->failedCount++;
        }
    }

    $this->updateProgress($this->totalItems, $this->totalItems);
    $this->log('INFO', "Total price update completed: {$processedCount} products processed");
}
    /**
     * Actualizar precio de un producto (versión optimizada para Total)
     */
    protected function updateProductPriceOptimized($sku, $icgProduct, $magentoProduct)
    {
        $webVisb = $icgProduct['WEBVISB'] ?? 'F';
        if (strtoupper($webVisb) !== 'T') {
            $this->skippedCount++;
            return;
        }

        $newPrice = (float) ($icgProduct['PVPTARIF'] ?? 0);
        $newSpecialPrice = isset($icgProduct['PVPOFER']) && $icgProduct['PVPOFER'] > 0 
            ? (float) $icgProduct['PVPOFER'] 
            : null;
        
        $newSpecialFromDate = $icgProduct['PVPOFER_DESDE'] ?? null;
        $newSpecialToDate = $icgProduct['PVPOFER_HASTA'] ?? null;

        // Comparar con datos en memoria
        if (!$this->hasRealPriceChangeInMemory($magentoProduct, $newPrice, $newSpecialPrice, $newSpecialFromDate, $newSpecialToDate)) {
            $this->skippedCount++;
            return;
        }

        $result = $this->magentoApi->updatePrice($sku, $newPrice, $newSpecialPrice, $newSpecialFromDate, $newSpecialToDate);

        if ($result['success']) {
            MagentoSku::updatePrice($sku, $newPrice, $newSpecialPrice, $newSpecialFromDate, $newSpecialToDate);
            
            $priceInfo = "€{$newPrice}";
            if ($newSpecialPrice) {
                $priceInfo .= " (Special: €{$newSpecialPrice})";
            }
            
            $this->log('SUCCESS', "Price updated for SKU {$sku}: {$priceInfo}", $sku);
            $this->successCount++;
        } else {
            throw new \Exception($result['error'] ?? 'Unknown error');
        }
    }

    /**
     * Comparar precios en memoria sin DB query
     */
    protected function hasRealPriceChangeInMemory($magentoProduct, $newPrice, $newSpecialPrice, $newFromDate, $newToDate)
    {
        if (abs($magentoProduct->price - $newPrice) > 0.01) return true;
        if (abs(($magentoProduct->special_price ?? 0) - ($newSpecialPrice ?? 0)) > 0.01) return true;
        
        $oldFrom = $magentoProduct->special_from_date ? Carbon::parse($magentoProduct->special_from_date)->format('Y-m-d') : null;
        $oldTo = $magentoProduct->special_to_date ? Carbon::parse($magentoProduct->special_to_date)->format('Y-m-d') : null;
        $newFrom = $newFromDate ? Carbon::parse($newFromDate)->format('Y-m-d') : null;
        $newTo = $newToDate ? Carbon::parse($newToDate)->format('Y-m-d') : null;
        
        if ($oldFrom !== $newFrom) return true;
        if ($oldTo !== $newTo) return true;
        
        return false;
    }

    /**
     * Actualizar precio de un producto (versión para Partial)
     */
    protected function updateProductPrice($sku, $icgProduct = null)
    {
        // Obtener datos del producto de ICG
        if (!$icgProduct) {
            $result = $this->icgApi->getProductBySku($sku);
            
            if (!$result['success']) {
                $this->skippedCount++;
                $this->log('WARNING', "Product not found in ICG: {$sku}", $sku);
                return;
            }
            
            $icgProduct = $result['data'];
        }

        $webVisb = $icgProduct['WEBVISB'] ?? 'F';
        if (strtoupper($webVisb) !== 'T') {
            $this->skippedCount++;
            return;
        }

        $price = (float) ($icgProduct['PVPTARIF'] ?? 0);
        $specialPrice = isset($icgProduct['PVPOFER']) && $icgProduct['PVPOFER'] > 0 
            ? (float) $icgProduct['PVPOFER'] 
            : null;
        
        $specialFromDate = $icgProduct['PVPOFER_DESDE'] ?? null;
        $specialToDate = $icgProduct['PVPOFER_HASTA'] ?? null;

        $result = $this->magentoApi->updatePrice($sku, $price, $specialPrice, $specialFromDate, $specialToDate);

        if ($result['success']) {
            MagentoSku::updatePrice($sku, $price, $specialPrice, $specialFromDate, $specialToDate);
            
            $priceInfo = "€{$price}";
            if ($specialPrice) {
                $priceInfo .= " (Special: €{$specialPrice})";
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
