<?php

namespace App\Http\Controllers;

use App\Models\MagentoSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MagentoSkuController extends Controller
{
    /**
     * Mostrar dashboard de SKUs
     */
    /**
 * Mostrar dashboard de SKUs
 */
public function index(Request $request)
{
    // Parámetros de búsqueda y paginación
    $search = $request->input('search');
    $perPage = $request->input('per_page', 25); // Default 25
    
    // Query base
    $query = MagentoSku::query();
    
    // Aplicar búsqueda si existe
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
        });
    }
    
    // Obtener SKUs paginados
    $skus = $query->latest('updated_at')
        ->paginate($perPage)
        ->withQueryString(); // Mantener parámetros en paginación
    
    // Estadísticas generales
    $stats = [
        'total_skus' => MagentoSku::count(),
        'last_sync' => MagentoSku::max('updated_at'),
        'search_results' => $search ? $skus->total() : null,
    ];

    return view('magento-skus.index', compact('stats', 'skus', 'search', 'perPage'));
}

    /**
     * Sincronizar SKUs manualmente (AJAX)
     */
    public function sync(Request $request)
    {
        try {
            // Ejecutar comando en background via job
            \Artisan::queue('magento:sync-skus');

            return response()->json([
                'success' => true,
                'message' => 'Sincronización iniciada. Esto puede tardar varios minutos.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sincronización: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas actualizadas (AJAX)
     */
    public function stats()
    {
        $stats = [
            'total_skus' => MagentoSku::count(),
            'last_sync' => MagentoSku::max('updated_at'),
            'last_sync_human' => MagentoSku::max('updated_at') 
                ? \Carbon\Carbon::parse(MagentoSku::max('updated_at'))->diffForHumans()
                : 'Nunca',
        ];

        return response()->json($stats);
    }
}
