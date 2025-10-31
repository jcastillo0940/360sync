<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Execution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_id',
        'workflow_id',
        'user_id',
        'action',
        'skus',
        'date_filter',
        'start_date',
        'end_date',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'total_items',
        'success_count',
        'failed_count',
        'skipped_count',
        'csv_filename',
        'csv_path',
        'ftp_uploaded',
        'result_message',
        'error_details',
        'configuration_snapshot',
        'trigger_type',
        'schedule_rule_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'ftp_uploaded' => 'boolean',
        'error_details' => 'array',
        'configuration_snapshot' => 'array',
    ];

    /**
     * Relación: Una ejecución pertenece a un workflow
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relación: Una ejecución pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una ejecución pertenece a una regla de programación (opcional)
     */
    public function scheduleRule()
    {
        return $this->belongsTo(ScheduleRule::class);
    }

    /**
     * Relación: Una ejecución tiene muchos logs
     */
    public function logs()
    {
        return $this->hasMany(ExecutionLog::class)->orderBy('logged_at', 'asc');
    }

    /**
     * Scope: Ejecuciones pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Ejecuciones en progreso
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope: Ejecuciones completadas
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            'completed_success',
            'completed_success_no_ftp',
            'completed_empty'
        ]);
    }

    /**
     * Scope: Ejecuciones exitosas
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [
            'completed_success',
            'completed_success_no_ftp'
        ]);
    }

    /**
     * Scope: Ejecuciones fallidas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Filtrar por rango de fechas
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Ejecuciones de hoy
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Marcar como iniciada
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Marcar como completada
     */
    public function markAsCompleted($status = 'completed_success', $message = null)
    {
        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => $status,
            'completed_at' => now(),
            'duration_seconds' => $duration,
            'result_message' => $message,
        ]);
    }

    /**
     * Marcar como fallida
     */
    public function markAsFailed($errorMessage, $errorDetails = null)
    {
        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => $duration,
            'result_message' => $errorMessage,
            'error_details' => $errorDetails,
        ]);
    }

    /**
     * Agregar log a la ejecución
     */
    public function addLog($level, $message, $context = null)
    {
        return $this->logs()->create([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'logged_at' => now(),
        ]);
    }

    /**
     * Obtener duración formateada
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    /**
     * Obtener tasa de éxito
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_items === 0) {
            return 0;
        }

        return round(($this->success_count / $this->total_items) * 100, 2);
    }

    /**
     * Verificar si está en progreso
     */
    public function isRunning()
    {
        return $this->status === 'running';
    }

    /**
     * Verificar si fue exitosa
     */
    public function isSuccessful()
    {
        return in_array($this->status, ['completed_success', 'completed_success_no_ftp']);
    }

    /**
     * Verificar si falló
     */
    public function hasFailed()
    {
        return $this->status === 'failed';
    }
}