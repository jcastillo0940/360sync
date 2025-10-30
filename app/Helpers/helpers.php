<?php

use Illuminate\Support\Str;
use Carbon\Carbon;

if (!function_exists('format_bytes')) {
    /**
     * Formatear bytes a formato legible
     */
    function format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('format_duration')) {
    /**
     * Formatear duración en segundos a formato legible
     */
    function format_duration($seconds)
    {
        if ($seconds < 60) {
            return round($seconds, 2) . 's';
        }
        
        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;
            return $minutes . 'm ' . round($secs) . 's';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . 'h ' . $minutes . 'm';
    }
}

if (!function_exists('status_badge')) {
    /**
     * Generar HTML de badge de estado
     */
    function status_badge($status)
    {
        $classes = [
            'completed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
        ];
        
        $labels = [
            'completed' => 'Completado',
            'pending' => 'Pendiente',
            'failed' => 'Fallido',
            'processing' => 'Procesando',
            'cancelled' => 'Cancelado',
        ];
        
        $class = $classes[$status] ?? 'bg-gray-100 text-gray-800';
        $label = $labels[$status] ?? ucfirst($status);
        
        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $class . '">' . $label . '</span>';
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitizar nombre de archivo
     */
    function sanitize_filename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}

if (!function_exists('generate_unique_code')) {
    /**
     * Generar código único
     */
    function generate_unique_code($prefix = '', $length = 8)
    {
        $code = strtoupper(Str::random($length));
        return $prefix ? $prefix . '-' . $code : $code;
    }
}

if (!function_exists('array_to_csv')) {
    /**
     * Convertir array a string CSV
     */
    function array_to_csv($data, $delimiter = ',', $enclosure = '"')
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter, $enclosure);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}

if (!function_exists('log_activity')) {
    /**
     * Registrar actividad del usuario
     */
    function log_activity($description, $properties = [], $logName = 'default')
    {
        \Log::info($description, array_merge([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $properties));
    }
}

if (!function_exists('is_json')) {
    /**
     * Verificar si un string es JSON válido
     */
    function is_json($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('percentage')) {
    /**
     * Calcular porcentaje
     */
    function percentage($value, $total, $decimals = 2)
    {
        if ($total == 0) {
            return 0;
        }
        
        return round(($value / $total) * 100, $decimals);
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Truncar texto con elipsis
     */
    function truncate_text($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('date_diff_human')) {
    /**
     * Diferencia de fechas en formato humano
     */
    function date_diff_human($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        return $date->diffForHumans();
    }
}

if (!function_exists('config_value')) {
    /**
     * Obtener valor de configuración del sistema
     */
    function config_value($key, $default = null)
    {
        $config = \App\Models\Configuration::where('key', $key)->first();
        return $config ? $config->value : $default;
    }
}

if (!function_exists('mask_sensitive_data')) {
    /**
     * Enmascarar datos sensibles
     */
    function mask_sensitive_data($value, $visibleChars = 4)
    {
        if (strlen($value) <= $visibleChars) {
            return str_repeat('*', strlen($value));
        }
        
        return substr($value, 0, $visibleChars) . str_repeat('*', strlen($value) - $visibleChars);
    }
}