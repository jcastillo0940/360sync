<?php

namespace App\Observers;

use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;

class ApiLogObserver
{
    /**
     * Handle the ApiLog "creating" event.
     */
    public function creating(ApiLog $apiLog): void
    {
        // Establecer timestamp si no existe
        if (empty($apiLog->timestamp)) {
            $apiLog->timestamp = now();
        }

        // Limpiar datos sensibles del request/response
        if (!empty($apiLog->request_data)) {
            $apiLog->request_data = $this->sanitizeData($apiLog->request_data);
        }

        if (!empty($apiLog->response_data)) {
            $apiLog->response_data = $this->sanitizeData($apiLog->response_data);
        }
    }

    /**
     * Handle the ApiLog "created" event.
     */
    public function created(ApiLog $apiLog): void
    {
        // Registrar solo errores en el log principal
        if ($apiLog->status_code >= 400) {
            Log::error('Error en llamada API', [
                'id' => $apiLog->id,
                'api_name' => $apiLog->api_name,
                'endpoint' => $apiLog->endpoint,
                'status_code' => $apiLog->status_code,
                'error_message' => $apiLog->error_message,
            ]);
        }
    }

    /**
     * Sanitizar datos sensibles
     */
    protected function sanitizeData($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $sensitiveKeys = ['password', 'token', 'api_key', 'secret', 'authorization'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '***HIDDEN***';
            }
        });

        return $data;
    }

    /**
     * Handle the ApiLog "deleted" event.
     */
    public function deleted(ApiLog $apiLog): void
    {
        Log::info('Log de API eliminado', [
            'id' => $apiLog->id,
            'api_name' => $apiLog->api_name,
        ]);
    }
}