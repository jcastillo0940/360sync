<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagentoSku extends Model
{
    protected $fillable = [
        'sku',
        'synced_at'
    ];

    protected $casts = [
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
     * Verificar si un SKU existe
     */
    public static function skuExists($sku)
    {
        return static::where('sku', $sku)->exists();
    }

    /**
     * Sincronizar SKUs desde Magento
     */
    public static function syncFromMagento(array $skus)
    {
        $now = now();
        
        // Preparar datos para inserción masiva
        $data = [];
        foreach ($skus as $sku) {
            $data[] = [
                'sku' => $sku,
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

        return count($skus);
    }
}