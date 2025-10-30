<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Execution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $workflows = Workflow::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->withCount('executions')
            ->orderBy('name')
            ->paginate(10);

        return view('workflows.index', compact('workflows'));
    }

    public function create()
    {
        return view('workflows.create');
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|string',
        'source' => 'required|string',
        'destination' => 'required|string',
        'config' => 'nullable|json',
        'priority' => 'nullable|string|in:low,medium,high',
        'is_active' => 'boolean',
    ]);

    $validated['is_active'] = $request->has('is_active');
    $validated['slug'] = Str::slug($validated['name'] . '-' . Str::random(6));
    $validated['icon'] = 'sync';
    $validated['color'] = 'blue';
    $validated['supports_partial'] = true;
    $validated['supports_date_filter'] = true;
    $validated['configuration'] = $validated['config'] ?? '{}';
    $validated['class_name'] = 'App\\Jobs\\Workflows\\' . Str::studly($validated['type']) . 'Job';
    
    unset($validated['config']);

    $workflow = Workflow::create($validated);

    return redirect()
        ->route('workflows.index')
        ->with('success', 'Workflow created successfully!');
}

    public function show($id)
    {
        $workflow = Workflow::with(['executions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(20);
        }])->findOrFail($id);

        $stats = [
            'total_executions' => $workflow->executions()->count(),
            'successful' => $workflow->executions()->successful()->count(),
            'failed' => $workflow->executions()->failed()->count(),
            'in_progress' => $workflow->executions()->running()->count(),
            'success_rate' => $workflow->success_rate,
            'avg_duration' => $workflow->executions()
                ->whereNotNull('duration_seconds')
                ->avg('duration_seconds'),
        ];

        return view('workflows.show', compact('workflow', 'stats'));
    }

    public function execute($id)
    {
        $workflow = Workflow::findOrFail($id);

        return view('workflows.execute', compact('workflow'));
    }

   public function processExecution(Request $request, $id)
{
    $workflow = Workflow::findOrFail($id);

    $validated = $request->validate([
        'action' => 'required|in:total,partial',
        'skus' => 'required_if:action,partial|nullable|string',
        'date_filter' => 'nullable|in:none,today,yesterday,last_week,custom',
        'start_date' => 'required_if:date_filter,custom|nullable|date',
        'end_date' => 'required_if:date_filter,custom|nullable|date|after_or_equal:start_date',
        'execution_type' => 'required|in:now,scheduled',
        'scheduled_date' => 'required_if:execution_type,scheduled|nullable|date',
        'scheduled_time' => 'required_if:execution_type,scheduled|nullable',
    ]);

    if ($validated['execution_type'] === 'scheduled') {
        return $this->scheduleExecution($workflow, $validated);
    }

    // Preparar configuración con filtros de fecha
    $configSnapshot = $workflow->configuration ?? [];
    
    if (!empty($validated['date_filter']) && $validated['date_filter'] !== 'none') {
        $configSnapshot['date_filter'] = $validated['date_filter'];
        
        if ($validated['date_filter'] === 'custom') {
            $configSnapshot['fecha_desde'] = $validated['start_date'];
            $configSnapshot['fecha_hasta'] = $validated['end_date'];
        }
    }

    $execution = Execution::create([
        'job_id' => 'job_' . Str::random(10),
        'workflow_id' => $workflow->id,
        'user_id' => auth()->id(),
        'action' => $validated['action'],
        'skus' => $validated['action'] === 'partial' ? $validated['skus'] : null,
        'date_filter' => $validated['date_filter'] ?? 'none',
        'date_from' => $validated['start_date'] ?? null,
        'date_to' => $validated['end_date'] ?? null,
        'status' => 'pending',
        'trigger_type' => 'manual',
        'configuration_snapshot' => $configSnapshot,
    ]);

    $workflow->incrementExecutionCount();

    \App\Jobs\ProcessWorkflowJob::dispatch($execution);

    return redirect()
        ->route('executions.show', $execution->id)
        ->with('success', 'Workflow ejecutándose. Job ID: ' . $execution->job_id);
}
    private function scheduleExecution($workflow, $validated)
    {
        return redirect()
            ->route('workflows.index')
            ->with('success', 'Workflow programado exitosamente');
    }

    public function toggle($id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->update(['is_active' => !$workflow->is_active]);

        $status = $workflow->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->back()
            ->with('success', "Workflow {$status} exitosamente");
    }

    public function getInfo($id)
    {
        $workflow = Workflow::with(['executions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }])->findOrFail($id);

        return response()->json([
            'id' => $workflow->id,
            'name' => $workflow->name,
            'slug' => $workflow->slug,
            'description' => $workflow->description,
            'icon' => $workflow->icon,
            'color' => $workflow->color,
            'is_active' => $workflow->is_active,
            'supports_partial' => $workflow->supports_partial,
            'supports_date_filter' => $workflow->supports_date_filter,
            'execution_count' => $workflow->execution_count,
            'success_rate' => $workflow->success_rate,
            'last_executed_at' => $workflow->last_executed_at?->format('Y-m-d H:i:s'),
            'recent_executions' => $workflow->executions->map(function ($execution) {
                return [
                    'id' => $execution->id,
                    'job_id' => $execution->job_id,
                    'status' => $execution->status,
                    'created_at' => $execution->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    public function stats($id, Request $request)
    {
        $workflow = Workflow::findOrFail($id);
        
        $period = $request->get('period', 'week');
        $days = match($period) {
            'today' => 1,
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 7,
        };

        $executions = $workflow->executions()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $stats = [
            'total' => $executions->count(),
            'successful' => $executions->whereIn('status', ['completed_success', 'completed_success_no_ftp'])->count(),
            'failed' => $executions->where('status', 'failed')->count(),
            'avg_duration' => round($executions->avg('duration_seconds'), 2),
            'total_items_processed' => $executions->sum('success_count'),
            'by_day' => $executions->groupBy(function ($execution) {
                return $execution->created_at->format('Y-m-d');
            })->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'successful' => $group->whereIn('status', ['completed_success', 'completed_success_no_ftp'])->count(),
                    'failed' => $group->where('status', 'failed')->count(),
                ];
            }),
        ];

        return response()->json($stats);
    }
}