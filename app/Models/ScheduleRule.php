<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ScheduleRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'workflow_id',
        'user_id',
        'action',
        'skus',
        'frequency',
        'execution_time',
        'day_of_week',
        'day_of_month',
        'is_enabled',
        'last_run_at',
        'next_run_at',
        'run_count',
        'configuration',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'configuration' => 'array',
    ];

    /**
     * Relación: Una regla pertenece a un workflow
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relación: Una regla pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una regla tiene muchas ejecuciones
     */
    public function executions()
    {
        return $this->hasMany(Execution::class, 'schedule_rule_id');
    }

    /**
     * Scope: Reglas activas
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: Reglas que deben ejecutarse ahora
     */
    public function scopeDueForExecution($query)
    {
        return $query->where('is_enabled', true)
                     ->where('next_run_at', '<=', now());
    }

    /**
     * Scope: Filtrar por frecuencia
     */
    public function scopeFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Calcular próxima ejecución
     */
    public function calculateNextRun()
    {
        $now = Carbon::now();
        $time = Carbon::parse($this->execution_time);

        switch ($this->frequency) {
            case 'daily':
                $next = $now->copy()->setTime($time->hour, $time->minute, 0);
                
                // Si ya pasó la hora hoy, programar para mañana
                if ($next->isPast()) {
                    $next->addDay();
                }
                break;

            case 'weekly':
                $next = $now->copy()->setTime($time->hour, $time->minute, 0);
                $targetDayOfWeek = $this->day_of_week; // 1 = Lunes, 7 = Domingo
                
                // Ajustar al día de la semana correcto
                while ($next->dayOfWeekIso != $targetDayOfWeek) {
                    $next->addDay();
                }
                
                // Si ya pasó, programar para la próxima semana
                if ($next->isPast()) {
                    $next->addWeek();
                }
                break;

            case 'monthly':
                $next = $now->copy()->setTime($time->hour, $time->minute, 0);
                $targetDay = $this->day_of_month;
                
                // Ajustar al día del mes correcto
                $next->day = min($targetDay, $next->daysInMonth);
                
                // Si ya pasó este mes, programar para el próximo mes
                if ($next->isPast()) {
                    $next->addMonth();
                    $next->day = min($targetDay, $next->daysInMonth);
                }
                break;

            default:
                $next = $now->copy()->addDay();
                break;
        }

        $this->update(['next_run_at' => $next]);

        return $next;
    }

    /**
     * Marcar como ejecutada
     */
    public function markAsExecuted()
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
        $this->calculateNextRun();
    }

    /**
     * Activar/Desactivar regla
     */
    public function toggle()
    {
        $this->update(['is_enabled' => !$this->is_enabled]);
        
        if ($this->is_enabled) {
            $this->calculateNextRun();
        }
    }

    /**
     * Obtener descripción legible de la frecuencia
     */
    public function getFrequencyDescriptionAttribute()
    {
        $time = Carbon::parse($this->execution_time)->format('H:i');

        switch ($this->frequency) {
            case 'daily':
                return "Diario a las {$time}";

            case 'weekly':
                $days = [
                    1 => 'Lunes', 
                    2 => 'Martes', 
                    3 => 'Miércoles', 
                    4 => 'Jueves', 
                    5 => 'Viernes', 
                    6 => 'Sábado', 
                    7 => 'Domingo'
                ];
                $dayName = $days[$this->day_of_week] ?? 'Desconocido';
                return "Semanal: Cada {$dayName} a las {$time}";

            case 'monthly':
                return "Mensual: Día {$this->day_of_month} a las {$time}";

            default:
                return "Frecuencia desconocida";
        }
    }

    /**
     * Obtener próxima ejecución formateada
     */
    public function getNextRunFormattedAttribute()
    {
        if (!$this->next_run_at) {
            return 'No programada';
        }

        return $this->next_run_at->diffForHumans();
    }

    /**
     * Verificar si debe ejecutarse ahora
     */
    public function shouldRunNow()
    {
        return $this->is_enabled && 
               $this->next_run_at && 
               $this->next_run_at->isPast();
    }

    /**
     * Boot method para calcular next_run_at automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rule) {
            if ($rule->is_enabled && !$rule->next_run_at) {
                $rule->calculateNextRun();
            }
        });
    }
}