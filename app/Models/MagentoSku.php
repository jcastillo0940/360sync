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
    public static function hasRealPriceChange($sku, $newPrice, $newSpecialPrice = null, $newFromDate = null, $newToDate = null)
    {
        $current = static::where('sku', $sku)->first();

        if (!$current) {
            return true; // Si no existe, asumimos que hay cambio
        }

        // Comparar precio regular (con tolerancia de 0.01)
        if (abs($current->price - $newPrice) > 0.01) {
            return true;
        }

        // Comparar precio especial
        $currentSpecial = $current->special_price;
        if (abs(($newSpecialPrice ?? 0) - ($currentSpecial ?? 0)) > 0.01) {
            return true;
        }

        // Comparar fechas de oferta
        if ($newSpecialPrice) {
            $currentFrom = $current->special_from_date ? $current->special_from_date->format('Y-m-d') : null;
            $currentTo = $current->special_to_date ? $current->special_to_date->format('Y-m-d') : null;

            if ($currentFrom != $newFromDate || $currentTo != $newToDate) {
                return true;
            }
        }

        return false;
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
```

### **Commit message:**
```
feat: Add price comparison methods to MagentoSku model
