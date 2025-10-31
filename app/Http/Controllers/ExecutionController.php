<?php

namespace App\Http\Controllers;

use App\Models\Execution;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExecutionController extends Controller
{
    /**
     * Mostrar lista de ejecuciones
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'in_progress');
        $search = $request->get('search');
        $period = $request->get('period', 'today');

        $dateRange = $this->getDateRange($period);

        $query = Execution::with(['workflow', 'user'])
            ->whereBetween('created_at', $dateRange);

        if ($tab === 'in_progress') {
            $query->whereIn('status', ['pending', 'running']);
        } else {
            $query->whereNotIn('status', ['pending', 'running']);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_id', 'like', "%{$search}%")
                  ->orWhereHas('workflow', function ($wq) use ($search) {
                      $wq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $executions = $query->orderBy('created_at', 'desc')
                           ->paginate(15);

        $workflows = Workflow::active()->get();

        return view('executions.index', compact('executions', 'tab', 'workflows', 'period'));
    }

    /**
     * Ejecutar workflow
     */
    public function execute(Request $request, $id)
    {
        $execution = Execution::findOrFail($id);

        if ($execution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Execution is not in pending status'
            ], 400);
        }

        $workflowType = $execution->workflow_type;
        
        $workflowClass = match($workflowType) {
            'product_creation' => \App\Jobs\ProductCreationJob::class,
            'price_update' => \App\Jobs\PriceUpdateJob::class,
            'stock_update' => \App\Jobs\StockUpdateJob::class,
            'product_update' => \App\Jobs\ProductUpdateJob::class,
            default => null,
        };

        if (!$workflowClass) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid workflow type'
            ], 400);
        }

        // Si es modo TOTAL, ejecutar en background
        if ($execution->action === 'total') {
            dispatch(new $workflowClass($execution))
                ->delay(now()->addSeconds(2));
            
            return response()->json([
                'success' => true,
                'message' => 'Execution started in background',
                'execution_id' => $execution->id,
                'mode' => 'background'
            ]);
        }

        // Si es modo PARTIAL, ejecutar inmediatamente
        try {
            dispatch_sync(new $workflowClass($execution));
            
            return response()->json([
                'success' => true,
                'message' => 'Execution completed',
                'execution_id' => $execution->id,
                'mode' => 'immediate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execution failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de una ejecución
     */
    public function show($id)
    {
        $execution = Execution::with(['workflow', 'user', 'logs'])
            ->findOrFail($id);

        return view('executions.show', compact('execution'));
    }

    /**
     * Obtener estado de la ejecución (AJAX para polling)
     */
    public function status($id)
    {
        $execution = Execution::with(['workflow', 'logs' => function ($query) {
            $query->orderBy('logged_at', 'desc')->limit(50);
        }])->findOrFail($id);

        return response()->json([
            'id' => $execution->id,
            'job_id' => $execution->job_id,
            'workflow_name' => $execution->workflow->name,
            'status' => $execution->status,
            'action' => $execution->action,
            'started_at' => $execution->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $execution->completed_at?->format('Y-m-d H:i:s'),
            'duration' => $execution->formatted_duration,
            'progress' => [
                'total_items' => $execution->total_items,
                'success_count' => $execution->success_count,
                'failed_count' => $execution->failed_count,
                'skipped_count' => $execution->skipped_count,
                'percentage' => $execution->total_items > 0 
                    ? round(($execution->success_count / $execution->total_items) * 100, 2)
                    : 0,
            ],
            'result_message' => $execution->result_message,
            'error_details' => $execution->error_details,
            'csv_filename' => $execution->csv_filename,
            'ftp_uploaded' => $execution->ftp_uploaded,
            'logs' => $execution->logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'level' => $log->level,
                    'message' => $log->formatted_message,
                    'time' => $log->formatted_time,
                    'badge_color' => $log->badge_color,
                    'icon' => $log->icon,
                    'progress_percentage' => $log->progress_percentage,
                ];
            }),
        ]);
    }

    /**
     * Obtener logs de la ejecución (AJAX)
     */
    public function logs($id, Request $request)
    {
        $execution = Execution::findOrFail($id);
        
        $level = $request->get('level');
        $limit = $request->get('limit', 100);

        $query = $execution->logs()->orderBy('logged_at', 'desc');

        if ($level) {
            $query->where('level', strtoupper($level));
        }

        $logs = $query->limit($limit)->get();

        return response()->json([
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'level' => $log->level,
                    'message' => $log->formatted_message,
                    'time' => $log->formatted_time,
                    'badge_color' => $log->badge_color,
                    'icon' => $log->icon,
                    'sku' => $log->sku,
                    'current_page' => $log->current_page,
                    'total_pages' => $log->total_pages,
                    'progress_percentage' => $log->progress_percentage,
                ];
            }),
            'total_count' => $execution->logs()->count(),
            'error_count' => $execution->logs()->errors()->count(),
            'warning_count' => $execution->logs()->warnings()->count(),
        ]);
    }

    /**
     * Cancelar una ejecución
     */
    public function cancel($id)
    {
        $execution = Execution::findOrFail($id);

        if (!in_array($execution->status, ['pending', 'running'])) {
            return redirect()
                ->back()
                ->with('error', 'Esta ejecución no puede ser cancelada');
        }

        $execution->markAsFailed('Cancelado por el usuario');

        return redirect()
            ->back()
            ->with('success', 'Ejecución cancelada exitosamente');
    }

    /**
     * Reintentar una ejecución fallida
     */
    public function retry($id)
    {
        $originalExecution = Execution::findOrFail($id);

        $newExecution = Execution::create([
            'job_id' => 'job_' . \Illuminate\Support\Str::random(10),
            'workflow_id' => $originalExecution->workflow_id,
            'user_id' => auth()->id(),
            'action' => $originalExecution->action,
            'skus' => $originalExecution->skus,
            'date_filter' => $originalExecution->date_filter,
            'start_date' => $originalExecution->start_date,
            'end_date' => $originalExecution->end_date,
            'status' => 'pending',
            'trigger_type' => 'manual',
            'configuration_snapshot' => $originalExecution->configuration_snapshot,
        ]);

        return redirect()
            ->route('executions.show', $newExecution->id)
            ->with('success', 'Ejecución reintentada. Job ID: ' . $newExecution->job_id);
    }

    /**
     * Descargar CSV generado
     */
    public function downloadCsv($id)
    {
        $execution = Execution::findOrFail($id);

        if (!$execution->csv_path || !file_exists($execution->csv_path)) {
            return redirect()
                ->back()
                ->with('error', 'Archivo CSV no encontrado');
        }

        return response()->download(
            $execution->csv_path,
            $execution->csv_filename
        );
    }

    /**
     * Eliminar ejecución
     */
    public function destroy($id)
    {
        $execution = Execution::findOrFail($id);

        if ($execution->status === 'running') {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar una ejecución en progreso');
        }

        if ($execution->csv_path && file_exists($execution->csv_path)) {
            unlink($execution->csv_path);
        }

        $execution->delete();

        return redirect()
            ->route('executions.index')
            ->with('success', 'Ejecución eliminada exitosamente');
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
            
            default:
                return [Carbon::today(), Carbon::tomorrow()];
        }
    }
}