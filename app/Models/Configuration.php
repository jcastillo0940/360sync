<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configurations';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el valor parseado según el tipo
     */
    public function getParsedValueAttribute()
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number', 'integer' => (int) $this->value,
            'float', 'decimal' => (float) $this->value,
            'json' => json_decode($this->value, true),
            'array' => is_array($this->value) ? $this->value : json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Scope para buscar por clave
     */
    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Obtener todas las configuraciones como array key => value
     */
    public static function getAllAsArray(): array
    {
        return static::all()->pluck('parsed_value', 'key')->toArray();
    }

    /**
     * Obtener valor de configuración por clave
     */
    public static function getValue($key, $default = null)
    {
        $config = static::where('key', $key)->first();
        return $config ? $config->parsed_value : $default;
    }

    /**
     * Establecer valor de configuración
     */
    public static function setValue($key, $value, $type = 'string'): bool
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}