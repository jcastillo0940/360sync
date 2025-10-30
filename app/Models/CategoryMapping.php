<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'nivel1',
        'nivel2',
        'nivel3',
        'nivel4',
        'nivel5',
        'icg_key',
        'magento_category_id',
        'magento_category_name',
        'magento_category_path',
        'category_level',
        'product_count',
        'last_synced_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        'product_count' => 'integer',
        'category_level' => 'integer',
    ];

    /**
     * Boot method para auto-generar icg_key antes de guardar
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->icg_key = $model->generateIcgKey();
            $model->category_level = $model->calculateCategoryLevel();
        });
    }

    /**
     * Genera la clave ICG basada en los niveles no nulos
     * Ej: 1004-10-1-2
     */
    public function generateIcgKey(): string
    {
        $parts = array_filter([
            $this->nivel1,
            $this->nivel2,
            $this->nivel3,
            $this->nivel4,
            $this->nivel5,
        ], function($value) {
            return !is_null($value) && $value !== '';
        });

        return implode('-', $parts);
    }

    /**
     * Calcula el nivel de profundidad de la categoría
     */
    public function calculateCategoryLevel(): int
    {
        $levels = [
            $this->nivel1,
            $this->nivel2,
            $this->nivel3,
            $this->nivel4,
            $this->nivel5,
        ];

        $count = 0;
        foreach ($levels as $level) {
            if (!is_null($level) && $level !== '') {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }

    /**
     * Obtiene el mapeo correcto para un producto basado en sus niveles
     * Busca desde el nivel más específico hasta el más general
     */
    public static function findBestMatchForProduct(array $productData): ?self
    {
        $nivel1 = $productData['NIVEL1'] ?? null;
        $nivel2 = $productData['NIVEL2'] ?? null;
        $nivel3 = $productData['NIVEL3'] ?? null;
        $nivel4 = $productData['NIVEL4'] ?? null;
        $nivel5 = $productData['NIVEL5'] ?? null;

        // Construir la clave ICG del producto
        $productIcgKey = self::buildIcgKeyFromLevels($nivel1, $nivel2, $nivel3, $nivel4, $nivel5);

        // Buscar coincidencia exacta
        $mapping = self::where('icg_key', $productIcgKey)
            ->where('is_active', true)
            ->first();

        if ($mapping) {
            return $mapping;
        }

        // Si no hay coincidencia exacta, buscar hacia arriba en la jerarquía
        // Ej: Si busca 1004-10-1-2 y no existe, busca 1004-10-1, luego 1004-10, etc.
        $levels = array_filter([$nivel1, $nivel2, $nivel3, $nivel4, $nivel5]);
        
        while (count($levels) > 0) {
            array_pop($levels); // Quita el último nivel
            $parentKey = implode('-', $levels);
            
            $mapping = self::where('icg_key', $parentKey)
                ->where('is_active', true)
                ->first();
                
            if ($mapping) {
                return $mapping;
            }
        }

        // Si no encuentra nada, buscar la categoría DEFAULT
        return self::where('icg_key', 'DEFAULT')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Construye una clave ICG desde niveles individuales
     */
    public static function buildIcgKeyFromLevels(...$levels): string
    {
        $parts = array_filter($levels, function($value) {
            return !is_null($value) && $value !== '';
        });

        return implode('-', $parts);
    }

    /**
     * Obtiene todos los productos asignados a esta categoría
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_mapping_id');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por nivel específico
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('category_level', $level);
    }

    /**
     * Scope para buscar por cualquier parte de la clave ICG
     */
    public function scopeSearchByIcg($query, string $search)
    {
        return $query->where('icg_key', 'like', "%{$search}%")
            ->orWhere('magento_category_name', 'like', "%{$search}%");
    }

    /**
     * Obtener el nombre completo de la categoría (path legible)
     */
    public function getFullCategoryNameAttribute(): string
    {
        if ($this->icg_key === 'DEFAULT') {
            return 'Default Category';
        }

        return $this->magento_category_name ?: "Category {$this->icg_key}";
    }

    /**
     * Obtener una representación visual del nivel
     */
    public function getLevelDisplayAttribute(): string
    {
        $levelNames = [
            1 => 'Level 1 (Main)',
            2 => 'Level 2 (Sub)',
            3 => 'Level 3',
            4 => 'Level 4',
            5 => 'Level 5 (Deepest)',
        ];

        return $levelNames[$this->category_level] ?? 'Unknown';
    }

    /**
     * Sincronizar el conteo de productos
     */
    public function syncProductCount(): void
    {
        $this->product_count = $this->products()->count();
        $this->save();
    }
}