<?php

namespace App\Services\Workflows;

use App\Models\Execution;
use Carbon\Carbon;

class OfferUpdateWorkflow extends BaseWorkflow
{
    /**
     * Ejecutar workflow de actualización de ofertas
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

            $this->complete("Offer update completed: {$this->successCount} offers updated, {$this->failedCount} failed, {$this->skippedCount} skipped");

        } catch (\Exception $e) {
            $this->fail("Offer update failed: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Ejecución parcial
     */
    protected function executePartial()
    {
        $skus = $this->getSkus();
        $this->totalItems = count($skus);

        $this->log('INFO', "Starting partial offer update for {$this->totalItems} SKUs");

        foreach ($skus as $index => $sku) {
            try {
                $this->updateProductOffer($sku);
                $this->updateProgress($index + 1, $this->totalItems);

            } catch (\Exception $e) {
                $this->log('ERROR', "Error updating offer for SKU {$sku}: " . $e->getMessage(), $sku);
                $this->failedCount++;
            }
        }
    }

    /**
     * Ejecución total
     */
    protected function executeTotal()
    {
        $this->log('INFO', 'Starting total offer update (processing all products)');

        $firstPage = $this->icgApi->getProducts(1, 100);
        
        if (!$firstPage['success']) {
            throw new \Exception("Failed to fetch products from ICG: " . ($firstPage['error'] ?? 'Unknown error'));
        }

        $totalPages = $firstPage['total_paginas'];
        $this->totalItems = $firstPage['total_registros'];

        $this->log('INFO', "Total products to process: {$this->totalItems} ({$totalPages} pages)");

        $processedCount = 0;

        for ($page = 1; $page <= $totalPages; $page++) {
            $this->log('INFO', "Processing page {$page}/{$totalPages}...", null, $page, $totalPages);
            
            $result = $this->icgApi->getProducts($page, 100);

            if (!$result['success']) {
                $this->log('WARNING', "Failed to fetch page {$page}: {$result['error']}");
                continue;
            }

            $products = $result['data'];

            foreach ($products as $product) {
                try {
                    $sku = $product['ARTCOD'] ?? null;
                    
                    if (!$sku) {
                        $this->skippedCount++;
                        continue;
                    }

                    // Verificar WEBVISB - si es F, no procesar
                    $webVisb = $product['WEBVISB'] ?? 'F';
                    if (strtoupper($webVisb) !== 'T') {
                        $this->log('INFO', "Product skipped (WEBVISB={$webVisb}): {$sku}", $sku);
                        $this->skippedCount++;
                        continue;
                    }

                    $this->updateProductOffer($sku, $product);
                    $processedCount++;
                    
                    if ($processedCount % 10 === 0) {
                        $this->updateProgress($processedCount, $this->totalItems);
                    }

                } catch (\Exception $e) {
                    $this->log('ERROR', "Error processing product {$sku}: " . $e->getMessage(), $sku);
                    $this->failedCount++;
                }
            }

            $this->log('INFO', "Page {$page}/{$totalPages} completed", null, $page, $totalPages);
        }
    }

    /**
     * Actualizar oferta de un producto
     */
    protected function updateProductOffer($sku, $icgProduct = null)
    {
        $this->log('INFO', "Processing offer for: {$sku}", $sku);
        
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
            $this->log('INFO', "Product skipped (WEBVISB={$webVisb}): {$sku}", $sku);
            $this->skippedCount++;
            return;
        }

        // Verificar que el producto existe en Magento
        if (!$this->magentoApi->productExists($sku)) {
            $this->log('WARNING', "Product does not exist in Magento: {$sku}", $sku);
            $this->skippedCount++;
            return;
        }

        // Obtener precios
        $regularPrice = (float) ($icgProduct['PVPTARIF'] ?? 0);
        $specialPrice = isset($icgProduct['PVPOFER']) && $icgProduct['PVPOFER'] > 0 
            ? (float) $icgProduct['PVPOFER'] 
            : null;

        // Obtener fechas de oferta desde ICG
        $ofertaDesde = $icgProduct['OfertaDesde'] ?? null;
        $ofertaHasta = $icgProduct['OfertaHasta'] ?? null;

        // Validar si la oferta está activa
        $isOfferActive = false;
        $offerReason = '';
        
