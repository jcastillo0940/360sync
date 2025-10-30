<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpClientService
{
    /**
     * Crear cliente HTTP con configuración segura
     * 
     * @param array $headers Headers adicionales
     * @param int $timeout Timeout en segundos
     * @param bool $verifySSL Verificar certificados SSL (true por defecto)
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public static function makeClient(array $headers = [], int $timeout = 30, bool $verifySSL = true)
    {
        $options = [
            'timeout' => $timeout,
            'connect_timeout' => 10,
        ];

        // Configuración de SSL
        if (!$verifySSL && app()->environment('local')) {
            // Solo permitir deshabilitar SSL en ambiente local
            $options['verify'] = false;
            Log::warning('⚠️ SSL verification disabled - Solo usar en desarrollo');
        } else {
            // Producción: siempre verificar SSL
            $options['verify'] = true;
        }

        return Http::withOptions($options)
            ->withHeaders($headers)
            ->retry(3, 100); // Reintentar 3 veces con 100ms de espera
    }

    /**
     * Cliente específico para Magento con SSL seguro
     */
    public static function magento(bool $verifySSL = true)
    {
        $apiUrl = config('services.magento.api_url', env('MAGENTO_API_URL'));
        $apiToken = config('services.magento.api_token', env('MAGENTO_API_TOKEN'));
        
        if (!$apiToken) {
            throw new \Exception('Magento API Token no configurado');
        }

        Log::info('🔗 Conectando con Magento', [
            'url' => $apiUrl,
            'ssl_verify' => $verifySSL,
            'environment' => app()->environment()
        ]);
        
        return self::makeClient([
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], 30, $verifySSL);
    }

    /**
     * Cliente específico para ICG con SSL seguro
     */
    public static function icg(bool $verifySSL = true)
    {
        $apiUrl = config('services.icg.api_url', env('ICG_API_URL'));
        $apiKey = config('services.icg.api_key', env('ICG_API_KEY'));
        
        if (!$apiKey) {
            throw new \Exception('ICG API Key no configurado');
        }

        Log::info('🔗 Conectando con ICG', [
            'url' => $apiUrl,
            'ssl_verify' => $verifySSL,
            'environment' => app()->environment()
        ]);
        
        return self::makeClient([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], 30, $verifySSL);
    }

    /**
     * Verificar salud de una API
     */
    public static function healthCheck(string $url, bool $verifySSL = true): array
    {
        try {
            $startTime = microtime(true);
            
            $response = self::makeClient([], 10, $verifySSL)->get($url);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_time_ms' => $responseTime,
                'ssl_verified' => $verifySSL,
                'tested_at' => now()->toDateTimeString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'ssl_verified' => $verifySSL,
                'tested_at' => now()->toDateTimeString()
            ];
        }
    }
}