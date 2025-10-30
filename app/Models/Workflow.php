<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'class_name',
        'description',
        'icon',
        'color',
        'is_active',
        'supports_partial',
        'supports_date_filter',
        'configuration',
        'execution_count',
        'last_executed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'supports_partial' => 'boolean',
        'supports_date_filter' => 'boolean',
        'configuration' => 'array',
        'last_executed_at' => 'datetime',
    ];

    /**
     * Relación: Un workflow tiene muchas ejecuciones
     */
    public function executions()
    {
        return $this->hasMany(Execution::class);
    }

    /**
     * Relación: Un workflow tiene muchas reglas de programación
     */
    public function scheduleRules()
    {
        return $this->hasMany(ScheduleRule::class);
    }

    /**
     * Scope: Solo workflows activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Workflows que soportan ejecución parcial
     */
    public function scopeSupportsPartial($query)
    {
        return $query->where('supports_partial', true);
    }

    /**
     * Obtener las últimas ejecuciones exitosas
     */
    public function recentSuccessfulExecutions($limit = 5)
    {
        return $this->executions()
            ->whereIn('status', ['completed_success', 'completed_success_no_ftp'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Incrementar contador de ejecuciones
     */
    public function incrementExecutionCount()
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }

    /**
     * Obtener tasa de éxito (porcentaje)
     */
    public function getSuccessRateAttribute()
    {
        $total = $this->executions()->count();
        
        if ($total === 0) {
            return 0;
        }

        $successful = $this->executions()
            ->whereIn('status', ['completed_success', 'completed_success_no_ftp'])
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}