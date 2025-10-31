<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagentoSku extends Model
{
    protected $fillable = [
        'sku',
        'price',
        'special_price',
        'special_from_date',
        'special_to_date',
        'synced_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'special_price' => 'decimal:2',
        'special_from_date' => 'date',
        'special_to_date' => 'date',
        'synced_at' => 'datetime'
    ];

    /**
     * Obtener todos los SKUs como array simple
     */
    public static function getAllSkusArray()
    {
        return static::pluck('sku')->toArray();
    }

    /**
     * Obtener todos los SKUs con sus precios como array asociativo
     */
    public static function getAllSkusWithPrices()
    {
        return static::get()->keyBy('sku')->map(function ($item) {
            return [
                'price' => $item->price,
                'special_price' => $item->special_price,
                'special_from_date' => $item->special_from_date ? $item->special_from_date->format('Y-m-d') : null,
                'special_to_date' => $item->special_to_date ? $item->special_to_date->format('Y-m-d') : null,
            ];
        })->toArray();
    }

    /**
     * Verificar si un SKU existe
     */
    public static function skuExists($sku)
    {
        return static::where('sku', $sku)->exists();
    }

    /**
     * Verificar si hay cambio real en el precio
     */
    public static function hasRealPriceChange($sku, $newPrice, $newSpecialPrice = null, $newSpecialFromDate = null, $newSpecialToDate = null)
{
    $existing = self::where('sku', $sku)->first();
    
    if (!$existing) {
        return true; // Si no existe, es un cambio
    }

    // Comparar precio regular (con tolerancia de 0.01)
    if (abs($existing->price - $newPrice) > 0.01) {
        return true;
    }

    // Comparar precio especial (con tolerancia de 0.01)
    $existingSpecial = $existing->special_price ?? 0;
    $newSpecial = $newSpecialPrice ?? 0;
    
    if (abs($existingSpecial - $newSpecial) > 0.01) {
        return true;
    }

    // ⭐ Normalizar fechas a solo YYYY-MM-DD para comparación
    $existingFrom = $existing->special_from_date 
        ? \Carbon\Carbon::parse($existing->special_from_date)->format('Y-m-d')
        : null;
    
    $existingTo = $existing->special_to_date 
        ? \Carbon\Carbon::parse($existing->special_to_date)->format('Y-m-d')
        : null;
    
    $newFrom = $newSpecialFromDate 
        ? \Carbon\Carbon::parse($newSpecialFromDate)->format('Y-m-d')
        : null;
    
    $newTo = $newSpecialToDate 
        ? \Carbon\Carbon::parse($newSpecialToDate)->format('Y-m-d')
        : null;

    // Comparar fechas (solo la parte de fecha, sin horas)
    if ($existingFrom !== $newFrom) {
        return true;
    }

    if ($existingTo !== $newTo) {
        return true;
    }

    return false; // No hay cambios reales
}

    /**
     * Actualizar precio de un SKU en la tabla local
     */
    public static function updatePrice($sku, $price, $specialPrice = null, $fromDate = null, $toDate = null)
    {
        return static::updateOrCreate(
            ['sku' => $sku],
            [
                'price' => $price,
                'special_price' => $specialPrice,
                'special_from_date' => $fromDate,
                'special_to_date' => $toDate,
                'synced_at' => now()
            ]
        );
    }

    /**
     * Sincronizar SKUs desde Magento (CON precios)
     */
    public static function syncFromMagento(array $products)
    {
        $now = now();
        
        // Preparar datos para inserción masiva
        $data = [];
        foreach ($products as $product) {
            $sku = is_array($product) ? $product['sku'] : $product;
            $price = is_array($product) ? ($product['price'] ?? null) : null;
            $specialPrice = is_array($product) ? ($product['special_price'] ?? null) : null;
            $fromDate = is_array($product) ? ($product['special_from_date'] ?? null) : null;
            $toDate = is_array($product) ? ($product['special_to_date'] ?? null) : null;

            $data[] = [
                'sku' => $sku,
                'price' => $price,
                'special_price' => $specialPrice,
                'special_from_date' => $fromDate,
                'special_to_date' => $toDate,
                'synced_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // Truncar tabla y reinsertar
        static::truncate();
        
        // Insertar en lotes de 1000
        foreach (array_chunk($data, 1000) as $chunk) {
            static::insert($chunk);
        }

        return count($products);
    }
}
