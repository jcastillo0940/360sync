<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Execution;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal
     */
    public function index(Request $request)
    {
        // Filtro de periodo
        $period = $request->get('period', 'today');
        
        $dateRange = $this->getDateRange($period);
        
        // KPIs principales
        $stats = [
            'successful' => [
                'executions' => Execution::successful()
                    ->whereBetween('created_at', $dateRange)
                    ->count(),
                'entities' => Execution::successful()
                    ->whereBetween('created_at', $dateRange)
                    ->sum('success_count'),
            ],
            'with_errors' => [
                'executions' => Execution::whereBetween('created_at', $dateRange)
                    ->where('failed_count', '>', 0)
                    ->count(),
                'entities' => Execution::whereBetween('created_at', $dateRange)
                    ->sum('failed_count'),
            ],
            'failed' => [
                'executions' => Execution::failed()
                    ->whereBetween('created_at', $dateRange)
                    ->count(),
                'entities' => 0,
            ],
        ];

        // Últimas ejecuciones
        $recentExecutions = Execution::with(['workflow', 'user'])
            ->whereBetween('created_at', $dateRange)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Workflows activos
        $workflows = Workflow::active()
            ->withCount(['executions' => function ($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            }])
            ->get();

        return view('dashboard', compact('stats', 'recentExecutions', 'workflows', 'period'));
    }

    /**
     * Obtener rango de fechas según el periodo
     */
    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::tomorrow()];
            
            case 'yesterday':
                return [Carbon::yesterday(), Carbon::today()];
            
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            
            case 'year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            
            default:
                return [Carbon::today(), Carbon::tomorrow()];
        }
    }

    /**
     * Obtener estadísticas en tiempo real (AJAX)
     */
    public function stats(Request $request)
    {
        $period = $request->get('period', 'today');
        $dateRange = $this->getDateRange($period);

        $stats = [
            'successful' => [
                'executions' => Execution::successful()
                    ->whereBetween('created_at', $dateRange)
                    ->count(),
                'entities' => Execution::successful()
                    ->whereBetween('created_at', $dateRange)
                    ->sum('success_count'),
            ],
            'with_errors' => [
                'executions' => Execution::whereBetween('created_at', $dateRange)
                    ->where('failed_count', '>', 0)
                    ->count(),
                'entities' => Execution::whereBetween('created_at', $dateRange)
                    ->sum('failed_count'),
            ],
            'failed' => [
                'executions' => Execution::failed()
                    ->whereBetween('created_at', $dateRange)
                    ->count(),
                'entities' => 0,
            ],
            'in_progress' => Execution::running()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Obtener ejecuciones recientes (AJAX)
     */
    public function recentExecutions(Request $request)
    {
        $period = $request->get('period', 'today');
        $dateRange = $this->getDateRange($period);

        $executions = Execution::with(['workflow', 'user'])
            ->whereBetween('created_at', $dateRange)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($execution) {
                return [
                    'id' => $execution->id,
                    'job_id' => $execution->job_id,
                    'workflow_name' => $execution->workflow->name,
                    'status' => $execution->status,
                    'started_at' => $execution->started_at?->format('Y-m-d H:i:s'),
                    'duration' => $execution->formatted_duration,
                    'success_count' => $execution->success_count,
                    'failed_count' => $execution->failed_count,
                ];
            });

        return response()->json($executions);
    }
}