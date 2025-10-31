<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRule;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleRuleController extends Controller
{
    /**
     * Mostrar planning/timeline de reglas programadas
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // Obtener todas las reglas activas
        $scheduleRules = ScheduleRule::with('workflow')
            ->enabled()
            ->orderBy('execution_time')
            ->get();

        // Obtener todas las ejecuciones programadas para la fecha seleccionada
        $scheduledExecutions = $this->getScheduledExecutionsForDate($selectedDate, $scheduleRules);

        $workflows = Workflow::active()->get();

        return view('schedule.index', compact('scheduleRules', 'scheduledExecutions', 'selectedDate', 'workflows'));
    }

    /**
     * Mostrar lista de reglas de programación
     */
    public function list(Request $request)
    {
        $search = $request->get('search');

        $rules = ScheduleRule::with(['workflow', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('workflow', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            })
            ->orderBy('is_enabled', 'desc')
            ->orderBy('execution_time')
            ->paginate(15);

        return view('schedule.list', compact('rules'));
    }

    /**
     * Mostrar formulario para crear regla
     */
    public function create()
    {
        $workflows = Workflow::active()->get();
        
        return view('schedule.create', compact('workflows'));
    }

    /**
     * Guardar nueva regla
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workflow_id' => 'required|exists:workflows,id',
            'action' => 'required|in:total,partial',
            'skus' => 'required_if:action,partial|nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'execution_time' => 'required|date_format:H:i',
            'day_of_week' => 'required_if:frequency,weekly|nullable|integer|between:1,7',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|between:1,31',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_enabled'] = true;

        $rule = ScheduleRule::create($validated);

        // Calcular próxima ejecución
        $rule->calculateNextRun();

        return redirect()
            ->route('schedule.list')
            ->with('success', 'Regla de programación creada exitosamente');
    }

    /**
     * Mostrar formulario para editar regla
     */
    public function edit($id)
    {
        $rule = ScheduleRule::with('workflow')->findOrFail($id);
        $workflows = Workflow::active()->get();

        return view('schedule.edit', compact('rule', 'workflows'));
    }

    /**
     * Actualizar regla
     */
    public function update(Request $request, $id)
    {
        $rule = ScheduleRule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workflow_id' => 'required|exists:workflows,id',
            'action' => 'required|in:total,partial',
            'skus' => 'required_if:action,partial|nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'execution_time' => 'required|date_format:H:i',
            'day_of_week' => 'required_if:frequency,weekly|nullable|integer|between:1,7',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|between:1,31',
            'description' => 'nullable|string',
        ]);

        $rule->update($validated);

        // Recalcular próxima ejecución
        $rule->calculateNextRun();

        return redirect()
            ->route('schedule.list')
            ->with('success', 'Regla actualizada exitosamente');
    }

    /**
     * Activar/Desactivar regla
     */
    public function toggle($id)
    {
        $rule = ScheduleRule::findOrFail($id);
        $rule->toggle();

        $status = $rule->is_enabled ? 'activada' : 'desactivada';

        return redirect()
            ->back()
            ->with('success', "Regla {$status} exitosamente");
    }

    /**
     * Eliminar regla
     */
    public function destroy($id)
    {
        $rule = ScheduleRule::findOrFail($id);
        $rule->delete();

        return redirect()
            ->route('schedule.list')
            ->with('success', 'Regla eliminada exitosamente');
    }

    /**
     * Ejecutar regla manualmente ahora
     */
    public function runNow($id)
    {
        $rule = ScheduleRule::with('workflow')->findOrFail($id);

        // Crear ejecución
        $execution = \App\Models\Execution::create([
            'job_id' => 'job_' . \Illuminate\Support\Str::random(10),
            'workflow_id' => $rule->workflow_id,
            'user_id' => auth()->id(),
            'action' => $rule->action,
            'skus' => $rule->skus,
            'date_filter' => 'none',
            'status' => 'pending',
            'trigger_type' => 'manual',
            'schedule_rule_id' => $rule->id,
            'configuration_snapshot' => $rule->workflow->configuration,
        ]);

        // Marcar como ejecutada
        $rule->markAsExecuted();

        // Disparar job
        // dispatch(new ProcessWorkflowJob($execution));

        return redirect()
            ->route('executions.show', $execution->id)
            ->with('success', 'Regla ejecutada manualmente. Job ID: ' . $execution->job_id);
    }

    /**
     * Obtener timeline de ejecuciones (AJAX)
     */
    public function timeline(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $scheduleRules = ScheduleRule::with('workflow')
            ->enabled()
            ->orderBy('execution_time')
            ->get();

        $timeline = $this->getScheduledExecutionsForDate($selectedDate, $scheduleRules);

        return response()->json($timeline);
    }

    /**
     * Obtener ejecuciones programadas para una fecha específica
     */
    private function getScheduledExecutionsForDate($date, $scheduleRules)
    {
        $timeline = [];

        foreach ($scheduleRules as $rule) {
            $shouldRunOnDate = false;

            switch ($rule->frequency) {
                case 'daily':
                    $shouldRunOnDate = true;
                    break;

                case 'weekly':
                    $shouldRunOnDate = $date->dayOfWeekIso == $rule->day_of_week;
                    break;

                case 'monthly':
                    $shouldRunOnDate = $date->day == $rule->day_of_month;
                    break;
            }

            if ($shouldRunOnDate) {
                $executionTime = Carbon::parse($date->format('Y-m-d') . ' ' . $rule->execution_time);

                $timeline[] = [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'workflow_name' => $rule->workflow->name,
                    'workflow_color' => $rule->workflow->color,
                    'execution_time' => $executionTime->format('H:i'),
                    'execution_timestamp' => $executionTime->timestamp,
                    'status' => $executionTime->isPast() ? 'executed' : 'programmed',
                    'frequency' => $rule->frequency_description,
                ];
            }
        }

        // Ordenar por hora de ejecución
        usort($timeline, function ($a, $b) {
            return $a['execution_timestamp'] <=> $b['execution_timestamp'];
        });

        return $timeline;
    }

    /**
     * Obtener información de la regla (AJAX)
     */
    public function getInfo($id)
    {
        $rule = ScheduleRule::with(['workflow', 'executions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }])->findOrFail($id);

        return response()->json([
            'id' => $rule->id,
            'name' => $rule->name,
            'workflow_name' => $rule->workflow->name,
            'action' => $rule->action,
            'frequency' => $rule->frequency,
            'frequency_description' => $rule->frequency_description,
            'execution_time' => $rule->execution_time,
            'is_enabled' => $rule->is_enabled,
            'last_run_at' => $rule->last_run_at?->format('Y-m-d H:i:s'),
            'next_run_at' => $rule->next_run_at?->format('Y-m-d H:i:s'),
            'next_run_formatted' => $rule->next_run_formatted,
            'run_count' => $rule->run_count,
            'description' => $rule->description,
            'recent_executions' => $rule->executions->map(function ($execution) {
                return [
                    'id' => $execution->id,
                    'job_id' => $execution->job_id,
                    'status' => $execution->status,
                    'created_at' => $execution->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }
}