<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutionLog extends Model
{
    use HasFactory;

    // Esta tabla no usa timestamps (solo logged_at)
    public $timestamps = false;

    protected $fillable = [
        'execution_id',
        'level',
        'message',
        'context',
        'sku',
        'current_page',
        'total_pages',
        'progress_percentage',
        'logged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Relación: Un log pertenece a una ejecución
     */
    public function execution()
    {
        return $this->belongsTo(Execution::class);
    }

    /**
     * Scope: Filtrar por nivel de log
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', strtoupper($level));
    }

    /**
     * Scope: Solo errores
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['ERROR', 'CRITICAL']);
    }

    /**
     * Scope: Solo información
     */
    public function scopeInfo($query)
    {
        return $query->where('level', 'INFO');
    }

    /**
     * Scope: Solo éxitos
     */
    public function scopeSuccess($query)
    {
        return $query->where('level', 'SUCCESS');
    }

    /**
     * Scope: Solo advertencias
     */
    public function scopeWarnings($query)
    {
        return $query->where('level', 'WARNING');
    }

    /**
     * Obtener color del badge según el nivel
     */
    public function getBadgeColorAttribute()
    {
        return match($this->level) {
            'DEBUG' => 'gray',
            'INFO' => 'blue',
            'SUCCESS' => 'green',
            'WARNING' => 'yellow',
            'ERROR' => 'red',
            'CRITICAL' => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtener icono según el nivel
     */
    public function getIconAttribute()
    {
        return match($this->level) {
            'DEBUG' => 'bug',
            'INFO' => 'information-circle',
            'SUCCESS' => 'check-circle',
            'WARNING' => 'exclamation-triangle',
            'ERROR' => 'x-circle',
            'CRITICAL' => 'shield-exclamation',
            default => 'information-circle',
        };
    }

    /**
     * Formatear el mensaje para mostrar en UI
     */
    public function getFormattedMessageAttribute()
    {
        $message = $this->message;

        // Si tiene SKU, agregarlo al inicio
        if ($this->sku) {
            $message = "[SKU: {$this->sku}] {$message}";
        }

        // Si tiene progreso de página, agregarlo
        if ($this->current_page && $this->total_pages) {
            $message .= " (Página {$this->current_page}/{$this->total_pages})";
        }

        // Si tiene porcentaje de progreso, agregarlo
        if ($this->progress_percentage !== null) {
            $message .= " - {$this->progress_percentage}%";
        }

        return $message;
    }

    /**
     * Obtener timestamp formateado
     */
    public function getFormattedTimeAttribute()
    {
        return $this->logged_at->format('H:i:s');
    }
}