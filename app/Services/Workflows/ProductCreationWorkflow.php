<?php

namespace App\Services\Workflows;

use App\Models\Execution;
use App\Models\CategoryMapping;
use Illuminate\Support\Facades\Log;

class ProductCreationWorkflow extends BaseWorkflow
{
    protected $categoryMappings = [];
    protected $warehouseMapping = [];

    public function execute(Execution $execution)
    {
        $this->execution = $execution;
        
        try {
            $this->start();

            $this->loadCategoryMappings();
            $this->loadWarehouseMapping();

            if ($this->execution->action === 'partial') {
                $this->executePartial();
            } else {
                $this->executeTotal();
            }

            $this->generateCsvAndUpload();

            $this->complete("Product creation completed: {$this->successCount} products created, {$this->failedCount} failed, {$this->skippedCount} skipped");

        } catch (\Exception $e) {
            $this->fail("Product creation failed: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function loadCategoryMappings()
    {
        $mappings = CategoryMapping::where('is_active', true)->get();
        
        foreach ($mappings as $mapping) {
            if (!empty($mapping->icg_category_path)) {
                $this->categoryMappings[$mapping->icg_category_path] = $mapping->magento_category_id;
            }
            
            if (!empty($mapping->icg_key)) {
                $this->categoryMappings[$mapping->icg_key] = $mapping->magento_category_id;
            }
        }
        
        $this->log('INFO', 'Loaded ' . count($this->categoryMappings) . ' category mappings');
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

        $this->log('INFO', "Starting partial product creation for {$this->totalItems} SKUs");

        foreach ($skus as $index => $sku) {
            try {
                $this->createProduct($sku);
                $this->updateProgress($index + 1, $this->totalItems);

            } catch (\Exception $e) {
                $this->log('ERROR', "Error creating product {$sku}: " . $e->getMessage(), $sku);
                $this->failedCount++;
            }
        }
    }

    protected function executeTotal()
    {
        $this->log('INFO', 'Starting total product sync (create/update)');

        $firstPage = $this->icgApi->getProducts(1, 100);
        
        if (!$firstPage['success']) {
            throw new \Exception("Failed to fetch products from ICG: " . ($firstPage['error'] ?? 'Unknown error'));
        }

        $totalPages = $firstPage['total_paginas'];
        $totalProducts = $firstPage['total_registros'];

        $this->log('INFO', "Total ICG products: {$totalProducts} ({$totalPages} pages)");
        $this->totalItems = $totalProducts;

        $processedCount = 0;

        for ($page = 1; $page <= $totalPages; $page++) {
            $this->log('INFO', "Processing page {$page}/{$totalPages}...", null, $page, $totalPages);
            
            $result = $this->icgApi->getProducts($page, 100);

            if (!$result['success']) {
                $this->log('WARNING', "Failed to fetch page {$page}: {$result['error']}");
                continue;
            }

            $products = $result['data'];

            if ($this->shouldFilterByWebvisb()) {
                $products = array_filter($products, function($product) {
                    return isset($product['WEBVISB']) && strtoupper($product['WEBVISB']) === 'T';
                });
            }

            foreach ($products as $product) {
                try {
                    $sku = $product['ARTCOD'] ?? null;
                    
                    if (!$sku) {
                        $this->skippedCount++;
                        continue;
                    }

                    $this->createProduct($sku, $product);
                    $processedCount++;
                    
                    if ($processedCount % 10 === 0) {
                        $this->updateProgress($processedCount, $this->totalItems);
                    }

                } catch (\Exception $e) {
                    $this->log('ERROR', "Error processing product {$sku}: " . $e->getMessage(), $sku);
                    $this->failedCount++;
                }
            }

            $this->log('INFO', "Page {$page}/{$totalPages} completed - Processed: {$processedCount}", null, $page, $totalPages);
        }
    }

    protected function createProduct($sku, $icgProduct = null)
    {
        $this->log('INFO', "Processing product: {$sku}", $sku);
        
        if (!$icgProduct) {
            $result = $this->icgApi->getProductBySku($sku);
            
            if (!$result['success']) {
                throw new \Exception("Product not found in ICG: {$sku}");
            }
            
            $icgProduct = $result['data'];
        }

        $webVisb = $icgProduct['WEBVISB'] ?? 'F';
        if (strtoupper($webVisb) !== 'T') {
            $this->log('INFO', "Product skipped (WEBVISB={$webVisb}): {$sku}", $sku);
            $this->skippedCount++;
            return;
        }

        $productData = $this->mapIcgToMagento($icgProduct);

        $exists = $this->magentoApi->productExists($sku);

        if ($exists) {
            $this->log('INFO', "Product exists, updating: {$sku}", $sku);
            $result = $this->magentoApi->updateProduct($sku, $productData);

            if ($result['success']) {
                $this->log('SUCCESS', "Product updated: {$sku} - {$productData['name']}", $sku);
                $this->assignStockToSources($sku, $icgProduct);
                $this->successCount++;
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error');
            }
        } else {
            $this->log('INFO', "Product does not exist, creating: {$sku}", $sku);
            $result = $this->magentoApi->createProduct($productData);

            if ($result['success']) {
                $this->log('SUCCESS', "Product created: {$sku} - {$productData['name']}", $sku);
                $this->assignStockToSources($sku, $icgProduct);
                $this->successCount++;
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error');
            }
        }
    }

    protected function assignStockToSources($sku, $icgProduct)
    {
        $this->log('INFO', "Assigning stock to sources for: {$sku}", $sku);
        
        $appliedStock = $icgProduct['APPLIEDSTOCK'] ?? 'T';
        $manageStock = strtoupper($appliedStock) === 'T';

        if (!$manageStock) {
            $this->log('INFO', "Product does not manage stock (APPLIEDSTOCK={$appliedStock}): {$sku}", $sku);
            
            $result = $this->magentoApi->updateStock($sku, 0, false, false);
            
            if ($result['success']) {
                $this->log('SUCCESS', "Stock management disabled for SKU {$sku}", $sku);
            }
            
            return;
        }

        $stockBySource = $this->prepareStockBySources($icgProduct);

        if (empty($stockBySource)) {
            $this->log('WARNING', "No valid stock sources found for SKU {$sku}", $sku);
            return;
        }

        $result = $this->magentoApi->updateStockBySources($sku, $stockBySource);

        if ($result['success']) {
            $totalStock = array_sum(array_column($stockBySource, 'qty'));
            $this->log('SUCCESS', "Stock assigned for SKU {$sku}: {$totalStock} units across " . count($stockBySource) . " sources", $sku);
        } else {
            $this->log('WARNING', "Failed to assign stock for SKU {$sku}: " . ($result['error'] ?? 'Unknown error'), $sku);
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

        // Incluir TODOS los stocks (0, negativos se convierten a 0, y positivos)
        $stockBySource[$sourceCode] = [
            'qty' => max(0, $disponible), // ⭐ Convierte negativos a 0
            'status' => $disponible > 0 ? 1 : 0 // In Stock solo si > 0
        ];
    }

    return $stockBySource;
}

    protected function mapIcgToMagento($icgProduct)
    {
        $sku = $icgProduct['ARTCOD'] ?? '';
        $name = $icgProduct['ARTDES'] ?? '';
        $description = $icgProduct['ARTOBSERV'] ?? $name;
        $price = (float) ($icgProduct['PVPTARIF'] ?? 0);
        $specialPrice = isset($icgProduct['PVPOFER']) && $icgProduct['PVPOFER'] > 0 
            ? (float) $icgProduct['PVPOFER'] 
            : null;
        $weight = (float) ($icgProduct['PESO'] ?? 0);
        $stock = (int) ($icgProduct['EXISTEN'] ?? 0);
        $ean = $icgProduct['ARTEAN'] ?? '';

        $categoryIds = $this->mapCategories($icgProduct);

        $customAttributes = [];
        
        if (!empty($ean)) {
            $customAttributes[] = [
                'attribute_code' => 'ean',
                'value' => $ean
            ];
        }

        if ($specialPrice) {
            $customAttributes[] = [
                'attribute_code' => 'special_price',
                'value' => $specialPrice
            ];
        }

        $config = $this->execution->configuration_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $categoryLinks = [];
        foreach ($categoryIds as $catId) {
            $categoryLinks[] = [
                'position' => 0,
                'category_id' => (string) $catId
            ];
        }

        return [
            'sku' => $sku,
            'name' => $name,
            'attribute_set_id' => $config['default_attribute_set_id'] ?? 4,
            'price' => $price,
            'status' => 1,
            'visibility' => 4,
            'type_id' => 'simple',
            'weight' => $weight,
            'extension_attributes' => [
                'stock_item' => [
                    'qty' => $stock,
                    'is_in_stock' => $stock > 0
                ],
                'category_links' => $categoryLinks
            ],
            'custom_attributes' => $customAttributes
        ];
    }

    protected function mapCategories($icgProduct)
    {
        $categoryIds = [];
        
        $nivel1 = $icgProduct['NIVEL1'] ?? null;
        $nivel2 = $icgProduct['NIVEL2'] ?? null;
        $nivel3 = $icgProduct['NIVEL3'] ?? null;
        $nivel4 = $icgProduct['NIVEL4'] ?? null;
        
        if ($nivel1) {
            $paths = [
                implode('-', array_filter([$nivel1, $nivel2, $nivel3, $nivel4])),
                implode('-', array_filter([$nivel1, $nivel2, $nivel3])),
                implode('-', array_filter([$nivel1, $nivel2])),
                $nivel1,
            ];
            
            foreach ($paths as $path) {
                if (isset($this->categoryMappings[$path])) {
                    $categoryIds[] = $this->categoryMappings[$path];
                    $this->log('SUCCESS', "Mapped category path '{$path}' to Magento category ID: {$this->categoryMappings[$path]}", $icgProduct['ARTCOD']);
                    break;
                }
            }
            
            if (empty($categoryIds)) {
                $fullPath = implode('-', array_filter([$nivel1, $nivel2, $nivel3, $nivel4]));
                $this->log('WARNING', "No category mapping found for path: {$fullPath}", $icgProduct['ARTCOD']);
            }
        } else {
            if (isset($icgProduct['Familia']) && !empty($icgProduct['Familia'])) {
                $familiaKey = $icgProduct['Familia'];
                if (isset($this->categoryMappings[$familiaKey])) {
                    $categoryIds[] = $this->categoryMappings[$familiaKey];
                } else {
                    $this->log('WARNING', "Category mapping not found for Familia: {$familiaKey}", $icgProduct['ARTCOD']);
                }
            }

            if (isset($icgProduct['Subfamilia']) && !empty($icgProduct['Subfamilia'])) {
                $subfamiliaKey = $icgProduct['Subfamilia'];
                if (isset($this->categoryMappings[$subfamiliaKey])) {
                    $categoryIds[] = $this->categoryMappings[$subfamiliaKey];
                }
            }
        }

        return array_unique($categoryIds);
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

        $csvFilename = $config['csv_filename'] ?? 'create_productos_' . date('YmdHis') . '.csv';
        $result = $this->csvGenerator->generateProductCreationCsv($csvData, $csvFilename);

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