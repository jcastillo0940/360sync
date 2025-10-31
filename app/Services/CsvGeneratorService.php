<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CsvGeneratorService
{
    protected $directory = 'csv_exports';

    /**
     * Generar archivo CSV
     */
    public function generate($filename, $headers, $data)
    {
        try {
            // Crear directorio si no existe
            $fullPath = storage_path("app/{$this->directory}");
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $filepath = "{$fullPath}/{$filename}";

            // Abrir archivo para escritura
            $file = fopen($filepath, 'w');

            if (!$file) {
                throw new \Exception("Could not create CSV file: {$filepath}");
            }

            // Escribir BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Escribir headers
            fputcsv($file, $headers, ';');

            // Escribir datos
            $rowCount = 0;
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
                $rowCount++;
            }

            fclose($file);

            Log::info('CSV Generated', [
                'filename' => $filename,
                'rows' => $rowCount,
                'size' => filesize($filepath)
            ]);

            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => $filename,
                'rows' => $rowCount,
                'size' => filesize($filepath)
            ];

        } catch (\Exception $e) {
            Log::error('CSV Generation Error', [
                'filename' => $filename,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar CSV de actualización de productos
     */
    public function generateProductUpdateCsv($products, $filename = null)
    {
        $filename = $filename ?? 'update_productos_' . date('YmdHis') . '.csv';

        $headers = [
            'sku',
            'name',
            'price',
            'special_price',
            'stock',
            'status',
            'visibility',
            'weight',
            'ean',
            'description'
        ];

        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                $product['sku'] ?? '',
                $product['name'] ?? '',
                $product['price'] ?? '',
                $product['special_price'] ?? '',
                $product['stock'] ?? '',
                $product['status'] ?? '1',
                $product['visibility'] ?? '4',
                $product['weight'] ?? '',
                $product['ean'] ?? '',
                $product['description'] ?? ''
            ];
        }

        return $this->generate($filename, $headers, $rows);
    }

    /**
     * Generar CSV de actualización de precios
     */
    public function generatePriceUpdateCsv($products, $filename = null)
    {
        $filename = $filename ?? 'update_precios_' . date('YmdHis') . '.csv';

        $headers = [
            'sku',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date'
        ];

        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                $product['sku'] ?? '',
                $product['price'] ?? '',
                $product['special_price'] ?? '',
                $product['special_from_date'] ?? '',
                $product['special_to_date'] ?? ''
            ];
        }

        return $this->generate($filename, $headers, $rows);
    }

    /**
     * Generar CSV de actualización de stock
     */
    public function generateStockUpdateCsv($products, $filename = null)
    {
        $filename = $filename ?? 'update_stock_' . date('YmdHis') . '.csv';

        $headers = [
            'sku',
            'qty',
            'is_in_stock',
            'warehouse'
        ];

        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                $product['sku'] ?? '',
                $product['qty'] ?? '',
                $product['is_in_stock'] ?? '1',
                $product['warehouse'] ?? 'default'
            ];
        }

        return $this->generate($filename, $headers, $rows);
    }

    /**
     * Generar CSV de creación de productos
     */
    public function generateProductCreationCsv($products, $filename = null)
    {
        $filename = $filename ?? 'create_productos_' . date('YmdHis') . '.csv';

        $headers = [
            'sku',
            'attribute_set_code',
            'product_type',
            'categories',
            'name',
            'description',
            'short_description',
            'price',
            'special_price',
            'weight',
            'qty',
            'visibility',
            'status',
            'tax_class_id',
            'ean',
            'brand',
            'familia',
            'subfamilia'
        ];

        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                $product['sku'] ?? '',
                $product['attribute_set_code'] ?? 'Default',
                $product['product_type'] ?? 'simple',
                $product['categories'] ?? '',
                $product['name'] ?? '',
                $product['description'] ?? '',
                $product['short_description'] ?? '',
                $product['price'] ?? '',
                $product['special_price'] ?? '',
                $product['weight'] ?? '',
                $product['qty'] ?? '0',
                $product['visibility'] ?? '4',
                $product['status'] ?? '1',
                $product['tax_class_id'] ?? '2',
                $product['ean'] ?? '',
                $product['brand'] ?? '',
                $product['familia'] ?? '',
                $product['subfamilia'] ?? ''
            ];
        }

        return $this->generate($filename, $headers, $rows);
    }

    /**
     * Leer archivo CSV
     */
    public function read($filepath)
    {
        try {
            if (!file_exists($filepath)) {
                throw new \Exception("CSV file not found: {$filepath}");
            }

            $data = [];
            $headers = [];
            
            if (($handle = fopen($filepath, 'r')) !== false) {
                // Leer headers
                $headers = fgetcsv($handle, 0, ';');
                
                // Leer datos
                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    $data[] = array_combine($headers, $row);
                }
                
                fclose($handle);
            }

            return [
                'success' => true,
                'headers' => $headers,
                'data' => $data,
                'rows' => count($data)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar archivo CSV
     */
    public function delete($filepath)
    {
        try {
            if (file_exists($filepath)) {
                unlink($filepath);
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => 'File not found'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener ruta completa del archivo
     */
    public function getFilePath($filename)
    {
        return storage_path("app/{$this->directory}/{$filename}");
    }
}