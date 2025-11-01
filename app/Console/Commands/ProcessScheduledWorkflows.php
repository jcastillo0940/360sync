<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleRule;
use App\Models\Execution;
use App\Jobs\ProcessWorkflowJob;
use Carbon\Carbon;

class ProcessScheduledWorkflows extends Command
{
    protected $signature = 'workflows:process-scheduled';
    protected $description = 'Process scheduled workflow executions';

    public function handle()
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        $this->info("Checking scheduled workflows for {$currentTime}...");

        // Obtener reglas que deben ejecutarse ahora
        $rules = ScheduleRule::with('workflow')
            ->enabled()
            ->where('execution_time', $currentTime)
            ->whereNull('last_run_at')
            ->orWhere(function($query) use ($now, $currentTime) {
                $query->enabled()
                    ->where('execution_time', $currentTime)
                    ->where(function($q) use ($now) {
                        $q->whereDate('last_run_at', '<', $now->toDateString());
                    });
            })
            ->get();

        if ($rules->isEmpty()) {
            $this->info('No workflows scheduled for this time.');
            return 0;
        }

        foreach ($rules as $rule) {
            // Verificar frecuencia
            if (!$this->shouldRunToday($rule, $now)) {
                continue;
            }

            $this->info("Executing: {$rule->name} (Workflow: {$rule->workflow->name})");

            // Crear ejecución
            $execution = Execution::create([
                'job_id' => 'job_' . \Illuminate\Support\Str::random(10),
                'workflow_id' => $rule->workflow_id,
                'user_id' => $rule->user_id,
                'action' => $rule->action,
                'skus' => $rule->skus,
                'date_filter' => 'none',
                'status' => 'pending',
                'trigger_type' => 'scheduled',
                'schedule_rule_id' => $rule->id,
                'configuration_snapshot' => $rule->workflow->configuration,
            ]);

            // Disparar job
            ProcessWorkflowJob::dispatch($execution);

            // Marcar como ejecutada
            $rule->markAsExecuted();

            $this->info("✓ Job dispatched: {$execution->job_id}");
        }

        $this->info("Processed {$rules->count()} scheduled workflow(s).");
        return 0;
    }

    private function shouldRunToday(ScheduleRule $rule, Carbon $date)
    {
        switch ($rule->frequency) {
            case 'daily':
                return true;

            case 'weekly':
                return $date->dayOfWeekIso == $rule->day_of_week;

            case 'monthly':
                return $date->day == $rule->day_of_month;

            case 'once':
                return $rule->last_run_at === null;

            default:
                return false;
        }
    }
}