        // Primero verificar que OfertaPrecio > 0
        if (!$specialPrice || $specialPrice <= 0) {
            $isOfferActive = false;
            $offerReason = "No special price (OfertaPrecio = 0 or null)";
        } 
        // Luego verificar que el precio especial sea menor que el precio regular
        elseif ($specialPrice >= $regularPrice) {
            $isOfferActive = false;
            $offerReason = "Special price is not less than regular price";
        } 
        // Finalmente verificar las fechas
        else {
            $now = Carbon::now();
            
            // Parsear fechas
            try {
                $fromDate = $ofertaDesde ? Carbon::parse($ofertaDesde) : null;
                $toDate = $ofertaHasta ? Carbon::parse($ofertaHasta) : null;
                
                // Verificar si las fechas son válidas (no son 1900-01-01)
                if ($fromDate && $fromDate->year == 1900) {
                    $fromDate = null;
                }
                if ($toDate && $toDate->year == 1900) {
                    $toDate = null;
                }
                
                // Validar rango de fechas
                if ($fromDate && $fromDate->isFuture()) {
                    // Oferta aún no comienza
                    $isOfferActive = false;
                    $offerReason = "Offer starts in the future: {$fromDate->format('Y-m-d')}";
                } elseif ($toDate && $toDate->isPast()) {
                    // Oferta ya expiró
                    $isOfferActive = false;
                    $offerReason = "Offer expired on: {$toDate->format('Y-m-d')}";
                } else {
                    // Oferta está activa
                    $isOfferActive = true;
                    $offerReason = "Offer is active";
                    if ($fromDate || $toDate) {
                        $offerReason .= " (";
                        if ($fromDate) $offerReason .= "from {$fromDate->format('Y-m-d')} ";
                        if ($toDate) $offerReason .= "until {$toDate->format('Y-m-d')}";
                        $offerReason .= ")";
                    }
                }
            } catch (\Exception $e) {
                // Error parseando fechas, asumir que no hay fechas válidas
                $isOfferActive = false;
                $offerReason = "Invalid offer dates";
                $this->log('WARNING', "Invalid offer dates for SKU {$sku}: {$e->getMessage()}", $sku);
            }
        }

        // Preparar datos de actualización
        $customAttributes = [];

        if ($isOfferActive) {
            // OFERTA ACTIVA: Actualizar precio especial con fechas de ICG
            $customAttributes[] = [
                'attribute_code' => 'special_price',
                'value' => $specialPrice
            ];
            
            // Si hay fechas válidas de ICG, usarlas; sino, usar fechas por defecto
            if (isset($fromDate) && $fromDate && $fromDate->year != 1900) {
                $customAttributes[] = [
                    'attribute_code' => 'special_from_date',
                    'value' => $fromDate->format('Y-m-d H:i:s')
                ];
            }
            
            if (isset($toDate) && $toDate && $toDate->year != 1900) {
                $customAttributes[] = [
                    'attribute_code' => 'special_to_date',
                    'value' => $toDate->format('Y-m-d H:i:s')
                ];
            } else {
                // Si no hay fecha de fin válida, poner 30 días por defecto
                $customAttributes[] = [
                    'attribute_code' => 'special_to_date',
                    'value' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s')
                ];
            }

            $discount = round((($regularPrice - $specialPrice) / $regularPrice) * 100, 2);
            $logMessage = "Offer activated for SKU {$sku}: €{$regularPrice} → €{$specialPrice} ({$discount}% off) - {$offerReason}";
        } else {
            // OFERTA NO ACTIVA: Limpiar precio especial y fechas (dejar en blanco/null)
            $customAttributes[] = [
                'attribute_code' => 'special_price',
                'value' => null
            ];
            $customAttributes[] = [
                'attribute_code' => 'special_from_date',
                'value' => null
            ];
            $customAttributes[] = [
                'attribute_code' => 'special_to_date',
                'value' => null
            ];

            $logMessage = "Offer removed/deactivated for SKU {$sku}: {$offerReason}";
        }

        $productData = [
            'price' => $regularPrice,
            'custom_attributes' => $customAttributes
        ];

        $result = $this->magentoApi->updateProduct($sku, $productData);

        if ($result['success']) {
            $this->log('SUCCESS', $logMessage, $sku);
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

        $csvFilename = $config['csv_filename'] ?? 'update_ofertas_' . date('YmdHis') . '.csv';
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
     * Recolectar datos para CSV
     */
    protected function collectCsvData()
    {
        // TODO: Implementar recolección de datos durante el proceso
        return [];
    }
}	