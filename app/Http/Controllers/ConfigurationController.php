<?php

namespace App\Http\Controllers;

use App\Models\SyncConfiguration;
use App\Services\API\IcgApiService;
use App\Services\API\MagentoApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConfigurationController extends Controller
{
    /**
     * Mostrar página de configuración
     */
    public function index()
    {
        // Obtener configuraciones agrupadas por categoría
        $apiConfigs = SyncConfiguration::where('category', 'api')
            ->visible()
            ->ordered()
            ->get();

        $ftpConfigs = SyncConfiguration::where('category', 'ftp')
            ->visible()
            ->ordered()
            ->get();

        $processConfigs = SyncConfiguration::where('category', 'process')
            ->visible()
            ->ordered()
            ->get();

        return view('configuration.index', compact('apiConfigs', 'ftpConfigs', 'processConfigs'));
    }

    /**
     * Actualizar configuraciones
     */
    public function update(Request $request)
    {
        Log::info("=== GUARDANDO CONFIGURACIONES ===");
        
        try {
            $configs = $request->input('config', []);
            $updated = 0;

            Log::info("Total campos recibidos: " . count($configs));

            foreach ($configs as $key => $value) {
                $config = SyncConfiguration::where('key', $key)->first();

                if (!$config) {
                    Log::warning("Configuración no encontrada: {$key}");
                    continue;
                }

                // Validar según el tipo
                $validator = Validator::make(
                    [$key => $value],
                    [$key => $config->validation_rules]
                );

                if ($validator->fails()) {
                    Log::error("Validación fallida para {$key}: " . json_encode($validator->errors()));
                    continue;
                }

                // Guardar valor (se encripta automáticamente si es necesario)
                $config->setValue($value);
                
                if (auth()->check()) {
                    $config->update(['updated_by' => auth()->id()]);
                }

                Log::info("✅ Guardado: {$key}");
                $updated++;
            }

            Log::info("Configuraciones actualizadas: {$updated}");

            if ($updated > 0) {
                return redirect()
                    ->back()
                    ->with('success', "✅ {$updated} configuraciones actualizadas exitosamente");
            }

            return redirect()
                ->back()
                ->with('error', '❌ No se pudo actualizar ninguna configuración');

        } catch (\Exception $e) {
            Log::error("Error al actualizar configuraciones: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Probar conexión con ICG API
     */
    public function testIcgApi(Request $request)
    {
        Log::info("========================================");
        Log::info("=== INICIO TEST ICG API ===");
        Log::info("========================================");
        
        try {
            // Obtener configuración actual
            $config = SyncConfiguration::getIcgApiConfig();
            
            Log::info("ICG URL: " . ($config['url'] ?: 'NO CONFIGURADA'));
            Log::info("ICG User: " . ($config['user'] ?: 'NO CONFIGURADO'));
            Log::info("ICG Password: " . ($config['password'] ? '***PRESENTE***' : 'NO CONFIGURADA'));

            // Validar que existan las configuraciones mínimas
            if (empty($config['url'])) {
                Log::warning("❌ Test ICG ABORTADO: URL no configurada");
                return response()->json([
                    'success' => false,
                    'message' => '❌ URL de ICG API no configurada. Por favor configúrala primero.',
                ], 400);
            }

            if (empty($config['user']) || empty($config['password'])) {
                Log::warning("❌ Test ICG ABORTADO: Credenciales incompletas");
                return response()->json([
                    'success' => false,
                    'message' => '❌ Usuario o contraseña de ICG no configurados. Por favor configúralos primero.',
                ], 400);
            }

            // Usar el servicio de ICG para probar la conexión
            $icgService = new IcgApiService();
            $result = $icgService->testConnection();

            Log::info("Resultado del test: " . json_encode($result));

            if ($result['success']) {
                // Marcar configuraciones como probadas exitosamente
                SyncConfiguration::where('key', 'icg_api_url')->first()->markAsTested(true);
                SyncConfiguration::where('key', 'icg_api_user')->first()->markAsTested(true);
                SyncConfiguration::where('key', 'icg_api_password')->first()->markAsTested(true);

                Log::info("✅ Test ICG EXITOSO");
                
                return response()->json([
                    'success' => true,
                    'message' => '✅ Conexión exitosa con ICG API',
                    'details' => $result['message'],
                    'tested_at' => now()->toDateTimeString()
                ]);
            }

            Log::warning("❌ Test ICG FALLIDO: " . $result['message']);
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error de conexión con ICG API',
                'details' => $result['message'],
            ], 500);

        } catch (\Exception $e) {
            Log::error("❌ EXCEPCIÓN en test ICG: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error al probar conexión: ' . $e->getMessage(),
            ], 500);
        } finally {
            Log::info("=== FIN TEST ICG API ===\n");
        }
    }

    /**
     * Probar conexión con Magento API
     */
    public function testMagentoApi(Request $request)
    {
        Log::info("========================================");
        Log::info("=== INICIO TEST MAGENTO API ===");
        Log::info("========================================");
        
        try {
            // Obtener configuración actual
            $config = SyncConfiguration::getMagentoApiConfig();
            
            Log::info("Magento URL: " . ($config['base_url'] ?: 'NO CONFIGURADA'));
            Log::info("Magento Token: " . ($config['api_token'] ? '***PRESENTE*** (longitud: ' . strlen($config['api_token']) . ')' : 'NO CONFIGURADO'));

            // Validar que existan las configuraciones mínimas
            if (empty($config['base_url'])) {
                Log::warning("❌ Test Magento ABORTADO: URL no configurada");
                return response()->json([
                    'success' => false,
                    'message' => '❌ URL de Magento no configurada. Por favor configúrala primero.',
                ], 400);
            }

            if (empty($config['api_token'])) {
                Log::warning("❌ Test Magento ABORTADO: Token no configurado");
                return response()->json([
                    'success' => false,
                    'message' => '❌ Token de Magento no configurado. Por favor configúralo primero.',
                ], 400);
            }

            // Usar el servicio de Magento para probar la conexión
            $magentoService = new MagentoApiService();
            $result = $magentoService->testConnection();

            Log::info("Resultado del test: " . json_encode($result));

            if ($result['success']) {
                // Marcar configuraciones como probadas exitosamente
                SyncConfiguration::where('key', 'magento_base_url')->first()->markAsTested(true);
                SyncConfiguration::where('key', 'magento_api_token')->first()->markAsTested(true);

                Log::info("✅ Test Magento EXITOSO");
                
                return response()->json([
                    'success' => true,
                    'message' => '✅ Conexión exitosa con Magento API',
                    'details' => $result['message'] ?? 'Conexión verificada',
                    'stores_count' => $result['stores_count'] ?? 0,
                    'tested_at' => now()->toDateTimeString()
                ]);
            }

            Log::warning("❌ Test Magento FALLIDO: " . ($result['message'] ?? 'Error desconocido'));
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error de conexión con Magento API',
                'details' => $result['message'] ?? 'Error desconocido',
            ], 500);

        } catch (\Exception $e) {
            Log::error("❌ EXCEPCIÓN en test Magento: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error al probar conexión: ' . $e->getMessage(),
            ], 500);
        } finally {
            Log::info("=== FIN TEST MAGENTO API ===\n");
        }
    }

    /**
     * Probar conexión FTP
     */
    public function testFtp(Request $request)
    {
        Log::info("========================================");
        Log::info("=== INICIO TEST FTP ===");
        Log::info("========================================");
        
        try {
            // Obtener configuración actual
            $config = SyncConfiguration::getFtpConfig();
            
            Log::info("FTP Server: " . ($config['server'] ?: 'NO CONFIGURADO'));
            Log::info("FTP Port: " . $config['port']);
            Log::info("FTP Username: " . ($config['username'] ?: 'NO CONFIGURADO'));
            Log::info("FTP Password: " . ($config['password'] ? '***PRESENTE***' : 'NO CONFIGURADA'));
            Log::info("FTP Enabled: " . ($config['enabled'] ? 'SÍ' : 'NO'));

            // Validar que FTP esté habilitado
            if (!$config['enabled']) {
                Log::info("ℹ️ Test FTP omitido: FTP está deshabilitado");
                return response()->json([
                    'success' => false,
                    'message' => 'ℹ️ FTP está deshabilitado en la configuración',
                ], 400);
            }

            // Validar configuración mínima
            if (empty($config['server']) || empty($config['username']) || empty($config['password'])) {
                Log::warning("❌ Test FTP ABORTADO: Configuración incompleta");
                return response()->json([
                    'success' => false,
                    'message' => '❌ Configuración FTP incompleta. Verifica servidor, usuario y contraseña.',
                ], 400);
            }

            Log::info("Intentando conectar a {$config['server']}:{$config['port']}...");
            
            // Intentar conexión FTP
            $connection = @ftp_connect($config['server'], $config['port'], 10);

            if (!$connection) {
                Log::error("❌ No se pudo establecer conexión TCP con el servidor FTP");
                return response()->json([
                    'success' => false,
                    'message' => '❌ No se pudo conectar al servidor FTP. Verifica host y puerto.',
                    'host' => $config['server'],
                    'port' => $config['port']
                ], 500);
            }

            Log::info("Conexión TCP establecida. Intentando login...");

            // Intentar login
            $login = @ftp_login($connection, $config['username'], $config['password']);

            if (!$login) {
                ftp_close($connection);
                Log::error("❌ Login FTP fallido - Credenciales incorrectas");
                return response()->json([
                    'success' => false,
                    'message' => '❌ Error de autenticación FTP. Verifica usuario y contraseña.',
                ], 401);
            }

            Log::info("Login exitoso. Obteniendo directorio actual...");
            $currentDir = ftp_pwd($connection);
            
            Log::info("Directorio actual: {$currentDir}");
            ftp_close($connection);

            // Marcar configuraciones como probadas exitosamente
            SyncConfiguration::where('key', 'ftp_server')->first()->markAsTested(true);
            SyncConfiguration::where('key', 'ftp_username')->first()->markAsTested(true);
            SyncConfiguration::where('key', 'ftp_password')->first()->markAsTested(true);

            Log::info("✅ Test FTP EXITOSO");
            
            return response()->json([
                'success' => true,
                'message' => '✅ Conexión FTP exitosa',
                'current_directory' => $currentDir,
                'host' => $config['server'],
                'port' => $config['port'],
                'tested_at' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error("❌ EXCEPCIÓN en test FTP: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error al probar conexión FTP: ' . $e->getMessage(),
            ], 500);
        } finally {
            Log::info("=== FIN TEST FTP ===\n");
        }
    }

    /**
     * Resetear configuraciones a valores por defecto
     */
    public function reset(Request $request)
    {
        try {
            Log::info("Reseteando configuraciones...");
            
            SyncConfiguration::truncate();
            
            // Volver a ejecutar el seeder
            \Artisan::call('db:seed', ['--class' => 'SyncConfigurationSeeder']);

            Log::info("✅ Configuraciones reseteadas exitosamente");

            return redirect()
                ->back()
                ->with('success', '✅ Configuraciones reseteadas a valores por defecto del .env');

        } catch (\Exception $e) {
            Log::error("Error al resetear configuraciones: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', '❌ Error al resetear: ' . $e->getMessage());
        }
    }

    /**
     * Exportar configuraciones (sin datos sensibles)
     */
    public function export()
    {
        $configs = SyncConfiguration::all()->map(function ($config) {
            return [
                'key' => $config->key,
                'value' => $config->type === 'password' ? '********' : $config->decrypted_value,
                'type' => $config->type,
                'category' => $config->category,
                'description' => $config->description,
            ];
        });

        $filename = 'configuration_export_' . date('Y-m-d_His') . '.json';

        return response()->json($configs, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Obtener valor de configuración específica
     */
    public function getValue($key)
    {
        $config = SyncConfiguration::where('key', $key)->first();

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'key' => $config->key,
            'value' => $config->decrypted_value,
            'type' => $config->type,
            'display_value' => $config->display_value,
        ]);
    }

    /**
     * Verificar estado de configuraciones requeridas
     */
    public function checkStatus()
    {
        $allComplete = SyncConfiguration::areRequiredConfigsComplete();
        $missing = SyncConfiguration::getMissingRequiredConfigs();

        return response()->json([
            'success' => $allComplete,
            'all_required_complete' => $allComplete,
            'missing_configs' => $missing->map(function ($config) {
                return [
                    'key' => $config->key,
                    'label' => $config->label,
                    'category' => $config->category,
                ];
            }),
        ]);
    }
}